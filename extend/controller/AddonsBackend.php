<?php
/**
 * 插件后台控制器基类
 */

namespace controller;

use app\admin\model\auth\Menu;
use library\Token;
use library\Tree;
use think\Controller;
use think\facade\Config;
use think\facade\Cookie;
use think\facade\Env;
use think\facade\Hook;
use think\facade\Session;

class AddonsBackend extends Backend
{
    public $addon;//当前插件名

    public function initialize(){
        parent::initialize();
    }

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