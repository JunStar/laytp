<?php
/**
 * Addons基类
 */

use think\facade\Config;
use think\facade\Env;

class Addons
{
    // 插件目录
    public $addons_path = '';
    // 插件信息作用域
    protected $infoRange = 'addon_info';
    // 插件配置作用域
    protected $configRange = 'addon_config';

    /**
     * 析构函数
     * @access public
     */
    public function __construct()
    {
        $name = $this->getName();
        // 获取当前插件目录
        $this->addons_path = Env::get('root_path') . 'addons' . DS . $name . DS;

        // 插件初始化
        if (method_exists($this, '_initialize')) {
            $this->_initialize();
        }
    }

    /**
     * 检查基础配置信息是否完整
     * @return bool
     */
    final public function checkInfo()
    {
        $info = $this->getInfo();
        $info_check_keys = ['name', 'title', 'intro', 'author', 'version', 'state'];
        foreach ($info_check_keys as $value) {
            if (!array_key_exists($value, $info)) {
                return false;
            }
        }
        return true;
    }

    /**
     * 读取基础配置信息
     * @param string $name
     * @return array
     */
    final public function getInfo($name = '')
    {
        if (empty($name)) {
            $name = $this->getName();
        }
        $info_key = $this->infoRange. '.' .$name;
        $info = Config::get($info_key);
        if ($info) {
            return $info;
        }
        $info_file = $this->addons_path . 'info.ini';
        if (is_file($info_file)) {
            $config = new \think\Config();
            $info = $config->parse($info_file, '', $info_key);
//            $info['url'] = addon_url($name);
        }
        Config::set($info_key, $info);

        return $info ? $info : [];
    }

    /**
     * 获取当前模块名
     * @return string
     */
    final public function getName()
    {
        $data = explode('\\', get_class($this));
        return strtolower(array_pop($data));
    }

    /**
     * 获取插件的配置数组
     * @param string $name 可选模块名
     * @return array
     */
    final public function getConfig($name = '')
    {
        if (empty($name)) {
            $name = $this->getName();
        }
        $config = Config::get($name, $this->configRange);
        if ($config) {
            return $config;
        }
        $config_file = $this->addons_path . 'config.php';
        if (is_file($config_file)) {
            $temp_arr = include $config_file;
            foreach ($temp_arr as $key => $value) {
                $config[$value['name']] = $value['value'];
            }
            unset($temp_arr);
        }
        Config::set($name, $config, $this->configRange);

        return $config;
    }
}