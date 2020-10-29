<?php

use think\migration\Migrator;

class UserToken extends Migrator
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $table = $this->table('user_token', [
            'id' => false,
            'engine' => 'InnoDB',
            'comment' => 'token与user_id映射',
            'collation' => 'utf8mb4_general_ci',
            'primary_key' => ['token']
        ]);

        if ($table->exists()) {
            $table->drop();
        }

        $table
            ->addColumn('token', 'string', ['length' => 100, 'default' => '', 'comment' => 'token'])
            ->addColumn('user_id', 'integer', ['length' => 11, 'default' => 0, 'comment' => '用户ID'])
            ->addColumn('create_time', 'integer', ['default' => 0, 'comment' => '创建时间'])
            ->addColumn('expire_time', 'integer', ['default' => 0, 'comment' => '更新时间'])
            ->create();
    }
}