<?php
namespace app\admin\controller;

use addons\aliyun_oss\service\Oss;
use addons\qiniu_kodo\service\Kodo;
use app\admin\model\Attachment;
use library\DirFile;
use library\Token;
use think\facade\Config;
use think\Controller;
use think\Db;
use think\Exception;
use think\facade\Cookie;
use think\facade\Env;

//继承controller，不走权限控制
class Ajax extends Controller
{
    //虽然不走权限控制，但是也要登录
    public function initialize(){
        $token = $this->request->param('admin_token', $this->request->param('admin_token', Cookie::get('admin_token')));
        $data = Token::get($token);
        if(!$data['user_id']){
            $this->error('请先登录');
        }
    }

    //根据菜单ID获取面包屑
    public function get_crumbs(){
        $menu_id = $this->request->param('menu_id');
        $menus_where['is_menu'] = 1;
        $menus_where['is_hide'] = 0;
        $menus = model('admin/auth.Menu')->where($menus_where)->order(['pid'=>'asc','sort'=>'desc'])->select()->toArray();
        $crumbs = get_crumbs($menus, $menu_id,true);
        array_shift($crumbs[1]);
        $this->success('获取成功','',$crumbs[1]);
    }

    //新上传接口
    public function upload($file=''){
        try{
            $qiniu_upload_radio = Config::get('addons.qiniu_kodo.open_status');
            if(!$qiniu_upload_radio){
                $qiniu_upload_radio = 'close';
            }
            $aliyun_oss_upload_radio = Config::get('addons.aliyun_oss.open_status');
            if(!$aliyun_oss_upload_radio){
                $aliyun_oss_upload_radio = 'close';
            }
            $local_upload_radio = Config::get('laytp.upload.radio');
            if($qiniu_upload_radio == 'close' && $aliyun_oss_upload_radio == 'close' && $local_upload_radio == 'close'){
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
                $file_url = $qiniu_yun->upload($info['tmp_name'],$object);

                $add['file_type'] = $this->request->param('accept');
                $add['file_path'] = $file_url;
                Attachment::create($add);
            }

            //上传至阿里云
            if($aliyun_oss_upload_radio == 'open'){
                $oss = Oss::instance();
                $file_url = $oss->upload($info['tmp_name'], $object);

                $add['file_type'] = $this->request->param('accept');
                $add['file_path'] = $file_url;
                Attachment::create($add);
            }

            //本地上传
            if($local_upload_radio == 'open'){
                $move_info = $file->move('uploads'); // 移动文件到指定目录 没有则创建
                $save_name = str_replace('\\','/',$move_info->getSaveName());
                $local_file_url = Config::get('laytp.upload.domain').'/uploads/'.$save_name;

                $add['file_type'] = $this->request->param('accept');
                $add['file_path'] = $local_file_url;
                Attachment::create($add);
            }
            $this->success('上传成功','',$file_url ? $file_url : $local_file_url);
        }catch (Exception $e){
            $this->error('上传失败,'.$e->getMessage());
        }
    }

    //ueditor编辑器上传
    public function ueditor_upload(){
        $file = request()->file('editormd-image-file');

        $this->upload($file);
    }

    public function editor_md_upload()
    {
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('editormd-image-file');

        $this->upload($file);
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

    //文件下载或者预览
    public function download(){
        $file_url = base64_decode( $this->request->param('file_url') );
        $pathinfo = pathinfo($file_url);
        if(substr($file_url,0,4) == 'http'){
            header('location:'.$file_url);
        }else{
            return download('.'.$file_url,'download.'.$pathinfo['extension']);
        }
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
            $token = $this->request->param('admin_token', $this->request->param('admin_token', Cookie::get('admin_token')));
            $data = Token::get($token);
            $password_hash = model('auth.User')->where('id','=',$data['user_id'])->value('password');
            if( !password_verify( $password, $password_hash ) )
            {
                $this->error('密码错误');
            }
            else
            {
                $this->success('密码正确');
            }
        }
        $this->assign('theme','2');
        return $this->fetch();
    }
}
