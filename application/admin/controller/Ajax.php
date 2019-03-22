<?php
namespace app\admin\controller;

use think\Controller;
use think\Exception;

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

    //省市区接口
    public function area(){
        $parent_id = $this->request->post('parent_id') ? $this->request->post('parent_id') : 0;
        $result = model('Area')->where('pid', '=', $parent_id)->select();
        $this->success('获取成功','',$result);
    }

    //文件下载
    public function download(){
        $file_url = base64_decode( $this->request->param('file_url') );
        $pathinfo = pathinfo($file_url);
        return download('.'.$file_url,'download.'.$pathinfo['extension']);
    }
}
