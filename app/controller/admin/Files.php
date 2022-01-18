<?php

namespace app\controller\admin;

use laytp\controller\Backend;
use app\service\admin\UserServiceFacade;
use laytp\library\CommonFun;
use laytp\library\UploadDomain;

/**
 * 附件管理
 */
class Files extends Backend
{
    /**
     * files模型对象
     * @var \app\model\Files
     */
    protected $model;
	public $hasSoftDel=1;//是否拥有软删除功能

    public function initialize()
    {
        parent::initialize();
        $this->model = new \app\model\Files();
    }
    
    //查看
    public function index(){
        $where = $this->buildSearchParams();
        $order = $this->buildOrder();
        $allData = $this->request->param('all_data');
        $data = $this->model->where($where)->order($order)->with(['category','createAdminUser','updateAdminUser']);
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
        return $this->error('当前版本暂时不支持在附件管理中添加附件');
        $post = CommonFun::filterPostData($this->request->post());
        $post['create_admin_user_id'] = UserServiceFacade::getUser()->id;
        $post['update_admin_user_id'] = UserServiceFacade::getUser()->id;
        $post['path'] = UploadDomain::singleDelUploadDomain($post['path']);
        if ($this->model->create($post)) {
            return $this->success('添加成功', $post);
        } else {
            return $this->error('操作失败');
        }
    }

    //不经过服务器方式上传成功后，回调的ajax请求地址，将上传成功的文件信息存入表中
    public function unViaSave(){
        $post = $this->request->post();
        $post['create_admin_user_id'] = UserServiceFacade::getUser()->id;
        $post['update_admin_user_id'] = UserServiceFacade::getUser()->id;
        $post['create_time'] = date('Y-m-d H:i:s');
        $post['update_time'] = date('Y-m-d H:i:s');
        $post['id'] = $this->model->insertGetId($post);
        if ($post['id']) {
            return $this->success('添加成功', $post);
        } else {
            return $this->error('操作失败');
        }
    }
    
    //编辑
    public function edit(){
        return $this->error('当前版本暂时不支持在附件管理中编辑附件');
        $id = $this->request->param('id');
        $info = $this->model->find($id);
        $post = CommonFun::filterPostData($this->request->post());
        $post['update_admin_user_id'] = UserServiceFacade::getUser()->id;
        $post['path'] = UploadDomain::singleDelUploadDomain($post['path']);
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
            return $this->exceptionError($e);
        }
    }

    //删除
    public function del(){
        $id = $this->request->param('id');
        $info = $this->model->find($id);
        $post = CommonFun::filterPostData($this->request->post());
        $post['update_admin_user_id'] = UserServiceFacade::getUser()->id;
        $post['path'] = UploadDomain::singleDelUploadDomain($post['path']);
        foreach ($post as $k => $v) {
            $info->$k = $v;
        }
        try {
            $updateRes = $info->save();
            if(!$updateRes){
                return $this->error('操作失败');
            }else{
                return $this->success('编辑成功');
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
                 ->with(['category','createAdminUser','updateAdminUser'])
                 ->order($order)->where($where)->paginate($limit)->toArray();
        return $this->success('回收站数据获取成功', $data);
    }
}
