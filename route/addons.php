<?php
//laytp插件机制路由规则

use think\facade\Request;
use think\facade\Route;

Route::domain('*', function(){
    Route::any('addons/:addon/', "\\library\\AddonsRoute@execute");

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