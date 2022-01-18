<?php
/**
 * 阿里云手机短信管理模型
 */
namespace app\model\plugin\ali;

use laytp\BaseModel;
use think\model\concern\SoftDelete;

class Sms extends BaseModel
{
	use SoftDelete;

    //模型名
    protected $name = 'plugin\ali_sms';

    //附加属性
    protected $append = [];

    //时间戳字段转换
    

    //表名
    

    //关联模型
    

    //新增属性的方法
    public function getCreateTimeIntAttr($value, $data)
	{
		return isset($data['create_time']) ? strtotime($data['create_time']) : 0;
	}

	public function getUpdateTimeIntAttr($value, $data)
	{
		return isset($data['update_time']) ? strtotime($data['update_time']) : 0;
	}

	public function getDeleteTimeIntAttr($value, $data)
	{
		return isset($data['delete_time']) ? strtotime($data['delete_time']) : 0;
	}
}
