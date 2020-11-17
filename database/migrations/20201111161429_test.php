<?php

use think\migration\Migrator;

class Test extends Migrator
{
    public function change()
    {
        $table = $this->table('test', [
            'engine' => 'InnoDB',
            'comment' => 'test',
            'collation' => 'utf8mb4_general_ci'
        ]);

        //删除表
        if ($table->exists()) {
            $table->drop();
        }

        $table
            ->addColumn('title', 'string', ['length' => 100, 'null' => 1, 'default' => '', 'comment' => '标题'])
            ->addColumn('title1', 'string', ['length' => 100, 'null' => 1, 'default' => '', 'comment' => '标题']);

        $table->create();
    }
}