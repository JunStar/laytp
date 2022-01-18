<?php

use think\migration\Migrator;

class PluginAliSms extends Migrator
{
    public function change()
    {
        $table = $this->table('plugin_ali_sms', [
            'engine'    => 'InnoDB',
            'comment'   => '阿里云手机短信管理',
            'collation' => 'utf8mb4_general_ci',
        ]);

        //删除表
        if ($table->exists()) {
            $table->drop();
        }

        $table
            ->addColumn('template_code', 'string', ['limit' => 255, 'default' => '', 'comment' => '阿里云短信模板ID'])
            ->addColumn('event', 'string', ['limit' => 255, 'default' => '', 'comment' => '事件名称'])
            ->addColumn('params', 'text', ['comment' => '短信内容参数'])
            ->addColumn('mobile', 'string', ['limit' => 255, 'default' => '', 'comment' => '收信人手机号码'])
            ->addColumn('status', 'boolean', ['limit' => 4, 'default' => '0', 'comment' => '状态。1=未使用,2=已使用,3=已过期,默认:1'])
            ->addColumn('expire_time', 'integer', ['limit' => 11, 'default' => '0', 'comment' => '过期时间，0表示永不过期'])
            ->addColumn('create_time', 'datetime', ['comment' => '创建时间'])
            ->addColumn('update_time', 'datetime', ['comment' => '更新时间'])
            ->addColumn('delete_time', 'datetime', ['comment' => '删除时间'])
        ;

        $table->create();
    }
}