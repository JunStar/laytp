<?php
use \think\facade\Route;

// 定义laytp插件路由规则
Route::any('addons/:addon/', "\\library\\AddonsRoute@execute");

Route::domain('autocreate', 'addons/autocreate');

return [

];
