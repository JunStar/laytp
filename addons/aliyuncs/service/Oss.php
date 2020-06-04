<?php
namespace addons\aliyuncs\service;

use OSS\OssClient;
use service\Service;
use think\facade\Config;

class Oss extends Service
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
            $endpoint = Config::get('addons.qiniu.bucket');
            $ossClient = new OssClient($access_key, $secret_key, $endpoint);
            $ossClient->uploadFile(Config::get('laytp.aliyun_oss.bucket'), $save_file_name, $local_file_name);
            return true;
        }catch (\Exception $e){
            $this->setError('上传失败,'.$e->getMessage());
            return false;
        }
    }
}