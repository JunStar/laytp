# 记录使用过的命令
```
#安装composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
#composer镜像使用阿里云的镜像
composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/
#安装tp6.x稳定版
composer create-project topthink/think laytp2.0
#更新tp版本
composer update topthink/framework
#安装tp多应用模式扩展
composer require topthink/think-multi-app
#安装数据库迁移工具
composer require topthink/think-migration
#创建laytp数据库迁移文件
php think migrate:create Laytp
#执行tp数据库迁移文件
php think migrate:run
#创建插入数据库数据文件，用于初始化数据库的数据
php think seed:create Laytp
#执行数据插入
php think seed:run
#安装tp模板引擎，一键生成Api文档需要使用到
composer require topthink/think-view
#验证码库
composer require topthink/think-captcha
#ide助手
composer require topthink/think-ide-helper

##用户安装过程
#安装composer库
composer update
#修改database.php或者.env文件，配置数据库连接
#执行数据库迁移命令，创建数据表，给数据表设置默认数据
php think migrate:run
php think seed:run
#配置nginx，以local.laytp2.com域名为例
1.本地hosts增加如下一行
127.0.0.1 local.laytp2.com
2.修改nginx虚拟主机目录，增加local.laytp2.com.conf文件，文件内容如下：
-nginx配置内容

```