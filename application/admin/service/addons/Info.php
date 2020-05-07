<?php
namespace app\admin\service\addons;

use library\DirFile;
use service\Service;
use think\Exception;
use think\facade\Config;
use think\facade\Env;

/**
 * 插件基础信息服务
 */
class Info extends Service
{
    /**
     * 获取插件绝对目录
     * @param $name 插件名称
     * @return string
     */
    public function getAddonPath($name){
        return Env::get('root_path') . 'addons' . DS . $name . DS;
    }

    /**
     * 检测插件是否完整
     *
     * @param   string $name 插件名称
     * @return  boolean
     */
    public function check($name)
    {
        if (!$name || !is_dir(Env::get('root_path') . 'addons' . DS . $name)) {
            $this->setError('插件不存在');
            return false;
        }
        return true;
    }

    /**
     * 读取插件的基础信息
     * @param string $name 插件名
     * @return array
     */
    public function getAddonInfo($name)
    {
        $addons_path = $this->getAddonPath($name);
        $info_file = $addons_path . 'info.ini';
        $info = [];
        if (is_file($info_file)) {
            $info = Config::parse($info_file,'','laytp_addons_'.$name);
        }
        return $info;
    }

    /**
     * 设置基础配置信息
     * @param string $name 插件名
     * @param array $array
     * @return boolean
     * @throws Exception
     */
    public function setAddonInfo($name, $array)
    {
        $addons_path = $this->getAddonPath($name);
        $file = $addons_path . 'info.ini';
        if (!isset($array['name'])) {
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


    public function getUrl($name,$url){
        $server = request()->server();
        return $server['REQUEST_SCHEME'].'://'.$server['SERVER_NAME'].'/addons/'.$name.'/'.$url;
    }

    /**
     * 根据域名获取插件名称
     */
    public function getAddonByDomain($domain){
        $addons_path = Env::get('root_path') . DS . 'addons';
        $addons_dir = DirFile::recurDir($addons_path);
        foreach($addons_dir as $k=>$v){
            if($v['type'] == 'dir'){
                $info = $this->getAddonInfo($v['baseName']);
                if($info['domain'] == $domain){
                    return $info['name'];
                }
            }
        }
        return false;
    }

    /**
     * 获取所有插件的info
     */
    public function getAddonsInfo(){
        $addons_path = Env::get('root_path') . DS . 'addons';
        $addons_dir = DirFile::recurDir($addons_path);
        $addons = [];
        foreach($addons_dir as $k=>$v){
            if($v['type'] == 'dir'){
                $addons[] = $this->getAddonInfo($v['baseName']);
            }
        }
        return $addons;
    }
}