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
	protected $defaultSoftDelete='0';
    //模型名
    protected $name = 'test';

    //时间戳字段转换
    protected $type = [
		'create_time'  =>  'timestamp:Y-m-d H:i:s',
	];
    //是否设置创建时间字段，当设置$createTime = false时，为关闭create_time自动写入，默认值为$createTime = 'create_time'
    protected $createTime = false;
    //是否设置更新时间字段，当设置$updateTime = false时，为关闭update_time自动写入，默认值为$updateTime = 'update_time'
    
    //是否设置删除时间字段，当设置$deleteTime = false时，为关闭delete_time自动写入，默认值为$deleteTime = 'delete_time'
    

    //表名
    

    //数组常量
    public $const = [
		'grade' => [
			'1'=>'一年级'
			,'2'=>'二年级'
			,'3'=>'三年级'
		],

		'status' => [
			'0'=>'关闭'
			,'1'=>'打开'
		],

		'hero' => [
			'0'=>'秀逗魔法师'
			,'1'=>'受折磨的灵魂'
			,'2'=>'船长'
			,'3'=>'虚空假面'
			,'4'=>'幻影刺客'
			,'5'=>'谜团'
			,'6'=>'全能骑士'
			,'7'=>'敌法师'
		],

		'hobby' => [
			'0'=>'游泳'
			,'1'=>'下棋'
			,'2'=>'游戏'
			,'3'=>'乒乓球'
			,'4'=>'羽毛'
			,'5'=>'跑步'
			,'6'=>'爬山'
			,'7'=>'美食'
		],

		'sign' => [
			'0'=>'热门'
			,'1'=>'首页'
			,'2'=>'顶级分类推荐'
			,'3'=>'二级分类推荐'
			,'4'=>'特定分类推荐'
			,'5'=>'轮播图'
			,'6'=>'置顶'
			,'7'=>'新闻'
		],
    ];

    //关联模型
    public function remt(){
        return $this->belongsTo('app\admin\model\test\relation\model\Table','category_id','id')->field('id,name');
    }

	public function category(){
        return $this->belongsTo('app\admin\model\test\Category','category_id','id')->field('id,name');
    }

    //获取数组常量的函数
    public function getArrayConstList($field_name){
        return $this->const[$field_name];
    }
}
