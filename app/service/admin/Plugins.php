<?php

namespace app\service\admin;

use app\model\admin\Menu;
use laytp\library\DirFile;
use laytp\library\Http;
use laytp\traits\Error;
use app\model\Migrations;
use think\facade\Config;
use think\facade\Filesystem;

/**
 * 插件市场服务实现者
 * Class Auth
 * @package app\service\admin
 */
class Plugins
{
    use Error;

    protected $ids=[];

    // 离线安装
    public function offLineInstall()
    {
        if (!class_exists('ZipArchive')) {
            $this->setError('PHP扩展ZipArchive没有正确安装');
            return false;
        }

        $file = request()->file('laytpUploadFile'); // 获取上传的文件
        if (!$file) {
            $this->setError('上传失败，请选择需要上传的文件');
            return false;
        }

        $fileExt = strtolower($file->getOriginalExtension());
        if($fileExt != 'zip'){
            $this->setError('仅允许上传zip文件');
            return false;
        }

        $fileName = $file->getOriginalName();
        $pathinfo = pathinfo($fileName);
        $plugin = $pathinfo['filename'];
        try{
            // 将文件上传到指定目录
            Filesystem::disk('local')->putFileAs('plugins', $file, $fileName );
            $this->insideInstall($plugin);
        }catch (\Exception $e){
            $this->setError($e->getMessage());
            return false;
        }

        return true;
    }

    // 安装
    public function install($plugin, $laytpGwToken)
    {
        if (!class_exists('ZipArchive')) {
            $this->setError('PHP扩展ZipArchive没有正确安装');
            return false;
        }

        $download = $this->download($plugin, $laytpGwToken);

        if(!$download){
            return false;
        }

        $install = $this->insideInstall($plugin);
        if(!$install){
            return false;
        }

        return true;
    }

    // 卸载
    public function unInstall($plugin)
    {
        $pluginDir = $this->getPluginPath($plugin) . DS;
        // 删除数据库文件
        $migrationsFile = DirFile::recurDir($pluginDir . 'database' . DS . 'migrations');
        if($migrationsFile){
            foreach($migrationsFile as $file){
                $baseNameArr = explode('_', $file['baseName']);
                $baseNameArr = explode('.', $baseNameArr[1]);
                $migration = Migrations::where('migration_name', '=', ucfirst($baseNameArr[0]))->find();
                if($migration) $migration->delete();
                @unlink(root_path() . 'database' . DS . 'migrations' . DS . $file['baseName']);
            }
        }
        // 删除菜单
        $info = $this->getPluginInfo($plugin);
        $menuIds = $info['menu_ids'];
        if($menuIds){
            Menu::destroy(function($query) use ($menuIds){
                $query->where('id', 'in', explode(',', $menuIds));
            });
        }
        // 删除public目录下的文件
        $this->removePublicFile($plugin);
        // 删除插件目录
        DirFile::rmDirs($pluginDir);
        // 修改系统插件配置文件config/plugin.php
        $this->unInstallPluginConf($plugin);
        return true;
    }

    // 获取上传zip文件所在目录
    public function getPluginRuntimeDir()
    {
        $dir = runtime_path() . 'storage' . DS . 'plugins';
        DirFile::createDir($dir);
        return $dir;
    }

    // 下载zip文件到本地
    public function download($plugin, $laytpGwToken)
    {
        $res = Http::post(Config::get('plugin.apiUrl') . "/plugins/install", ['plugin'=>$plugin], array(
            CURLOPT_HTTPHEADER => array(
                "token: ".$laytpGwToken
            ),
        ));
        $resArr = json_decode($res, true);
        if($resArr['code'] === 1){
            $this->setError(1);
            return false;
        }else if($resArr['code'] === 2){
            $this->setError(2);
            return false;
        }
        $url = $resArr['data']['url'];
        $zipSteam = Http::get($url, [], [
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER     => [
                'X-REQUESTED-WITH: XMLHttpRequest'
            ]
        ]);

        $file = $this->getPluginRuntimeDir() . DS . $plugin . '.zip';
        if(file_exists($file)){
            @unlink($file);
        }
        if ($write = fopen($file, 'w')) {
            fwrite($write, $zipSteam);
            fclose($write);
        }
        return true;
    }

    // 内部安装过程
    public function insideInstall($plugin)
    {
        try{
            // 解压zip文件
            $this->unzip($plugin);

            // 复制database文件并执行php think migrate:run命令
            $this->migrate($plugin);

            // 复制静态文件
            $this->copyPublicFile($plugin);

            // 生成菜单，同时将新增的菜单id写入info.ini配置文件中，便于卸载时同时删除菜单
            $this->createMenu($plugin);

            // 修改系统插件配置文件config/plugin.php
            $this->installPluginConf($plugin);
            return true;
        }catch (\Exception $e){
            $this->setError($e->getMessage(). $e->getLine() . $e->getFile());
            return false;
        }
    }

    /**
     * 解压插件zip文件
     * @param $plugin
     * @return bool
     * @throws \Exception
     */
    public function unzip($plugin)
    {
        $file = $this->getPluginRuntimeDir() . DS . $plugin . '.zip';
        $zip = new \ZipArchive;
        if ($zip->open($file) !== TRUE) {
            @unlink($file);
            throw new \Exception('不能打开zip文件');
        }
        if(strtolower(trim($zip->getNameIndex(0),'/')) == strtolower($plugin)){
            $dir = root_path() . 'plugin' . DS;
        }else{
            $dir = root_path() . 'plugin' . DS . $plugin . DS;
        }
        if (!$zip->extractTo($dir)) {
            $zip->close();
            @unlink($file);
            throw new \Exception('不能提取zip文件');
        }
        $zip->close();
        @unlink($file);
        return true;
    }

    // 插件代码文件解压后，执行php run:migrate命令，安装数据库文件
    public function migrate($plugin)
    {
        $pluginDir = $this->getPluginPath($plugin) . DS;
        if(is_dir($pluginDir . 'database' . DS . 'migrations')){
            DirFile::copyDirs($pluginDir . 'database' . DS . 'migrations', root_path() . 'database' . DS . 'migrations');
            // 删除数据库migrations表，已经安装过的版本
            $list = scandir($pluginDir . 'database' . DS . 'migrations');
            $migrationNameArr = [];
            foreach ($list as $value) {
                $pathinfo = pathinfo($value);
                if ($pathinfo['extension'] == 'php') {
                    $tempArr = explode('_', $pathinfo['filename']);
                    $migrationNameArr[] = ucfirst($tempArr[1]);
                }
            }
            Migrations::where('migration_name', 'in', $migrationNameArr)->delete();
            sleep(1);
            exec('php ' . app()->getRootPath() . '\think migrate:run');
        }
        return true;
    }

    // 获取插件路径
    public function getPluginPath($plugin)
    {
        return app()->getRootPath() . DS . 'plugin' . DS . $plugin;
    }

    // 获取插件信息
    public function getPluginInfo($plugin)
    {
        $pluginPath = $this->getPluginPath($plugin);
        $infoFile = $pluginPath . DS . 'info.ini';
        $info = [];
        if (is_file($infoFile)) {
            $info = parse_ini_file($infoFile, true, INI_SCANNER_TYPED) ?: [];
        }
        return $info;
    }

    /**
     * 设置插件配置信息
     * @param $plugin
     * @param $array
     * @return bool
     */
    public function setPluginInfo($plugin, $array)
    {
        $pluginPath = $this->getPluginPath($plugin);
        $file = $pluginPath . DS . 'info.ini';
        if (!isset($array['name'])) {
            $this->setError("插件配置写入失败");
            return false;
        }
        $res = array();
        foreach ($array as $key => $val) {
            if (is_array($val)) {
                $res[] = "[$key]";
                foreach ($val as $sKey => $sVal)
                    $res[] = "$sKey = " . (is_numeric($sVal) ? $sVal : $sVal);
            } else
                $res[] = "$key = " . (is_numeric($val) ? $val : $val);
        }
        if ($handle = fopen($file, 'w')) {
            fwrite($handle, implode("\n", $res) . "\n");
            fclose($handle);
        } else {
            $this->setError("文件没有写入权限");
            return false;
        }
        return true;
    }

    // 生成插件菜单
    public function createMenu($plugin)
    {
        $menuFile = root_path() . 'plugin' . DS . $plugin . DS . 'menu.php';
        if(!is_file($menuFile)){
            return true;
        }
        $menus = include_once $menuFile;
        $info = $this->getPluginInfo($plugin);
        if(isset($info['parent_menu']) && $info['parent_menu'] === 'first'){
            $firstMenuId = Menu::where(['pid' => 0, 'is_show' => 1])->order(['sort'=>'desc', 'id'=>'asc'])->value('id');
            $ids = $this->createMenuIds($menus, $firstMenuId);
        }else{
            $ids = $this->createMenuIds($menus, 0);
        }
        $info['menu_ids'] = implode(',', $ids);
        if(!$this->setPluginInfo($plugin, $info)) return false;
        return true;
    }

    public function createMenuIds($menus, $pid=0)
    {
        foreach($menus as $menu){
            $id = Menu::insertGetId([
                'name' => $menu['name'],
                'des' => isset($menu['des']) ? $menu['des'] : '',
                'href' => isset($menu['href']) ? $menu['href'] : '',
                'rule' => isset($menu['rule']) ? $menu['rule'] : '',
                'is_menu' => $menu['is_menu'],
                'pid' => $pid,
                'icon' => isset($menu['icon']) ? $menu['icon'] : ''
            ]);
            $this->ids[] = $id;

            if(isset($menu['children'])){
                self::createMenuIds($menu['children'],$id);
            }
        }
        return $this->ids;
    }

    // 复制静态文件，包括html css js
    public function copyPublicFile($plugin)
    {
        $pluginDir = $this->getPluginPath($plugin) . DS;
        if(is_dir($pluginDir . 'public')){
            DirFile::copyDirs($pluginDir . 'public', root_path() . 'public');
        }
        return true;
    }

    // 删除静态文件，包括html css js
    public function removePublicFile($plugin)
    {
        $pluginDir = $this->getPluginPath($plugin) . DS;
        $pluginHtmlDir = $pluginDir . 'public' . DS . 'admin' . DS . 'plugin' . DS . $plugin;
        $publicHtmlDir = root_path() . 'public' . DS . 'admin' . DS . 'plugin' . DS . $plugin;
        if(is_dir($pluginHtmlDir) && $publicHtmlDir){
            DirFile::rmDirs($publicHtmlDir);
        }
        $pluginStaticDir = $pluginDir . 'public' . DS . 'static' . DS . 'plugin' . DS . $plugin;
        $publicStaticDir = root_path() . 'public' . DS . 'static' . DS . 'plugin' . DS . $plugin;
        if(is_dir($pluginStaticDir) && $publicStaticDir){
            DirFile::rmDirs($publicStaticDir);
        }
        return true;
    }

    // 重新生成config/plugin.php文件
    public function installPluginConf($plugin)
    {
        $pluginConf = Config::get('plugin');
        $pluginConf['installed'][] = $plugin;
        $pluginConf['installed'] = array_unique($pluginConf['installed']);
        sort($pluginConf['installed']);
        $fileName = root_path() .  DS . 'config' . DS . 'plugin.php';
        file_put_contents($fileName,"<?php\nreturn " . var_export($pluginConf,true) . ';');
        return true;
    }

    // 重新生成config/plugin.php文件
    public function unInstallPluginConf($plugin)
    {
        $pluginConf = Config::get('plugin');
        foreach($pluginConf['installed'] as $k=>$installed){
            if($installed === $plugin) unset($pluginConf['installed'][$k]);
        }
        $pluginConf['installed'] = array_unique($pluginConf['installed']);
        sort($pluginConf['installed']);
        $fileName = root_path() .  DS . 'config' . DS . 'plugin.php';
        file_put_contents($fileName,"<?php\nreturn " . var_export($pluginConf,true) . ';');
        return true;
    }
}