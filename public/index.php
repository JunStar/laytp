<?php
namespace think;

//允许跨域
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: token, Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Methods: POST,GET');

define('APP_PATH', __DIR__ . '/../application/');

// 判断是否安装LayTp
if (!is_file(APP_PATH . 'admin/command/Install/install.lock'))
{
    header("location:/install.php");
    exit;
}

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
    define('LT_VERSION', '1.0.0');
}

// 加载基础文件
require __DIR__ . '/../thinkphp/base.php';

// 支持事先使用静态方法设置Request对象和Config对象

// 执行应用并响应
Container::get('app')->run()->send();
