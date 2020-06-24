<?php
/**
 * 附件管理模型
 */
namespace app\admin\model;

use model\Base;
use think\model\concern\SoftDelete;

class Attachment extends Base
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
}
