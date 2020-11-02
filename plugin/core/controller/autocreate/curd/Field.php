<?php

namespace plugin\core\controller\autocreate\curd;

use laytp\controller\Backend;

class Field extends Backend
{
    protected $model;//当前模型对象
    protected $noNeedAuth = [];

    protected function _initialize()
    {
        $this->model = new \plugin\core\model\autocreate\curd\Field();
    }

    //添加
    public function add()
    {
        $post = $this->request->post();
        $post['addition'] = json_encode($post['addition'], JSON_UNESCAPED_UNICODE);
        if ($this->model->create($post)) {
            return $this->success('添加成功', $post);
        } else {
            return $this->error('操作失败');
        }
    }
}