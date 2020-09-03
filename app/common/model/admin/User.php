<?php
/**
 * 后台管理员表模型
 */
namespace app\common\model\admin;

use laytp\BaseModel;
use think\model\concern\SoftDelete;

class User extends BaseModel
{
    //模型名
    protected $name = 'admin_user';

    use SoftDelete;
    protected $defaultSoftDelete = 0;

    

    //数组常量
    public $const = [
		'is_super_manager' => [
			'2'=>'否'
			,'1'=>'是'
		],
        'status' => [
            '2'=>'禁用'
            ,'1'=>'正常'
        ]
    ];
}
