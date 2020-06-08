<?php
namespace addons\aliyun_mobilemsg\validate;

//发送邮件验证器
use addons\aliyun_mobilemsg\model\Mobilemsg;
use think\facade\Config;
use think\Validate;

class Send extends Validate
{
    //数组顺序就是检测的顺序，比如这里，会先检测code验证码的正确性
    protected $rule =   [
        'mobile'  =>  'require|checkMobile:',
        'event'  =>  'require|checkEvent:',
    ];

    //定义内置方法检验失败后返回的字符
    protected $message  =   [
        'mobile.require'  => '手机号码不能为空',
        'event.require'  => '事件名称不能为空',
    ];

    //自定义密码检验方法
    protected function checkMobile($mobile){
        $mobilemsg_model = new Mobilemsg();
        $mobilemsg_last = $mobilemsg_model->where('mobile','=',$mobile)->order('id','desc')->find();
        if($mobilemsg_last){
            if( time() - strtotime($mobilemsg_last['create_time'] ) < Config::get('addons.aliyun_mobilemsg.interval_time') ){
                return '发送频繁';
            }else{
                return true;
            }
        }else{
            return true;
        }
    }

    //自定义密码检验方法
    protected function checkEvent($event){
        $events = array_keys(Config::get('addons.aliyun_mobilemsg.template'));
        return !in_array($event,$events) ? '事件名称参数错误' : true;
    }
}