<?php

use think\migration\Migrator;

class PluginCoreRole extends Migrator
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
        $table = $this->table('plugin_core_role', [
            'engine' => 'InnoDB',
            'comment' => '后台角色',
            'collation' => 'utf8mb4_general_ci'
        ]);

        if ($table->exists()) {
            $table->drop();
        }

        $table
            ->addColumn('name', 'string', ['length' => 100, 'default' => '', 'comment' => '名称'])
            ->addColumn('create_time', 'integer', ['length' => 11, 'default' => 0, 'comment' => '创建时间'])
            ->addColumn('update_time', 'integer', ['length' => 11, 'default' => 0, 'comment' => '更新时间'])
            ->addColumn('delete_time', 'integer', ['length' => 11, 'default' => 0, 'comment' => '删除时间'])
            ->create();
    }
}