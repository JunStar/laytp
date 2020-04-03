<?php
/**
 * 一键生成Curd测试分类模型模型
 */
namespace app\admin\model\test;

use model\Backend;
use think\model\concern\SoftDelete;

class Category extends Backend
{
	use SoftDelete;
    //模型名
    protected $name = 'test_category';

    //表名
    
}
