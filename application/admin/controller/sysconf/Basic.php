<?php
namespace app\admin\controller\sysConf;

use controller\Backend;

class Basic extends Backend
{
    public function index()
    {
        $where['group'] = 'basic';

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
        $assign['site_name'] = isset( $assign['site_name'] ) ? $assign['site_name'] : '';
        $assign['record'] = isset( $assign['record'] ) ? $assign['record'] : '';
        $assign['static_version'] = isset( $assign['static_version'] ) ? $assign['static_version'] : '';
        $this->assign($assign);
        return $this->fetch();
    }
}