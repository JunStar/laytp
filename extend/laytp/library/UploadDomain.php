<?php

namespace laytp\library;

use app\model\Files;
use app\service\ConfServiceFacade;
use laytp\traits\Error;
use think\facade\Config;
use think\facade\Env;

/**
 * 上传文件域名前缀处理类
 */
class UploadDomain
{
    use Error;

    /**
     * 检测上传的文件
     * @param $fileName
     * @param $fileSize
     * @param $fileExt
     * @param $fileMime
     * @return bool
     */
    public function check($fileName, $fileSize, $fileExt, $fileMime)
    {
        $allowSize = request()->param('size', ConfServiceFacade::get('system.upload.size'));
        if (!$this->checkSize($fileSize, $allowSize)) {
            return false;
        }
        $allowExt = request()->param('mime', ConfServiceFacade::get('system.upload.mime'));
        if (!$this->checkExt($fileName, $fileExt, $allowExt)) {
            return false;
        }
        if (!$this->checkMime($fileMime)) {
            return false;
        }
        return true;
    }

    /**
     * 检测上传文件大小
     * @param $fileSize
     * @param string $allowSize
     * @return bool
     */
    public function checkSize($fileSize, $allowSize = '')
    {
        $allowSize = str_replace(' ', '', $allowSize);
        $allowUploadSizeConf = ConfServiceFacade::get('system.upload.size');
        // 没有配置，也没有传入上传文件大小限制参数，即程序上不限制上传文件大小
        if (!$allowUploadSizeConf && !$allowSize) {
            return true;
        }
        $maxSize = $allowSize ? $allowSize : $allowUploadSizeConf;
        preg_match('/(\d+)(\w+)/', $maxSize, $matches);
        $type = strtolower($matches[2]);
        $typeDict = ['b' => 0, 'k' => 1, 'kb' => 1, 'm' => 2, 'mb' => 2, 'gb' => 3, 'g' => 3];
        $maxSize = (int)$maxSize * pow(1024, isset($typeDict[$type]) ? $typeDict[$type] : 0);
        if ($fileSize > $maxSize) {
            $this->setError('上传失败，文件大小超过 ' . $allowSize);
            return false;
        }
        return true;
    }

    /**
     * 检测上传文件后缀
     * @param $fileName
     * @param $fileExt
     * @param string $allowExt
     * @return bool
     */
    public function checkExt($fileName, $fileExt, $allowExt = '')
    {
        $allowExtArr = explode(',', strtolower($allowExt));
        //禁止上传PHP和HTML文件
        if (in_array($fileExt, ['php', 'html', 'htm'])) {
            $this->setError('上传失败，禁止上传php和html文件');
            return false;
        }
        //验证文件后缀
        $allowExtConf = ConfServiceFacade::get('system.upload.mime');
        // 没有配置，也没有传入上传文件大小限制参数，即程序上不限制上传文件大小
        if (!$allowExtConf && !$allowExt) {
            $this->setError('上传失败，允许上传的文件后缀为空，请到系统配置 - 上传配置进行设置');
            return false;
        }
        if ($allowExt !== '*' && (!in_array($fileExt, $allowExtArr))) {
            $this->setError('上传失败，允许上传的文件后缀为' . $allowExt . '，实际上传文件[ ' . $fileName . ' ]的后缀为' . $fileExt);
            return false;
        }
        return true;
    }

    /**
     * 检测上传文件类型
     * @param $fileMime
     * @return bool
     */
    public function checkMime($fileMime)
    {
        //禁止上传PHP和HTML文件
        if (in_array($fileMime, ['text/x-php', 'text/html'])) {
            $this->setError('上传失败，禁止上传php和html文件');
            return false;
        }
        return true;
    }

    /**
     * 编辑器内容，添加上传文件的域名前缀，一般提供给数据模型层getFiledAttr方法调用
     * @param $string
     * @param $uploadType
     * @return string|string[]|null
     */
    static public function addUploadDomain($string, $uploadType = 'local')
    {
        $uploadDomain = self::getUploadDomain($uploadType, 'via');
        //ueditor编辑器正则替换所有的图片、视频、音频
        /*    $string       = preg_replace("/(<img .*?src=\")^(?!http)(.*?)(\".*?>+)/is", "\${1}{$uploadDomain}\${2}\${3}", $string);*/
        preg_match_all("/(<img .*?src=\")(.*?)(\".*?>+)/is", $string, $matches);
        if ($matches && isset($matches['2'])) {
            foreach ($matches['2'] as $item) {
                if (substr($item, 0, 4) != 'http') {
                    $string = str_replace($item, $uploadDomain . $item, $string);
                }
            }
        }
        $string = preg_replace("/(<video .*?src=\")(.*?)(\".*?>+)/is", "\${1}{$uploadDomain}\${2}\${3}", $string);
        $string = preg_replace("/(<source .*?src=\")(.*?)(\".*?>+)/is", "\${1}{$uploadDomain}\${2}\${3}", $string);

        //meditor编辑器正则替换所有的图片
        $string = preg_replace("/(\!\[\]\()(.*?)(\))/is", "\${1}{$uploadDomain}\${2}\${3}", $string);
        return $string;
    }

    /**
     * 编辑器内容，删除上传文件的域名前缀，一般提供给控制器添加和编辑方法调用
     * @param $string
     * @param $uploadType
     * @return string|string[]|null
     */
    static public function delUploadDomain($string, $uploadType = 'local')
    {
        $uploadDomain = addcslashes(self::getUploadDomain($uploadType, 'via'), '/');
        //ueditor编辑器正则替换所有的图片、视频、音频
        $string = preg_replace("/(<img .*?src=\"){$uploadDomain}(.*?)(\".*?>+)/is", "\${1}\${2}\${3}", $string);
        $string = preg_replace("/(<video .*?src=\"){$uploadDomain}(.*?)(\".*?>+)/is", "\${1}\${2}\${3}", $string);
        $string = preg_replace("/(<source .*?src=\"){$uploadDomain}(.*?)(\".*?>+)/is", "\${1}\${2}\${3}", $string);

        //meditor编辑器正则替换所有的图片
        $string = preg_replace("/(\!\[\]\(){$uploadDomain}(.*?)(\))/is", "\${1}\${2}\${3}", $string);
        return $string;
    }

    /**
     * 获取文件上传后访问的域名
     * @param string $uploadType 上传方式
     * @param string $viaServer 上传是否经过服务器
     * @return mixed|string
     */
    static public function getUploadDomain($uploadType = 'local', $viaServer = 'via')
    {
        $uploadDomain = '';
        if ($uploadType === 'local') {
            $uploadDomain = Env::get('domain.static', request()->domain() . '/static');
        } else if ($uploadType === 'qiniu-kodo') {
            $uploadDomain = Env::get('KODO.domain');
        } else if ($uploadType === 'ali-oss') {
            if ($viaServer === 'via') {
                $uploadDomain = Env::get('OSS.domain');
            } else {
                $uploadDomain = Env::get('STS.domain');
            }
        }
        return $uploadDomain;
    }

    // 设置上传方式为本地时的path，提供给当前类的singleAddUploadDomain方法调用
    static public function setLocalPath($data)
    {
        $uploadDomain = self::getUploadDomain($data['upload_type']);
        $uploadType = $data['upload_type'];
        $value = $data['path'];
        if($uploadType === 'local'){
            if($uploadDomain){
                $value = '/storage/' . $data['path'];
            }else{
                $value = '/static' . $value;
            }
        }
        return $value;
    }

    /**
     * 上传控件，单个，添加域名前缀，提供给Files模型层getPathAttr方法调用
     * @param $data
     * @return string
     */
    static public function singleAddUploadDomain($data)
    {
        $uploadDomain = self::getUploadDomain($data['upload_type'], $data['via_server']);
        $value = self::setLocalPath($data);
        return $uploadDomain . '/' . ltrim($value, '/');
    }

    /**
     * 上传控件，单个，保存path时，删除掉域名前缀，此方法仅FileController的add方法调用
     * @param $data
     * @return mixed
     */
    static public function singleDelUploadDomain($data)
    {
        $uploadDomain = self::getUploadDomain($data['upload_type'], $data['via_server']);
        return str_replace($uploadDomain, '', $data['path']);
    }

    /**
     * 上传控件，多个，整理成前端需要的数据，以[, ]组合的字符串
     * @param $fileIds
     * @return array|boolean
     */
    static public function multiJoin($fileIds)
    {
        try {
            $files = Files::where('id', 'in', $fileIds)->select()->toArray();
            $idArr = [];
            $pathArr = [];
            $filenameArr = [];
            foreach ($files as $v) {
                $idArr[] = $v['id'];
                $pathArr[] = $v['path'];
                $filenameArr[] = $v['name'];
            }
            return [
                'id' => $fileIds,
                'path' => join(', ', $pathArr),
                'filename' => join(', ', $filenameArr)
            ];
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 获取默认的用户头像
     * @return string
     */
    public static function getDefaultAvatar()
    {
        $path = '/admin/images/avatar.jpg';
        $uploadDomain = Env::get('domain.static', '/static');
        return $uploadDomain . $path;
    }
}