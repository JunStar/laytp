<?php
namespace app\common\controller;

use think\captcha\Captcha;
use think\Controller;
use think\facade\Config;

class Common extends Controller
{
    /**
     * 获取验证码图片
     * @return \think\Response
     */
    public function verifyCode(){
        $captcha = new Captcha((array) Config::pull('captcha'));
        return $captcha->entry();
    }
}
