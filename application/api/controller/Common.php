<?php
namespace app\api\controller;

use controller\Api;
use library\QiniuYun;
use think\facade\Config;
use think\facade\Env;

/**
 * 公用接口
 */
class Common extends Api{
    /**
     * @ApiTitle    (文件上传)
     * @ApiSummary  (文件上传)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/common/upload)
     * @ApiParams   (name="file", type="file", required=true, description="文件")
     * @ApiReturnParams   (name="code", type="integer", required=true, sample="0")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="返回成功")
     * @ApiReturnParams   (name="data", type="object", sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}", description="扩展数据返回")
     * @ApiReturn
({
    'code':'1',
    'msg':'返回成功'
})
     */
    public function upload()
    {
        try{
            $file = $this->request->file('file'); // 获取上传的文件
            if(!$file){
                $this->error('请选择需要的上传文件');
            }

            $upload = Config::get('laytp.upload');
            preg_match('/(\d+)(\w+)/', $upload['maxsize'], $matches);
            $type = strtolower($matches[2]);
            $typeDict = ['b' => 0, 'k' => 1, 'kb' => 1, 'm' => 2, 'mb' => 2, 'gb' => 3, 'g' => 3];
            $size = (int)$upload['maxsize'] * pow(1024, isset($typeDict[$type]) ? $typeDict[$type] : 0);

            $fileInfo = $file->getInfo();
            $suffix = strtolower(pathinfo($fileInfo['name'], PATHINFO_EXTENSION));
            $suffix = $suffix && preg_match("/^[a-zA-Z0-9]+$/", $suffix) ? $suffix : 'file';

            $mimetypeArr = explode(',', strtolower($upload['mimetype']));
            $typeArr = explode('/', $fileInfo['type']);

            //禁止上传PHP和HTML文件
            if (in_array($fileInfo['type'], ['text/x-php', 'text/html']) || in_array($suffix, ['php', 'html', 'htm'])) {
                $this->error('文件类型被禁止上传');
            }
            //验证文件后缀
            if ($upload['mimetype'] !== '*' &&
                (
                    !in_array($suffix, $mimetypeArr)
                    || (stripos($typeArr[0] . '/', $upload['mimetype']) !== false && (!in_array($fileInfo['type'], $mimetypeArr) && !in_array($typeArr[0] . '/*', $mimetypeArr)))
                )
            ) {
                $this->error('文件类型被禁止上传');
            }

            $info = $file->validate(['size' => $size])->move('uploads'); // 移动文件到指定目录 没有则创建
            if($info->getError()){
                $this->error('上传失败，'.$info->getError());
            }else{
                $upload_way = Config::get('laytp.upload.way') ? Config::get('laytp.upload.way') : 'local';
                $save_name = str_replace('\\','/',$info->getSaveName());
                $file_name = '/uploads/'.$save_name;
                if($upload_way == 'local'){
                    $this->success('上传成功','',['data'=>$file_name]);
                }else if($upload_way == 'qiniu'){
                    $qiniu_yun = QiniuYun::instance();
                    if($qiniu_yun->upload(
                        Config::get('laytp.upload.qiniu_access_key')
                        ,Config::get('laytp.upload.qiniu_secret_key')
                        ,Config::get('laytp.upload.qiniu_bucket')
                        ,Env::get('root_path') . 'public' . $file_name
                        ,$file_name
                    )){
                        $this->success('上传成功',['url'=>$file_name]);
                    }else{
                        $this->error('上传失败,'.$qiniu_yun->getMessage());
                    }
                }
            }
        }catch (Exception $e){
            $this->error('上传失败',['data'=>$e->getMessage()]);
        }
    }
}