<?php

namespace app\controller\admin;

use laytp\controller\Backend;
use laytp\library\CommonFun;
use laytp\library\UploadDomain;

/**
 * 官方举例，生成常规CURD
 */
class Test extends Backend
{

    /**
     * test模型对象
     * @var \app\model\Test
     */
    protected $model;
    protected $hasSoftDel=1;//是否拥有软删除功能

    protected $noNeedLogin = []; // 无需登录即可请求的方法
    protected $noNeedAuth = ['index', 'info']; // 无需鉴权即可请求的方法

    public function _initialize()
    {
        $this->model = new \app\model\Test();
    }

    
    //查看和搜索列表
    public function index(){
        $where = $this->buildSearchParams();
        $order = $this->buildOrder();
        $allData = $this->request->param('all_data');
        $data = $this->model->where($where)->order($order)->with(['category','province','city','district','img_file','video_file','music_file','file_file','selAdminUser']);
        if($allData){
            $data = $data->select()->toArray();
        }else{
            $limit = $this->request->param('limit', 10);
            $data = $data->paginate($limit)->toArray();
        }
        return $this->success('数据获取成功', $data);
    }

    
    //添加
    public function add()
    {
        $post = CommonFun::filterPostData($this->request->post());
        
        $post['ueditor'] = UploadDomain::delUploadDomain($post['ueditor'], 'ali-oss');
		$post['meditor'] = UploadDomain::delUploadDomain($post['meditor'], 'local');
        if ($this->model->create($post)) {
            return $this->success('添加成功', $post);
        } else {
            return $this->error('操作失败');
        }
    }

    
    //查看详情
    public function info()
    {
        $id   = $this->request->param('id');
        $info = $this->model->with(['img_file','video_file','music_file','file_file'])->find($id);
        return $this->success('获取成功', $info);
    }

    
    //编辑
    public function edit(){
        $id = $this->request->param('id');
        $info = $this->model->find($id);
        $post = CommonFun::filterPostData($this->request->post());
        
        $post['ueditor'] = UploadDomain::delUploadDomain($post['ueditor'], 'ali-oss');
		$post['meditor'] = UploadDomain::delUploadDomain($post['meditor'], 'local');
        foreach ($post as $k => $v) {
            $info->$k = $v;
        }
        try {
            $updateRes = $info->save();
            if ($updateRes) {
                return $this->success('编辑成功');
            } else {
                return $this->error('操作失败');
            }
        } catch (\Exception $e) {
            return $this->exceptionError($e);
        }
    }

    
    //回收站
    public function recycle(){
        $where = $this->buildSearchParams();
        $order = $this->buildOrder();
        $limit = $this->request->param('limit', 10);
        $data  = $this->model->onlyTrashed()
                 ->with(['category','province','city','district','selAdminUser'])
                 ->order($order)->where($where)->paginate($limit)->toArray();
        return $this->success('回收站数据获取成功', $data);
    }

    
    //设置单行输入框（可编辑）
    public function setInputEdit()
    {
        $id       = $this->request->post('id');
        $fieldVal = $this->request->post('field_val');
        $isRecycle = $this->request->post('is_recycle');
        $update['input_edit'] = $fieldVal;
        try {
            if($isRecycle) {
                $updateRes = $this->model->onlyTrashed()->where('id', '=', $id)->update($update);
            } else {
                $updateRes = $this->model->where('id', '=', $id)->update($update);
            }
            if ($updateRes) {
                return $this->success('操作成功');
            } else if ($updateRes === 0) {
                return $this->success('未作修改');
            } else {
                return $this->error('操作失败');
            }
        } catch (\Exception $e) {
            return $this->error('数据库异常，操作失败');
        }
    }
		
    //设置状态（开关单选）
    public function setStatus()
    {
        $id       = $this->request->post('id');
        $fieldVal = $this->request->post('field_val');
        $isRecycle = $this->request->post('is_recycle');
        $update['status'] = $fieldVal;
        try {
            if($isRecycle) {
                $updateRes = $this->model->onlyTrashed()->where('id', '=', $id)->update($update);
            } else {
                $updateRes = $this->model->where('id', '=', $id)->update($update);
            }
            if ($updateRes) {
                return $this->success('操作成功');
            } else if ($updateRes === 0) {
                return $this->success('未作修改');
            } else {
                return $this->error('操作失败');
            }
        } catch (\Exception $e) {
            return $this->error('数据库异常，操作失败');
        }
    }
}
