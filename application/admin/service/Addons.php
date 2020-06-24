<?php
namespace app\admin\service;

use app\admin\service\addons\Info;
use app\admin\service\addons\Menu;
use library\DirFile;
use library\Http;
use PDO;
use service\Service;
use think\Exception;
use think\facade\Config;
use think\facade\Env;

/**
 * 插件服务
 */
class Addons extends Service
{
    public $_info;
    public $_menu;
    public $_config;

    public function __construct()
    {
        $this->_info = new Info();
        $this->_menu = new Menu();
        $this->_config = new \app\admin\service\addons\Config();
    }

    /**
     * 远程下载插件
     *
     * @param   string $name 插件名称
     * @param   array $extend 扩展参数
     * @return  string
     * @throws  AddonException
     * @throws  Exception
     */
    public function download($name, $extend = [])
    {
        $addonTmpDir = Env::get('runtime_path') . 'addons' . DS;
        if (!is_dir($addonTmpDir)) {
            @mkdir($addonTmpDir, 0755, true);
        }
        $tmpFile = $addonTmpDir . $name . ".zip";
        $options = [
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER     => [
                'X-REQUESTED-WITH: XMLHttpRequest'
            ]
        ];
        $ret = Http::sendRequest(self::getServerUrl() . '/api/addons/download', array_merge(['name' => $name], $extend), 'POST', $options);
        if ($ret['ret']) {
            if (substr($ret['msg'], 0, 1) == '{') {
                $json = (array)json_decode($ret['msg'], true);
                if($json['code']){
                    //如果传回的是一个下载链接,则再次下载
                    if ($json['data'] && isset($json['data']['url'])) {
                        array_pop($options);
                        $ret = Http::sendRequest($json['data']['url'], [], 'GET', $options);
                        if (!$ret['msg']) {
                            //下载返回错误，抛出异常
                            $this->setError($json['msg']);
                            return false;
                        }
                        if(file_exists($tmpFile)){
                            @unlink($tmpFile);
                        }
                        if ($write = fopen($tmpFile, 'w')) {
                            fwrite($write, $ret['msg']);
                            fclose($write);
                            return $tmpFile;
                        }
                    }
                    if(file_exists($tmpFile)){
                        @unlink($tmpFile);
                    }
                    if ($write = fopen($tmpFile, 'w')) {
                        fwrite($write, base64_decode($json['data']));
                        fclose($write);
                        return $tmpFile;
                    }
                }else{
                    $this->setError($json['msg']);
                    return false;
                }
            }
            $this->setError('没有权限写入临时文件');
            return false;
        }
        $this->setError('无法下载插件');
        return false;
    }

    //离线安装插件
    public function off_line_install($name){
        $addons_path = Env::get('root_path') . DS . 'addons' . DS;

        if (!$name || (is_dir($addons_path . $name))) {
            $this->setError($name.'插件已经存在');
            return false;
        }

        // 解压插件
        $addonDir = $this->unzip($name);

        //检测下载下来的插件包是否完整
        $checkRes = $this->_info->check($name);
        if(!$checkRes){
            @DirFile::rmDirs($addonDir);
            $this->setError($checkRes['msg']);
            return false;
        }

        //初始化配置
        $config = include_once $addons_path. $name . DS . 'config.php';
        $default_config = [];
        foreach($config as $k=>$v){
            if(isset($v['content'])){
                $default_config[$v['key']] = $v['content'];
            }
        }
        $addons = Config::get('addons.');
        $addons[$name] = $default_config;
        $file_name = Env::get('root_path') .  DS . 'config' . DS . 'addons.php';
        file_put_contents($file_name,"<?php\nreturn ".var_export($addons,true).';');

        //生成menu
        $menus = include_once $addonDir.'menu.php';
        $menu_ids = $this->_menu->create($menus);

        $info = $this->_info->getAddonInfo($name);
        $info['state'] = 1;
        $info['menu_ids'] = implode(',',$menu_ids);
        $this->_info->setAddonInfo($name,$info);

        // 导入sql文件
        $this->importSql($name);

        //复制静态文件
        $source_static_dir = $addonDir . DS . 'static';
        if(is_dir($source_static_dir)){
            $dest_static_dir = Env::get('root_path') . 'public' . DS . 'addons' . DS . $name . DS . 'static';
            if(!is_dir($dest_static_dir)){
                DirFile::createDir($dest_static_dir);
            }
            DirFile::copyDirs($source_static_dir,$dest_static_dir);
        }

        return true;
    }

    /**
     * 安装插件
     *
     * @param   string $name 插件名称
     * @param   boolean $force 是否覆盖
     * @param   array $extend 扩展参数
     * @return  boolean
     * @throws  Exception
     * @throws  AddonException
     */
    public function install($name, $force = true, $extend = [])
    {
        $addons_path = Env::get('root_path') . DS . 'addons' . DS;
        if(!$addons_path){
            DirFile::createDir($addons_path);
        }
        if (!$name || (is_dir($addons_path . $name) && !$force)) {
            $this->setError('插件已经存在');
            return false;
        }

        // 远程下载插件
        $tmpFile = $this->download($name, $extend);
        if(!$tmpFile){
            $this->setError($this->getError());
            return false;
        }

        // 解压插件
        $addonDir = $this->unzip($name);

        // 移除临时文件
        @unlink($tmpFile);

        //检测下载下来的插件包是否完整
        $checkRes = $this->_info->check($name);
        if(!$checkRes){
            @DirFile::rmDirs($addonDir);
            $this->setError($checkRes['msg']);
            return false;
        }

        //初始化配置
        $config = include_once $addons_path. $name . DS . 'config.php';
        $default_config = [];
        foreach($config as $k=>$v){
            if(isset($v['content'])){
                $default_config[$v['key']] = $v['content'];
            }
        }
        $addons = Config::get('addons.');
        $addons[$name] = $default_config;
        $file_name = Env::get('root_path') .  DS . 'config' . DS . 'addons.php';
        file_put_contents($file_name,"<?php\nreturn ".var_export($addons,true).';');

        //生成menu
        if(file_exists($addonDir.'menu.php')){
            $menus = include_once $addonDir.'menu.php';
            $menu_ids = $this->_menu->create($menus);
            $info = $this->_info->getAddonInfo($name);
            $info['state'] = 1;
            $info['menu_ids'] = implode(',',$menu_ids);
            $this->_info->setAddonInfo($name,$info);
        }

        // 导入sql文件
        $this->importSql($name);

        //复制静态文件
        $source_static_dir = $addonDir . DS . 'static';
        if(is_dir($source_static_dir)){
            $dest_static_dir = Env::get('root_path') . 'public' . DS . 'addons' . DS . $name . DS . 'static';
            if(!is_dir($dest_static_dir)){
                DirFile::createDir($dest_static_dir);
            }
            DirFile::copyDirs($source_static_dir,$dest_static_dir);
        }

        return true;
    }

    //卸载插件
    public function uninstall($name)
    {
        try{
            //删除菜单
            $info = $this->_info->getAddonInfo($name);
            if(isset($info['menu_ids'])){
                $menu_ids = $info['menu_ids'];
                $this->_menu->delete($menu_ids);
            }
            //删除插件目录
            DirFile::rmDirs(Env::get('root_path') . 'addons' . DS . $name);
            //删除配置项
            $addons = Config::get('addons.');
            if(isset($addons[$name])){
                unset($addons[$name]);
                $file_name = Env::get('root_path') .  DS . 'config' . DS . 'addons.php';
                file_put_contents($file_name,"<?php\nreturn ".var_export($addons,true).';');
            }
            //删除静态文件
            $api_file = Env::get('root_path') . DS . 'public' . DS . 'addons' . DS . $name;
            DirFile::rmDirs($api_file);
            return true;
        }catch (Exception $e){
            $this->setError($e->getMessage());
            return false;
        }
    }

    /**
     * 获取远程服务器
     * @return  string
     */
    protected function getServerUrl()
    {
        return Config::get('addons.api_url');
    }

    /**
     * 解压插件
     *
     * @param   string $name 插件名称
     * @return  string
     * @throws  Exception
     */
    public function unzip($name)
    {
        $file = Env::get('runtime_path') . 'addons' . DS . $name . '.zip';
        if (class_exists('ZipArchive')) {
            $zip = new \ZipArchive;
            if ($zip->open($file) !== TRUE) {
                $this->setError('不能打开zip文件');
                return false;
            }
            if(strtolower(trim($zip->getNameIndex(0),'/')) == strtolower($name)){
                $dir = Env::get('root_path') . 'addons' . DS;
                $return_dir = $dir . DS . $name . DS;
            }else{
                $dir = Env::get('root_path') . 'addons' . DS . $name . DS;
                $return_dir = $dir;
            }
            if (!$zip->extractTo($dir)) {
                $zip->close();
                $this->setError('不能提取zip文件');
                return false;
            }
            $zip->close();
            return $return_dir;
        }
        $this->setError('无法执行解压操作，请确保ZipArchive安装正确');
        return false;
    }

    /**
     * 导入sql文件
     * @param $name
     * @return bool
     */
    public function importSql($name){
        $addon_dir = $this->_info->getAddonPath($name);
        $sql_file = $addon_dir . 'install.sql';
        if(!file_exists($sql_file)){
            return true;
        }

        $database = Config::get('database.');
        $mysql_database = $database['database'];
        $mysql_hostname = $database['hostname'];
        $mysql_port = $database['hostport'];
        $mysql_username = $database['username'];
        $mysql_password = $database['password'];
        $mysql_prefix = $database['prefix'];
        $pdo = new PDO("mysql:host={$mysql_hostname};port={$mysql_port}", $mysql_username, $mysql_password, array(
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
        ));

        $sql = @file_get_contents($addon_dir . 'install.sql');
        $sql = str_replace("`{\$prefix}", "`{$mysql_prefix}", $sql);
        $pdo->query("USE `{$mysql_database}`");
        $pdo->exec($sql);
        return true;
    }
}