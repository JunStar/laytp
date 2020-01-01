<?php
namespace app\common\service;

use app\common\model\email\Template;
use service\Service;
use think\facade\Config;

class Email extends Service
{
    //发送邮件
    public function send($to, $event, $template_date, $from=''){
        $template = Template::where('event',$event)->find();
        $template_content = '';
        foreach($template_date as $k=>$v){
            $template_content = str_replace('{'.$k.'}',$v,$template->template);
        }
        $email_lib = \library\Email::instance();
        if($from){
            $email_lib->from($from);
        }
        $result = $email_lib->to($to)->subject($template->title)->message($template_content,($template->ishtml==1)?true:false)->send();
        if($result){
            //插入邮件表
            $data = [
                'template_id' => $template->id,
                'event' => $template->event,
                'content' => $template_content,
                'from' => $from ? $from : Config::get('laytp.email.from'),
                'to' => $to,
                'expire_time' => $template->expire ? time() + $template->expire : 0,
                'params' => json_encode($template_date, JSON_UNESCAPED_UNICODE),
            ];
            \app\common\model\Email::create($data);
            return true;
        }else{
            $this->setError($email_lib->getError());
            return false;
        }
    }

    //验证验证码
    public function checkCode($to, $event, $code){
        $email = \app\common\model\Email::where([['to','=',$to],['event','=',$event],['params','=',json_encode(['code'=>$code],JSON_UNESCAPED_UNICODE)]])->find();
        if(!$email){
            $this->setError('验证码错误');
            return false;
        }
        if($email->status == 2){
            $this->setError('验证码已使用');
            return false;
        }
        if($email->status == 3){
            $this->setError('验证码已过期');
            return false;
        }
        if($email->expire_time && $email->expire_time < time()){
            \app\common\model\Email::where('id','=',$email->id)->update(['status'=>3]);
            $this->setError('验证码已过期');
            return false;
        }
        \app\common\model\Email::where('id','=',$email->id)->update(['status'=>2]);
        return true;
    }
}