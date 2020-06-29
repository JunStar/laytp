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

        $info = $this->_info->getAddonInfo($name);
        if(array_key_exists('lt_version', $info) && ($info['lt_version'] > LT_VERSION)){
            $this->setError('当前框架版本过低');
            DirFile::rmDirs($addons_path.$name);
            return false;
        }

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
        if(file_exists($addons_path. $name . DS . 'config.php') || (array_key_exists('is_editor',$info) && $info['is_editor'] == 1)){
            $default_config = [];
            if(file_exists($addons_path. $name . DS . 'config.php')){
                $config = include_once $addons_path. $name . DS . 'config.php';
                foreach($config as $k=>$v){
                    if(isset($v['content'])){
                        $default_config[$v['key']] = $v['content'];
                    }
                }
            }

            $addons = Config::get('addons.');
            $addons[$name] = $default_config;

            //如果是编辑器插件，需要修改addons.php的配置文件内容中的editor项，增加此编辑器的标识
            if(array_key_exists('is_editor',$info) && $info['is_editor'] == 1){
                $addons['editor'][] = $name;
            }

            $file_name = Env::get('root_path') .  DS . 'config' . DS . 'addons.php';
            file_put_contents($file_name,"<?php\nreturn ".var_export($addons,true).';');
        }

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
        $result[] = $this->importSql($name);

        //复制静态文件
        $result[] = $this->copyStatic($name);

        if(check_res($result)){
            return true;
        }else{
            $this->setError($this->getError());
            return false;
        }
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

            //删除配置项
            $addons = Config::get('addons.');
            if(isset($addons[$name])){
                unset($addons[$name]);
            }
            //如果是编辑器插件，需要修改addons.php的配置文件内容中的editor项，删除此编辑器的标识
            if(array_key_exists('is_editor',$info) && $info['is_editor'] == 1){
                foreach($addons['editor'] as $k=>$v){
                    if($v == $name){
                        unset($addons['editor'][$k]);
                    }
                }
                sort($addons['editor']);
            }
            $file_name = Env::get('root_path') .  DS . 'config' . DS . 'addons.php';
            file_put_contents($file_name,"<?php\nreturn ".var_export($addons,true).';');

            //删除静态文件
            if( !$this->rmStatic($name) ){
                $this->setError($this->getError());
                return false;
            }

            //删除插件目录
            DirFile::rmDirs(Env::get('root_path') . 'addons' . DS . $name);
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

    /**
     * 复制静态目录
     */
    public function copyStatic($name){
        $addon_dir = $this->_info->getAddonPath($name);
        $source_static_dir = $addon_dir . DS . 'static';
        if(is_dir($source_static_dir)){
            //对三个特殊文件进行复制操作
            $js_file = $source_static_dir . DS . 'js_file.html';
            //application/admin/view/public/layout/file/js_file.html
            $dest_js_file = Env::get('app_path').DS.'admin'.DS.'view'.DS.'public'.DS.'layout'.DS.'file'.DS.'js_file.html';
            if(file_exists($js_file)){
                if(!file_exists($dest_js_file)){
                    $this->setError('框架文件application/admin/view/public/layout/file/js_file.html不存在');
                    return false;
                }
                file_put_contents($dest_js_file,file_get_contents($js_file)."\r\n",FILE_APPEND);
            }

            $css_file = $source_static_dir . DS . 'css_file.html';
            //application/admin/view/public/layout/file/js_file.html
            $dest_css_file = Env::get('app_path').DS.'admin'.DS.'view'.DS.'public'.DS.'layout'.DS.'file'.DS.'css_file.html';
            if(file_exists($css_file)){
                if(!file_exists($dest_css_file)){
                    $this->setError('框架文件application/admin/view/public/layout/file/css_file.html不存在');
                    return false;
                }
                file_put_contents($dest_css_file,file_get_contents($css_file)."\r\n",FILE_APPEND);
            }

            $js_global_var = $source_static_dir . DS . 'js_global_var.html';
            //application/admin/view/public/layout/file/js_global_var.html
            $dest_js_global_var = Env::get('app_path').DS.'admin'.DS.'view'.DS.'public'.DS.'layout'.DS.'file'.DS.'css_file.html';
            if(file_exists($js_global_var)){
                if(!file_exists($dest_js_global_var)){
                    $this->setError('框架文件application/admin/view/public/layout/file/js_global_var.html不存在');
                    return false;
                }
                file_put_contents($dest_js_global_var,file_get_contents($js_global_var)."\r\n",FILE_APPEND);
            }

            //对library进行复制，将library下所有文件复制到public/static/library
            $source_library_dir = $source_static_dir . DS . 'library';
            if(is_dir($source_library_dir)){
                $dest_library_dir = Env::get('root_path') . 'public' . DS . 'static' . DS . 'library';
                if(!is_dir($dest_library_dir)){
                    $this->setError('框架目录public/static/library不存在');
                    return false;
                }
                DirFile::copyDirs($source_library_dir,$dest_library_dir);
            }

            //将static下除了三个特殊文件和library文件夹，其他的都复制到public/addons/$name/目录下
            if(is_dir($source_static_dir)){
                $dest_addons_dir = Env::get('root_path') . 'public' . DS . 'addons' . DS . $name;
                $dest_addons_files = scandir($source_static_dir);
                foreach($dest_addons_files as $fileName){
                    if(in_array($fileName, array('.', '..','library'))) {
                        continue;
                    }
                    if(is_dir($source_static_dir.DS.$fileName)){
                        if(!is_dir($dest_addons_dir.DS.$fileName)){
                            DirFile::createDir($dest_addons_dir.DS.$fileName);
                        }
                        DirFile::copyDirs($source_static_dir.DS.$fileName,$dest_addons_dir.DS.$fileName);
                    }
                }
            }
        }
        return true;
    }

    /**
     * 删除静态文件
     */
    public function rmStatic($name){
        $addon_dir = $this->_info->getAddonPath($name);
        $source_static_dir = $addon_dir . 'static';
        if(is_dir($source_static_dir)) {
            //对三个特殊文件进行删除操作
            $js_file = $source_static_dir . DS . 'js_file.html';
            //application/admin/view/public/layout/file/js_file.html
            $dest_js_file = Env::get('app_path') . DS . 'admin' . DS . 'view' . DS . 'public' . DS . 'layout' . DS . 'file' . DS . 'js_file.html';
            if (file_exists($js_file)) {
                if (!file_exists($dest_js_file)) {
                    $this->setError('框架文件application/admin/view/public/layout/file/js_file.html不存在');
                    return false;
                }
                $js_file_arr = file($js_file);
                foreach($js_file_arr as $k=>$v){
                    $js_file_arr[$k] = trim($v);
                }
                $dest_js_file_arr = file($dest_js_file);
                foreach($dest_js_file_arr as $k=>$v){
                    $dest_js_file_arr[$k] = trim($v);
                }
                $res_js_file_arr = array_diff($dest_js_file_arr, $js_file_arr);
                $res_js_file = implode("\n",$res_js_file_arr)."\n";
                file_put_contents($dest_js_file, $res_js_file);
            }

            $css_file = $source_static_dir . DS . 'css_file.html';
            //application/admin/view/public/layout/file/css_file.html
            $dest_css_file = Env::get('app_path') . DS . 'admin' . DS . 'view' . DS . 'public' . DS . 'layout' . DS . 'file' . DS . 'css_file.html';
            if (file_exists($css_file)) {
                if (!file_exists($dest_css_file)) {
                    $this->setError('框架文件application/admin/view/public/layout/file/css_file.html不存在');
                    return false;
                }
                $css_file_arr = file($css_file);
                foreach($css_file_arr as $k=>$v){
                    $css_file_arr[$k] = trim($v);
                }
                $dest_css_file_arr = file($dest_css_file);
                foreach($dest_css_file_arr as $k=>$v){
                    $dest_css_file_arr[$k] = trim($v);
                }
                $res_css_file_arr = array_diff($dest_css_file_arr, $css_file_arr);
                $res_css_file = implode("\n",$res_css_file_arr)."\n";
                file_put_contents($dest_css_file, $res_css_file);
            }

            $js_global_var = $source_static_dir . DS . 'js_global_var.html';
            //application/admin/view/public/layout/file/js_global_var.html
            $dest_js_global_var = Env::get('app_path') . DS . 'admin' . DS . 'view' . DS . 'public' . DS . 'layout' . DS . 'file' . DS . 'js_global_var.html';
            if (file_exists($js_global_var)) {
                if (!file_exists($dest_js_global_var)) {
                    $this->setError('框架文件application/admin/view/public/layout/file/js_global_var.html不存在');
                    return false;
                }
                $js_global_var_arr = file($css_file);
                foreach($js_global_var_arr as $k=>$v){
                    $js_global_var_arr[$k] = trim($v);
                }
                $dest_js_global_var_arr = file($dest_js_global_var);
                foreach($dest_js_global_var_arr as $k=>$v){
                    $dest_js_global_var_arr[$k] = trim($v);
                }
                $res_js_global_var_arr = array_diff($dest_js_global_var_arr, $js_global_var_arr);
                $res_js_global_var = implode("\n",$res_js_global_var_arr)."\n";
                file_put_contents($dest_js_global_var, $res_js_global_var);
            }

            //对library下layui/extends下文件进行删除操作
            $exntends_dir = $source_static_dir.DS.'library'.DS.'layui'.DS.'extends';
            if(is_dir($exntends_dir)){
                $extends_files = scandir($exntends_dir);
                foreach($extends_files as $fileName){
                    if(in_array($fileName, array('.', '..'))) {
                        continue;
                    }
                    $true_extends_file = Env::get('root_path').DS.'public'.DS.'static'.DS.'library'.DS.'layui'.DS.'extends'.DS.$fileName;
                    if(file_exists($true_extends_file)){
                        unlink($true_extends_file);
                    }
                }
            }

            //对library其他目录进行删除操作
            $library = $source_static_dir.DS.'library';
            if(is_dir($library)){
                $extends_files = scandir($library);
                foreach($extends_files as $fileName){
                    if(in_array($fileName, array('.', '..','layui'))) {
                        continue;
                    }
                    $true_library_dir = Env::get('root_path').DS.'public'.DS.'static'.DS.'library'.DS.$fileName;
                    if(is_dir($true_library_dir)){
                        DirFile::rmDirs($true_library_dir);
                    }
                }
            }

            //删除public/addons/$name下的文件
            $addons_public_dir = Env::get('root_path').DS.'public'.DS.'addons'.DS.$name;
            if(is_dir($addons_public_dir)){
                DirFile::rmDirs($addons_public_dir);
            }
        }
        return true;
    }
}