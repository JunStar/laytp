<?php
/**
 * 一键生成Api文档
 */
namespace addons\autocreate\admin\controller;

use controller\Backend;

class Apidoc extends Backend
{
    public function index(){
        $where['group'] = 'apidoc';

        if( $this->request->isAjax() && $this->request->isPost() ){
            $post = filterPostData($this->request->post("row/a"));
            $update_res = model('Sysconf')->saveData($post,$where['group']);
            if( $update_res || $update_res === 0 ){
                $exec_res = exec_command('app\admin\command\Api',['--output='.$post['apidoc_file_name'].'.html', '--title='.$post['apidoc_title']]);
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
        $assign['apidoc_file_name'] = isset( $assign['apidoc_file_name'] ) ? $assign['apidoc_file_name'] : 'api';
        $assign['apidoc_title'] = isset( $assign['apidoc_title'] ) ? $assign['apidoc_title'] : 'LayTp - api文档';
        $this->assign($assign);
        return $this->fetch();
    }
}