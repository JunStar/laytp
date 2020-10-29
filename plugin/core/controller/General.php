<?php

namespace plugin\core\controller;

use plugin\core\validate\general\Profile;
use plugin\core\model\User;
use plugin\core\service\UserServiceFacade;
use laytp\controller\Backend;

class General extends Backend
{
    public function profile()
    {
        $post = filter_post_data($this->request->post());
        $validate = new Profile();
        if (!$validate->check($post)) {
            return $this->error($validate->getError());
        }
        try {
            if ($post['password']) {
                $post['password'] = password_hash(md5($post['password']), PASSWORD_DEFAULT);
            } else {
                unset($post['password']);
                unset($post['re_password']);
            }
            $updateRes = User::update($post, ['id' => UserServiceFacade::getUser()->id]);
            if ($updateRes || $updateRes === 0) {
                return $this->success('操作成功');
            } else {
                return $this->error('操作失败');
            }
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}