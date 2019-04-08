<?php
/**
 * Created by LayTp.
 * User: JunStar
 * Date: 18-9-9
 * Time: 上午1:44
 */
namespace app\admin\validate\autocreate\curd;

use think\Validate;

class import extends Validate
{
    protected $rule = [
        'table_name' =>  'require',
        'table_comment'  =>  'require',
    ];

    protected $message = [
        'table_name.require'  =>  '表名不能为空',
        'table_comment.require' =>  '表注释不能为空',
    ];

    protected $scene = [
        'add'   =>  ['name','email'],
        'edit'  =>  ['email'],
    ];
}