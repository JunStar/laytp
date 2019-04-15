<?php
namespace app\admin\controller;

use controller\Backend;
use think\facade\Env;

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
            $update_res = model('Sysconf')->insert($post,true);
            if( $update_res || $update_res === 0 ){
                //写入配置文件
                $file_name = Env::get('app_path') . DS . 'admin' . DS . 'config' . DS . $post['group'] . '.php';
                $group_config = model('Sysconf')->where('group', $post['group'])->field('key,value')->select()->toArray();
                $result_config = [];
                foreach($group_config as $k=>$v){
                    $result_config[$v['key']] = $v['value'];
                }
                file_put_contents($file_name,"<?php\nreturn ".var_export($result_config,true).';');
                return $this->success('操作成功');
            }else if( $update_res === null ){
                return $this->error('操作失败');
            }
        }
    }
}