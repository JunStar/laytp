<?php
namespace addons\aliyun_mobilemsg\service;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use addons\aliyun_mobilemsg\model\Mobilemsg;
use service\Service;
use think\facade\Config;

class Mobile extends Service
{
    //发送手机短信
    public function send($mobile, $event, $template_param){
        try {
            AlibabaCloud::accessKeyClient(Config::get('addons.aliyun_mobilemsg.access_key'), Config::get('addons.aliyun_mobilemsg.secret_key'))
                ->regionId('cn-hangzhou')
                ->asDefaultClient();

            $mobile_msg_template = Config::get('addons.aliyun_mobilemsg.template');
            $result = AlibabaCloud::rpc()
                ->product('Dysmsapi')
                // ->scheme('https') // https | http
                ->version('2017-05-25')
                ->action('SendSms')
                ->method('POST')
                ->host('dysmsapi.aliyuncs.com')
                ->options([
                    'query' => [
                        'RegionId' => "cn-hangzhou",
                        'PhoneNumbers' => $mobile,
                        'SignName' => Config::get('addons.aliyun_mobilemsg.sign'),
                        'TemplateCode' => $mobile_msg_template[$event],
                        'TemplateParam' => json_encode($template_param, JSON_UNESCAPED_UNICODE),
                    ],
                ])
                ->request()->toArray();
            if($result['Code'] == 'OK'){
                //插入短信表
                $data = [
                    'template_code' => $mobile_msg_template[$event],
                    'event' => $event,
                    'mobile' => $mobile,
                    'expire_time' => Config::get('addons.aliyun_mobilemsg.expire_time') ? time() + Config::get('addons.aliyun_mobilemsg.expire_time') : 0,
                    'params' => json_encode($template_param, JSON_UNESCAPED_UNICODE),
                ];
                Mobilemsg::create($data);
                return true;
            }else{
                $this->setError($result['Message']);
                return false;
            }
        } catch (ClientException $e) {
            $this->setError($e->getErrorMessage());
            return false;
        } catch (ServerException $e) {
            $this->setError($e->getErrorMessage());
            return false;
        }
    }

    //检测验证码
    public function checkCode($mobile, $event, $code){
        if(!Config::get('app_debug')) {
            $message = Mobilemsg::where([['mobile', '=', $mobile], ['event', '=', $event], ['params', '=', json_encode(['code' => $code], JSON_UNESCAPED_UNICODE)]])->find();
            if (!$message) {
                $this->setError('验证码错误');
                return false;
            }
            if ($message->status == 2) {
                $this->setError('验证码已使用');
                return false;
            }
            if ($message->status == 3) {
                $this->setError('验证码已过期');
                return false;
            }
            if ($message->expire_time && $message->expire_time < time()) {
                Mobilemsg::where('id', '=', $message->id)->update(['status' => 3]);
                $this->setError('验证码已过期');
                return false;
            }
            Mobilemsg::where('id', '=', $message->id)->update(['status' => 2]);
        }
        return true;
    }
}