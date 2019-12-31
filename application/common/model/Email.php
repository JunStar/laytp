<?php
/**
 * 邮件管理模型
 */
namespace app\common\model;

use model\Backend;
use think\model\concern\SoftDelete;

class Email extends Backend
{
	use SoftDelete;
    //模型名
    protected $name = 'email';

    //时间戳字段转换
    
    //是否关闭create_time自动写入
    
    //是否关闭update_time自动写入
    
    //是否关闭delete_time自动写入
    

    //表名
    

    //数组常量
    public $const = [
		'status' => [
			'1'=>'未使用'
			,'2'=>'已使用'
			,'3'=>'已过期'
		],
    ];

    //关联模型
    

    //获取数组常量的函数
    public function getArrayConstList($field_name){
        return $this->const[$field_name];
    }
}
