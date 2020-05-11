<?php
/**
 * 一键生成CURD测试表模型
 */
namespace app\admin\model;

use model\Backend;
use think\model\concern\SoftDelete;

class Test extends Backend
{
	use SoftDelete;
    //模型名
    protected $name = 'test';

    //时间戳字段转换
    
    //是否关闭create_time自动写入
    
    //是否关闭update_time自动写入
    
    //是否关闭delete_time自动写入
    

    //表名
    

    //数组常量
    public $const = [

    ];

    //关联模型
    

    //获取数组常量的函数
    
}
