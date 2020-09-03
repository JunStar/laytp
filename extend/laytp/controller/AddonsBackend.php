<?php
/**
 * 插件后台控制器基类
 */

namespace laytp\controller;

use think\facade\Config;
use think\facade\Request;
use think\Template;

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
    protected function fetch($vars = [], $template = '' , $config = [])
    {
        $request = Request::instance();
        $url = $request->url();
        $url_arr = array_filter(explode('/',$url));

        $module = isset($url_arr[3]) ? $url_arr[3] : 'index';

        $addon = $url_arr[2];
        $module = $module ? trim(strtolower($module)) : 'index';

        $root = dirname(dirname(dirname(realpath(__FILE__ ))));
        $config = [
            'view_path' => $root.DS.Config::get('view.view_dir_name').DS.'addons'.DS.$addon.DS.$module.DS.Request::controller().DS.Request::action(),
            'cache_path' => '../runtime/addons/'.$addon.'/'.$module.'/'
        ];
        $view = new Template(array_merge(Config::get('view'), $config));
        return $view->fetch($template, $vars);
    }
}