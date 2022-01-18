<?php

use think\migration\Migrator;

class PluginEmail extends Migrator
{
    public function change()
    {
        $table = $this->table('plugin_curd_table', [
            'engine'    => 'InnoDB',
            'comment'   => '自动生成curd数据表管理',
            'collation' => 'utf8mb4_general_ci',
        ]);

        //删除表
        if ($table->exists()) {
            $table->drop();
        }

        $table
            ->addColumn('table', 'string', ['limit' => 255, 'default' => '', 'comment' => '表名'])
            ->addColumn('comment', 'text', ['comment' => '表注释'])
            ->addColumn('engine', 'text', ['comment' => '存储引擎'])
            ->addColumn('collation', 'string', ['limit' => 255, 'default' => '', 'comment' => '字符集(排序规则)'])
            ->addColumn('is_hide_pk', 'boolean', ['limit' => 4, 'default' => '0', 'comment' => '是否隐藏主键列.2=不隐藏,1=隐藏'])
            ->addColumn('is_create_number', 'boolean', ['limit' => 4, 'default' => '0', 'comment' => '是否生成序号列.2=不生成,1=生成'])
            ->addColumn('has_create_table', 'boolean', ['limit' => 4, 'default' => '0', 'comment' => '是否已经生成表结构.0=未生成,1=已生成'])
            ->addColumn('has_create_menu', 'boolean', ['limit' => 4, 'default' => '0', 'comment' => '是否已经生成菜单.0=未生成,1=已生成'])
            ->addColumn('create_type', 'boolean', ['limit' => 4, 'default' => '0', 'comment' => '生成类型.1=常规CURD,2=分类CURD'])
            ->addColumn('create_addition', 'text', ['comment' => '生成附加属性.只有分类CURD才会有值.存储一个json.格式:{"parent_field":"父级字段","name_field":"分类名字段","order_field":"排序字段","order_type":"排序方式,ASC或者DESC"}'])
            ->addColumn('create_time', 'datetime', ['null' => 1, 'comment' => '创建时间'])
            ->addColumn('update_time', 'datetime', ['null' => 1, 'comment' => '更新时间'])
            ->addColumn('delete_time', 'datetime', ['null' => 1, 'comment' => '删除时间'])
        ;

        $data = [
            [
                'table' => Config::get("database.connections." . Config::get("database.default") . ".prefix") . 'area',
                'comment' => '地区管理',
                'engine' => 'InnoDB',
                'collation' => 'utf8mb4_general_ci',
                'is_hide_pk' => 2,
                'is_create_number' => 2,
                'has_create_table' => 1,
                'has_create_menu' => 1,
                'create_type' => 2,
                'create_addition' => '{"parent_field":"pid","id_order_type":"ASC","order_field":"sort","order_type":"DESC"}',
                'create_time' => date('Y-m-d H:i:s'),
                'update_time' => date('Y-m-d H:i:s'),
            ],
            [
                'table' => Config::get("database.connections." . Config::get("database.default") . ".prefix") . 'user',
                'comment' => '会员管理',
                'engine' => 'InnoDB',
                'collation' => 'utf8mb4_general_ci',
                'is_hide_pk' => 2,
                'is_create_number' => 2,
                'has_create_table' => 1,
                'has_create_menu' => 1,
                'create_type' => 1,
                'create_addition' => '',
                'create_time' => date('Y-m-d H:i:s'),
                'update_time' => date('Y-m-d H:i:s'),
            ],
            [
                'table' => Config::get("database.connections." . Config::get("database.default") . ".prefix") . 'admin_user',
                'comment' => '后台管理员',
                'engine' => 'InnoDB',
                'collation' => 'utf8mb4_general_ci',
                'is_hide_pk' => 2,
                'is_create_number' => 2,
                'has_create_table' => 1,
                'has_create_menu' => 1,
                'create_type' => 1,
                'create_addition' => '',
                'create_time' => date('Y-m-d H:i:s'),
                'update_time' => date('Y-m-d H:i:s'),
            ],
            [
                'table' => Config::get("database.connections." . Config::get("database.default") . ".prefix") . 'test_category',
                'comment' => '官方举例，生成分类CURD',
                'engine' => 'InnoDB',
                'collation' => 'utf8mb4_general_ci',
                'is_hide_pk' => 2,
                'is_create_number' => 2,
                'has_create_table' => 1,
                'has_create_menu' => 1,
                'create_type' => 2,
                'create_addition' => '{"parent_field":"pid","id_order_type":"ASC","order_field":"sort","order_type":"DESC"}',
                'create_time' => date('Y-m-d H:i:s'),
                'update_time' => date('Y-m-d H:i:s'),
            ],
            [
                'table' => Config::get("database.connections." . Config::get("database.default") . ".prefix") . 'test',
                'comment' => '官方举例，生成常规CURD',
                'engine' => 'InnoDB',
                'collation' => 'utf8mb4_general_ci',
                'is_hide_pk' => 2,
                'is_create_number' => 2,
                'has_create_table' => 1,
                'has_create_menu' => 1,
                'create_type' => 1,
                'create_addition' => '{"parent_field":"","id_order_type":"ASC","order_field":"","order_type":"DESC"}',
                'create_time' => date('Y-m-d H:i:s'),
                'update_time' => date('Y-m-d H:i:s'),
            ],
        ];

        $table->setData($data)->create();
    }
}