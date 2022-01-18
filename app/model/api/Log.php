<?php
/**
 * Api请求日志模型
 */
namespace app\model\api;

use laytp\BaseModel;

class Log extends BaseModel
{

    //模型名
    protected $name = 'api_log';

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
}
