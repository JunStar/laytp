<?php

namespace plugin\autocreate\validate\curd;

use plugin\autocreate\model\curd\Table;
use think\Validate;

class AddTable extends Validate
{
    //数组顺序就是检测的顺序，比如这里，会先检测code验证码的正确性
    protected $rule = [
        'database' => 'require',
        'table'    => 'require|checkTable:',
    ];

    //定义内置方法检验失败后返回的字符
    protected $message = [
        'database.require' => '数据库不能为空',
        'table.require'    => '表名不能为空',
    ];

    //自定义验证码检验方法
    protected function checkTable($table, $rule, $data)
    {
        $exist = Table::where('database', '=', $data['database'])->where('table', '=', $data['table'])->find();
        if ($exist) {
            return '表名已经存在';
        } else {
            return true;
        }
    }
}