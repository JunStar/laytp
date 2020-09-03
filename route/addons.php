<?php
//laytp插件路由
use think\facade\Route;

Route::rule('/addons/:addon', "\library\AddonsRoute@execute");
