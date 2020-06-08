<?php
namespace addons\aliyun_mobileauth\service;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use service\Service;
use think\facade\Config;

class Mobile extends Service
{
    //根据token获取手机号,用于本机手机号一键免密登录
    public function getMobileByToken($access_token){
        try {
            AlibabaCloud::accessKeyClient(Config::get('addons.aliyun_mobileauth.access_key_id'), Config::get('addons.aliyun_mobileauth.access_key_secret'))
                ->regionId('cn-hangzhou')
                ->asDefaultClient();
            $result = AlibabaCloud::rpc()
                ->product('Dypnsapi')
                 ->scheme('https') // https | http
                ->version('2017-05-25')
                ->action('GetMobile')
                ->method('POST')
                ->host('dypnsapi.aliyuncs.com')
                ->options([
                    'query' => [
                        'RegionId' => "cn-hangzhou",
                        'AccessToken' => $access_token
                    ],
                ])
                ->request();
            $res = $result->toArray();
            if($res['Code'] == 'OK'){
                return $res['GetMobileResultDTO']['Mobile'];
            }else{
                $this->setError($res['Message']);
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
}