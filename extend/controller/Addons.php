<?php
/**
 * Addons基类
 */

namespace controller;

use think\facade\Config;

class Addons
{
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
        $info = Config::get($name, $this->infoRange);
        if ($info) {
            return $info;
        }
        $info_file = $this->addons_path . 'info.ini';
        if (is_file($info_file)) {
            $info = Config::parse($info_file, '', $name, $this->infoRange);
            $info['url'] = addon_url($name);
        }
        Config::set($name, $info, $this->infoRange);

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
}