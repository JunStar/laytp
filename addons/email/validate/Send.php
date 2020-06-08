<?php
namespace addons\email\validate;

//发送邮件验证器
use addons\email\admin\model\Email;
use addons\email\admin\model\email\Template;
use think\facade\Config;
use think\Validate;

class Send extends Validate
{
    //数组顺序就是检测的顺序，比如这里，会先检测code验证码的正确性
    protected $rule =   [
        'email'  =>  'require|email|checkEmail:',
        'event'  =>  'require|checkEvent:',
    ];

    //定义内置方法检验失败后返回的字符
    protected $message  =   [
        'email.require'  => '邮箱不能为空',
        'email.email'  => '邮箱格式错误',
        'event.require'  => '事件名称不能为空',
    ];

    //自定义检验方法
    protected function checkEmail($email){
        $email_model = new Email();
        $max_send_num = Config::get('addons.email.max_send_num') ? Config::get('addons.email.max_send_num') : 3;
        $email_limit_list = $email_model->where('to','=',$email)->order('id','desc')->limit($max_send_num)->select()->toArray();
        if($email_limit_list){
            if( time() - strtotime($email_limit_list[0]['create_time'] ) < 60 ){
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
        $template_model = new Template();
        $template_id = $template_model->getFieldByEvent($event, 'id');
        return !$template_id ? '事件名称参数错误' : true;
    }
}