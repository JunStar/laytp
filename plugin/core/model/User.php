<?php
/**
 * 后台管理员表模型
 */

namespace plugin\core\model;

use laytp\BaseModel;
use think\model\concern\SoftDelete;
use plugin\core\model\user\Role;

class User extends BaseModel
{
    use SoftDelete;
    protected $defaultSoftDelete = 0;

    //模型名
    protected $name = 'plugin_core_user';

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

    /**
     * 对于后台所有使用上传组件上传的文件，都只返回数据库中存储的信息
     * 这样在Upload组件中，input直接显示数据库存储的字段信息，而要展示图片时，自行获取系统配置进行拼接
     * 对于前端Api接口，需要在接口中拼接好图片或这文件的完整访问路径
     * @param $avatar
     * @return string
     */
    public function getAvatarAttr($avatar)
    {
        return $avatar ? $avatar : '/static/plugin/core/img/default_avatar.png';
    }

    public function roleIds()
    {
        return $this->hasMany(Role::class, 'plugin_core_user_id');
    }
}
