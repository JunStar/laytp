<?php

use think\migration\Migrator;

class FilesCategory extends Migrator
{
    public function change()
    {
        $table = $this->table('files_category', [
            'engine' => 'InnoDB',
            'comment' => '附件分类管理',
            'collation' => 'utf8mb4_general_ci'
        ]);

        //删除表
        if ($table->exists()) {
            $table->drop();
        }

        $table
            ->addColumn('name', 'string', ['limit' => 255, 'null' => 0, 'default' => '0','comment' => '分类名称'])
			->addColumn('pid', 'integer', ['limit' => 11, 'default' => '0','comment' => '父级'])
            ->addColumn('sort', 'integer', ['limit' => 11, 'null' => 1, 'default' => '0','comment' => '排序'])
			->addColumn('create_time', 'datetime', ['null' => 1, 'comment' => '创建时间'])
			->addColumn('update_time', 'datetime', ['null' => 1, 'comment' => '更新时间'])
			->addColumn('delete_time', 'datetime', ['null' => 1, 'comment' => '删除时间'])
        ;

        $table->create();
    }
}