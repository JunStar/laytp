<?php

use think\migration\Migrator;

class User extends Migrator
{
    public function change()
    {
        $table = $this->table('user', [
            'engine'    => 'InnoDB',
            'comment'   => '会员管理',
            'collation' => 'utf8mb4_general_ci',
        ]);

        //删除表
        if ($table->exists()) {
            $table->drop();
        }

        $table
            ->addColumn('email', 'string', ['limit' => 255, 'default' => '', 'comment' => '邮箱'])
            ->addColumn('password', 'string', ['limit' => 255, 'default' => '', 'comment' => '密码'])
            ->addColumn('nickname', 'string', ['limit' => 255, 'default' => '', 'comment' => '昵称'])
            ->addColumn('sex', 'boolean', ['limit' => 4, 'default' => '0', 'comment' => '性别.1=男,2=女,'])
            ->addColumn('vip_time', 'integer', ['limit' => 11, 'default' => '0', 'comment' => 'vip到期时间.单位秒.0表示未开通过vip'])
            ->addColumn('avatar', 'string', ['limit' => 255, 'default' => '', 'comment' => '头像'])
            ->addColumn('login_time', 'datetime', ['comment' => '注册时间'])
            ->addColumn('login_ip', 'string', ['limit' => 255, 'default' => '', 'comment' => '登录IP'])
            ->addColumn('status', 'boolean', ['limit' => 4, 'default' => '0', 'comment' => '账号状态.2=锁定,1=正常,默认:1'])
            ->addColumn('create_time', 'datetime', ['comment' => '创建时间'])
            ->addColumn('update_time', 'datetime', ['comment' => '更新时间'])
            ->addColumn('delete_time', 'datetime', ['comment' => '删除时间'])
        ;

        $table->create();
    }
}