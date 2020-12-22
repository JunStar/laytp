<?php

/**
 * mdeditor上传图片接口
 */

namespace addons\mdeditor\api\controller;

use addons\aliyun_oss\service\Oss;
use addons\qiniu_kodo\service\Kodo;
use app\admin\model\Attachment;
use controller\Api;
use think\Exception;
use think\facade\Config;

class Common extends Api
{
    protected $no_need_login = ['*'];

    //上传接口
    public function upload($file = '')
    {
        try {
            $qiniu_upload_radio = Config::get('addons.qiniu_kodo.open_status');
            if (!$qiniu_upload_radio) {
                $qiniu_upload_radio = 'close';
            }
            $aliyun_oss_upload_radio = Config::get('addons.aliyun_oss.open_status');
            if (!$aliyun_oss_upload_radio) {
                $aliyun_oss_upload_radio = 'close';
            }
            $local_upload_radio = Config::get('laytp.upload.radio');
            if ($qiniu_upload_radio == 'close' && $aliyun_oss_upload_radio == 'close' && $local_upload_radio == 'close') {
                $this->error('上传失败,请开启一种上传方式');
            }
            $file = $file ? $file : ($this->request->file('file') ?: (is_array($this->request->file()) ? current($this->request->file()) : '')); // 获取上传的文件
            if (!$file) {
                $this->error('上传失败,请选择需要的上传文件');
            }
            $info       = $file->getInfo();
            $path_info  = pathinfo($info['name']);
            $file_ext   = strtolower($path_info['extension']);
            $save_name  = date("Ymd") . "/" . md5(uniqid(mt_rand())) . ".{$file_ext}";
            $upload_dir = $this->request->param('upload_dir');
            $upload_dir = $upload_dir ? $upload_dir . '/' : '';

            $object = $upload_dir . $save_name;//上传至阿里云或者七牛云的文件名

            $upload = Config::get('laytp.upload');
            preg_match('/(\d+)(\w+)/', $upload['maxsize'], $matches);
            $type     = strtolower($matches[2]);
            $typeDict = ['b' => 0, 'k' => 1, 'kb' => 1, 'm' => 2, 'mb' => 2, 'gb' => 3, 'g' => 3];
            $size     = (int)$upload['maxsize'] * pow(1024, isset($typeDict[$type]) ? $typeDict[$type] : 0);
            if ($info['size'] > (int)$size) {
                $this->error('上传失败,文件大小超过' . $upload['maxsize']);
                return false;
            }

            $suffix = strtolower(pathinfo($info['name'], PATHINFO_EXTENSION));
            $suffix = $suffix && preg_match("/^[a-zA-Z0-9]+$/", $suffix) ? $suffix : 'file';

            $mimetypeArr = explode(',', strtolower($upload['mimetype']));
            $typeArr     = explode('/', $info['type']);

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

            $file_url       = '';
            $local_file_url = '';
            //上传至七牛云
            if ($qiniu_upload_radio == 'open') {
                $qiniu_yun = Kodo::instance();
                $file_url  = $qiniu_yun->upload($info['tmp_name'], $object);

                $add['file_type'] = $this->request->param('accept');
                $add['file_path'] = $file_url;
                Attachment::create($add);
            }

            //上传至阿里云
            if ($aliyun_oss_upload_radio == 'open') {
                $oss      = Oss::instance();
                $file_url = $oss->upload($info['tmp_name'], $object);

                $add['file_type'] = $this->request->param('accept');
                $add['file_path'] = $file_url;
                Attachment::create($add);
            }

            //本地上传
            if ($local_upload_radio == 'open') {
                $move_info      = $file->move('uploads'); // 移动文件到指定目录 没有则创建
                $save_name      = str_replace('\\', '/', $move_info->getSaveName());
                $local_file_url = Config::get('laytp.upload.domain') . '/uploads/' . $save_name;

                $add['file_type'] = $this->request->param('accept');
                $add['file_path'] = $local_file_url;
                Attachment::create($add);
            }
            return json_encode(['url' => $file_url ? $file_url : $local_file_url, 'success' => 1]);
        } catch (Exception $e) {
            $this->error('上传失败,' . $e->getMessage());
        }
    }
}