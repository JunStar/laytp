<?php
/**
 * 后台管理员表模型
 */
namespace app\admin\model\auth;

use think\Model;
use think\model\concern\SoftDelete;

class User extends Model
{
    use SoftDelete;
    protected $defaultSoftDelete = '0000-00-00 00:00:00';
    //模型名
    protected $name = 'admin_user';

    //表名
    

    //数组常量
    public $const = [
		'is_super_manager' => [
			'0'=>'否'
			,'1'=>'是'
		],
        'status' => [
            '0'=>'禁用'
            ,'1'=>'启用'
        ],
        'is_del' => [
            '0'=>'否'
            ,'1'=>'是'
        ]
    ];

    //获取数组常量的函数
    public function getArrayConstList($field_name){
        return $this->const[$field_name];
    }
}
