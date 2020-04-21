<?php
/**
 * 插件控制器基类
 */

namespace controller;

use think\Controller;
use think\facade\Env;

class Addons extends Controller
{
    /**
     * 重写fetch方法，定义模板初始路径
     * @param string $template
     * @param array $vars
     * @param array $config
     * @return mixed|string
     * @throws \Exception
     */
    protected function fetch($template = '', $vars = [], $config = [])
    {
        $arr = explode('\\',get_called_class());
        $addon = $arr['1'];
        $module = $arr['2'];
        $this->view->config(
            ['view_path'=>Env::get('root_path')."addons/{$addon}/{$module}/view/"]
        );
        return $this->view->fetch($template);
    }
}