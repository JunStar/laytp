## 前言
> 基于ThinkPHP6+LayUI开发
<div align="center">
<img src="https://static.laytp.com/component/front/images/un28.png" width="126" height="66"/>
</div>
<p align="center"><strong>价值源自分享 成功在于坚持</strong></p>

<p align="center">
	<a href="https://www.laytp.com" target="_blank">官方平台</a> 
    <a href="https://demo.laytp.com" rel="nofollow" >在线演示</a>
	<a href="https://www.laytp.com/doc.html" target="_blank">框架文档</a>
</p>
<p align="center">
<font size="20" >开源不易，右上角请点击stars，感恩！</font>
</p>

<p>LayTp交流①群:843093362</p>

<b>在线演示 <a href="https://demo.laytp.com"  target="_blank">https://demo.laytp.com</a> </b><br/>
<b>超管账号  admin 123456 </b><br/>
<b>演示站仅提供数据查看功能，更多功能请自行在本地安装Laytp框架后体验<br/>

<img src="https://img.shields.io/badge/license-Apache-blue.svg" />
<img src="https://img.shields.io/badge/ThinkPHP-6.x.x-brightgreen.svg" alt="thinkphp">
<img src="https://img.shields.io/badge/Layui-2.6.n-red.svg" alt="layui">
<img src="https://gitee.com/junstar/laytp/badge/star.svg?theme=gvp" alt="star">
<img src="https://gitee.com/junstar/laytp/badge/fork.svg?theme=gvp" alt="fork">
<img src="https://img.shields.io/badge/LayTp交流①群-843093362-blue.svg" alt="一群">

## 开发初衷

Laytp开发的初衷是避免重复造轮子，提高开发效率！

## 框架优势

* `PHP框架选用简单且优美的ThinkPHP6`

* `参考官方文档，只需会PHP LayUI 开箱即用`

* `前后端完全分离，代码清晰明了`

* `封装常用Html表单元素，且仅需使用Html标记语言即可渲染表单元素`

* `基于Token验证的用户鉴权`

* `前端JavaScript鉴权，后端AUTH类鉴权，减少请求`
  
* `提供插件市场，方便框架丰富功能`

* `永久免费提供一键生成CURD插件，简单重复性CURD代码可以可视化一键生成`

* `永久免费提供一键生成Api文档插件，Api文档可以使用MEditor和代码注解生成`

* `代码安全质量高，修复大部分低危、高危代码漏洞`

## 集成功能

- [x] `API模块` token鉴权，签名规则
- [x] `权限管理` 后台权限管理
    - [x] `管理员管理` 后台管理员管理
    - [x] `角色管理` 后台管理员角色管理
    - [x] `菜单管理` 后台菜单管理
- [x] `系统配置` 每个配置页面独立设置，仅需复制配置样例，修改html文件即可拥有多个配置界面
- [x] `插件市场` 可开发定制属于自己的插件，可安装升级社区插件，目前提供的插件：
    - [x] `生成CRUD插件` 前后端代码的生成（php、html、layui、sql）支持一键生成CRUD 。
    - [x] `生成Api文档插件` Api文档可以使用MEditor和代码注解生成
    - [x] `UEditor编辑器插件` 与`阿里云OSS对象存储插件`和`七牛云KODO对象存储插件`深度兼容，编辑器的上传也可以上传到`阿里云`和`七牛云`
    - [x] `MEditor编辑器插件` 与`阿里云OSS对象存储插件`和`七牛云KODO对象存储插件`深度兼容，编辑器的上传也可以上传到`阿里云`和`七牛云`
    - [x] `阿里云OSS对象存储插件` 
    - [x] `七牛云KODO对象存储插件` 
    - [x] `阿里云短信插件`
    - [x] `Email邮件服务插件`
- [x] `操作日志` 用户后台操作日志，Api接口请求日志，后台管理员登录日志
- [x] `地区管理`
- [x] `附件管理`

## 常用表单元素封装
对于常用表单元素，在`Laytp`中，无需写太多JS代码，你只需要像如下这样
```
<div class="表单元素class标识"
     data-属性名="属性值"
     >
</div>
```
就可以渲染常用表单元素，比如上传组件，多选下拉框组件，编辑器，省市区联动下拉框等

## 安装使用
1、首先将本框架直接clone到你本地,或者直接下载
```
git clone https://gitee.com/junstar/laytp
```
2、创建一个数据库
```
数据库字符集 utf8mb4 -- UTF-8 Unicode
排序规则 utf8mb4_general_ci
```
3、复制根目录下的.example.env文件成.env文件，修改.env文件的数据库连接部分
```
[DATABASE]
TYPE = mysql
HOSTNAME = 127.0.0.1
DATABASE = 创建的数据库名称
USERNAME = 数据库用户名
PASSWORD = 数据库密码
HOSTPORT = 3306
CHARSET = utf8
DEBUG = true
PREFIX = 数据库前缀，推荐使用lt_
```
4、根目录执行 composer install 安装必要的Composer包，包括ThinkPHP6框架和ThinkPHP6的其他代码！
```
composer install
```
5、根目录执行ThinkPHP6的数据库迁移命令，这里会导入Laytp框架需要的数据库文件
```
php think migrate:run
```

## 软件截图
去demo站看吧
- 地址：https://demo.laytp.com
- 用户名：admin
- 密码：123456

## 框架文档
https://www.laytp.com/doc/laytp.html

## 更新日志
https://www.laytp.com/doc/laytp/7.html