<?php

namespace app\resource\admin;

use laytp\Resource;

/**
 * 用户资源
 */
class User extends Resource
{
    /**
     * 用户首页列表需要将关联模型取到的role_ids转换成以逗号分隔的字符串
     * @param $data
     * @return mixed
     */
    static public function info($data)
    {
        $roleIdsArr = [];
        if($data){
            foreach ($data['role_ids'] as $k => $v) {
                $roleIdsArr[] = $v['admin_role_id'];
            }
            $data['role_ids'] = implode(',', $roleIdsArr);
        }
        return $data;
    }
}