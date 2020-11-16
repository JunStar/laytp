<?php
//laytp插件路由
use think\facade\Route;

Route::rule('/plugin/:plugin', "laytp\library\PluginRoute@execute");
