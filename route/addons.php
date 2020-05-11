<?php
//laytp插件机制路由规则

use think\facade\Request;
use think\facade\Route;

Route::domain('*', function(){
    //正常插件访问路由，例：http://www.yourdomain.com/addons/demo/[module]/[controller]/[action]
    Route::any('addons/:addon/', "\\library\\AddonsRoute@execute");

    //绑定了二级域名插件访问路由，例：http://demo.yourdomain.com/[module]/[controller]/[action]
    $request = Request::instance();
    $host = $request->host();
    $addon_service = new \app\admin\service\Addons();
    $addon = $addon_service->_info->getAddonByDomain($host);

    $addon_service = new \app\admin\service\Addons();
    $addon_info = $addon_service->_info->getAddonInfo($addon);
    if($addon_info){
        exit(\library\AddonsRoute::domain_execute($addon));
    }
});