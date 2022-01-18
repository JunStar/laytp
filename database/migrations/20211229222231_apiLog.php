<?php

use think\migration\Migrator;

class ApiLog extends Migrator
{
    public function change()
    {
        $table = $this->table('api_log', [
            'engine'    => 'InnoDB',
            'comment'   => 'Api请求日志管理',
            'collation' => 'utf8mb4_general_ci',
        ]);

        //删除表
        if ($table->exists()) {
            $table->drop();
        }

        $table
            ->addColumn('rule', 'string', ['limit' => 255, 'default' => '', 'comment' => '路由规则'])
            ->addColumn('request_body', 'text', ['comment' => 'body参数'])
            ->addColumn('request_header', 'text', ['comment' => 'header参数'])
            ->addColumn('ip', 'string', ['limit' => 255, 'default' => '', 'comment' => 'ip'])
            ->addColumn('status_code', 'string', ['limit' => 255, 'default' => '', 'comment' => '返回状态码'])
            ->addColumn('response_body', 'string', ['limit' => 255, 'default' => '', 'comment' => '返回内容'])
            ->addColumn('create_time', 'datetime', ['null' => 1, 'comment' => '创建时间'])
        ;

        $table->create();
    }
}