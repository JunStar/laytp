<?php
/**
 * 省市区模型
 */
namespace app\admin\model;

use model\Backend;

class Sysconf extends Backend
{
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