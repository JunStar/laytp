<?php
namespace library;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use service\Menu;
use think\Db;
use think\Exception;
use think\facade\Config;
use think\facade\Env;
use think\Loader;

/**
 * 插件服务
 * @package think\addons
 */
abstract class Addons
{
    /**
     * 远程下载插件
     *
     * @param   string $name 插件名称
     * @param   array $extend 扩展参数
     * @return  string
     * @throws  AddonException
     * @throws  Exception
     */
    public static function download($name, $extend = [])
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
        $ret = Http::sendRequest(self::getServerUrl() . '/addons/download', array_merge(['name' => $name], $extend), 'POST', $options);
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
                            return parent::error($json['msg']);
                        }
                    } else {
                        if ($write = fopen($tmpFile, 'w')) {
                            fwrite($write, base64_decode($json['data']));
                            fclose($write);
                            return parent::success($json['msg'],$tmpFile);
                        }
                    }
                }else{
                    return parent::error($json['msg']);
                }
            }
            return parent::error('没有权限写入临时文件');
        }
        return parent::error('无法下载远程文件');
    }

    /**
     * 远程安装插件
     *
     * @param   string $name 插件名称
     * @param   boolean $force 是否覆盖
     * @param   array $extend 扩展参数
     * @return  boolean
     * @throws  Exception
     * @throws  AddonException
     */
    public static function install($name, $force = true, $extend = [])
    {
        $addons_path = Env::get('root_path') . DS . 'addons' . DS;
        if (!$name || (is_dir($addons_path . $name) && !$force)) {
            return parent::error('插件已经存在');
        }

        // 远程下载插件
        $tmpFile = Addons::download($name, $extend);
        if(!$tmpFile['code']){
            return parent::error('插件下载失败');
        }

        // 解压插件
        $addonDir = Addons::unzip($name);

        // 移除临时文件
        @unlink($tmpFile['data']);

        $checkRes = Addons::check($name);
        if(!$checkRes['code']){
            @DirFile::rmDirs($addonDir);
            return parent::error($checkRes['msg']);
        }

        if (!$force) {
            if( !Services::noconflict($name) ){
                return parent::error('发现冲突文件');
            }
        }

        $addonDir = Env::get('root_path') . 'addons' . DS . $name . DS;

        // 复制文件
        foreach (self::getCheckDirs() as $k => $dir) {
            if (is_dir($addonDir . $dir)) {
                DirFile::copyDirs($addonDir . $dir, Env::get('root_path') . $dir);
            }
        }

        try {
            // 默认启用该插件
            $info = self::getAddonInfo($name);
            if (!$info['state']) {
                $info['state'] = 1;
                self::setAddonInfo($name, $info);
            }

            // 执行安装脚本
            $class = self::getAddonClass($name);
            if ($class) {
                $class->install();
                return parent::success("安装成功");
            }else{
                return parent::error("{$name}类不存在");
            }
        } catch (Exception $e) {
            return parent::error($e->getMessage());
        }

        // 导入sql文件
        Services::importSql($name);

        return parent::success('成功');
    }

    //卸载插件
    public static function uninstall($name)
    {
        $addon = self::getAddonClass($name);
        if (is_object($addon)) {
            $addon->uninstall();
            //删除掉插件文件
            DirFile::rmDirs(Env::get('root_path') . 'addons' . DS . $name);
            return parent::success("卸载成功");
        }else{
            return parent::error("{$name}类不存在");
        }
    }

    /**
     * 获取远程服务器
     * @return  string
     */
    protected static function getServerUrl()
    {
        return Config::get('addons.api_url');
    }

    /**
     * 是否有冲突
     *
     * @param   string $name 插件名称
     * @return  boolean
     * @throws  AddonException
     */
    public static function noConflict($name)
    {
        // 检测冲突文件
        $list = self::getGlobalFiles($name, true);
        if ($list) {
            //发现冲突文件，抛出异常
            throw new Exception('发现冲突文件，请覆盖安装');
        }
        return true;
    }

    /**
     * 获取插件在全局的文件
     *
     * @param   string $name 插件名称
     * @return  array 是否检测冲突
     */
    public static function getGlobalFiles($name, $onlyconflict = false)
    {
        $list = [];
        $addonDir = Addons::getAddonsPath($name);
        // 扫描插件目录是否有覆盖的文件
        foreach (self::getCheckDirs() as $k => $dir) {
            $checkDir = Env::get('root_path') . DS . $dir . DS;
            if (!is_dir($checkDir))
                continue;
            //检测到存在插件外目录
            if (is_dir($addonDir . $dir)) {
                //匹配出所有的文件
                $files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($addonDir . $dir, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST
                );

                foreach ($files as $fileinfo) {
                    if ($fileinfo->isFile()) {
                        $filePath = $fileinfo->getPathName();
                        $path = str_replace($addonDir, '', $filePath);
                        if ($onlyconflict) {
                            $destPath = Env::get('root_path') . $path;
                            if (is_file($destPath)) {
                                if (filesize($filePath) != filesize($destPath) || md5_file($filePath) != md5_file($destPath)) {
                                    $list[] = $path;
                                }
                            }
                        } else {
                            $list[] = $path;
                        }
                    }
                }
            }
        }
        return $list;
    }

    /**
     * 解压插件
     *
     * @param   string $name 插件名称
     * @return  string
     * @throws  Exception
     */
    public static function unzip($name)
    {
        $file = Env::get('root_path') . 'addons' . DS . $name . '.zip';
        $dir = Env::get('root_path') . 'addons' . DS . $name . DS;
        if (class_exists('ZipArchive')) {
            $zip = new \ZipArchive;
            if ($zip->open($file) !== TRUE) {
                throw new Exception('不能打开zip文件');
            }
            if (!$zip->extractTo($dir)) {
                $zip->close();
                throw new Exception('不能提取zip文件');
            }
            $zip->close();
            return $dir;
        }
        throw new Exception('无法执行解压操作，请确保ZipArchive安装正确');
    }

    /**
     * 离线安装插件
     * @param $name
     * @return mixed
     * @throws Exception
     */
    public static function off_line_install($name){
        $addons_path = Env::get('root_path') . DS . 'addons' . DS;
        if (!$name || (is_dir($addons_path . $name))) {
            // 移除插件的ZIP包
            @unlink($addons_path . $name . '.zip');
            throw new Exception('插件已经存在');
        }
        try{
            // 解压插件
            Addons::unzip($name);

            //检测插件基础信息是否完整
            Addons::check($name);

            //执行安装过程
            Addons::sys_install($name);

            // 移除插件的ZIP包
            @unlink($addons_path . $name . '.zip');
        }catch (Exception $e){
            // 移除插件的ZIP包
            @unlink($addons_path . $name . '.zip');
            //删除下载的插件
            DirFile::rmDirs($addons_path);
            throw new Exception($e->getMessage());
        }
    }

    //安装过程
    public static function sys_install($name, $force = true){
        //生成菜单
//        Addons::createMenu($name);
        //导入sql
//        Addons::importSql($name);
        //修改插件状态
        Addons::editState($name);
        //如果不是覆盖安装需要检测冲突
        if(!$force)
            Addons::noConflict($name);
        //移动和复制application和public两个文件夹
        Addons::copyDir($name);
    }

    //生成菜单
    public static function createMenu($name){
        $addons_path = Addons::getAddonsPath($name);
        if(is_file($addons_path . 'menu.php')){
            $menus = Config::load($addons_path . 'menu.php','laytp_addons_menu_' . $name);
            if(!$menus){
                return true;
            }
            $menu_service = new Menu();
            $menu_service->create($menus);
        }
        return true;
    }

    //导入sql
    public static function importSql($name){
        $addons_path = Addons::getAddonsPath($name);
        $sqlFile = $addons_path . DS . 'install.sql';
        if (is_file($sqlFile)) {
            $lines = file($sqlFile);
            $templine = '';
            foreach ($lines as $line) {
                if (substr($line, 0, 2) == '--' || $line == '' || substr($line, 0, 2) == '/*')
                    continue;

                $templine .= $line;
                if (substr(trim($line), -1, 1) == ';') {
                    $templine = str_ireplace('__PREFIX__', config('database.prefix'), $templine);
                    $templine = str_ireplace('INSERT INTO ', 'INSERT IGNORE INTO ', $templine);
                    Db::execute($templine);
                    $templine = '';
                }
            }
        }
        return true;
    }

    //修改插件状态，默认设置为打开
    public static function editState($name, $state = 1){
        $info = Addons::getInfo($name);
        $info['state'] = $state;
        Addons::setInfo($name, $info);
    }

    //移动和复制application和public两个文件夹
    public static function copyDir($name){
        $addonDir = Addons::getAddonsPath($name);
        foreach (self::getCheckDirs() as $k => $dir) {
            if (is_dir($addonDir . $dir)) {
                DirFile::copyDirs($addonDir . $dir, Env::get('root_path') . $dir);
            }
        }
        return true;
    }

    /**
     * 设置基础配置信息
     * @param string $name  插件名
     * @param array  $array 配置数据
     * @return boolean
     * @throws Exception
     */
    public static function setInfo($name, $array)
    {
        $addons_path = Addons::getAddonsPath($name);
        $file = $addons_path . 'info.ini';
        if (!isset($array['name']) || !isset($array['title']) || !isset($array['version'])) {
            throw new Exception("插件配置写入失败");
        }
        $res = array();
        foreach ($array as $key => $val) {
            if (is_array($val)) {
                $res[] = "[$key]";
                foreach ($val as $skey => $sval)
                    $res[] = "$skey = " . (is_numeric($sval) ? $sval : $sval);
            } else
                $res[] = "$key = " . (is_numeric($val) ? $val : $val);
        }
        if ($handle = fopen($file, 'w')) {
            fwrite($handle, implode("\n", $res) . "\n");
            fclose($handle);
            //清空当前配置缓存
            Config::set($name, NULL, 'laytp_addons_'.$name);
        } else {
            throw new Exception("文件没有写入权限");
        }
        return true;
    }

    /**
     * 检测插件是否完整
     *
     * @param   string $name 插件名称
     * @return  boolean
     * @throws  Exception
     */
    public static function check($name)
    {
        $addons_path = Addons::getAddonsPath($name);
        if (!$name || !is_dir($addons_path) ) {
            throw new Exception("插件不存在");
        }

        Addons::checkInfo($name);
    }

    public static function getInfo($name){
        $addons_path = Addons::getAddonsPath($name);
        $info_file = $addons_path . 'info.ini';
        $info = [];
        if (is_file($info_file)) {
            $info = Config::parse($info_file,'','laytp_addons_'.$name);
        }
        return $info;
    }

    public static function checkInfo($name){
        $info = Addons::getInfo($name);
        $info_check_keys = ['name', 'title', 'intro', 'author', 'version', 'state'];
        $addons_path = Addons::getAddonsPath($name);
        foreach ($info_check_keys as $value) {
            if (!array_key_exists($value, $info)) {
                DirFile::rmDirs($addons_path);
                throw new Exception('插件基础信息不完整');
            }
        }
        return true;
    }

    public static function getAddonsPath($name){
        return Env::get('root_path') . 'addons' . DS . $name . DS;
    }

    /**
     * 获取检测的全局文件夹目录
     * @return  array
     */
    protected static function getCheckDirs()
    {
        return [
            'application',
            'public'
        ];
    }
}