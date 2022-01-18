<?php

use think\migration\Migrator;

class PluginEmail extends Migrator
{
    public function change()
    {
        $table = $this->table('plugin_email', [
            'engine'    => 'InnoDB',
            'comment'   => '邮件管理',
            'collation' => 'utf8mb4_general_ci',
        ]);

        //删除表
        if ($table->exists()) {
            $table->drop();
        }

        $table
            ->addColumn('template_id', 'integer', ['limit' => 11, 'default' => '0', 'comment' => '模板ID'])
            ->addColumn('event', 'string', ['limit' => 255, 'default' => '', 'comment' => '事件名称'])
            ->addColumn('params', 'text', ['default' => '', 'comment' => '邮件内容参数'])
            ->addColumn('content', 'text', ['default' => '', 'comment' => '邮件内容'])
            ->addColumn('from', 'string', ['limit' => 255, 'default' => '', 'comment' => '发件人邮箱'])
            ->addColumn('to', 'string', ['limit' => 255, 'default' => '', 'comment' => '收件人邮箱'])
            ->addColumn('status', 'boolean', ['limit' => 4, 'default' => '0', 'comment' => '状态。1=未使用,2=已使用,3=已过期,默认:1'])
            ->addColumn('expire_time', 'integer', ['limit' => 11, 'default' => '0', 'comment' => '过期时间，0表示永不过期'])
            ->addColumn('create_time', 'datetime', ['null' => 1, 'comment' => '创建时间'])
            ->addColumn('update_time', 'datetime', ['null' => 1, 'comment' => '更新时间'])
            ->addColumn('delete_time', 'datetime', ['null' => 1, 'comment' => '删除时间'])
        ;

        $table->create();
    }
}