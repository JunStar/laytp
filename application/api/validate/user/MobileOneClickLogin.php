<?php
//手机号+手机验证码注册验证器
namespace app\api\validate\user;

use app\common\model\Device;
use app\common\service\Mobile;
use think\Validate;

class MobileOneClickLogin extends Validate
{
    //数组顺序就是检测的顺序
    protected $rule =   [
        'access_token'      =>  'require',
    ];

    //定义内置方法检验失败后返回的字符
    protected $message  =   [
        'access_token.require'  => 'Token不能为空'
    ];
}