<?php

namespace plugin\core\controller\autocreate;

use laytp\controller\Backend;

class Api extends Backend
{
    //生成常规CURD
    public function create()
    {
        $api = new \plugin\core\library\autocreate\Api();
        if ($api->execute('api文档')) {
            return $this->success('生成成功');
        } else {
            return $this->error($api->getError());
        }
    }
}
