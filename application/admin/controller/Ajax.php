<?php
namespace app\admin\controller;

use library\DirFile;
use think\Controller;
use think\Db;
use think\Exception;
use think\facade\Env;
use think\facade\Session;

//集成controller，不走权限控制
class Ajax extends Controller
{
    //上传接口
    public function upload(){
        try{
            $file = $this->request->file('file'); // 获取上传的文件
            $info = $file->move('uploads'); // 移动文件到指定目录 没有则创建
            if($info->getError()){
                $this->error('上传失败，'.$info->getError());
            }else{
                $add['file_type'] = $this->request->param('accept');
                $add['file_path'] = '/uploads/'.$info->getSaveName();
                model('Attachment')->create($add);
                $this->success('上传成功','',['data'=>'/uploads/'.$info->getSaveName()]);
            }
        }catch (Exception $e){
            $this->error('上传失败','',['data'=>$e->getMessage()]);
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
            DirFile::deldir($dir_cache);
        }
        $dir_log = Env::get("root_path"). 'runtime' . DS . 'log';
        if(is_dir($dir_log)){
            DirFile::deldir($dir_log);
        }
        $dir_temp = Env::get("root_path"). 'runtime' . DS . 'temp';
        if(is_dir($dir_temp)){
            DirFile::deldir($dir_temp);
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
