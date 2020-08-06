<?php
namespace app\admin\controller;

use controller\Backend;

class Dashboard extends Backend
{
    public function index(){
        /**
         * 为同时兼容支持php-fpm和think-swoole两种方式提供webserver
         * swoole中不允许使用exit和die函数进行程序流程控制
         * 当有ref参数时，只需要显示$this->fetch('admin@ltiframe/index');其他页面等待ajax请求响应
         */
        if($this->ref){
            return $this->fetch('admin@ltiframe/index');
        }
        return $this->fetch();
    }
}
