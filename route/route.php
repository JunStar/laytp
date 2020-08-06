<?php

use think\facade\Route;

//获取验证码图片地址
Route::get('verifyCode', '\app\common\controller\Common@verifyCode');