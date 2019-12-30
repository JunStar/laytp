<?php
/**
 * 前台用户管理模型
 */
namespace app\common\model;

use model\Backend;
use think\model\concern\SoftDelete;

class User extends Backend
{
	use SoftDelete;
    //模型名
    protected $name = 'user';

    //关联模型
}
