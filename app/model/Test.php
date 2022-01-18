<?php
/**
 * 官方举例，生成常规CURD模型
 */
namespace app\model;

use laytp\BaseModel;
use think\model\concern\SoftDelete;
use laytp\library\UploadDomain;

class Test extends BaseModel
{
	use SoftDelete;

    //模型名
    protected $name = 'test';

    //附加属性
    protected $append = ['imgs_file','videos_file','musics_file','files_file'];

    //时间戳字段转换
    

    //表名
    

    //关联模型
    public function category(){
        return $this->belongsTo('app\model\test\Category','category_id','id');
    }

	public function province(){
        return $this->belongsTo('app\model\Area','province_id','id');
    }

	public function city(){
        return $this->belongsTo('app\model\Area','city_id','id');
    }

	public function district(){
        return $this->belongsTo('app\model\Area','district_id','id');
    }

	public function imgFile(){
        return $this->belongsTo('app\model\Files','img','id');
    }

	public function videoFile(){
        return $this->belongsTo('app\model\Files','video','id');
    }

	public function musicFile(){
        return $this->belongsTo('app\model\Files','music','id');
    }

	public function fileFile(){
        return $this->belongsTo('app\model\Files','file','id');
    }

	public function selAdminUser(){
        return $this->belongsTo('app\model\admin\User','sel_admin_user_id','id');
    }

    //新增属性的方法
    public function getImgsFileAttr($value, $data)
	{
		return $data['imgs'] ? UploadDomain::multiJoin($data['imgs']) : '';
	}

	public function getVideosFileAttr($value, $data)
	{
		return $data['videos'] ? UploadDomain::multiJoin($data['videos']) : '';
	}

	public function getMusicsFileAttr($value, $data)
	{
		return $data['musics'] ? UploadDomain::multiJoin($data['musics']) : '';
	}

	public function getFilesFileAttr($value, $data)
	{
		return $data['files'] ? UploadDomain::multiJoin($data['files']) : '';
	}

	public function getUeditorAttr($value, $data)
	{
		return $value ? UploadDomain::addUploadDomain($value, 'ali-oss') : '';
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

	public function getMeditorAttr($value, $data)
	{
		return $value ? UploadDomain::addUploadDomain($value) : '';
	}
}
