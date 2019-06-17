<?php
/**
 * 附件管理模型
 */
namespace app\admin\model;

use model\Backend;
use think\model\concern\SoftDelete;

class Attachment extends Backend
{
	use SoftDelete;
    //模型名
    protected $name = 'attachment';

    //表名
    

    //数组常量
    public $const = [
		'file_type' => [
			'images'=>'图片'
			,'video'=>'视频'
			,'audio'=>'音频'
			,'file'=>'文件'
		],
    ];

    //关联模型
    

    //获取数组常量的函数
    public function getArrayConstList($field_name){
        return $this->const[$field_name];
    }
}
