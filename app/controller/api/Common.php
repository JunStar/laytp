<?php

namespace app\controller\api;

use app\service\ConfServiceFacade;
use laytp\library\Random;
use laytp\library\UploadDomain;
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
     * @ApiSummary  (文件上传，兼容阿里云OSS、七牛云KODO和本地上传，自行在接口中传递参数选择上传方式，阿里云OSS和七牛云KODO需要后端安装相应插件)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api.common/upload)
     * @ApiHeaders  (name="token", type="string", required="true", description="用户登录后得到的Token")
     * @ApiParams   (name="file", type="file", required="true", description="文件")
     * @ApiParams   (name="upload_type", type="string", required="false", description="上传方式，允许为空，local=本地上传，ali-oss=阿里云OSS上传，qiniu-kodo=七牛云KODO上传，默认为local", sample="avatar")
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
            $uploadType = $this->request->param('upload_type', 'local');
            if (!in_array($uploadType, ['local', 'ali-oss', 'qiniu-kodo'])) {
                return $this->error($uploadType . '上传方式未定义');
            }
            $file = $this->request->file('laytpUploadFile'); // 获取上传的文件
            if (!$file) {
                return $this->error('上传失败，请选择需要上传的文件');
            }
            $fileExt = strtolower($file->getOriginalExtension());
            $uploadDomain = new UploadDomain();
            if (!$uploadDomain->check($file->getOriginalName(), $file->getSize(), $fileExt, $file->getMime())) {
                return $this->error($uploadDomain->getError());
            }
            $saveName = date("Ymd") . "/" . md5(uniqid(mt_rand())) . ".{$fileExt}";
            /**
             * 不能以斜杆开头
             *  - 因为OSS存储时，不允许以/开头
             */
            $uploadDir = $this->request->param('dir');
            $object = $uploadDir ? $uploadDir . '/' . $saveName : $saveName;//设置了上传目录的上传文件名
            $filePath = $object; //保存到lt_files中的path

            //如果上传的是图片，验证图片的宽和高
            $accept = $this->request->param('accept');
            if ($accept == "image") {
                $width = $this->request->param('width');
                $height = $this->request->param('height');
                if ($width || $height) {
                    $imageInfo = getimagesize($file->getFileInfo());
                    if (($width && $imageInfo[0] > $width) || ($height && $imageInfo[1] > $height)) {
                        return $this->error('上传失败，图片尺寸要求宽：' . $width . 'px，高：' . $height . 'px，实际上传文件[ ' . $file->getOriginalName() . ' ]的尺寸为宽' . $imageInfo[0] . 'px，高：' . $imageInfo[1] . 'px');
                    }
                }
            }

            $inputValue = "";
            //上传至七牛云
            if ($uploadType == 'qiniu-kodo') {
                if(ConfServiceFacade::get('qiniuKodo.conf.switch') != 1){
                    return $this->error('未开启七牛云KODO存储，请到七牛云KODO配置中开启');
                }
                $kodoConf = [
                    'accessKey' => ConfServiceFacade::get('qiniuKodo.conf.accessKey'),
                    'secretKey' => ConfServiceFacade::get('qiniuKodo.conf.secretKey'),
                    'bucket' => ConfServiceFacade::get('qiniuKodo.conf.bucket'),
                    'domain' => ConfServiceFacade::get('qiniuKodo.conf.domain'),
                ];
                $kodo = Kodo::instance();
                $kodoRes = $kodo->upload($file->getPathname(), $object, $kodoConf);
                if ($kodoRes) {
                    $inputValue = $kodoRes;
                } else {
                    return $this->error($kodo->getError());
                }
            }

            //上传至阿里云
            if ($uploadType == 'ali-oss') {
                if(ConfServiceFacade::get('system.aliOss.switch') != 1){
                    return $this->error('未开启阿里云OSS存储，请到阿里云OSS配置中开启');
                }
                $ossConf = [
                    'accessKeyID' => ConfServiceFacade::get('aliOss.conf.accessKeyID'),
                    'accessKeySecret' => ConfServiceFacade::get('aliOss.conf.accessKeySecret'),
                    'bucket' => ConfServiceFacade::get('aliOss.conf.bucket'),
                    'endpoint' => ConfServiceFacade::get('aliOss.conf.endpoint'),
                    'domain' => ConfServiceFacade::get('aliOss.conf.domain'),
                ];
                $oss = Oss::instance();
                $ossUploadRes = $oss->upload($file->getPathname(), $object, $ossConf);
                if ($ossUploadRes) {
                    $inputValue = $ossUploadRes;
                } else {
                    return $this->error($oss->getError());
                }
            }

            //本地上传
            if ($uploadType == 'local') {
                $uploadDir = ltrim('/', $uploadDir);
                $saveName = Filesystem::putFileAs('/' . $uploadDir, $file, '/' . $object);
                $filePath = $saveName;
                $staticDomain = Env::get('domain.static');
                if ($staticDomain) {
                    $inputValue = $staticDomain . '/storage/' . $saveName;
                } else {
                    $inputValue = request()->domain() . '/static/storage/' . $saveName;
                }
            }

            //将inputValue存入lt_files表中
            $filesModel = new \app\model\Files();
            $fileId = $filesModel->insertGetId([
                'category_id' => (int)$this->request->param('file_category_id', 0),
                'name' => $file->getOriginalName(),
                'file_type' => $this->request->param('accept'),
                'path' => $filePath,
                'upload_type' => $uploadType,
                'size' => $file->getSize(),
                'ext' => $file->getExtension(),
                'create_admin_user_id' => 0,
                'update_admin_user_id' => 0,
                'create_time' => date('Y-m-d H:i:s'),
                'update_time' => date('Y-m-d H:i:s'),
            ]);

            return $this->success('上传成功', [
                'id' => $fileId,
                'path' => $inputValue,
                'name' => $file->getOriginalName(),
            ]);
        } catch (\Exception $e) {
            return $this->exceptionError($e);
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