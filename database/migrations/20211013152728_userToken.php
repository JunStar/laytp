<?php

use think\migration\Migrator;

class UserToken extends Migrator
{
    public function change()
    {
        $table = $this->table('user_token', [
            'engine'    => 'InnoDB',
            'comment'   => '会员token管理',
            'collation' => 'utf8mb4_general_ci',
        ]);

        //删除表
        if ($table->exists()) {
            $table->drop();
        }

        $table
            ->addColumn('token', 'string', ['limit' => 255, 'default' => '', 'comment' => 'token'])
            ->addColumn('user_id', 'integer', ['limit' => 11, 'default' => '0', 'comment' => '用户ID'])
            ->addColumn('create_time', 'integer', ['limit' => 11, 'default' => '0', 'comment' => '创建时间'])
            ->addColumn('expire_time', 'integer', ['limit' => 11, 'default' => '0', 'comment' => '有效期']);

        $table->create();
    }
}