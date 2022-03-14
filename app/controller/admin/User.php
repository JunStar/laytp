<?php

namespace app\controller\admin;

use app\model\admin\login\Log;
use app\service\admin\AuthServiceFacade;
use app\service\admin\UserServiceFacade;
use app\validate\admin\user\Add;
use app\validate\admin\user\Edit;
use app\validate\admin\user\Login;
use app\validate\admin\user\singleEdit;
use laytp\controller\Backend;
use laytp\library\CommonFun;
use laytp\library\Random;
use laytp\library\Str;
use laytp\library\Token;
use think\facade\Config;
use think\facade\Db;
use think\facade\Cache;

/**
 * 后台管理员控制器
 */
class User extends Backend
{
    protected $model;
    //当前模型对象
    protected $noNeedLogin = ['login', 'logout'];
    protected $noNeedAuth = ['loginInfo', 'singleEdit'];

    protected function _initialize()
    {
        $this->model = new \app\model\admin\User();
    }

    public function login()
    {
        //获取表单提交数据
        $param = $this->request->post();
        //防止密码爆破
        $fail = Cache::get('laytp-admin-login-num-' . $param['username'], 1);
        if ($fail >= 5) return $this->error('失败次数过多，请三分钟后再试');
        //验证表单提交
        $validate = new Login();
        if (!$validate->check($param)) {
            $param['password'] = '******';
            //登录失败也不记录用户密码
            Log::create([
                'login_status' => 2,
                'admin_id' => 0,
                'request_body' => json_encode($param),
                'request_header' => json_encode($this->request->header()),
                'ip' => $this->request->ip(),
                'create_time' => date('Y-m-d H:i:s'),
            ]);
            Cache::set('laytp-admin-login-num-' .$param['username'], $fail + 1, 180);
            return $this->error($validate->getError());
        }
        //设置登录信息
        $loginUserInfo = \app\model\admin\User::where('username', '=', $param['username'])
            ->with(['avatar_file'])->field(UserServiceFacade::getAllowFields())->findOrEmpty();
        $loginUserInfo->login_time = date('Y-m-d H:i:s');
        $loginUserInfo->login_ip = $this->request->ip();
        $loginUserInfo->save();
        $userId = $loginUserInfo['id'];
        $token = Random::uuid();
        $loginUserInfo['token'] = $token;
        Token::set($token, $userId, 24 * 60 * 60 * 3);

        $param['password'] = '******';
        //登录成功不记录用户密码
        Log::create([
            'login_status' => 1,
            'admin_id' => $userId,
            'request_body' => json_encode($param),
            'request_header' => json_encode($this->request->header()),
            'ip' => $this->request->ip(),
            'create_time' => date('Y-m-d H:i:s'),
        ]);

        $authList = AuthServiceFacade::getAuthList($userId);
        return $this->success('登录成功', [
            'user' => $loginUserInfo,
            'authList' => $authList,
            'pluginConf' => Config::get('plugin'),
        ]);
    }

    public function loginInfo()
    {
        $loginUserInfo = UserServiceFacade::getUserInfo();
        $authList = AuthServiceFacade::getAuthList($loginUserInfo['id']);

        return $this->success('获取成功', [
            'user' => $loginUserInfo,
            'authList' => $authList,
            'pluginConf' => ['editor' => ['ueditor', 'meditor']],
        ]);
    }

    //退出登录

    public function logout()
    {
        $token = $this->request->header('laytp-admin-token', $this->request->cookie('laytpAdminToken'));
        Token::delete($token);
        return $this->success('退出成功');
    }

    //查看

    public function index()
    {
        $where = $this->buildSearchParams();
        $order = $this->buildOrder();
        $data = $this->model->where($where)->with(['avatar_file'])->order($order);
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
            $post = CommonFun::filterPostData($this->request->post());
            $validate = new Add();
            if (!$validate->check($post)) throw new \Exception($validate->getError());

            $post['password'] = Str::createPassword($post['password']);
            $saveRes = $this->model->save($post);
            if (!$saveRes) throw new \Exception('保存基础信息失败');

            if ($post['role_ids']) {
                $roleIds = explode(',', $post['role_ids']);
                $data = [];
                foreach ($roleIds as $k => $v) {
                    $data[] = ['admin_role_id' => $v, 'admin_user_id' => $this->model->id];
                }
                $roleUser = new \app\model\admin\role\User();
                $saveAllRes = $roleUser->saveAll($data);
                if (!$saveAllRes) throw new \Exception('保存角色信息失败');
            }

            Db::commit();
            return $this->success('操作成功');
        } catch (\Exception $e) {
            Db::rollback();
            return $this->error('数据库异常，操作失败');
        }
    }

    //查看详情

    public function info()
    {
        $id = $this->request->param('id');
        $info = $this->model->with(['role_ids', 'avatar_file'])->findOrEmpty($id)->toArray();
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
            if (!$user) throw new \Exception('id参数错误');

            $validate = new Edit();
            if (!$validate->check($post)) throw new \Exception($validate->getError());
            if ($post['password']) {
                $post['password'] = Str::createPassword($post['password']);
            } else {
                unset($post['password']);
                unset($post['re_password']);
            }
            $updateRes = $user->update($post);
            if (!$updateRes) throw new \Exception('保存基本信息失败');

            $userRole = new \app\model\admin\role\User();
            $deleteRes = $userRole->where('admin_user_id', '=', $post['id'])->delete();
            if (!is_numeric($deleteRes)) throw new \Exception('删除用户角色失败');

            if ($post['role_ids']) {
                $roleIds = explode(',', $post['role_ids']);
                $data = [];
                foreach ($roleIds as $k => $v) {
                    $data[] = ['admin_role_id' => $v, 'admin_user_id' => $user->id];
                }

                $saveAllRes = $userRole->saveAll($data);
                if (!$saveAllRes) throw new \Exception('保存用户角色失败');
            }

            Db::commit();
            return $this->success('操作成功');
        } catch (\Exception $e) {
            Db::rollback();
            return $this->exceptionError($e);
        }
    }

    //修改个人资料

    public function singleEdit()
    {
        $post = CommonFun::filterPostData($this->request->post());
        $validate = new singleEdit();
        if (!$validate->check($post)) {
            return $this->error($validate->getError());
        }
        if (!$post['password']) {
            unset($post['password']);
        } else {
            $post['password'] = Str::createPassword($post['password']);
        }
        $user = $this->model->with(['avatar_file'])->find($post['id']);
        if (!$user) {
            return $this->error('ID参数错误');
        }
        $res = $user->update($post);
        if ($res) {
            return $this->success('操作成功');
        } else {
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
        try {
            if ($this->model->destroy($ids)) {
                return $this->success('数据删除成功');
            } else {
                return $this->error('数据删除失败');
            }
        } catch (\Exception $e) {
            return $this->exceptionError($e);
        }
    }

    //设置状态

    public function setStatus()
    {
        $id = $this->request->post('id');
        $fieldVal = $this->request->post('field_val');
        $isRecycle = $this->request->post('is_recycle');
        $update['status'] = $fieldVal;
        try {
            if ($isRecycle) {
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
        $id = $this->request->post('id');
        $fieldVal = $this->request->post('field_val');
        $isRecycle = $this->request->post('is_recycle');
        $update['is_super_manager'] = $fieldVal;
        try {
            if ($isRecycle) {
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