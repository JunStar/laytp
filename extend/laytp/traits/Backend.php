<?php

namespace laytp\traits;

use think\facade\Db;

trait Backend
{
    /**
     * 查看
     *  no_page参数表示是否进行分页，默认不传表示进行分页查询
     * @return mixed
     */
    public function index()
    {
        $where = $this->buildSearchParams();
        $order = $this->buildOrder();
        $noPage = $this->request->param('no_page');
        $data = $this->model->where($where)->order($order);
        if ($noPage) {
            $data = $data->select();
        }else{
            $limit = $this->request->param('limit', 10);
            $data = $data->paginate($limit);
        }
        return $this->success('数据获取成功', $data);
    }

    //添加
    public function add()
    {
        $post = filter_post_data($this->request->post());
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
        $post = filter_post_data($this->request->post());
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

    //表格编辑
    public function tableEdit()
    {
        $ids = explode(',', $this->request->param('ids'));
        $field = $this->request->param('field');
        $field_val = $this->request->param('field_val');
        $save[$field] = $field_val;
        try {
            $updateRes = $this->model->where('id', 'in', $ids)->update($save);
            if ($updateRes) {
                return $this->success('操作成功');
            } else if ($updateRes === 0) {
                return $this->success('未做修改');
            } else if ($updateRes === null) {
                return $this->error('操作失败');
            }
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    //回收站表格编辑
    public function recycleTableEdit()
    {
        $ids = explode(',', $this->request->param('ids'));
        $field = $this->request->param('field');
        $field_val = $this->request->param('field_val');
        $save[$field] = $field_val;
        try {
            $updateRes = $this->model->onlyTrashed()->where('id', 'in', $ids)->update($save);
            if ($updateRes) {
                return $this->success('操作成功');
            } else if ($updateRes === 0) {
                return $this->success('未做修改');
            } else if ($updateRes === null) {
                return $this->error('操作失败');
            }
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    //删除
    public function del()
    {
        $ids = array_filter(explode(',', $this->request->param('ids')));
        if (!$ids) {
            return $this->error('参数ids不能为空');
        }
        if ($this->model->destroy($ids)) {
            return $this->success('数据删除成功');
        } else {
            return $this->error('数据删除失败');
        }
    }

    //回收站
    public function recycle()
    {
        $where = $this->buildSearchParams();
        $order = $this->buildOrder();
        $limit = $this->request->param('limit', 10);
        $data = $this->model->onlyTrashed()->order($order)->where($where)->paginate($limit);
        return $this->success('回收站数据获取成功', $data);
    }

    //还原
    public function restore()
    {
        $where[] = ['id', 'in', $this->request->param('ids')];
        if ($this->model->restore($where)) {
            return $this->success('数据成功还原');
        } else {
            return $this->error('操作失败');
        }
    }

    //彻底删除
    public function trueDel()
    {
        Db::startTrans();
        try {
            $ids = $this->request->param('ids');
            $res = $this->model->onlyTrashed()->where('id', 'in', $ids)->select();
            $result = [];
            foreach ($res as $key => $item) {
                $result[] = $item->force()->delete();
            }
            if (check_res($result)) {
                Db::commit();
                return $this->success('数据已经彻底删除');
            } else {
                Db::rollback();
                return $this->error('数据彻底删除失败');
            }
        } catch (\Exception $e) {
            Db::rollback();
            return $this->error($e->getMessage());
        }
    }
}