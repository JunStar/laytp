<?php
/**
 * 附件分类管理模型
 */
namespace app\model\files;

use laytp\BaseModel;
use think\model\concern\SoftDelete;

class Category extends BaseModel
{
	use SoftDelete;

    //模型名
    protected $name = 'files_category';

    //时间戳字段转换
    

    //表名
    

    //关联模型
    public function parent(){
        return $this->belongsTo('app\model\files\Category','pid','id');
    }

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
