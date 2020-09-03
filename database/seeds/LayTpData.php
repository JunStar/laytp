<?php

use think\migration\Seeder;

class LayTpData extends Seeder
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run()
    {
        $this->initAdminUser();//初始化管理员
        $this->initAdminMenu();//初始化菜单
        $this->initSysConf();//初始化系统配置
    }

    /**
     * 初始化管理员
     */
    public function initAdminUser()
    {
        $data = [
            'username' => 'admin',
            'nickname' => '超级管理员',
            'password' => password_hash('123456', PASSWORD_DEFAULT),
            'is_super_manager' => 1,
            'create_time' => time(),
            'update_time' => time(),
        ];

        $this->insert('admin_user', $data);
    }

    /**
     * 初始化菜单
     */
    public function initAdminMenu()
    {
        $data = [
            '1'=>[
                'name' => '控制台',
                'rule' => 'admin/dashboard/index',
                'is_menu' => 1,
                'sort' => 0,
                'pid' => 0,
                'is_show' => 1,
                'icon' => 'layui-icon layui-icon-console',
                'des' => '控制台',
                'create_time' => time(),
                'update_time' => time(),
            ],
            '2'=>[
                'name' => '快捷菜单',
                'rule' => 'admin/dashboard/index',
                'is_menu' => 1,
                'sort' => 0,
                'pid' => 1,
                'is_show' => 1,
                'icon' => 'layui-icon layui-icon-console',
                'des' => '快捷菜单',
                'create_time' => time(),
                'update_time' => time(),
            ],
            '3'=>[
                'name' => '首页',
                'rule' => 'admin/dashboard/index',
                'is_menu' => 1,
                'sort' => 0,
                'pid' => 2,
                'is_show' => 1,
                'icon' => 'layui-icon layui-icon-home',
                'des' => '快捷菜单',
                'create_time' => time(),
                'update_time' => time(),
            ],
            '4'=>[
                'name' => '权限管理',
                'rule' => 'admin/auth.user/index',
                'is_menu' => 1,
                'sort' => 0,
                'pid' => 1,
                'is_show' => 1,
                'icon' => 'layui-icon layui-icon-user',
                'des' => '权限管理',
                'create_time' => time(),
                'update_time' => time(),
            ],
            '5'=>[
                'name' => '后台管理员',
                'rule' => 'admin/auth.user/index',
                'is_menu' => 1,
                'sort' => 0,
                'pid' => 4,
                'is_show' => 1,
                'icon' => 'layui-icon layui-icon-friends',
                'des' => '后台管理员',
                'create_time' => time(),
                'update_time' => time(),
            ],
            '6'=>[
                'name' => '角色管理',
                'rule' => 'admin/auth.role/index',
                'is_menu' => 1,
                'sort' => 0,
                'pid' => 4,
                'is_show' => 1,
                'icon' => 'layui-icon layui-icon-username',
                'des' => '角色管理',
                'create_time' => time(),
                'update_time' => time(),
            ],
            '7'=>[
                'name' => '菜单管理',
                'rule' => 'admin/auth.menu/index',
                'is_menu' => 1,
                'sort' => 0,
                'pid' => 4,
                'is_show' => 1,
                'icon' => 'layui-icon layui-icon-menu-fill',
                'des' => '菜单管理',
                'create_time' => time(),
                'update_time' => time(),
            ],
            '8'=>[
                'name' => '访问日志',
                'rule' => 'admin/log/index',
                'is_menu' => 1,
                'sort' => 0,
                'pid' => 4,
                'is_show' => 1,
                'icon' => 'layui-icon layui-icon-list',
                'des' => '访问日志',
                'create_time' => time(),
                'update_time' => time(),
            ],
            '9'=>[
                'name' => '常规管理',
                'rule' => '',
                'is_menu' => 1,
                'sort' => 0,
                'pid' => 1,
                'is_show' => 1,
                'icon' => 'layui-icon layui-icon-util',
                'des' => '常规管理',
                'create_time' => time(),
                'update_time' => time(),
            ],
            '10'=>[
                'name' => '系统配置',
                'rule' => 'admin/sysconf/index',
                'is_menu' => 1,
                'sort' => 0,
                'pid' => 9,
                'is_show' => 1,
                'icon' => 'layui-icon layui-icon-set-sm',
                'des' => '系统配置',
                'create_time' => time(),
                'update_time' => time(),
            ],
//            '11'=>[
//                'name' => '添加分组',
//                'rule' => 'admin/sysconf/addGroup',
//                'is_menu' => 0,
//                'sort' => 0,
//                'pid' => 10,
//                'is_show' => 1,
//                'icon' => 'layui-icon layui-icon-set-sm',
//                'des' => '添加分组',
//                'create_time' => time(),
//                'update_time' => time(),
//            ],
            '12'=>[
                'name' => '个人设置',
                'rule' => 'admin/general/profile',
                'is_menu' => 1,
                'sort' => 0,
                'pid' => 9,
                'is_show' => 1,
                'icon' => 'layui-icon layui-icon-username',
                'des' => '个人设置',
                'create_time' => time(),
                'update_time' => time(),
            ],
        ];

        foreach ($data as $d) {
            $this->insert('admin_menu', $d);
        }
    }

    public function initSysConf(){
        $data = [
            [
                'group' => 'layTpSys',
                'key' => 'configGroup',
                'name' => 'layTp系统配置分组',
                'value' => '{"basic":"\u57fa\u7840\u914d\u7f6e","upload":"\u4e0a\u4f20\u914d\u7f6e"}',
                'type' => 'array',
                'tip' => '系统配置的tab切换',
                'content' => ''
            ],
            [
                'group' => 'basic',
                'key' => 'siteName',
                'name' => '站点名称',
                'value' => 'LayTp极速后台开发框架',
                'type' => 'input',
                'tip' => '站点名称',
                'content' => ''
            ],
            [
                'group' => 'basic',
                'key' => 'loginCaptcha',
                'name' => '登录验证码',
                'value' => '0',
                'type' => 'switch',
                'tip' => '后台登录是否需要输入验证码',
                'content' => '{{"value":"0","text":"不需要"},{"value":"1","text":"需要"}}'
            ],
            [
                'group' => 'basic',
                'key' => 'firstMenuNum',
                'name' => '顶部菜单显示个数',
                'value' => '7',
                'type' => 'input',
                'tip' => '顶部菜单显示个数，超过时，会隐藏多余的菜单',
                'content' => ''
            ],
            [
                'group' => 'apiDoc',
                'key' => 'apiDocFileName',
                'name' => '',
                'value' => 'api',
                'type' => '',
                'tip' => '',
                'content' => ''
            ],
            [
                'group' => 'apiDoc',
                'key' => 'apiDocTitle',
                'name' => '',
                'value' => 'LayTp - api文档',
                'type' => '',
                'tip' => '',
                'content' => ''
            ],
            [
                'group' => 'upload',
                'key' => 'domain',
                'name' => '域名',
                'value' => '',
                'type' => 'input',
                'tip' => '访问上传文件的域名',
                'content' => ''
            ],
            [
                'group' => 'upload',
                'key' => 'maxsize',
                'name' => '最大上传文件大小',
                'value' => '500mb',
                'type' => 'input',
                'tip' => '最大上传文件大小，单位自行输入kb,mb,gb',
                'content' => ''
            ],
            [
                'group' => 'upload',
                'key' => 'mimeType',
                'name' => '允许上传的文件类型',
                'value' => 'jpg,png,bmp,jpeg,gif,zip,rar,xls,xlsx,mp4',
                'type' => 'input',
                'tip' => '允许上传的文件类型，多个以英文逗号分隔，*表示不限制',
                'content' => ''
            ],
            [
                'group' => 'upload',
                'key' => 'switch',
                'name' => '本地上传开关',
                'value' => 'open',
                'type' => 'switch',
                'tip' => '本地上传开关',
                'content' => '{{"value":"close","text":"关闭"},{"value":"open","text":"打开"}}'
            ],
        ];

        foreach ($data as $d) {
            $this->insert('admin_sysconf', $d);
        }
    }
}