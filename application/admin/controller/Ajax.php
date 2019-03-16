<?php
namespace app\admin\controller;

use think\Controller;
use think\Exception;

class Ajax extends Controller
{
    public function upload(){
        $file = $this->request->file('file'); // 获取上传的文件
        try{
            $info = $file->move('uploads'); // 移动文件到指定目录 没有则创建
            $this->success('上传成功','',['data'=>'/uploads/'.$info->getSaveName()]);
        }catch (Exception $e){
            $this->error('上传失败','',['data'=>$e->getMessage()]);
        }
    }
}
