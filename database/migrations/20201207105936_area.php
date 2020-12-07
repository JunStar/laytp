<?php

use think\migration\Migrator;

class Area extends Migrator
{
    public function change()
    {
        $table = $this->table('area', [
            'engine' => 'InnoDB',
            'comment' => '地区管理',
            'collation' => 'utf8mb4_general_ci'
        ]);

        //删除表
        if ($table->exists()) {
            $table->drop();
        }

        $table
            ->addColumn('pid', 'integer', ['limit' => 11, 'null' => 1, 'default' => '0', 'comment' => '父id'])
			->addColumn('short_name', 'string', ['limit' => 100, 'null' => 1, 'default' => '', 'comment' => '简称'])
			->addColumn('merge_name', 'string', ['limit' => 100, 'null' => 1, 'default' => '', 'comment' => '全称'])
			->addColumn('level', 'boolean', ['limit' => 4, 'null' => 1, 'default' => '1', 'comment' => '层级'])
			->addColumn('pinyin', 'string', ['limit' => 100, 'null' => 1, 'default' => '', 'comment' => '拼音'])
			->addColumn('code', 'string', ['limit' => 100, 'null' => 1, 'default' => '', 'comment' => '长途区号'])
			->addColumn('zip', 'string', ['limit' => 100, 'null' => 1, 'default' => '', 'comment' => '邮编'])
			->addColumn('first', 'string', ['limit' => 50, 'null' => 1, 'default' => '', 'comment' => '首字母'])
			->addColumn('lng', 'string', ['limit' => 100, 'null' => 1, 'default' => '', 'comment' => '经度'])
			->addColumn('lat', 'string', ['limit' => 100, 'null' => 1, 'default' => '', 'comment' => '纬度'])
			
        ;

        $table->create();
    }
}