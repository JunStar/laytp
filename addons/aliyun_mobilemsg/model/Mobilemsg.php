<?php
/**
 * 手机短信管理模型
 */
namespace addons\aliyun_mobilemsg\model;

use model\Backend;
use think\model\concern\SoftDelete;

class Mobilemsg extends Backend
{
	use SoftDelete;
    //模型名
    protected $name = 'aliyun_mobilemsg';

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
}
