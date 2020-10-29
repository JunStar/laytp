<?php

namespace laytp\library;

use think\exception\HttpException;
use think\facade\Config;
use think\facade\Env;
use think\facade\Middleware;
use think\facade\Request;
use think\Response;
use think\Route;

class PluginRoute extends Route
{
    protected $middleware;
    protected $actionName;

    /**
     * 插件路由
     *  路由访问规则，http(s)://yourDomain/plugin/[插件名称]/[插件controller目录下的类名，多级目录以.号分割]/[方法名]/[参数列表]
     * @param null $plugin
     * @return mixed
     */
    public function execute($plugin = null)
    {
        $this->middleware = Middleware::instance();
        $this->request = Request::instance();
        $url = $this->request->url();
        $url_arr = array_filter(explode('/', $url));

        $controller = isset($url_arr[3]) ? $url_arr[3] : 'Index';
        $plugin = $plugin ? trim(strtolower($plugin)) : $plugin;
        if (!defined('LT_PLUGIN')) {
            define('LT_PLUGIN', $plugin);
        }
        $controller = $controller ? str_replace('.', '\\', trim($controller)) : 'Index';
        $classAndAction = $this->getAddonClassAndAction($plugin, $controller);

        $this->request->setController($controller)->setAction($classAndAction['action']);

        $common_func_file = Env::get('root_path') . DS . 'plugin' . DS . $plugin . DS . 'common.php';
        if (file_exists($common_func_file)) {
            include_once Env::get('root_path') . DS . 'plugin' . DS . $plugin . DS . 'common.php';
        }

        $class = $classAndAction['class'];
        $action = $classAndAction['action'];
        $instance = app()->make($class, [], true);
        $this->actionName = $action;

        try {
            $this->registerControllerMiddleware($instance);
        } catch (\ReflectionException $e) {
            throw new HttpException(500, $e->getMessage());
        }

        return $this->middleware->pipeline('controller')
            ->send($this->request)
            ->then(function () use ($instance) {
                // 获取当前操作名
                $suffix = Config::get('route.action_suffix');
                $action = $this->actionName . $suffix;

                if (is_callable([$instance, $action])) {
                    $vars = $this->request->param();
                    try {
                        $reflect = new \ReflectionMethod($instance, $action);
                        // 严格获取当前操作方法名
                        $actionName = $reflect->getName();
                        if ($suffix) {
                            $actionName = substr($actionName, 0, -strlen($suffix));
                        }

                        $this->request->setAction($actionName);
                    } catch (\Exception $e) {
                        $reflect = new \ReflectionMethod($instance, '__call');
                        $vars = [$action, $vars];
                        $this->request->setAction($action);
                    }
                } else {
                    // 操作不存在
                    throw new HttpException(404, 'method not exists:' . get_class($instance) . '->' . $action . '()');
                }

                $data = $this->app->invokeReflectMethod($instance, $reflect, $vars);

                return $this->autoResponse($data);
            });
    }

    /**
     * 得到插件完整类名
     * @param $plugin
     * @param $controller
     * @return array
     */
    public function getAddonClassAndAction($plugin, $controller)
    {
        $request = Request::instance();
        $url = $request->url();
        $url_arr = array_filter(explode('/', $url));

        $class = 'plugin\\' . $plugin . '\\controller\\' . $controller;

        if (!class_exists($class)) {
            throw new HttpException(404, $class . '类不存在');
        }

        $class = 'plugin\\' . $plugin . '\\controller\\' . $controller;
        if (isset($url_arr[4])) {
            $action_param = explode('?', $url_arr[4]);
            $action = $action_param[0];
        } else {
            $action = 'index';
        }
        $action = $action ? str_replace('.' . Config::get("route.url_html_suffix"), '', trim($action)) : 'index';

        if (!method_exists($class, $action)) {
            throw new HttpException(404, $class . '->' . $action . '()' . '方法不存在');
        }

        return ['class' => $class, 'action' => $action];
    }

    /**
     * 使用反射机制注册控制器中间件
     * @param $controller
     * @throws \ReflectionException
     */
    protected function registerControllerMiddleware($controller): void
    {
        $class = new \ReflectionClass($controller);

        if ($class->hasProperty('middleware')) {
            $reflectionProperty = $class->getProperty('middleware');
            $reflectionProperty->setAccessible(true);

            $middlewares = $reflectionProperty->getValue($controller);

            foreach ($middlewares as $key => $val) {
                if (!is_int($key)) {
                    if (isset($val['only']) && !in_array($this->request->action(true), array_map(function ($item) {
                            return strtolower($item);
                        }, is_string($val['only']) ? explode(",", $val['only']) : $val['only']))) {
                        continue;
                    } elseif (isset($val['except']) && in_array($this->request->action(true), array_map(function ($item) {
                            return strtolower($item);
                        }, is_string($val['except']) ? explode(',', $val['except']) : $val['except']))) {
                        continue;
                    } else {
                        $val = $key;
                    }
                }

                if (is_string($val) && strpos($val, ':')) {
                    $val = explode(':', $val);
                    if (count($val) > 1) {
                        $val = [$val[0], array_slice($val, 1)];
                    }
                }

                $this->middleware->controller($val);
            }
        }
    }

    protected function autoResponse($data): Response
    {
        if ($data instanceof Response) {
            $response = $data;
        } elseif (!is_null($data)) {
            // 默认自动识别响应输出类型
            $type = $this->request->isJson() ? 'json' : 'html';
            $response = Response::create($data, $type);
        } else {
            $data = ob_get_clean();

            $content = false === $data ? '' : $data;
            $status = '' === $content && $this->request->isJson() ? 204 : 200;
            $response = Response::create($content, 'html', $status);
        }

        return $response;
    }
}