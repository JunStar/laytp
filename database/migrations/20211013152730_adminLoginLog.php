<?php

use think\migration\Migrator;

class AdminLoginLog extends Migrator
{
    public function change()
    {
        $table = $this->table('admin_login_log', [
            'engine'    => 'InnoDB',
            'comment'   => '后台登录日志管理',
            'collation' => 'utf8mb4_general_ci',
        ]);

        //删除表
        if ($table->exists()) {
            $table->drop();
        }

        $table
            ->addColumn('login_status', 'boolean', ['limit' => 4, 'default' => '0', 'comment' => '登录状态.1=成功,2=失败'])
            ->addColumn('admin_id', 'integer', ['limit' => 11, 'default' => '0', 'comment' => '登录者'])
            ->addColumn('request_body', 'text', ['comment' => 'body参数'])
            ->addColumn('request_header', 'text', ['comment' => 'header参数'])
            ->addColumn('ip', 'string', ['limit' => 255, 'default' => '', 'comment' => 'ip'])
            ->addColumn('create_time', 'datetime', ['null' => 1, 'comment' => '创建时间'])
        ;

        $table->create();
    }
}