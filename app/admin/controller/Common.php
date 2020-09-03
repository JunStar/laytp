<?php
namespace app\admin\controller;

use addons\aliyun_oss\service\Oss;
use addons\qiniu_kodo\service\Kodo;
use app\admin\model\Attachment;
use laytp\controller\Backend;
use think\Exception;
use think\facade\Config;

class Common extends Backend
{
    protected $noNeedAuth=['*'];

    //新上传接口
    public function upload($file=''){
        try{
            $qiniuUploadSwitch = Config::get('addons.qiniuKodo.switch');
            if(!$qiniuUploadSwitch){
                $qiniuUploadSwitch = 'close';
            }
            $aliyunOssUploadSwitch = Config::get('addons.aliyunOss.switch');
            if(!$aliyunOssUploadSwitch){
                $aliyunOssUploadSwitch = 'close';
            }
            $localUploadSwitch = Config::get('laytp.upload.switch');
            if($qiniuUploadSwitch == 'close' && $aliyunOssUploadSwitch == 'close' && $localUploadSwitch == 'close'){
                $this->error('上传失败,请开启一种上传方式');
            }
            $file = $file ? $file : ($this->request->file('file') ? : (is_array($this->request->file()) ? current($this->request->file()) : '')); // 获取上传的文件
            if(!$file){
                $this->error('上传失败,请选择需要的上传文件');
            }
            $info       = $file->getInfo();
            $path_info  = pathinfo($info['name']);
            $file_ext   = strtolower($path_info['extension']);
            $save_name  = date("Ymd") . "/" . md5(uniqid(mt_rand())) . ".{$file_ext}";
            $uploadDir = $this->request->param('upload_dir');
            $uploadDir = $uploadDir ? $uploadDir . '/' : '';

            $object     = $uploadDir . $save_name;//上传至阿里云或者七牛云的文件名

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

            $mimeTypeArr = explode(',', strtolower($upload['mimeType']));
            $typeArr = explode('/', $info['type']);

            //禁止上传PHP和HTML文件
            if (in_array($info['type'], ['text/x-php', 'text/html']) || in_array($suffix, ['php', 'html', 'htm'])) {
                $this->error('上传失败,文件类型被禁止上传');
            }
            //验证文件后缀
            if ($upload['mimeType'] !== '*' &&
                (
                    !in_array($suffix, $mimeTypeArr)
                    || (stripos($typeArr[0] . '/', $upload['mimeType']) !== false && (!in_array($info['type'], $mimeTypeArr) && !in_array($typeArr[0] . '/*', $mimeTypeArr)))
                )
            ) {
                $this->error('上传失败,文件类型被禁止上传');
            }

            $file_url = '';
            $local_file_url = '';
            //上传至七牛云
            if($qiniuUploadSwitch == 'open'){
                $qiniu_yun = Kodo::instance();
                $file_url = $qiniu_yun->upload($info['tmp_name'],$object);

                $add['file_type'] = $this->request->param('accept');
                $add['file_path'] = $file_url;
            }

            //上传至阿里云
            if($aliyunOssUploadSwitch == 'open'){
                $oss = Oss::instance();
                $file_url = $oss->upload($info['tmp_name'], $object);

                $add['file_type'] = $this->request->param('accept');
                $add['file_path'] = $file_url;
            }

            //本地上传
            if($localUploadSwitch == 'open'){
                $move_info = $file->move('uploads'); // 移动文件到指定目录 没有则创建
                $save_name = str_replace('\\','/',$move_info->getSaveName());
                $local_file_url = Config::get('laytp.upload.domain').'/uploads/'.$save_name;

                $add['file_type'] = $this->request->param('accept');
                $add['file_path'] = $local_file_url;
            }
            $this->success('上传成功','',$file_url ? $file_url : $local_file_url);
        }catch (Exception $e){
            $this->error('上传失败,'.$e->getMessage());
        }
    }
}
