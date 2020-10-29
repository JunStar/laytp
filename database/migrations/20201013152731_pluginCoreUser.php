<?php

use think\migration\Migrator;

class PluginCoreUser extends Migrator
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
        $table = $this->table('plugin_core_user', [
            'engine' => 'InnoDB',
            'comment' => '后台管理员',
            'collation' => 'utf8mb4_general_ci'
        ]);

        if ($table->exists()) {
            $table->drop();
        }

        $table
            ->addColumn('username', 'string', ['length' => 255, 'default' => '', 'comment' => '用户名'])
            ->addColumn('nickname', 'string', ['length' => 255, 'default' => '', 'comment' => '昵称'])
            ->addColumn('password', 'string', ['length' => 255, 'default' => '', 'comment' => '密码'])
            ->addColumn('avatar', 'string', ['length' => 255, 'default' => '', 'comment' => '头像'])
            ->addColumn('is_super_manager', 'boolean', ['length' => 4, 'default' => 2, 'comment' => '是否超管.2=否,1=是'])
            ->addColumn('status', 'boolean', ['length' => 4, 'default' => 1, 'comment' => '状态.2.禁用,1.正常'])
            ->addColumn('create_time', 'integer', ['default' => 0, 'comment' => '创建时间'])
            ->addColumn('update_time', 'integer', ['default' => 0, 'comment' => '更新时间'])
            ->addColumn('delete_time', 'integer', ['default' => 0, 'comment' => '删除时间']);

        $data = [
            'username' => 'admin',
            'nickname' => '超级管理员',
            'password' => password_hash(md5('123456'), PASSWORD_DEFAULT),
            'is_super_manager' => 1,
            'create_time' => time(),
            'update_time' => time(),
        ];

        $table->insert($data)->create();
    }
}