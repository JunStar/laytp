<?php

use think\migration\Migrator;

class PluginApiDoc extends Migrator
{
    public function change()
    {
        $table = $this->table('plugin_apidoc', [
            'engine'    => 'InnoDB',
            'comment'   => 'Api文档',
            'collation' => 'utf8mb4_general_ci',
        ]);

        //删除表
        if ($table->exists()) {
            $table->drop();
        }

        $table
            ->addColumn('title', 'string', ['limit' => 255, 'default' => '', 'comment' => '标题'])
            ->addColumn('des', 'text', ['comment' => '描述'])
            ->addColumn('create_time', 'datetime', ['null' => 1, 'comment' => '创建时间'])
            ->addColumn('update_time', 'datetime', ['null' => 1, 'comment' => '更新时间'])
            ->addColumn('delete_time', 'datetime', ['null' => 1, 'comment' => '删除时间'])
        ;

        $data = [
            [
                'title' => '文档更新',
                'des' => '本文档分为两个部分，`全局说明文档`和`其他文档`。

# 全局说明文档
全局说明下的文档在后台生成Api文档菜单中进行添加，添加完成后点击生成Api文档按钮即可在本文档中看见

# 其他文档
其他文档使用PHP程序，通过PHP的类反射机制，获取到`app/controller/api/`目录下所有文件的注解来进行生成。

注解规则请查阅`laytp.com`官网手册或者框架`app/controller/api/Demo.php`文件。

如果后端程序员更新了`api`接口程序，并且使用了相应的注解规则，在后台点击`生成Api文档`按钮即可在本文档看到最新的Api文档

# 注意点
由于文档使用的是静态html展示，而浏览器对html页面是有缓存的。所以如果文档进行了更新。可能需要强制刷新页面才能看到最新的文档。',
                'create_time' => date('Y-m-d H:i:s'),
                'update_time' => date('Y-m-d H:i:s'),
            ],
            [
                'title' => '在线测试功能使用',
                'des' => '本文档使用`Laytp极速后台开发框架 - 生成Api文档插件`进行生成。
如果需要使用本文档的在线测试功能，需要先进行Api文档配置。

### Api文档配置
点击本文档右上角配置图标会弹出配置层。
配置项包括：Api请求域名、签名开关、Header参数。

#### Api请求域名
后台使用单域名部署模式，则无需修改，使用默认值即可。
后台使用多域名部署模式，则后端程序员需要提供请求Api的域名地址。使用者不要以/结尾，将请求Api的域名地址填入此处

#### 签名开关
请求Api有签名中间件，签名中间件是否启用要根据后台[系统配置 - 基础配置 - Api签名开关]是否开启的配置来决定。如果后台配置[Api签名开关]开启了，此处也要开启。此处开启后，使用本文档的在线测试功能时，在ajax请求头部会自动添加request-time和sign两个参数。至于签名具体如何生成，请查阅[签名相关]章节说明

#### Header参数
此处填入后端程序员自定义的请求头部参数。框架自定义的请求头部参数有用户登录凭证token，请求时间戳request-time，签名sign三个。一个复杂的系统，一般还会在Header头部定义一些公用参数。比如平台标识，代理标识等等。这些公用参数根据需求不同由后端程序员定义，在使用本文档时，自行在此处进行添加

### 配置保存
点击保存按钮，或者点击页面阴影部分遮罩层，配置都会进行保存

### 配置持久化
Api文档配置使用的是localStorage存储在浏览器端进行持久化的。无需担心页面关闭后，配置不存在的问题',
                'create_time' => date('Y-m-d H:i:s'),
                'update_time' => date('Y-m-d H:i:s'),
            ],
            [
                'title' => '统一说明',
                'des' => '# 请求方式
默认使用POST方式进行请求

# 请求头Content-Type
请求头的Content-Type定义请求参数的数据类型。ThinkPHP6兼容多种常用请求头方式。比如`application/json`、`application/form-data`和 `application/x-www-form-urlencoded`。

一般的接口，客户端可以使用`application/json`，自行将参数定义成`json`格式进行参数传递。

文件上传不支持`json`方式上传文件`Base64`内容，需要使用提交表单方式上传文件。

# 接口域名
- 正式环境
由后端程序员提供

- 测试环境
由后端程序员提供

# 后台地址
- 正式环境
    - 访问地址
        由后端程序员提供
    - 账号
        admin
    - 密码
        123456
- 测试环境
    - 访问地址
        由后端程序员提供
    - 账号
        admin
    - 密码
        123456',
                'create_time' => date('Y-m-d H:i:s'),
                'update_time' => date('Y-m-d H:i:s'),
            ],
            [
                'title' => '签名相关',
                'des' => '客户端请求Api接口程序，后端有签名中间件对请求进行拦截。

签名中间件是否启用要根据后台[系统配置 - 基础配置 - Api签名开关]是否开启的配置来决定。

如果后台配置[Api签名开关]开启了，那么客户端在请求Api接口时，需要在Header部分传递两个参数`request-time`和`sign`。

`request-time`的值由客户端自行定义为当前Unix时间戳。

`sign`的值由签名生成规则计算生成。

# 签名生成规则
- 客户端自行定义request-time的值为当前Unix时间戳，并经过`md5`运算得到`stringA`

- 后台系统配置 - 基础配置 - Api签名Key的值为生成签名的Key，并经过`md5`运算得到`stringB`

- 连接`stringA`和`stringB`后经过`md5`运算得到`stringC`

- 最后将`stringC`全部转成大写即是客户端需要在请求头部分传递的`sign`值',
                'create_time' => date('Y-m-d H:i:s'),
                'update_time' => date('Y-m-d H:i:s'),
            ],
            [
                'title' => '接口返回',
                'des' => '接口统一返回`json`格式的数据。

# 返回说明
                
|参数名|必然存在|类型|说明|
|:----    |:---|:----- |-----   |
|code |是  |integer |接口返回码.0=常规正确码，表示常规操作成功；1=常规错误码，客户端仅需提示message；其他返回码与具体业务相关。框架实现了的唯一其他返回码：10401，前端需要跳转至登录界面。在一个复杂的交互过程中，你可能需要自行定义其他返回码|
|msg |是  |string | 接口返回文字描述    |
|time |是  |integer | 接口返回时间戳，单位秒 |
|data |是  |object/array | 附加数据。单条数据是对象，多条数据是数组。当为空时，会返回一个空对象{}|
 
# 返回示例
 ``` 
 {
 	"code":1,
 	"msg":"签名错误",
 	"time":1613628412,
 	"data":{}
 }
 ```',
                'create_time' => date('Y-m-d H:i:s'),
                'update_time' => date('Y-m-d H:i:s'),
            ],
        ];

        $table->setData($data)->create();
    }
}