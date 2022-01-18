<?php
//自定义路由
use think\facade\Route;

// api子域名路由指定
Route::domain('localapi',function () {
    Route::rule('/plugin/:plugin', "laytp\library\PluginRoute@execute");
    Route::get('captcha/[:config]','\\think\\captcha\\CaptchaController@index');
    Route::rule('/:pathInfo', '/api.:pathInfo')->pattern(['pathInfo'=>'[\w\.\/]+']);
    Route::miss(function() {
        return json([
            "code" => 0,
            "msg" => '路由不存在',
            "data" => new stdClass()
        ]);
    });
});

// adminapi子域名路由指定
Route::domain('localadminapi',function () {
    Route::rule('/plugin/:plugin', "laytp\library\PluginRoute@execute");
    Route::get('captcha/[:config]','\\think\\captcha\\CaptchaController@index');
    Route::rule('/:pathInfo', '/admin.:pathInfo')->pattern(['pathInfo'=>'[\w\.\/]+']);
    Route::miss(function() {
        return json([
            "code" => 0,
            "msg" => '路由不存在',
            "data" => new stdClass()
        ]);
    });
});