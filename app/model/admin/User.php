<?php
/**
 * 后台管理员表模型
 */

namespace app\model\admin;

use laytp\BaseModel;
use laytp\library\UploadDomain;
use think\model\concern\SoftDelete;

class User extends BaseModel
{
    use SoftDelete;

    //模型名
    protected $name = 'admin_user';

    //数组常量
    public $const = [
        'is_super_manager' => [
            '2'   => '否'
            , '1' => '是',
        ],
        'status'           => [
            '2'   => '禁用'
            , '1' => '正常',
        ],
    ];

    public function avatarFile(){
        return $this->belongsTo('app\model\Files','avatar','id');
    }

    // 定义默认头像
    public function getAvatarFileAttr($value){
        if(!$value){
            return [
                'id' => "",
                'filename' => '默认头像',
                'path' => UploadDomain::getDefaultAvatar(),
            ];
        }else{
            return $value;
        }
    }

    public function roleIds()
    {
        return $this->hasMany(\app\model\admin\role\User::class, 'admin_user_id');
    }
}
