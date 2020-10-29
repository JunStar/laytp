<?php

use think\migration\Migrator;

class PluginCoreRoleMenu extends Migrator
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
        $table = $this->table('plugin_core_role_menu', [
            'engine' => 'InnoDB',
            'comment' => '角色与菜单关联',
            'collation' => 'utf8mb4_general_ci'
        ]);

        if ($table->exists()) {
            $table->drop();
        }

        $table
            ->addColumn('plugin_core_menu_id', 'integer', ['length' => 11, 'default' => 0, 'comment' => '菜单ID'])
            ->addColumn('plugin_core_role_id', 'integer', ['length' => 11, 'default' => 0, 'comment' => '角色ID'])
            ->create();
    }
}