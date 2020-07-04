<?php
//laytp插件机制路由规则

use think\facade\Route;

Route::any('/addons/:addon/', "\\library\\AddonsRoute@execute");

$addons_domains = \think\facade\Config::get('addons.domains');
if($addons_domains){
    $host = \think\facade\Request::server('HTTP_HOST');
    if(in_array($host,$addons_domains)){
        Route::domain('autocreate.laytp.com', function(){
            $flip_addons_domains = array_flip(\think\facade\Config::get('addons.domains'));
            $host = \think\facade\Request::server('HTTP_HOST');
            exit(\library\AddonsRoute::domain_execute($flip_addons_domains[$host]));
        });
    }
}
