<?php
namespace app\admin\controller;

use library\DirFile;
use library\QiniuYun;
use OSS\OssClient;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use think\facade\Config;
use think\Controller;
use think\Db;
use think\Exception;
use think\facade\Env;
use think\facade\Session;

//集成controller，不走权限控制
class Ajax extends Controller
{
    public function initialize(){
        $admin_user_id = Session::get('admin_user_id');
        if(!$admin_user_id){
            $this->error('请先登录');
        }
    }

    //根据菜单ID获取面包屑
    public function get_crumbs(){
        $menu_id = $this->request->param('menu_id');
        $menus_where['is_menu'] = 1;
        $menus_where['is_hide'] = 0;
        $menus = model('admin/auth.Menu')->where($menus_where)->order(['pid'=>'asc','sort'=>'desc'])->select()->toArray();
        $crumbs = get_crumbs($menus, $menu_id);
        array_shift($crumbs[1]);
        $this->success('获取成功','',$crumbs[1]);
    }

    //新上传接口
    public function upload(){
        try{
            $qiniu_upload_radio = Config::get('laytp.upload.qiniu_radio');
            $aliyun_oss_upload_radio = Config::get('laytp.upload.aliyun_radio');
            $local_upload_radio = Config::get('laytp.upload.radio');
            if($qiniu_upload_radio == 1 && $aliyun_oss_upload_radio == 1 && $local_upload_radio == 1){
                $this->error('上传失败','','请开启一种上传方式');
            }

            $file = $this->request->file('file'); // 获取上传的文件
            if(!$file){
                $this->error('上传失败','','请选择需要的上传文件');
            }
            $info       = $file->getInfo();
            $path_info  = pathinfo($info['name']);
            $file_ext   = strtolower($path_info['extension']);
            $save_name  = date("Ymd") . "/" . md5(uniqid(mt_rand())) . ".{$file_ext}";
            $upload_dir = $this->request->param('upload_dir');
            $object     = $upload_dir . '/' . $save_name;//上传至阿里云或者七牛云的文件名

            $upload = Config::get('laytp.upload');
            preg_match('/(\d+)(\w+)/', $upload['maxsize'], $matches);
            $type = strtolower($matches[2]);
            $typeDict = ['b' => 0, 'k' => 1, 'kb' => 1, 'm' => 2, 'mb' => 2, 'gb' => 3, 'g' => 3];
            $size = (int)$upload['maxsize'] * pow(1024, isset($typeDict[$type]) ? $typeDict[$type] : 0);
            if ($info['size'] > (int) $size) {
                $this->error('上传失败','','文件大小超过'.$upload['maxsize']);
                return false;
            }

            $suffix = strtolower(pathinfo($info['name'], PATHINFO_EXTENSION));
            $suffix = $suffix && preg_match("/^[a-zA-Z0-9]+$/", $suffix) ? $suffix : 'file';

            $mimetypeArr = explode(',', strtolower($upload['mimetype']));
            $typeArr = explode('/', $info['type']);

            //禁止上传PHP和HTML文件
            if (in_array($info['type'], ['text/x-php', 'text/html']) || in_array($suffix, ['php', 'html', 'htm'])) {
                $this->error('上传失败','','文件类型被禁止上传');
            }
            //验证文件后缀
            if ($upload['mimetype'] !== '*' &&
                (
                    !in_array($suffix, $mimetypeArr)
                    || (stripos($typeArr[0] . '/', $upload['mimetype']) !== false && (!in_array($info['type'], $mimetypeArr) && !in_array($typeArr[0] . '/*', $mimetypeArr)))
                )
            ) {
                $this->error('上传失败','','文件类型被禁止上传');
            }

            $file_url = '';
            $local_file_url = '';
            //上传至七牛云
            if($qiniu_upload_radio == 2){
                $qiniu_yun = QiniuYun::instance();
                $qiniu_yun->upload(
                    Config::get('laytp.qiniu_kodo.access_key')
                    ,Config::get('laytp.qiniu_kodo.secret_key')
                    ,Config::get('laytp.qiniu_kodo.bucket')
                    ,$info['tmp_name']
                    ,$object
                );
                $file_url = Config::get('laytp.qiniu_kodo.domain') . '/' . $object;

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
                $local_file_url = '/uploads/'.$save_name;

                $add['file_type'] = $this->request->param('accept');
                $add['file_path'] = $local_file_url;
                model('Attachment')->create($add);
            }
            return $this->success('上传成功','',$file_url);
        }catch (Exception $e){
            $this->error('上传失败','',$e->getMessage());
        }
    }

    //旧的上传接口，待遗弃
    public function old_upload(){
        try{
            $file = $this->request->file('file'); // 获取上传的文件
            if(!$file){
                $this->error('上传失败','','请选择需要的上传文件');
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
                $this->error('上传失败','','文件类型被禁止上传');
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
                $this->error('上传失败','',$info->getError());
            }else{
                $add['file_type'] = $this->request->param('accept');
                $save_name = str_replace('\\','/',$info->getSaveName());
                $add['file_path'] = '/uploads/'.$save_name;
                model('Attachment')->create($add);
                $file_name = '/uploads/'.$save_name;
                $upload_way = Config::get('laytp.upload.way') ? Config::get('laytp.upload.way') : 'local';
                if($upload_way == 'local'){
                    $this->success('上传成功','',$file_name);
                }else if($upload_way == 'qiniu'){
                    $qiniu_yun = QiniuYun::instance();
                    if($qiniu_yun->upload(
                        Config::get('laytp.upload.qiniu_access_key')
                        ,Config::get('laytp.upload.qiniu_secret_key')
                        ,Config::get('laytp.upload.qiniu_bucket')
                        ,Env::get('root_path') . 'public' . $file_name
                        ,$file_name
                    )){
                        $this->success('上传成功','',$file_name);
                    }else{
                        $this->error('上传失败','',$qiniu_yun->getMessage());
                    }
                }
            }
        }catch (Exception $e){
            $this->error('上传失败','',$e->getMessage());
        }
    }

    public function editor_md_upload()
    {
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('editormd-image-file');

        // 移动到框架应用根目录/public/uploads/ 目录下
        if($file){
            $info = $file->move('./uploads');
            if($info){
                return json_encode(['url'=>'/uploads/' . $info->getSaveName(),'success'=>1]);
            }else{
                // 上传失败获取错误信息
                return $this->error($file->getError());
            }
        }
    }

    /**
     * 联动下拉框接口
     *  数据库pid记录上级ID，name记录名称
     */
    public function linkage(){
        $table_name = $this->request->param('table_name');
        $search_field = $this->request->param('search_field');
        $search_field_val = $this->request->param('search_field_val');
        $show_field = $this->request->param('show_field');
        $result = Db::table($table_name)->where($search_field, '=', $search_field_val)->field('id,'.$show_field)->select();
        $this->success('获取成功','',$result);
    }

    //文件下载
    public function download(){
        $file_url = base64_decode( $this->request->param('file_url') );
        $pathinfo = pathinfo($file_url);
        return download('.'.$file_url,'download.'.$pathinfo['extension']);
    }

    //弹窗展示视频
    public function show_video(){
        $assign['video_path'] = base64_decode( $this->request->param('path') );
        $this->assign($assign);
        return $this->fetch();
    }

    //清除缓存
    public function clear_cache(){
        $dir_cache = Env::get("root_path"). 'runtime' . DS . 'cache';
        if(is_dir($dir_cache)){
            DirFile::rmDirs($dir_cache);
        }
        $dir_log = Env::get("root_path"). 'runtime' . DS . 'log';
        if(is_dir($dir_log)){
            DirFile::rmDirs($dir_log);
        }
        $dir_temp = Env::get("root_path"). 'runtime' . DS . 'temp';
        if(is_dir($dir_temp)){
            DirFile::rmDirs($dir_temp);
        }
        $dir_addons = Env::get("root_path"). 'runtime' . DS . 'addons';
        if(is_dir($dir_addons)){
            DirFile::rmDirs($dir_addons);
        }
        $this->success('操作成功');
    }

    //锁屏
    public function lock_screen(){
        if($this->request->isAjax()){
            $password = $this->request->param('password');
            $admin_user_id = Session::get('admin_user_id');
            $password_hash = model('auth.User')->where('id','=',$admin_user_id)->value('password');
            if( !password_verify( $password, $password_hash ) )
            {
                $this->error('密码错误');
            }
            else
            {
                $this->success('密码正确');
            }
        }
        return $this->fetch();
    }
}
