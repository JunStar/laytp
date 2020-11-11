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
            ->addColumn('field', 'string', ['length' => 100, 'default' => '', 'comment' => '字段名'])
            ->addColumn('comment', 'string', ['length' => 100, 'default' => '', 'comment' => '字段注释'])
            ->addColumn('data_type', 'string', ['length' => 100, 'default' => '', 'comment' => '存储类型'])
            ->addColumn('length', 'integer', ['length' => 11, 'default' => '100', 'comment' => '存储长度'])
            ->addColumn('default', 'string', ['length' => 100, 'default' => '', 'comment' => '默认值'])
            ->addColumn('is_empty', 'boolean', ['limit' => 1, 'default' => 2, 'comment' => '是否允许为空，1=允许，2=不允许'])
            ->addColumn('is_thead_sort', 'boolean', ['limit' => 1, 'default' => 2, 'comment' => '是否允许点击表头进行排序，1=允许，2=不允许'])
            ->addColumn('default_select_sort', 'string', ['limit' => 100, 'default' => '', 'comment' => '默认查询排序，asc正序，desc倒序'])
            ->addColumn('show_search', 'boolean', ['limit' => 1, 'default' => 2, 'comment' => '是否在搜索表单显示，1=显示，2=不显示'])
            ->addColumn('show_table', 'boolean', ['limit' => 1, 'default' => 2, 'comment' => '是否在数据表格显示，1=显示，2=不显示'])
            ->addColumn('show_add', 'boolean', ['limit' => 1, 'default' => 2, 'comment' => '是否在添加表单中显示，1=显示，2=不显示'])
            ->addColumn('show_edit', 'boolean', ['limit' => 1, 'default' => 2, 'comment' => '是否在编辑表单中显示，1=显示，2=不显示'])
            ->addColumn('show_sort', 'integer', ['length' => 11, 'default' => 0, 'comment' => '显示排序'])
            ->addColumn('form_type', 'string', ['length' => 100, 'default' => '', 'comment' => '表单元素'])
            ->addColumn('addition', 'text', ['null' => 1, 'comment' => '附加设置']);

        $data = [
            ['table_id' => 1, 'field' => 'name', 'comment' => '分类名', 'data_type' => 'string', 'length' => 100, 'form_type' => 'input'],

            [
                'table_id' => 2,
                'field' => 'title',
                'comment' => '标题',
                'data_type' => 'string',
                'length' => 100,
                'is_empty' => 1,
                'is_thead_sort' => 1,
                'default_select_sort' => '',
                'show_search' => 2,
                'show_table' => 2,
                'show_add' => 2,
                'show_edit' => 2,
                'show_sort' => 0,
                'form_type' => 'input',
                'addition' => json_encode(['verify' => ''], JSON_UNESCAPED_UNICODE)
            ],
        ];

        $table->setData($data)->create();
    }
}