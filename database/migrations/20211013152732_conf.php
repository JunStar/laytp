<?php

use think\migration\Migrator;

class Conf extends Migrator
{
    public function change()
    {
        $table = $this->table('conf', [
            'engine'    => 'InnoDB',
            'comment'   => '系统配置',
            'collation' => 'utf8mb4_general_ci',
        ]);

        //删除表
        if ($table->exists()) {
            $table->drop();
        }

        $table
            ->addColumn('group', 'string', ['limit' => 255, 'default' => '', 'comment' => '配置分组'])
            ->addColumn('key', 'string', ['limit' => 255, 'default' => '', 'comment' => '配置key'])
            ->addColumn('value', 'text', ['comment' => '配置值'])
            ->addColumn('form_type', 'string', ['limit' => 255, 'default' => '', 'comment' => '配置表单元素'])
        ;

        $data = [
            '1'  => [
                'group'     => 'system.upload',
                'key'       => 'defaultType',
                'value'     => '200MB',
                'form_type' => 'input',
            ],
            '2'  => [
                'group'     => 'system.upload',
                'key'       => 'size',
                'value'     => '200MB',
                'form_type' => 'input',
            ],
            '3'  => [
                'group'     => 'system.upload',
                'key'       => 'mime',
                'value'     => 'png,jpg,gif,jpeg,doc,xls,pdf',
                'form_type' => 'input',
            ],
        ];

        $table->setData($data)->create();
    }
}