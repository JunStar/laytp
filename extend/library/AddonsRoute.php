<?php
namespace library;

use app\admin\service\Addons;
use think\Container;
use think\exception\HttpException;
use think\facade\Config;
use think\facade\Request;
use think\Loader;
use think\Route;

class AddonsRoute extends Route {
    /**
     * 插件路由
     * @param string $addon
     * @param string $module
     * @param string $controller
     * @param string $action
     * @return mixed
     */
    public function execute($addon = null)
    {
        $request = Request::instance();
        $url = $request->url();
        $url_arr = array_filter(explode('/',$url));

        if( isset($url_arr['3']) ){
            if(!strstr($url_arr['3'],'?')){
                $module = $url_arr['3'];
            }else{
                $module = 'index';
            }
        }else{
            $module = 'index';
        }

        if( isset($url_arr['4']) ){
            if(!strstr($url_arr['4'],'?')){
                $controller = $url_arr['4'];
            }else{
                $controller = 'index';
            }
        }else{
            $controller = 'index';
        }

        if( isset($url_arr['5']) ){
            if(!strstr($url_arr['5'],'?')){
                $action = $url_arr['5'];
            }else{
                $action = 'index';
            }
        }else{
            $action = 'index';
        }

        // 是否自动转换控制器和操作名
        $convert = Config::get('url_convert');
        $filter = $convert ? 'strtolower' : 'trim';

        $addon = $addon ? trim(call_user_func($filter, $addon)) : '';
        $module = $module ? trim(call_user_func($filter, $module)) : 'index';
        $controller = $controller ? trim(call_user_func($filter, $controller)) : 'index';
        $action = $action ? trim(call_user_func($filter, $action)) : 'index';

        if (!empty($addon) && !empty($module) && !empty($controller) && !empty($action)) {
            $addons_server = new Addons();
            $info = $addons_server->_info->getAddonInfo($addon);
            if (!$info) {
                throw new HttpException(404, $addon.'插件不存在');
            }
            if (!$info['state']) {
                throw new HttpException(500, $addon.'插件已关闭');
            }

            // 设置当前请求的控制器、操作
            $request->setModule($module)->setController($controller)->setAction($action);

            $class = self::get_addon_class($addon, $module, 'module.controller', $controller);
            if (!$class) {
                throw new HttpException(404, Loader::parseName($controller, 'module.controller').'控制器不存在');
            }

            if(substr($controller,0,3) == 'api'){
                $instance = new $class($request);
            }else{
                $instance = new $class(Container::get('app'));
            }

            $vars = [];
            if (is_callable([$instance, $action])) {
                // 执行操作方法
                $call = [$instance, $action];
            } elseif (is_callable([$instance, '_empty'])) {
                // 空操作
                $call = [$instance, '_empty'];
                $vars = [$action];
            } else {
                // 操作不存在
                throw new HttpException(404, get_class($instance) . '->' . $action . '()'.'方法不存在');
            }

            return call_user_func_array($call, $vars);
        } else {
            abort(500, $addon . '插件不允许空操作');
        }
    }

    /**
     * 获取插件类的类名
     * @param string $name  插件名
     * @param string $module  模块名
     * @param string $type  返回命名空间类型
     * @param string $class 当前类名
     * @return string
     */
    public static function get_addon_class($name, $module, $type = 'module.controller', $class = null)
    {
        $class = Loader::parseName($class);
        // 处理多级控制器情况
        if (!is_null($class) && strpos($class, '.')) {
            $class = explode('.', $class);

            $class[count($class) - 1] = Loader::parseName(end($class), 1);
            $class = implode('\\', $class);
        } else {
            $class = Loader::parseName(is_null($class) ? $name : $class, 1);
        }
        switch ($type) {
            case 'module.controller':
                $namespace = "addons\\" . $name . "\\" . $module . "\\controller\\" . $class;
                break;
            case 'controller':
                $namespace = "addons\\" . $name . "\\controller\\" . $class;
                break;
            default:
                $namespace = "addons\\" . $name . "\\" . $class;
        }
        return class_exists($namespace) ? $namespace : '';
    }
}