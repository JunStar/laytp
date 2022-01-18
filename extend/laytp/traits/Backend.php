<?php

namespace laytp\traits;

use laytp\library\CommonFun;
use think\facade\Db;

trait Backend
{
    /**
     * 列表
     *  all_data参数的值为true时，表示查询表中所有数据集，否则进行分页查询
     * @return mixed
     */
    public function index()
    {
        $where   = $this->buildSearchParams();
        $order   = $this->buildOrder();
        $allData = $this->request->param('all_data');
        $data    = $this->model->where($where)->order($order);
        if ($allData) {
            $data = $data->select()->toArray();
        } else {
            $limit = $this->request->param('limit', 10);
            $data  = $data->paginate($limit)->toArray();
        }
        return $this->success('数据获取成功', $data);
    }

    //查看详情
    public function info()
    {
        $id   = $this->request->param('id');
        $info = $this->model->find($id);
        return $this->success('获取成功', $info);
    }

    //添加
    public function add()
    {
        $post = CommonFun::filterPostData($this->request->post());
        try{
            if ($this->model->create($post)) {
                return $this->success('添加成功', $post);
            } else {
                return $this->error('操作失败');
            }
        } catch (\Exception $e) {
            return $this->exceptionError($e);
        }
    }

    //编辑
    public function edit()
    {
        $id   = $this->request->param('id');
        $info = $this->model->find($id);
        if (!$info) {
            return $this->error('ID参数错误');
        }
        $post = CommonFun::filterPostData($this->request->post());
        foreach ($post as $k => $v) {
            $info->$k = $v;
        }
        try {
            $updateRes = $info->save();
            if ($updateRes) {
                return $this->success('编辑成功');
            } else {
                return $this->error('编辑失败');
            }
        } catch (\Exception $e) {
            return $this->exceptionError($e);
        }
    }

    //删除
    public function del()
    {
        $ids = array_filter($this->request->post('ids'));
        if (!$ids) {
            return $this->error('参数ids不能为空');
        }
        try{
            if ($this->model->destroy($ids)) {
                return $this->success('数据删除成功');
            } else {
                return $this->error('数据删除失败');
            }
        }catch (\Exception $e){
            return $this->exceptionError($e);
        }
    }

    //回收站
    public function recycle()
    {
        $where   = $this->buildSearchParams();
        $order   = $this->buildOrder();
        $allData = $this->request->param('all_data');
        $data    = $this->model->onlyTrashed()->order($order)->where($where);
        if ($allData) {
            $data = $data->select()->toArray();
        } else {
            $limit = $this->request->param('limit', 10);
            $data  = $data->paginate($limit)->toArray();
        }
        return $this->success('回收站数据获取成功', $data);
    }

    //还原
    public function restore()
    {
        $where[] = ['id', 'in', $this->request->post('ids')];
        if ($this->model->restore($where)) {
            return $this->success('数据成功还原');
        } else {
            return $this->error('操作失败');
        }
    }

    //真实删除
    public function trueDel()
    {
        Db::startTrans();
        try {
            $ids    = $this->request->post('ids');
            $res    = $this->model->onlyTrashed()->where('id', 'in', $ids)->select();
            foreach ($res as $key => $item) {
                $delRes = $item->force()->delete();
                if(!$delRes) throw new \Exception('删除失败');
            }

            Db::commit();
            return $this->success('数据已经彻底删除');
        } catch (\Exception $e) {
            Db::rollback();
            return $this->error('数据库异常，操作失败');
        }
    }
}