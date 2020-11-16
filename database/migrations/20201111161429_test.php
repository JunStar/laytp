<?php

use think\migration\Migrator;

class Test extends Migrator
{
    public function change()
    {
        $table = $this->table('lt_test', [
            'engine' => 'InnoDB',
            'comment' => '{%tableComment%}',
            'collation' => '{%collation%}'
        ]);

        //删除表
        if ($table->exists()) {
            $table->drop();
        }

        $table
            ->addColumn('title', 'string', ['length' => 100, 'null' => 1, 'default' => '', 'comment' => '标题']);

        $table->create();
    }
}