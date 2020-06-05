<?php
/**
 * 邮件模板管理模型
 */
namespace addons\email\admin\model\email;

use model\Backend;
use think\model\concern\SoftDelete;

class Template extends Backend
{
	use SoftDelete;
    //模型名
    protected $name = 'email_template';

    //时间戳字段转换
    
    //是否关闭create_time自动写入
    
    //是否关闭update_time自动写入
    
    //是否关闭delete_time自动写入
    

    //表名
    

    //数组常量
    public $const = [
		'ishtml' => [
			'2'=>'不是'
			,'1'=>'是'
		],
    ];

    //关联模型
    

    //获取数组常量的函数
    public function getArrayConstList($field_name){
        return $this->const[$field_name];
    }
}
