<?php
// 定义laytp插件路由规则
\think\facade\Route::any('addons/:addon/', "\\library\\AddonsRoute@execute");

return [

];
