<?php
namespace library;

use app\admin\service\Addons;
use think\Container;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\facade\Config;
use think\facade\Env;
use think\facade\Request;
use think\Loader;
use think\Response;
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

        foreach($url_arr as $k=>$v){
            if($url_arr[$k] == $addon){
                $module = isset($url_arr[$k+1]) ? $url_arr[$k+1] : 'index';
                $controller = isset($url_arr[$k+2]) ? $url_arr[$k+2] : 'index';
                if(isset($url_arr[$k+3])){
                    $action_param = explode('?',$url_arr[$k+3]);
                    $temp_action = $action_param[0];
                    $temp_action_arr = explode('.',$action_param[0]);
                    $action = $temp_action_arr[0];
                }else{
                    $action = 'index';
                }
                break;
            }
        }

        // 是否自动转换控制器和操作名
        $convert = Config::get('url_convert');
        $filter = $convert ? 'strtolower' : 'trim';

        $addon = $addon ? trim(call_user_func($filter, $addon)) : '';
        define('LT_ADDON', 'addons/'.$addon);
        $module = $module ? trim(call_user_func($filter, $module)) : 'index';
        $controller = $controller ? trim(call_user_func($filter, $controller)) : 'index';
        $action = $action ? trim(call_user_func($filter, $action)) : 'index';

        if (!empty($addon) && !empty($module) && !empty($controller) && !empty($action)) {
            $addons_server = new Addons();
            $info = $addons_server->_info->getAddonInfo($addon);
            if (!$info) {
                $result = [
                    'code' => 0,
                    'msg'  => $addon.'插件未安装',
                    'data' => '',
                    'url'  => '',
                    'wait' => 3,
                ];
                $app     = Container::get('app');
                $response = Response::create($result, 'jump',404)->options(['jump_template' => $app['config']->get('dispatch_success_tmpl')]);

                throw new HttpResponseException($response);
//                throw new HttpException(404, $addon.'插件未安装');
            }
            if (!$info['state']) {
                $result = [
                    'code' => 0,
                    'msg'  => $addon.'插件已关闭',
                    'data' => '',
                    'url'  => '',
                    'wait' => 3,
                ];
                $app     = Container::get('app');
                $response = Response::create($result, 'jump',500)->options(['jump_template' => $app['config']->get('dispatch_success_tmpl')]);
                throw new HttpResponseException($response);
            }

            $info['lt_version'] = isset($info['lt_version']) ? $info['lt_version'] : '1.0.0';
            if(LT_VERSION < $info['lt_version']){
                $result = [
                    'code' => 0,
                    'msg'  => 'laytp框架版本过低',
                    'data' => '',
                    'url'  => '',
                    'wait' => 3,
                ];
                $app     = Container::get('app');
                $response = Response::create($result, 'jump',500)->options(['jump_template' => $app['config']->get('dispatch_success_tmpl')]);
                throw new HttpResponseException($response);
            }

            // 设置当前请求的控制器、操作
            $request->setModule($module)->setController($controller)->setAction($action);

            $class = self::get_addon_class($addon, $module, 'module.controller', $controller);
            if (!$class) {
                throw new HttpException(404, Loader::parseName($controller, 'module.controller').'控制器不存在');
            }

            //加载common.php
            $common_func_file = Env::get('root_path') . DS . 'addons' . DS . $addon . DS . 'common.php';
            if(is_file($common_func_file)){
                include_once Env::get('root_path') . DS . 'addons' . DS . $addon . DS . 'common.php';
            }

//            if(substr($controller,0,4) == 'api' . DS){
//                $instance = new $class($request);
//            }else{
                $instance = new $class(Container::get('app'));
//            }

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

    //插件绑定域名路由器
    public static function domain_execute($addon = ''){
        $request = Request::instance();
        $url = $request->url();
        $url_arr = array_merge(array_filter(explode('/',$url)));
        $module = isset($url_arr[0]) ? $url_arr[0] : 'index';
        $controller = isset($url_arr[1]) ? $url_arr[1] : 'index';
        if(isset($url_arr[2])){
            $action_param = explode('?',$url_arr[2]);
            $action = $action_param[0];
        }else{
            $action = 'index';
        }
//        $action = isset($url_arr[2]) ? $url_arr[2] : 'index';

        // 是否自动转换控制器和操作名
        $convert = Config::get('url_convert');
        $filter = $convert ? 'strtolower' : 'trim';

        $addons_domains = array_flip(\think\facade\Config::get('addons.domains'));
        $host = Request::server('HTTP_HOST');

        $addon = $addon ? trim(call_user_func($filter, $addon)) : $addons_domains[$host];
        $module = $module ? trim(call_user_func($filter, $module)) : 'index';
        $controller = $controller ? trim(call_user_func($filter, $controller)) : 'index';
        $action = $action ? trim(call_user_func($filter, $action)) : 'index';

        if (!empty($addon) && !empty($module) && !empty($controller) && !empty($action)) {
            $addons_server = new Addons();
            $info = $addons_server->_info->getAddonInfo($addon);
            if (!$info) {
                $result = [
                    'code' => 0,
                    'msg'  => $addon.'插件未安装',
                    'data' => '',
                    'url'  => '',
                    'wait' => 3,
                ];
                $app     = Container::get('app');
                $response = Response::create($result, 'jump',404)->options(['jump_template' => $app['config']->get('dispatch_success_tmpl')]);

                throw new HttpResponseException($response);
//                throw new HttpException(404, $addon.'插件未安装');
            }
            if (!$info['state']) {
                $result = [
                    'code' => 0,
                    'msg'  => $addon.'插件已关闭',
                    'data' => '',
                    'url'  => '',
                    'wait' => 3,
                ];
                $app     = Container::get('app');
                $response = Response::create($result, 'jump',500)->options(['jump_template' => $app['config']->get('dispatch_success_tmpl')]);
                throw new HttpResponseException($response);
            }

            // 设置当前请求的控制器、操作
            $request->setModule($module)->setController($controller)->setAction($action);
            $class = self::get_addon_class($addon, $module, 'module.controller', $controller);
            if (!$class) {
                throw new HttpException(404, Loader::parseName($controller, 'module.controller').'控制器不存在');
            }

//            if(substr($controller,0,3) == 'api'){
//                $instance = new $class($request);
//            }else{
                $instance = new $class(Container::get('app'));
//            }

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