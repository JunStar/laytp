<?php

use think\migration\Migrator;

class PluginEmailTemplate extends Migrator
{
    public function change()
    {
        $table = $this->table('plugin_email_template', [
            'engine'    => 'InnoDB',
            'comment'   => '邮件管理',
            'collation' => 'utf8mb4_general_ci',
        ]);

        //删除表
        if ($table->exists()) {
            $table->drop();
        }

        $table
            ->addColumn('event', 'string', ['limit' => 255, 'default' => '', 'comment' => '事件名称'])
            ->addColumn('title', 'string', ['limit' => 255, 'default' => '', 'comment' => '模板标题'])
            ->addColumn('template', 'text', ['default' => '', 'comment' => '模板内容，支持html标签，用{$参数名}来设置参数'])
            ->addColumn('ishtml', 'boolean', ['limit' => 4, 'default' => '0', 'comment' => '模板是否为html.2=不是,1=是,默认:2'])
            ->addColumn('expire', 'integer', ['limit' => 11, 'default' => '0', 'comment' => '过期时长，单位秒，0表示永不过期'])
            ->addColumn('create_time', 'datetime', ['null' => 1, 'comment' => '创建时间'])
            ->addColumn('update_time', 'datetime', ['null' => 1, 'comment' => '更新时间'])
            ->addColumn('delete_time', 'datetime', ['null' => 1, 'comment' => '删除时间'])
        ;

        $table->create();
    }
}