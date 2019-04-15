<?php
/**
 * 省市区模型
 */
namespace app\admin\model;

use model\Backend;

class Sysconf extends Backend
{
    public function addData($post){
        $data['group'] = $post['group'];
        $data['type'] = $post['type'];
        $data['key'] = $post['key'];
        $data['name'] = $post['name'];
        $data['value'] = $post['value'];
        $this->insert($data,true);
        return true;
    }

    public function saveData($post,$group){
        foreach($post as $k=>$v){
            $data['group'] = $group;
            $data['key'] = $k;
            $data['value'] = $v;
            $this->insert($data,true);
        }
        return true;
    }
}