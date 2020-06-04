<?php
namespace app\api\controller;

use addons\qiniu\service\Kodo;
use app\api\validate\email\Send;
use app\common\service\Email;
use app\common\service\Mobile;
use controller\Api;
use library\Random;
use think\facade\Config;

/**
 * 公用接口
 * @ApiWeigh (100)
 */
class Common extends Api{

    public $no_need_login = ['send_email_code','check_email_code','send_mobile_code'];

    /**
     * @ApiTitle    (文件上传)
     * @ApiSummary  (文件上传)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/common/upload)
     * @ApiHeaders  (name="token", type="string", required="true", description="用户登录后得到的Token")
     * @ApiParams   (name="file", type="file", required="true", description="文件")
     * @ApiParams   (name="upload_dir", type="string", required="false", description="上传目录，允许为空", sample="avatar")
     * @ApiReturnParams   (name="err_code", type="integer", description="错误码.0=没有错误，表示操作成功；1=常规错误码，客户端仅需提示msg；其他错误码与具体业务相关，其他错误码举例：10401。前端需要跳转至登录界面。")
     * @ApiReturnParams   (name="msg", type="string", description="返回描述")
     * @ApiReturnParams   (name="time", type="integer", description="请求时间，Unix时间戳，单位秒")
     * @ApiReturnParams   (name="data", type="null", description="null")
     * @ApiReturn
({
    'err_code':0,
    'msg':'上传成功',
    'time':'15632654875',
    'data':null
})
     */
    public function upload(){
        try{
            $qiniu_upload_radio = Config::get('addons.qiniu.open_status');
            if(!$qiniu_upload_radio){
                $qiniu_upload_radio = 'close';
            }
            $aliyun_oss_upload_radio = Config::get('laytp.upload.aliyun_radio');
            $local_upload_radio = Config::get('laytp.upload.radio');
            if($qiniu_upload_radio == 'close' && $aliyun_oss_upload_radio == 1 && $local_upload_radio == 1){
                $this->error('未开启上传方式');
            }

            $file = $this->request->file('file'); // 获取上传的文件
            if(!$file){
                $this->error('上传失败,请选择需要的上传文件');
            }
            $info       = $file->getInfo();
            $path_info  = pathinfo($info['name']);
            $file_ext   = strtolower($path_info['extension']);
            $save_name  = date("Ymd") . "/" . md5(uniqid(mt_rand())) . ".{$file_ext}";
            $upload_dir = $this->request->param('upload_dir');
            $upload_dir = $upload_dir ? $upload_dir . '/' : '';

            $object     = $upload_dir . $save_name;//上传至阿里云或者七牛云的文件名

            $upload = Config::get('laytp.upload');
            preg_match('/(\d+)(\w+)/', $upload['maxsize'], $matches);
            $type = strtolower($matches[2]);
            $typeDict = ['b' => 0, 'k' => 1, 'kb' => 1, 'm' => 2, 'mb' => 2, 'gb' => 3, 'g' => 3];
            $size = (int)$upload['maxsize'] * pow(1024, isset($typeDict[$type]) ? $typeDict[$type] : 0);
            if ($info['size'] > (int) $size) {
                $this->error('上传失败,文件大小超过'.$upload['maxsize']);
                return false;
            }

            $suffix = strtolower(pathinfo($info['name'], PATHINFO_EXTENSION));
            $suffix = $suffix && preg_match("/^[a-zA-Z0-9]+$/", $suffix) ? $suffix : 'file';

            $mimetypeArr = explode(',', strtolower($upload['mimetype']));
            $typeArr = explode('/', $info['type']);

            //禁止上传PHP和HTML文件
            if (in_array($info['type'], ['text/x-php', 'text/html']) || in_array($suffix, ['php', 'html', 'htm'])) {
                $this->error('上传失败,文件类型被禁止上传');
            }
            //验证文件后缀
            if ($upload['mimetype'] !== '*' &&
                (
                    !in_array($suffix, $mimetypeArr)
                    || (stripos($typeArr[0] . '/', $upload['mimetype']) !== false && (!in_array($info['type'], $mimetypeArr) && !in_array($typeArr[0] . '/*', $mimetypeArr)))
                )
            ) {
                $this->error('上传失败,文件类型被禁止上传');
            }

            $file_url = '';
            $local_file_url = '';
            //上传至七牛云
            if($qiniu_upload_radio == 'open'){
                $qiniu_yun = Kodo::instance();
                $qiniu_yun->upload($info['tmp_name'],$object);
                $file_url = Config::get('addons.qiniu.domain') . '/' . $object;

                $add['file_type'] = $this->request->param('accept');
                $add['file_path'] = $file_url;
                model('Attachment')->create($add);
            }

            //上传至阿里云
            if($aliyun_oss_upload_radio == 2){
                $ossClient = new OssClient(Config::get('laytp.aliyun_oss.access_key_id'), Config::get('laytp.aliyun_oss.access_key_secret'), Config::get('laytp.aliyun_oss.endpoint'));
                $ossClient->uploadFile(Config::get('laytp.aliyun_oss.bucket'), $object, $info['tmp_name']);
                $file_url = Config::get('laytp.aliyun_oss.bucket_url') . '/' . $object;

                $add['file_type'] = $this->request->param('accept');
                $add['file_path'] = $file_url;
                model('Attachment')->create($add);
            }

            //本地上传
            if($local_upload_radio == 2){
                $move_info = $file->move('uploads'); // 移动文件到指定目录 没有则创建
                $save_name = str_replace('\\','/',$move_info->getSaveName());
                $local_file_url = Config::get('laytp.upload.domain').'/uploads/'.$save_name;

                $add['file_type'] = $this->request->param('accept');
                $add['file_path'] = $local_file_url;
                model('Attachment')->create($add);
            }

            $this->success('上传成功',$file_url ? $file_url : $local_file_url);
        }catch (\Exception $e){
            $this->error('上传失败,'.$e->getMessage());
        }
    }

    /**
     * @ApiTitle    (发送邮箱验证码)
     * @ApiSummary  (发送邮箱验证码)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/common/send_email_code)
     * @ApiParams   (name="email", type="string", required="true", description="邮箱")
     * @ApiParams   (name="event", type="string", required="true", sample="register",description="事件名称")
     * @ApiReturnParams   (name="code", type="integer", required="true", sample="0")
     * @ApiReturnParams   (name="msg", type="string", required="true", sample="返回成功")
     * @ApiReturnParams   (name="data", type="object", sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}", description="扩展数据返回")
     * @ApiReturn
({
    'code':'1',
    'msg':'返回成功'
})
     */
    public function send_email_code(){
        if(!$this->request->isPost()){
            $this->error('请使用POST请求');
        }

        $params['email'] = $this->request->param('email');
        $params['event'] = $this->request->param('event');

        $validate = new Send();
        if($validate->check($params)){
            $email_service = new Email();
            if($email_service->send($params['email'],$params['event'],['code'=>Random::numeric()])){
                $this->success('发送成功');
            }else{
                $this->error('发送失败,'.$email_service->getError());
            }
        }else{
            $this->error('发送失败,'.$validate->getError());
        }
    }

    /**
     * @ApiTitle    (验证邮箱验证码)
     * @ApiSummary  (验证邮箱验证码)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/common/check_email_code)
     * @ApiParams   (name="email", type="string", required="true", description="邮箱")
     * @ApiParams   (name="event", type="string", required="true", sample="register",description="事件名称")
     * @ApiParams   (name="code", type="string", required="true", sample="register",description="邮箱验证码")
     * @ApiReturnParams   (name="code", type="integer", required="true", sample="0")
     * @ApiReturnParams   (name="msg", type="string", required="true", sample="返回成功")
     * @ApiReturnParams   (name="data", type="object", sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}", description="扩展数据返回")
     * @ApiReturn
    ({
    'code':'1',
    'msg':'返回成功'
    })
     */
    public function check_email_code(){
        if(!$this->request->isPost()){
            $this->error('请使用POST请求');
        }

        $params['email'] = $this->request->param('email');
        $params['event'] = $this->request->param('event');
        $params['code'] = $this->request->param('code');

        $email_service = new Email();
        if($email_service->checkCode($params['email'],$params['event'],$params['code'])){
            $this->success('验证成功');
        }else{
            $this->error('验证失败,'.$email_service->getError());
        }
    }

    /**
     * @ApiTitle    (发送手机验证码)
     * @ApiSummary  (发送手机验证码)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/common/send_mobile_code)
     * @ApiHeaders  (name="token", type="string", required="true", description="用户登录后得到的Token")
     * @ApiParams   (name="mobile", type="string", required="true", description="手机号码")
     * @ApiParams   (name="event", type="string", required="true", sample="reg_login",description="事件名称，check=验证手机号,bind=绑定手机号,reg_login=使用手机号+验证码的方式进行注册或登录")
     * @ApiReturnParams   (name="code", type="integer", required="true", sample="0")
     * @ApiReturnParams   (name="msg", type="string", required="true", sample="返回成功")
     * @ApiReturnParams   (name="time", type="integer", description="请求时间，Unix时间戳，单位秒")
     * @ApiReturnParams   (name="data", type="null", description="只会返回null")
     * @ApiReturn
    ({
    "code": 0,
    "msg": "发送失败,触发分钟级流控Permits:1",
    "time": 1584667483,
    "data": null
    })
     */
    public function send_mobile_code()
    {
        if(!$this->request->isPost()){
            $this->error('请使用POST请求');
        }

        $params['mobile'] = $this->request->param('mobile');
        $params['event'] = $this->request->param('event');

        $validate = new \app\api\validate\mobilemsg\Send();
        if($validate->check($params)){
            $mobile_service = new Mobile();
            if($mobile_service->send($params['mobile'],$params['event'],['code'=>Random::numeric()])){
                $this->success('发送成功');
            }else{
                $this->error('发送失败,'.$mobile_service->getError());
            }
        }else{
            $this->error('发送失败,'.$validate->getError());
        }
    }
}