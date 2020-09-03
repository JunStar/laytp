<?php
namespace laytp\library;

use think\exception\HttpException;
use think\facade\Env;
use think\facade\Request;
use think\Route;

class AddonsRoute extends Route {
    /**
     * 插件路由
     * @param null $addon
     * @return mixed
     */
    public function execute($addon = null)
    {
        $request = Request::instance();
        $url = $request->url();
        $url_arr = array_filter(explode('/',$url));
        $module = isset($url_arr[3]) ? $url_arr[3] : 'index';
        $controller = isset($url_arr[4]) ? $url_arr[4] : 'Index';
        $addon = $addon ? trim(strtolower($addon)) : $addon;
        $module = $module ? trim(strtolower($module)) : 'index';
        $controller = $controller ? trim($controller) : 'Index';
        $classAndAction = $this->getAddonClassAndAction($addon, $module, $controller);
        $request->setController($controller)->setAction($classAndAction['action']);

        $common_func_file = Env::get('root_path') . DS . 'addons' . DS . $addon . DS . 'common.php';
        if(file_exists($common_func_file)){
            include_once Env::get('root_path') . DS . 'addons' . DS . $addon . DS . 'common.php';
        }

        $class = $classAndAction['class'];
        $action = $classAndAction['action'];
        $instance = new $class(app());
        $call = [$instance, $action];
        $vars = [$action];
        return call_user_func_array($call, $vars);
    }

    /**
     * 得到插件完整类名
     */
    public function getAddonClassAndAction($addon, $module, $controller){
        $request = Request::instance();
        $url = $request->url();
        $url_arr = array_filter(explode('/',$url));

        $class = 'addons\\'.$addon.'\\'.$module.'\\controller\\'.$controller;

        if(class_exists($class)){
            if(isset($url_arr[5])){
                $action_param = explode('?',$url_arr[5]);
                $action = $action_param[0];
            }else{
                $action = 'index';
            }
            $action = $action ? trim(strtolower($action)) : 'index';
            return ['class'=>$class,'action'=>$action];
        }else{
            $class = 'addons\\'.$addon.'\\controller\\'.$module;
            if(isset($url_arr[4])){
                $action_param = explode('?',$url_arr[4]);
                $action = $action_param[0];
            }else{
                $action = 'index';
            }
            $action = $action ? trim(strtolower($action)) : 'index';
            if(class_exists($class)){
                return ['class'=>$class,'action'=>$action];
            }else{
                throw new HttpException(404, $class . '->' . $action . '()'.'方法不存在');
            }
        }
    }
}