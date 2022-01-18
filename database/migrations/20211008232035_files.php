<?php

use think\migration\Migrator;

class Files extends Migrator
{
    public function change()
    {
        $table = $this->table('files', [
            'engine' => 'InnoDB',
            'comment' => '附件管理',
            'collation' => 'utf8mb4_general_ci'
        ]);

        //删除表
        if ($table->exists()) {
            $table->drop();
        }

        $table
            ->addColumn('category_id', 'integer', ['limit' => 11, 'default' => '0','comment' => '所属分类'])
			->addColumn('name', 'string', ['limit' => 255, 'default' => '','comment' => '文件名称'])
			->addColumn('file_type', 'string', ['limit' => 255, 'default' => '','comment' => '文件类型.image=图片,video=视频,music=音频,file=文件,'])
			->addColumn('path', 'string', ['limit' => 255, 'default' => '','comment' => '文件路径'])
			->addColumn('upload_type', 'string', ['limit' => 255, 'default' => '','comment' => '上传方式.local=本地上传,ali-oss=阿里云OSS,qiniu-kodo=七牛云KODO,'])
			->addColumn('via_server', 'string', ['limit' => 255, 'default' => 'via','comment' => '上传是否经过服务器。via=经过，unVia=不经过，默认via'])
			->addColumn('ext', 'string', ['limit' => 255, 'default' => '','comment' => '文件后缀'])
			->addColumn('size', 'string', ['limit' => 255, 'default' => '','comment' => '文件大小，字节数，单位B'])
			->addColumn('create_admin_user_id', 'integer', ['limit' => 11, 'default' => '0','comment' => '创建者'])
			->addColumn('update_admin_user_id', 'integer', ['limit' => 11, 'default' => '0','comment' => '最后更新者'])
			->addColumn('create_time', 'datetime', ['null' => 1, 'comment' => '创建时间'])
			->addColumn('update_time', 'datetime', ['null' => 1, 'comment' => '更新时间'])
			->addColumn('delete_time', 'datetime', ['null' => 1, 'comment' => '删除时间'])
			
        ;

        $table->create();
    }
}