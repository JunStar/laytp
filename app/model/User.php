<?php
/**
 * 会员管理模型
 */
namespace app\model;

use laytp\BaseModel;
use laytp\library\UploadDomain;
use think\model\concern\SoftDelete;

class User extends BaseModel
{
    use SoftDelete;

    //模型名，导出功能需要读取此值，所以需要设置成public
    protected $name = 'user';

    //时间戳字段转换
    protected $type = [
        'vip_time' => 'timestamp:Y-m-d H:i:s',
    ];

    protected $append = ['vip_time_int'];

    //表名


    //关联模型


    //新增属性的方法
    public function getVipTimeIntAttr($value, $data)
    {
        return isset($data['vip_time']) ? $data['vip_time'] : 0;
    }

    public function getLoginTimeIntAttr($value, $data)
    {
        return isset($data['login_time']) ? strtotime($data['login_time']) : 0;
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
