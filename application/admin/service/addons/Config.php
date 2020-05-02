<?php
namespace app\admin\service\addons;

use service\Service;
use think\facade\Env;

/**
 * 插件基础信息服务
 */
class Config extends Service
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
     * 获取配置项
     * @param $name
     * @return mixed
     */
    public function getConfig($name){
        $config = [];
        $config_file = $this->getAddonPath($name) . 'config.php';
        if(!file_exists($config_file)){
            return $config;
        }
        $config = include_once $this->getAddonPath($name) . 'config.php';
        return $config;
    }
}