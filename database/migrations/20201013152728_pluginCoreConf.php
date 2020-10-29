<?php

use think\migration\Migrator;

class PluginCoreConf extends Migrator
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
        $table = $this->table('plugin_core_conf', [
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
            ->addIndex(['group', 'key'], ['unique' => true]);

        $data = [
            [
                'group' => 'layTpSys',
                'key' => 'configGroup',
                'name' => 'layTp系统配置分组',
                'value' => '[{"name":"基础配置","value":"basic","icon":"layui-icon layui-icon-set-fill"},{"name":"上传配置","value":"upload","icon":"layui-icon layui-icon-upload-drag"}]',
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
                'key' => 'loginNeedCaptcha',
                'name' => '登录验证码',
                'value' => '1',
                'type' => 'switch',
                'tip' => '后台登录是否需要输入验证码',
                'content' => '[{"value":"0","text":"不需要"},{"value":"1","text":"需要"}]'
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
                'name' => '本地域名',
                'value' => '',
                'type' => 'input',
                'tip' => '本地上传方式，访问上传资源的域名，不要以/结尾',
                'content' => ''
            ],
            [
                'group' => 'upload',
                'key' => 'size',
                'name' => '最大上传文件大小',
                'value' => '500mb',
                'type' => 'input',
                'tip' => '最大上传文件大小，单位自行输入kb,mb,gb',
                'content' => ''
            ],
            [
                'group' => 'upload',
                'key' => 'mime',
                'name' => '允许上传的文件类型',
                'value' => 'jpg,png,bmp,jpeg,gif,zip,rar,xls,xlsx,mp4',
                'type' => 'input',
                'tip' => '允许上传的文件类型，多个以英文逗号分隔，*表示不限制',
                'content' => ''
            ],
            [
                'group' => 'upload',
                'key' => 'type',
                'name' => '上传方式',
                'value' => 'local',
                'type' => 'select_single',
                'tip' => '上传方式，七牛云和阿里云需要安装插件',
                'content' => '[{"value":"local","text":"本地"},{"value":"qiniu","text":"七牛云"},{"value":"aliyun","text":"阿里云"}]'
            ],
        ];

        $table->setData($data)->create();
    }
}