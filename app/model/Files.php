<?php
/**
 * 附件管理模型
 */
namespace app\model;

use laytp\BaseModel;
use think\model\concern\SoftDelete;
use laytp\library\UploadDomain;

class Files extends BaseModel
{
	use SoftDelete;

    //模型名
    protected $name = 'files';

    //时间戳字段转换
    

    //表名
    

    //关联模型
    public function category(){
        return $this->belongsTo('app\model\files\Category','category_id','id');
    }

	public function createAdminUser(){
        return $this->belongsTo('app\model\admin\User','create_admin_user_id','id');
    }

	public function updateAdminUser(){
        return $this->belongsTo('app\model\admin\User','update_admin_user_id','id');
    }

    //新增属性的方法
    public function getPathAttr($value, $data)
	{
		return $value ? UploadDomain::singleAddUploadDomain($data) : '';
	}

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
