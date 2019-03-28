<?php
namespace app\admin\controller\sysConf;

use controller\Backend;

class Role extends Backend
{
    public function index()
    {
        $where['group'] = 'role';

        if( $this->request->isAjax() && $this->request->isPost() ){
            $post = filterPostData($this->request->post("row/a"));
            $update_res = model('Sysconf')->saveData($post,$where['group']);
            if( $update_res || $update_res === 0 ){
                return $this->success('操作成功');
            }else if( $update_res === null ){
                return $this->error('操作失败');
            }
        }

        $conf = model('Sysconf')->where($where)->select()->toArray();
        foreach($conf as $v){
            $assign[$v['key']] = $v['value'];
        }
        $assign['bt_field'] = isset( $assign['bt_field'] ) ? $assign['bt_field'] : '';
        $assign['open_content_range'] = isset( $assign['open_content_range'] ) ? $assign['open_content_range'] : '';
        $this->assign($assign);
        return $this->fetch();
    }
}