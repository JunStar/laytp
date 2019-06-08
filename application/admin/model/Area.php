<?php
/**
 * 地区表模型
 */
namespace app\admin\model;

use model\Backend;

class Area extends Backend
{
    //模型名
    protected $name = 'area';

    //表名
    

    //数组常量
    public $const = [
		'level' => [
			'1'=>'省'
			,'2'=>'市'
			,'3'=>'区县'
		],
    ];

    //关联模型
    public function parent(){
        return $this->belongsTo('app\admin\model\Area','pid','id');
    }

    //获取数组常量的函数
    public function getArrayConstList($field_name){
        return $this->const[$field_name];
    }
}
