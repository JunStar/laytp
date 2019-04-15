<?php
/**
 * 省市区模型
 */
namespace app\admin\model;

use model\Backend;

class Sysconf extends Backend
{
    public function saveData($post){
        foreach($post as $k=>$v){
            $data['group'] = $post['group'];
            $data['key'] = $k;
            $data['value'] = $v;
            $this->insert($data,true);
        }
        return true;
    }
}