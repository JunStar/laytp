<?php

namespace app\controller\admin\api;

use app\service\api\UserServiceFacade;
use app\validate\admin\user\Add;
use app\validate\admin\user\Edit;
use app\validate\admin\user\singleEdit;
use laytp\controller\Backend;
use laytp\library\CommonFun;
use laytp\library\Str;
use laytp\library\UploadDomain;
use think\facade\Db;

/**
 * 会员管理
 */
class User extends Backend
{
    protected $model;//当前模型对象
    protected $noNeedLogin = ['login'];
    protected $noNeedAuth = [];

    protected function _initialize()
    {
        $this->model = new \app\model\User();
    }

    //查看
    public function index()
    {
        $where = $this->buildSearchParams();
        $order = $this->buildOrder();
        $data = $this->model->where($where)->with(['avatar_file'])->field(UserServiceFacade::getAllowFields())->order($order);
        $allData = $this->request->param('all_data');
        if ($allData) {
            $data = $data->select();
        } else {
            $limit = $this->request->param('limit', 10);
            $data = $data->paginate($limit)->toArray();
        }

        return $this->success('数据获取成功', $data);
    }

    //添加
    public function add()
    {
        Db::startTrans();
        try {
            $post     = CommonFun::filterPostData($this->request->post());

            $validate = new Add();
            if (!$validate->check($post)) throw new \Exception($validate->getError());

            $post['password'] = Str::createPassword($post['password']);
            $result           = $this->model->save($post);
            if(!$result) throw new \Exception("添加失败");

            if ($post['role_ids']) {
                $roleIds = explode(',', $post['role_ids']);
                $data    = [];
                foreach ($roleIds as $k => $v) {
                    $data[] = ['admin_role_id' => $v, 'admin_user_id' => $this->model->id];
                }
                $roleUser = new \app\model\admin\role\User();
                $result   = $roleUser->saveAll($data);
                if(!$result) throw new \Exception("添加失败");
            }

            Db::commit();
            return $this->success('添加成功');
        } catch (\Exception $e) {
            Db::rollback();
            return $this->exceptionError($e);
        }
    }

    //查看详情
    public function info()
    {
        $id   = $this->request->param('id');
        $info = $this->model->with(['role_ids','avatar_file'])->findOrEmpty($id)->toArray();
        $data = \app\resource\admin\User::info($info);
        return $this->success('获取成功', $data);
    }

    //编辑
    public function edit()
    {
        Db::startTrans();
        try {
            $post = CommonFun::filterPostData($this->request->post());
            $user = $this->model->findOrEmpty($post['id']);
            if (!$user) throw new \Exception("id参数错误");

            $validate = new Edit();
            if (!$validate->check($post)) throw new \Exception($validate->getError());

            if ($post['password']) {
                $post['password'] = Str::createPassword($post['password']);
            } else {
                unset($post['password']);
                unset($post['re_password']);
            }
            $updateUser = $user->update($post);
            if (!$updateUser) throw new \Exception("用户基本信息保存失败");

            $userRole  = new \app\model\admin\role\User();
            $deleteRes = $userRole->where("admin_user_id", '=', $post['id'])->delete();
            if (!is_numeric($deleteRes)) throw new \Exception("用户角色删除失败");

            if ($post['role_ids']) {
                $roleIds = explode(',', $post['role_ids']);
                $data    = [];
                foreach ($roleIds as $k => $v) {
                    $data[] = ['admin_role_id' => $v, 'admin_user_id' => $user->id];
                }

                $saveAllRole = $userRole->saveAll($data);
                if (!$saveAllRole) throw new \Exception("用户角色保存失败");
            }

            Db::commit();
            return $this->success('操作成功');
        } catch (\Exception $e) {
            Db::rollback();
            return $this->exceptionError($e);
        }
    }

    //修改个人资料
    public function singleEdit(){
        $post = CommonFun::filterPostData($this->request->post());
        $validate = new singleEdit();
        if(!$validate->check($post)){
            return $this->error($validate->getError());
        }
        if(!$post['password']){
            unset($post['password']);
        }else{
            $post['password'] = Str::createPassword($post['password']);
        }
        $user = $this->model->with(['avatar_file'])->find($post['id']);
        if (!$user) {
            return $this->error('ID参数错误');
        }
        $res  = $user->update($post);
        if($res){
            return $this->success('操作成功');
        }else{
            return $this->error('操作失败');
        }
    }

    //删除
    public function del()
    {
        $ids = array_filter($this->request->param('ids'));
        if (!$ids) {
            return $this->error('参数ids不能为空');
        }
        if (in_array(1, $ids)) {
            return $this->error('不允许删除初始化用户');
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

    //设置状态
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

    //设置是否为超管
    public function setIsSuperManager()
    {
        $id       = $this->request->post('id');
        $fieldVal = $this->request->post('field_val');
        $isRecycle = $this->request->post('is_recycle');
        $update['is_super_manager'] = $fieldVal;
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