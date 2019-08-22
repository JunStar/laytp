<?php
namespace app\admin\services;

use library\DirFile;
use library\Http;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use services\Services;
use think\Exception;
use think\facade\Config;
use think\facade\Env;
use think\Loader;

/**
 * 插件服务
 * @package think\addons
 */
class Addons extends Services
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
     * 安装插件
     *
     * @param   string $name 插件名称
     * @param   boolean $force 是否覆盖
     * @param   array $extend 扩展参数
     * @return  boolean
     * @throws  Exception
     * @throws  AddonException
     */
    public static function install($name, $force = false, $extend = [])
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
//            @DirFile::rmDirs($addonDir);
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
            if (class_exists($class)) {
                $addon = new $class();
                $addon->install();
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
            return parent::error('发现冲突文件',['conflictlist' => $list]);
        }
        return true;
    }

    /**
     * 获取插件在全局的文件
     *
     * @param   string $name 插件名称
     * @return  array
     */
    public static function getGlobalFiles($name, $onlyconflict = false)
    {
        $list = [];
        $addonDir = ADDON_PATH . $name . DS;
        // 扫描插件目录是否有覆盖的文件
        foreach (self::getCheckDirs() as $k => $dir) {
            $checkDir = ROOT_PATH . DS . $dir . DS;
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
                            $destPath = ROOT_PATH . $path;
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
        $file = Env::get('runtime_path') . 'addons' . DS . $name . '.zip';
        $dir = Env::get('root_path') . 'addons' . DS . $name . DS;
        if (class_exists('ZipArchive')) {
            $zip = new \ZipArchive;
            if ($zip->open($file) !== TRUE) {
                return parent::error('不能打开zip文件');
            }
            if (!$zip->extractTo($dir)) {
                $zip->close();
                return parent::error('不能提取zip文件');
            }
            $zip->close();
            return $dir;
        }
        return parent::error('无法执行解压操作，请确保ZipArchive安装正确');
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
        if (!$name || !is_dir(Env::get('root_path') . 'addons' . DS . $name)) {
            return parent::error('插件不存在');
        }
        $addonClass = Addons::getAddonClass($name);
        if (!$addonClass) {
            return parent::error('插件主启动程序不存在');
        }
        include_once(Env::get('root_path') . 'addons' . DS . $name . DS . ucfirst($name) . '.php');
        $addon = new $addonClass();
        if (!$addon->checkInfo()) {
            return parent::error('配置文件不完整');
        }
        return parent::success('正常');
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

    /**
     * 读取插件的基础信息
     * @param string $name 插件名
     * @return array
     */
    public static function getAddonInfo($name)
    {
        $addon = self::getAddonInstance($name);
        if (!$addon) {
            return [];
        }
        return $addon->getInfo($name);
    }

    /**
     * 设置基础配置信息
     * @param string $name 插件名
     * @param array $array
     * @return boolean
     * @throws Exception
     */
    public static function setAddonInfo($name, $array)
    {
        $file = ADDON_PATH . $name . DIRECTORY_SEPARATOR . 'info.ini';
        $addon = self::getAddonInstance($name);
        $array = $addon->setInfo($name, $array);
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
            Config::set($name, NULL, 'addoninfo');
        } else {
            throw new Exception("文件没有写入权限");
        }
        return true;
    }

    /**
     * 获取插件的单例
     * @param $name
     * @return mixed|null
     */
    public static function getAddonInstance($name)
    {
        static $_addons = [];
        if (isset($_addons[$name])) {
            return $_addons[$name];
        }
        $class = self::getAddonClass($name);
        if (class_exists($class)) {
            $_addons[$name] = new $class();
            return $_addons[$name];
        } else {
            return null;
        }
    }

    /**
     * 获取插件类的类名
     * @param $name 插件名
     * @param string $type 返回命名空间类型
     * @param string $class 当前类名
     * @return string
     */
    public static function getAddonClass($name, $type = 'hook', $class = null)
    {
        $class_name = "addons\\" . ucfirst($name);
        return $class_name;
        $name = Loader::parseName($name);
        // 处理多级控制器情况
        if (!is_null($class) && strpos($class, '.')) {
            $class = explode('.', $class);

            $class[count($class) - 1] = Loader::parseName(end($class), 1);
            $class = implode('\\', $class);
        } else {
            $class = Loader::parseName(is_null($class) ? $name : $class, 1);
        }
        switch ($type) {
            case 'controller':
                $namespace = "addons\\" . $name . "\\controller\\" . $class;
                break;
            default:
                $namespace = "addons\\" . $name . "\\" . $class;
        }
        return class_exists($namespace) ? $namespace : '';
    }

    /**
     * 获取插件类的配置值值
     * @param string $name 插件名
     * @return array
     */
    public static function getAddonConfig($name)
    {
        $addon = self::getAddonInstance($name);
        if (!$addon) {
            return [];
        }
        return $addon->getConfig($name);
    }
}