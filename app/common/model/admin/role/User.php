<?php
/**
 * 角色与用户关联模型
 */
namespace app\common\model\admin\role;

use laytp\BaseModel;

class User extends BaseModel
{
    protected $name = 'admin_role_user';

    public function menu(){
        return $this->belongsTo('app\common\model\admin\Menu','user_id','id');
    }
}