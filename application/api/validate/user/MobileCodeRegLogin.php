<?php
//手机号+手机验证码注册验证器
namespace app\api\validate\user;

use app\common\model\Device;
use app\common\service\Mobile;
use think\facade\Config;
use think\Validate;

class MobileCodeRegLogin extends Validate
{
    //数组顺序就是检测的顺序
    protected $rule =   [
        'mobile'      =>  'require',
        'code'      =>  'require|checkCode:'
    ];

    //定义内置方法检验失败后返回的字符
    protected $message  =   [
        'mobile.require'  => '手机号不能为空',
        'code.require'  => '验证码不能为空',
    ];

    public function checkCode($code, $rule, $data){
        if(!Config::get('app_debug')){
            $mobile_service = new Mobile();
            if(!$mobile_service->checkCode($data['mobile'],'reg_login',$code)){
                return '验证码错误';
            }
        }
        return true;
    }
}