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
        $group = $this->request->param('group');
        $group = $group ? $group : 'basic';
        $this->assign('group', $group);

        $config = model('Sysconf')->where('group','=',$group)->select()->toArray();
        foreach($config as $k=>$v){
            $temp = json_decode( $v['content'], true );
            if(is_array($temp)){
                $config[$k]['content'] = $temp;
                $config[$k]['content_value'] = array_keys($temp);
                $config[$k]['content_text'] = array_values($temp);

            }
        }
        $this->assign('config', $config);

        return $this->fetch();
    }

    public function set_config(){
        if( $this->request->isAjax() && $this->request->isPost() ){
            $post = filterPostData($this->request->post("row/a"));
            $content = explode("\n", $post['content']);
            $return = [];
            foreach($content as $v){
                $temp = explode('|', $v);
                $return[$temp[0]] = $temp[1];
            }
            $post['content'] = json_encode($return);
            $update_res = model('Sysconf')->insert($post,true);
            if( $update_res || $update_res === 0 ){
                //写入配置文件
                $file_name = Env::get('app_path') . DS . 'admin' . DS . 'config' . DS . $post['group'] . '.php';
                $group_config = model('Sysconf')->where('group','=', $post['group'])->field('key,value,type')->select()->toArray();
                $result_config = [];
                foreach($group_config as $k=>$v){
                    if($v['value']){
                        if($v['type'] == 'array'){
                            $result_config[$v['key']] = json_decode( $v['value'], true );
                        }else{
                            $result_config[$v['key']] = $v['value'];
                        }
                    }
                }
                file_put_contents($file_name,"<?php\nreturn ".var_export($result_config,true).';');
                return $this->success('操作成功');
            }else if( $update_res === null ){
                return $this->error('操作失败');
            }
        }
    }
}