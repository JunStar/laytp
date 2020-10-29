<?php

use think\migration\Migrator;

class PluginAutocreateCurdField extends Migrator
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
        $table = $this->table('plugin_autocreate_curd_field', [
            'engine' => 'InnoDB',
            'comment' => '自动生成curd字段列表',
            'collation' => 'utf8mb4_general_ci'
        ]);

        if ($table->exists()) {
            $table->drop();
        }

        $table
            ->addColumn('table_id', 'integer', ['length' => 11, 'default' => 0, 'comment' => '表ID'])
            ->addColumn('field', 'string', ['length' => 255, 'default' => '', 'comment' => '字段名'])
            ->addColumn('comment', 'string', ['length' => 255, 'default' => '', 'comment' => '字段注释'])
            ->addColumn('form_type', 'string', ['length' => 255, 'default' => '', 'comment' => '表单元素'])
            ->addColumn('sort', 'string', ['length' => 11, 'default' => 0, 'comment' => '排序']);

        $data = [
            ['table_id' => 1, 'field' => 'title', 'comment' => '标题', 'form_type' => 'input', 'sort' => 0],
            ['table_id' => 2, 'field' => 'name', 'comment' => '分类名', 'form_type' => 'input', 'sort' => 0],
        ];

        $table->setData($data)->create();
    }
}