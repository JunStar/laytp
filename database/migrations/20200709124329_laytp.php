<?php

use think\migration\Migrator;

class LayTp extends Migrator
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
        $this->createAdminUser();//后台管理员
        $this->createUserToken();//token与user_id映射
        $this->createAdminMenu();//后台菜单
        $this->createAdminRole();//后台角色
        $this->createAdminRoleMenu();//角色与菜单关联
        $this->createAdminRoleUser();//角色与管理员关联
        $this->createAdminSysconf();//系统配置
    }

    public function createAdminUser()
    {
        $table = $this->table('admin_user', [
            'engine' => 'InnoDB',
            'comment' => '后台管理员',
            'collation' => 'utf8mb4_general_ci'
        ]);

        if ($table->exists()) {
            $table->drop();
        }

        $table
            ->addColumn('username', 'string', ['length' => 255, 'default' => '', 'comment' => '用户名'])
            ->addColumn('nickname', 'string', ['length' => 255, 'default' => '', 'comment' => '昵称'])
            ->addColumn('password', 'string', ['length' => 255, 'default' => '', 'comment' => '密码'])
            ->addColumn('avatar', 'string', ['length' => 255, 'default' => '', 'comment' => '头像'])
            ->addColumn('is_super_manager', 'boolean', ['length' => 4, 'default' => 2, 'comment' => '是否为超级管理员.2=不是,1=是'])
            ->addColumn('status', 'boolean', ['length' => 4, 'default' => 1, 'comment' => '状态.2.禁用,1.正常'])
            ->addColumn('create_time', 'integer', ['default' => 0, 'comment' => '创建时间'])
            ->addColumn('update_time', 'integer', ['default' => 0, 'comment' => '更新时间'])
            ->addColumn('delete_time', 'integer', ['default' => 0, 'comment' => '删除时间'])
            ->create();
    }

    public function createUserToken()
    {
        $table = $this->table('user_token', [
            'id' => false,
            'engine' => 'InnoDB',
            'comment' => 'token与user_id映射',
            'collation' => 'utf8mb4_general_ci',
            'primary_key' => ['token']
        ]);

        if ($table->exists()) {
            $table->drop();
        }

        $table
            ->addColumn('token', 'string', ['length' => 100, 'default' => '', 'comment' => 'token'])
            ->addColumn('user_id', 'integer', ['length' => 11, 'default' => 0, 'comment' => '用户ID'])
            ->addColumn('create_time', 'integer', ['default' => 0, 'comment' => '创建时间'])
            ->addColumn('expire_time', 'integer', ['default' => 0, 'comment' => '更新时间'])
            ->create();
    }

    public function createAdminMenu()
    {
        $table = $this->table('admin_menu', [
            'engine' => 'InnoDB',
            'comment' => '后台菜单',
            'collation' => 'utf8mb4_general_ci'
        ]);

        if ($table->exists()) {
            $table->drop();
        }

        $table
            ->addColumn('name', 'string', ['length' => 100, 'default' => '', 'comment' => '名称'])
            ->addColumn('rule', 'string', ['length' => 100, 'default' => '', 'comment' => '路由规则'])
            ->addColumn('is_menu', 'boolean', ['default' => 1, 'comment' => '是否为menu.2=不是,1=是'])
            ->addColumn('sort', 'integer', ['default' => 0, 'comment' => '排序，从大到小，倒叙排序'])
            ->addColumn('pid', 'integer', ['default' => 0, 'comment' => '上级ID'])
            ->addColumn('is_show', 'integer', ['default' => 1, 'comment' => '是否显示.2=隐藏,1=显示'])
            ->addColumn('icon', 'string', ['length' => 100, 'default' => '', 'comment' => '图标'])
            ->addColumn('des', 'string', ['length' => 255, 'default' => '', 'comment' => '描述'])
            ->addColumn('create_time', 'integer', ['default' => 0, 'comment' => '创建时间'])
            ->addColumn('update_time', 'integer', ['default' => 0, 'comment' => '更新时间'])
            ->addColumn('delete_time', 'integer', ['default' => 0, 'comment' => '删除时间'])
            ->create();
    }

    public function createAdminRole()
    {
        $table = $this->table('admin_role', [
            'engine' => 'InnoDB',
            'comment' => '后台角色',
            'collation' => 'utf8mb4_general_ci'
        ]);

        if ($table->exists()) {
            $table->drop();
        }

        $table
            ->addColumn('name', 'string', ['length' => 255, 'default' => '', 'comment' => '名称'])
            ->addColumn('create_time', 'integer', ['length' => 11, 'default' => 0, 'comment' => '创建时间'])
            ->addColumn('update_time', 'integer', ['length' => 11, 'default' => 0, 'comment' => '更新时间'])
            ->addColumn('delete_time', 'integer', ['length' => 11, 'default' => 0, 'comment' => '删除时间'])
            ->create();
    }

    public function createAdminRoleMenu()
    {
        $table = $this->table('admin_role_menu', [
            'engine' => 'InnoDB',
            'comment' => '角色与菜单关联',
            'collation' => 'utf8mb4_general_ci'
        ]);

        if ($table->exists()) {
            $table->drop();
        }

        $table
            ->addColumn('menu_id', 'integer', ['length' => 11, 'default' => 0, 'comment' => '菜单ID'])
            ->addColumn('role_id', 'integer', ['length' => 11, 'default' => 0, 'comment' => '角色ID'])
            ->create();
    }

    public function createAdminRoleUser()
    {
        $table = $this->table('admin_role_user', [
            'engine' => 'InnoDB',
            'comment' => '角色与管理员关联',
            'collation' => 'utf8mb4_general_ci'
        ]);

        if ($table->exists()) {
            $table->drop();
        }

        $table
            ->addColumn('menu_id', 'integer', ['length' => 11, 'default' => 0, 'comment' => '菜单ID'])
            ->addColumn('role_id', 'integer', ['length' => 11, 'default' => 0, 'comment' => '角色ID'])
            ->create();
    }

    public function createAdminSysconf()
    {
        $table = $this->table('admin_sysconf', [
            'engine' => 'InnoDB',
            'comment' => '系统配置',
            'collation' => 'utf8mb4_general_ci'
        ]);

        if ($table->exists()) {
            $table->drop();
        }

        $table
            ->addColumn('group', 'string', ['length' => 100, 'default' => '', 'comment' => '分组名'])
            ->addColumn('name', 'string', ['length' => 100, 'default' => '', 'comment' => '缓存名称'])
            ->addColumn('key', 'string', ['length' => 100, 'default' => '', 'comment' => '缓存key'])
            ->addColumn('value', 'text', ['comment' => '缓存值'])
            ->addColumn('type', 'string', ['length' => 255, 'default' => '', 'comment' => '设置缓存使用的Html表单类型。{"input":"单行文本输入框","single_select":"单选下拉框","multi_select":"多选下拉框","checkbox":"复选框","radio":"单选按钮","single_image":"图片","multi_image":"图片(多个)","single_file":"文件"}'])
            ->addColumn('tip', 'string', ['length' => 255, 'default' => '', 'comment' => '缓存描述'])
            ->addColumn('content', 'text', ['comment' => '字典数据'])
            ->addIndex(['group', 'key'],['unique'  =>  true])
            ->create();
    }
}
