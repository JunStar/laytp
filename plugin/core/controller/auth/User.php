<?php

namespace plugin\core\controller\auth;

use plugin\core\service\UserServiceFacade;
use plugin\core\validate\auth\user\Add;
use plugin\core\validate\auth\user\Edit;
use laytp\controller\Backend;
use think\facade\Db;

/**
 * 后台管理员控制器
 */
class User extends Backend
{
    protected $model;//当前模型对象
    protected $noNeedAuth = ['info'];

    protected function _initialize()
    {
        $this->model = new \plugin\core\model\User();
    }

    /**
     * 根据token获取登录用户信息
     * @param \plugin\core\service\User $user
     * @return \think\response\Json
     */
    public function info()
    {
        return $this->success('获取成功', UserServiceFacade::getUser());
    }

    //查看
    public function index()
    {
        $where = $this->buildSearchParams();
        $order = $this->buildOrder();
        $select_page = $this->request->param('select_page');
        $limit = $select_page ? $this->request->param('pageSize') : $this->request->param('limit');
        $data = $this->model->with(['role_ids'])->where($where)->order($order)->paginate($limit)->toArray();
        $data = \plugin\core\resource\auth\User::index($data);
        return $this->success('数据获取成功', $data);
    }

    //添加
    public function add()
    {
        Db::startTrans();
        try {
            $post = filter_post_data($this->request->post());
            $validate = new Add();
            if (!$validate->check($post)) {
                return $this->error($validate->getError());
            }
            $post['password'] = password_hash(md5($post['password']), PASSWORD_DEFAULT);
            $post['avatar'] = $post['avatar'] ? $post['avatar'] : '/static/plugin/core/img/default_avatar.png';
            $result[] = $this->model->save($post);

            if ($post['role_ids']) {
                $roleIds = explode(',', $post['role_ids']);
                $data = [];
                foreach ($roleIds as $k => $v) {
                    $data[] = ['plugin_core_role_id' => $v, 'plugin_core_user_id' => $this->model->id];
                }
                $roleUser = new \plugin\core\model\user\Role();
                $result[] = $roleUser->saveAll($data);
            }
            if (check_res($result)) {
                Db::commit();
                return $this->success('操作成功');
            } else {
                Db::rollback();
                return $this->error('操作失败');
            }
        } catch (\Exception $e) {
            Db::rollback();
            return $this->error($e->getMessage());
        }
    }

    //编辑
    public function edit()
    {
        Db::startTrans();
        try {
            $post = filter_post_data($this->request->post());
            $user = $this->model->find($post['id']);
            if (!$user) {
                return $this->error('ID参数错误');
            }
            $validate = new Edit();
            if (!$validate->check($post)) {
                return $this->error($validate->getError());
            }
            if ($post['password']) {
                $post['password'] = password_hash(md5($post['password']), PASSWORD_DEFAULT);
            } else {
                unset($post['password']);
                unset($post['re_password']);
            }
            $result[] = $user->update($post);
            $roleUser = new \plugin\core\model\user\Role();
            $deleteRes = $roleUser->where("plugin_core_user_id", '=', $post['id'])->delete();
            $result[] = ($deleteRes || $deleteRes === 0) ? true : false;
            if ($post['role_ids']) {
                $roleIds = explode(',', $post['role_ids']);
                $data = [];
                foreach ($roleIds as $k => $v) {
                    $data[] = ['plugin_core_role_id' => $v, 'plugin_core_user_id' => $user->id];
                }

                $result[] = $roleUser->saveAll($data);
            }
            if (check_res($result)) {
                Db::commit();
                return $this->success('操作成功');
            } else {
                Db::rollback();
                return $this->error('操作失败');
            }
        } catch (\Exception $e) {
            Db::rollback();
            return $this->error('操作失败');
        }
    }

    //删除
    public function del()
    {
        $ids = array_filter(explode(',', $this->request->param('ids')));
        if (in_array(1, $ids)) {
            return $this->error('不允许删除初始化用户');
        }
        return parent::del();
    }

    //表格编辑
    public function tableEdit()
    {
        $field = $this->request->param('field');
        $field_val = $this->request->param('field_val');
        $ids = $this->request->param('ids');
        $idsArr = explode(',', $ids);
        if (in_array(1, $idsArr)) {
            if ($field == 'is_super_manager' && $field_val == 2) {
                return $this->error('不允许将初始化账号设置成非超管');
            }
            if ($field == 'status' && $field_val == 2) {
                return $this->error('不允许禁用初始化账号');
            }
        }

        return parent::tableEdit();
    }
}