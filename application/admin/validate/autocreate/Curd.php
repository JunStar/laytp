<?php
/**
 * Created by JunAdmin.
 * User: JunStar
 * Date: 18-9-9
 * Time: 上午1:44
 */
namespace app\admin\validate\autocreate;

use think\Validate;

class Curd extends Validate
{
    protected $rule = [
        'name'  =>  'require|max:25',
        'email' =>  'email',
    ];

    protected $message = [
        'name.require'  =>  '用户名必须',
        'email' =>  '邮箱格式错误',
    ];

    protected $scene = [
        'add'   =>  ['name','email'],
        'edit'  =>  ['email'],
    ];
}