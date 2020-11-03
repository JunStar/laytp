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
        if (array_key_exists('addition', $post)) {
            $post['addition'] = json_encode($post['addition'], JSON_UNESCAPED_UNICODE);
        } else {
            $post['addition'] = '';
        }
        if ($this->model->create($post)) {
            return $this->success('添加成功', $post);
        } else {
            return $this->error('操作失败');
        }
    }

    //编辑
    public function edit()
    {
        $id = $this->request->param('id');
        $info = $this->model->find($id);
        $post = $this->request->post();
        if (array_key_exists('addition', $post)) {
            $post['addition'] = json_encode($post['addition'], JSON_UNESCAPED_UNICODE);
        } else {
            $post['addition'] = '';
        }
        foreach ($post as $k => $v) {
            $info->$k = $v;
        }
        try {
            $updateRes = $info->save();
            if ($updateRes) {
                return $this->success('编辑成功');
            } else if ($updateRes === 0) {
                return $this->success('未做修改');
            } else if ($updateRes === null) {
                return $this->error('操作失败');
            }
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}