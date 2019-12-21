<?php
namespace library;

use Qiniu\Auth;
use Qiniu\Storage\UploadManager;

/**
 * Class QiniuYun
 */
class QiniuYun
{
    protected static $instance;

    /**
     * 单例
     * @return static
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    /**
     * 上传文件
     * @param $access_key
     * @param $secret_key
     * @param $bucket
     * @param $local_file_name
     * @param $save_file_name
     * @return bool
     * @throws Exception
     */
    public function upload($access_key, $secret_key, $bucket, $local_file_name, $save_file_name){
        try{
            $client = new Auth($access_key,$secret_key);
            $token = $client->uploadToken($bucket);
            $upload_mgr = new UploadManager();
            list($ret, $err) = $upload_mgr->putFile($token,$save_file_name,$local_file_name);
            if ($err !== null) {
                $this->setMessage('上传失败,'.$err);
                return false;
            } else {
                return true;
            }
        }catch (\Exception $e){
            $this->setMessage('上传失败,'.$e->getMessage());
            return false;
        }
    }

    public function getMessage(){
        return $this->_message;
    }

    public function setMessage($message){
        $this->_message = $message;
    }
}