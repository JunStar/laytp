<?php
namespace app\admin\controller;

use think\Controller;
use think\Db;
use think\Exception;

//集成controller，不走权限控制
class Ajax extends Controller
{
    //上传接口
    public function upload(){
        try{
            $file = $this->request->file('file'); // 获取上传的文件
            $info = $file->move('uploads'); // 移动文件到指定目录 没有则创建
            $this->success('上传成功','',['data'=>'/uploads/'.$info->getSaveName()]);
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
}
