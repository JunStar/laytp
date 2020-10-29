<?php

namespace plugin\core\resource\auth;

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
    static public function index($data)
    {
        foreach ($data['data'] as $k => $v) {
            $roleIdsArr = [];
            foreach ($data['data'][$k]['role_ids'] as $vv) {
                $roleIdsArr[] = $vv['plugin_core_role_id'];
            }
            $data['data'][$k]['role_ids'] = implode(',', $roleIdsArr);
        }
        return $data;
    }
}