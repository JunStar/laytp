<?php

use think\migration\Migrator;

class PluginAutocreateCurdTable extends Migrator
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
        $table = $this->table('plugin_autocreate_curd_table', [
            'engine' => 'InnoDB',
            'comment' => '自动生成curd数据表',
            'collation' => 'utf8mb4_general_ci'
        ]);

        if ($table->exists()) {
            $table->drop();
        }

        $table
            ->addColumn('table', 'string', ['length' => 100, 'default' => '', 'comment' => '表名'])
            ->addColumn('comment', 'string', ['length' => 100, 'default' => '', 'comment' => '表注释'])
            ->addColumn('engine', 'string', ['length' => 100, 'default' => '', 'comment' => '存储引擎'])
            ->addColumn('collation', 'string', ['length' => 100, 'default' => '', 'comment' => '字符集(排序规则)'])
            ->addColumn('autocreate_time', 'integer', ['length' => 11, 'default' => 0, 'comment' => '生成时间']);

        $data = [
            ['connection' => 'mysql', 'table' => 'lt_test_category', 'comment' => '官方举例，生成分类CURD', 'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci'],
            ['connection' => 'mysql1', 'table' => 'lt_test', 'comment' => '官方举例，生成常规CURD', 'engine' => 'InnoDB', 'collation' => 'utf8mb4_general_ci'],
        ];

        $table->setData($data)->create();
    }
}