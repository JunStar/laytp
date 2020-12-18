<?php

namespace plugin\core\controller;

use plugin\aliyun_oss\service\Oss;
use plugin\qiniu_kodo\service\Kodo;
use plugin\core\model\Menu;
use plugin\core\service\AuthServiceFacade;
use plugin\core\service\UserServiceFacade;
use laytp\controller\Backend;
use laytp\library\Tree;
use think\facade\Config;
use think\facade\Filesystem;

class Common extends Backend
{
    protected $noNeedAuth = ['*'];
    protected $noNeedLogin = ['getLoginNeedCaptchaConf'];

    /**
     * 获取登录后台是否需要验证码的配置
     * 这个接口是后台登录界面使用的，给这个接口独立的访问权限，无需登录，无需鉴权
     */
    public function getLoginNeedCaptchaConf()
    {
        return $this->success('获取成功', Config::get('laytp.basic.loginNeedCaptcha'));
    }

    /**
     * 获取所有的缓存信息，包括系统配置，菜单数据，用户信息
     */
    public function getCache()
    {
        $menuTreeObj = Tree::instance();
        //获取所有权限节点列表，目前仅角色管理，设置用户权限能用到这个缓存数据
        $where[] = ['is_show', '=', 1];
        $menus   = Menu::where($where)->select();
        $menuTreeObj->init($menus);
        $treeNodes = $menuTreeObj->getTreeArray(0);
        //当前登录者拥有的权限节点列表数据
        $authList = AuthServiceFacade::getAuthList();
        $menuTreeObj->init($authList);
        //由列表数据转化成树形结构数据
        $authTree = $menuTreeObj->getTreeArray(0);
        $menuList = [];
        foreach ($authList as $auth) {
            ($auth['is_menu'] === 1) && $menuList[] = $auth;
        }
        $menuTreeObj->init($menuList);
        $menuTree = $menuTreeObj->getTreeArray(0);

        $authArr = [];
        foreach ($authList as $k => $v) {
            $authArr[] = trim($v['rule'], '/');
        }

        $authArr = array_filter(array_unique($authArr));
        sort($authArr);

        return $this->success('获取成功', [
            'sysConf' => Config::get('laytp'),
            'menu'    => ['treeNodes' => $treeNodes, 'menuTree' => $menuTree, 'menuList' => $menuList, 'authTree' => $authTree, 'authList' => $authArr],
            'user'    => UserServiceFacade::getUserInfo(),
        ]);
    }

    //上传接口
    public function upload()
    {
        try {
            $uploadType = Config::get("laytp.upload.type");
            $file       = $this->request->file('layTpUploadFile'); // 获取上传的文件
            if (!$file) {
                return $this->error('上传失败,请选择需要上传的文件');
            }
            $fileExt   = strtolower($file->getOriginalExtension());
            $saveName  = date("Ymd") . "/" . md5(uniqid(mt_rand())) . ".{$fileExt}";
            $uploadDir = $this->request->param('dir', '/');
            $object    = $uploadDir . $saveName;//上传至阿里云或者七牛云的文件名
            $upload    = Config::get('laytp.upload');
            $size      = $this->request->param('size', $upload['size']);
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
                if (!$canUpload) return $this->error('上传失败，允许上传的文件类型为' . implode(',', $mimeArr) . '，实际上传文件[ ' . $file->getOriginalName() . ' ]的文件类型为' . $file->getMime());
            }

            //如果上传的是图片，验证图片的宽和高
            $accept = $this->request->param('accept');
            if ($accept == "image") {
                $width  = $this->request->param('width');
                $height = $this->request->param('height');
                if ($width || $height) {
                    $imageInfo = getimagesize($file->getFileInfo());
                    if ($width && $imageInfo[0] > $width) {
                        return $this->error('上传失败，要求图片宽度最多' . $width . 'px，实际上传文件[ ' . $file->getOriginalName() . ' ]的宽度为' . $imageInfo[0] . 'px');
                    }
                    if ($height && $imageInfo[1] > $height) {
                        return $this->error('上传失败，要求图片高度最多' . $height . 'px，实际上传文件[ ' . $file->getOriginalName() . ' ]的高度为' . $imageInfo[1] . 'px');
                    }
                }
            }

            $inputValue = "";
            //上传至七牛云
            if ($uploadType == 'qiniu') {
                $qiniuYun = Kodo::instance();
                $qiniuYun->upload($file->getPathname(), $object);
                $inputValue = $object;
            }

            //上传至阿里云
            if ($uploadType == 'aliyun') {
                $oss = Oss::instance();
                $oss->upload($file->getPathname(), $object);
                $inputValue = $object;
            }

            //本地上传
            if ($uploadType == 'local') {
                $saveName   = Filesystem::putFileAs($uploadDir, $file, $object);
                $saveName   = str_replace('\\', '/', $saveName);
                $inputValue = '/storage/' . $saveName;
            }
            return $this->success('上传成功', $inputValue);
        } catch (\Exception $e) {
            return $this->error('上传失败，' . $e->getMessage());
        }
    }
}
