<?php

namespace app\controller\api;

use app\service\ConfServiceFacade;
use laytp\library\Random;
use plugin\ali_oss\service\Oss;
use plugin\ali_sms\service\AliSmsServiceFacade;
use plugin\email\service\EmailServiceFacade;
use plugin\email\validate\Send;
use plugin\qiniu_kodo\service\Kodo;
use laytp\controller\Api;
use think\facade\Env;
use think\facade\Filesystem;

/**
 * 公用接口
 * @ApiWeigh (100)
 */
class Common extends Api
{
    public $noNeedLogin = ['sendEmailCode','checkEmailCode','sendMobileCode'];

    public $noNeedCheckSign = [];

    /*@formatter:off*/
    /**
     * @ApiTitle    (文件上传)
     * @ApiSummary  (文件上传，兼容阿里云OSS、七牛云KODO和本地上传，需在后台[系统配置 - 上传配置]选择上传方式)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api.common/upload)
     * @ApiHeaders  (name="token", type="string", required="true", description="用户登录后得到的Token")
     * @ApiParams   (name="file", type="file", required="true", description="文件")
     * @ApiParams   (name="upload_dir", type="string", required="false", description="上传目录，允许为空", sample="avatar")
     * @ApiReturnParams   (name="code", type="integer", description="错误码.0=没有错误，表示操作成功；1=常规错误码，客户端仅需提示msg；其他错误码与具体业务相关，其他错误码举例：10401。前端需要跳转至登录界面。")
     * @ApiReturnParams   (name="msg", type="string", description="返回描述")
     * @ApiReturnParams   (name="time", type="integer", description="请求时间，Unix时间戳，单位秒")
     * @ApiReturnParams   (name="data", type="null", description="null")
     * @ApiReturn
({
    'code':0,
    'msg':'上传成功',
    'time':'15632654875',
    'data':null
})
     */
    /*@formatter:on*/
    public function upload()
    {
        try {
            $uploadType = ConfServiceFacade::get('system.upload.type');
            $file       = $this->request->file('file'); // 获取上传的文件
            if (!$file) {
                return $this->error('上传失败，请选择需要上传的文件');
            }
            $fileExt   = strtolower($file->getOriginalExtension());
            $saveName  = date("Ymd") . "/" . md5(uniqid(mt_rand())) . ".{$fileExt}";
            $uploadDir = $this->request->param('dir', '');
            $object    = $uploadDir . $saveName;//上传至阿里云或者七牛云的文件名
            $upload    = ConfServiceFacade::groupGet('system.upload');
            if (!$upload) {
                return $this->error('上传配置未保存，请到后台[控制台 - 常规管理 - 系统配置 - 上传配置]，点击保存配置');
            }
            $size = $this->request->param('size', $upload['size']);
            preg_match('/(\d+)(\w+)/', $size, $matches);
            $type     = strtolower($matches[2]);
            $typeDict = ['b' => 0, 'k' => 1, 'kb' => 1, 'm' => 2, 'mb' => 2, 'gb' => 3, 'g' => 3];
            $size     = (int)$size * pow(1024, isset($typeDict[$type]) ? $typeDict[$type] : 0);
            if ($file->getSize() > $size) {
                return $this->error('上传失败，文件大小超过' . $size);
            }

            $allowMime = $this->request->param('mime', $upload['mime']);
            $mimeArr   = explode(',', strtolower($allowMime));
            //禁止上传PHP和HTML文件
            if (in_array($file->getMime(), ['text/x-php', 'text/html']) || in_array($fileExt, ['php', 'html', 'htm'])) {
                return $this->error('上传失败，禁止上传php和html文件');
            }
            //验证文件后缀
            if ($allowMime !== '*' && (!in_array($fileExt, $mimeArr))) {
                return $this->error('上传失败，允许上传的文件后缀为' . implode(',', $mimeArr) . '，实际上传文件[ ' . $file->getOriginalName() . ' ]的后缀为' . $fileExt);
            }
            //验证文件的mime
            if ($allowMime !== '*') {
                $canUpload = false;
                foreach ($mimeArr as $mime) {
                    if (stripos($file->getMime(), $mime) !== false) {
                        $canUpload = true;
                        break;
                    }
                }
                if (!$canUpload) return $this->error('上传失败，允许上传的文件类型为' . implode(',', $mimeArr) . '，实际上传文件[ ' . $file->getOriginalName() . ' ]的文件类型为' . $file->getMime() . '。您可以到常规管理-系统配置-上传配置中添加允许上传的文件类型来避免这个错误');
            }

            $inputValue = "";
            //上传至七牛云
            if ($uploadType == 'qiniu') {
                $kodo = Kodo::instance();
                $inputValue = $kodo->upload($file->getPathname(), $object);
                if(!$inputValue){
                    return $this->error('上传失败,' . $kodo->getError());
                }
            }

            //上传至阿里云
            if ($uploadType == 'aliyun') {
                $oss = Oss::instance();
                $inputValue = $oss->upload($file->getPathname(), $object);
                if(!$inputValue){
                    return $this->error($oss->getError());
                }
            }

            //本地上传
            if ($uploadType == 'local') {
                $saveName   = Filesystem::putFileAs($uploadDir, $file, $object);
                $saveName   = str_replace('\\', '/', $saveName);
                $staticDomain = Env::get('domain.static');
                if($staticDomain){
                    $inputValue = $staticDomain . '/storage/' . $saveName;
                }else{
                    $inputValue = request()->domain() . '/static/storage/' . $saveName;
                }
            }
            return $this->success('上传成功', $inputValue);
        } catch (\Exception $e) {
            return $this->error('上传异常');
        }
    }

    /*@formatter:off*/
    /**
     * @ApiTitle    (发送邮箱验证码)
     * @ApiSummary  (发送邮箱验证码)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api.common/sendEmailCode)
     * @ApiParams   (name="email", type="string", required="true", description="邮箱")
     * @ApiParams   (name="event", type="string", required="true", sample="register",description="事件名称")
     * @ApiReturnParams   (name="code", type="integer", description="错误码.0=没有错误，表示操作成功；1=常规错误码，客户端仅需提示msg；其他错误码与具体业务相关，其他错误码举例：10401。前端需要跳转至登录界面。")
     * @ApiReturnParams   (name="msg", type="string", description="返回描述")
     * @ApiReturnParams   (name="time", type="integer", description="请求时间，Unix时间戳，单位秒")
     * @ApiReturnParams   (name="data", type="null", description="null")
     * @ApiReturn
({
    'code':0,
    'msg':'发送成功',
    'time':'15632654875',
    'data':null
})
     */
    /*@formatter:on*/
    public function sendEmailCode(){
        $emailStatus = ConfServiceFacade::get('system.email.status');
        if($emailStatus !== 'open'){
            return $this->error('邮件功能未开启，请先到插件市场安装邮件插件，并在[邮件配置]中开启');
        }

        $params['email'] = $this->request->param('email');
        $params['event'] = $this->request->param('event');

        $validate = new Send();
        if(!$validate->check($params)) return $this->error('发送失败,'.$validate->getError());

        if(EmailServiceFacade::send($params['email'], $params['event'], ['code'=>Random::numeric()])){
            return $this->success('发送成功');
        }else{
            return $this->error('发送失败,'.EmailServiceFacade::getError());
        }
    }

    /*@formatter:off*/
    /**
     * @ApiTitle    (验证邮箱验证码)
     * @ApiSummary  (验证邮箱验证码)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api.common/checkEmailCode)
     * @ApiParams   (name="email", type="string", required="true", description="邮箱")
     * @ApiParams   (name="event", type="string", required="true", sample="register",description="事件名称")
     * @ApiParams   (name="code", type="string", required="true", sample="register",description="邮箱验证码")
     * @ApiReturnParams   (name="code", type="integer", description="错误码.0=没有错误，表示操作成功；1=常规错误码，客户端仅需提示msg；其他错误码与具体业务相关，其他错误码举例：10401。前端需要跳转至登录界面。")
     * @ApiReturnParams   (name="msg", type="string", description="返回描述")
     * @ApiReturnParams   (name="time", type="integer", description="请求时间，Unix时间戳，单位秒")
     * @ApiReturnParams   (name="data", type="null", description="null")
     * @ApiReturn
({
    'code':0,
    'msg':'发送成功',
    'time':'15632654875',
    'data':null
})
     */
    /*@formatter:on*/
    public function checkEmailCode(){
        $emailStatus = ConfServiceFacade::get('system.email.status');
        if($emailStatus !== 'open'){
            return $this->error('邮件功能未开启，请先到插件市场安装邮件插件，并在[邮件配置]中开启');
        }

        $params['email'] = $this->request->param('email');
        $params['event'] = $this->request->param('event');
        $params['code'] = $this->request->param('code');

        if(EmailServiceFacade::checkCode($params['email'],$params['event'],$params['code'])){
            return $this->success('验证成功');
        }else{
            return $this->error('验证失败,'.EmailServiceFacade::getError());
        }
    }

    /*@formatter:off*/
    /**
     * @ApiTitle    (发送手机验证码)
     * @ApiSummary  (发送手机验证码)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api.common/sendMobileCode)
     * @ApiHeaders  (name="token", type="string", required="true", description="用户登录后得到的Token")
     * @ApiParams   (name="mobile", type="string", required="true", description="手机号码")
     * @ApiParams   (name="event", type="string", required="true", sample="reg_login",description="事件名称，reg_login=使用手机号+验证码的方式进行注册或登录")
     * @ApiReturnParams   (name="code", type="integer", required="true", sample="0")
     * @ApiReturnParams   (name="msg", type="string", required="true", sample="返回成功")
     * @ApiReturnParams   (name="time", type="integer", description="请求时间，Unix时间戳，单位秒")
     * @ApiReturnParams   (name="data", type="null", description="只会返回null")
     * @ApiReturn
({
    "code": 1,
    "msg": "发送失败,触发分钟级流控Permits:1",
    "time": 1584667483,
    "data": null
})
     */
    /*@formatter:on*/
    public function sendMobileCode(){
        $aliSmsStatus = ConfServiceFacade::groupGet('aliSms.conf');
        if(!$aliSmsStatus){
            return $this->error('请先到插件市场安装阿里云手机短信插件，并进行相关配置');
        }

        $params['mobile'] = $this->request->param('mobile');
        $params['event'] = $this->request->param('event');

        $validate = new \plugin\ali_sms\validate\Send();
        if(!$validate->check($params)) return $this->error('发送失败,'.$validate->getError());

        if(AliSmsServiceFacade::send($params['mobile'],$params['event'],['code'=>Random::numeric()])){
            return $this->success('发送成功');
        }else{
            return $this->error('发送失败,'.AliSmsServiceFacade::getError());
        }
    }
}