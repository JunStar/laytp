<?php
namespace addons\qiniu\service;

use addons\qiniu\library\Qiniu\Auth;
use addons\qiniu\library\Qiniu\Storage\UploadManager;
use service\Service;
use think\facade\Config;

/**
 * Class Kodo
 * @package addons\qiniu\service
 */
class Kodo extends Service
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
     * @param $local_file_name
     * @param $save_file_name
     * @return bool
     * @throws Exception
     */
    public function upload($local_file_name, $save_file_name){
        try{
            $access_key = Config::get('addons.qiniu.access_key');
            $secret_key = Config::get('addons.qiniu.secret_key');
            $bucket = Config::get('addons.qiniu.bucket');
            $client = new Auth($access_key,$secret_key);
            $token = $client->uploadToken($bucket);
            $upload_mgr = new UploadManager();
            list($ret, $err) = $upload_mgr->putFile($token,$save_file_name,$local_file_name);
            if ($err !== null) {
                $this->setError('上传失败,'.$err);
                return false;
            } else {
                return Config::get('addons.qiniu.domain') . '/' . $save_file_name;
            }
        }catch (\Exception $e){
            $this->setError('上传失败,'.$e->getMessage());
            return false;
        }
    }
}