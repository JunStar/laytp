<?php
namespace app\admin\controller;

use controller\Backend;

class Sysconf extends Backend
{
    protected $model;

    public function initialize()
    {
        parent::initialize();
        $this->model = new \app\admin\model\Sysconf();
    }

    public function index(){
        return $this->fetch();
    }

    public function set_config(){
        if( $this->request->isAjax() && $this->request->isPost() ){
            $post = filterPostData($this->request->post("row/a"));
            $update_res = model('Sysconf')->saveData($post);
            if( $update_res || $update_res === 0 ){
                //写入配置文件
                $file_name = Env::get('app_path') . DS . 'admin' . DS . 'config' . DS . $post['group'] . '.php';
                file_put_contents($file_name,"<?php\nreturn ".var_export($post,true).';');
                return $this->success('操作成功');
            }else if( $update_res === null ){
                return $this->error('操作失败');
            }
        }
    }
}