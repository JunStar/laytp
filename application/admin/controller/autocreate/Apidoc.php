<?php
/**
 * 一键生成Curd
 */
namespace app\admin\controller\autocreate;

use controller\Backend;

class Apidoc extends Backend
{
    public function index(){
        $where['group'] = 'apidoc';

        if( $this->request->isAjax() && $this->request->isPost() ){
            $post = filterPostData($this->request->post("row/a"));
            $update_res = model('Sysconf')->saveData($post,$where['group']);
            if( $update_res || $update_res === 0 ){
                $exec_res = exec_command('app\admin\command\Api',['--output='.$post['apidoc_name'].'.html']);
                if($exec_res['code']){
                    return $this->success('操作成功');
                }else{
                    return $this->error($exec_res['msg']);
                }
            }else if( $update_res === null ){
                return $this->error('操作失败');
            }
        }

        $conf = model('Sysconf')->where($where)->select()->toArray();
        foreach($conf as $v){
            $assign[$v['key']] = $v['value'];
        }
        $assign['apidoc_name'] = isset( $assign['apidoc_name'] ) ? $assign['apidoc_name'] : 'api';
        $this->assign($assign);
        return $this->fetch();
    }
}