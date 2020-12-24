<?php

//header('Access-Control-Allow-Origin: http://www.baidu.com'); //设置http://www.baidu.com允许跨域访问
//header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With'); //设置允许的跨域header
date_default_timezone_set("Asia/chongqing");
error_reporting(E_ERROR);
header("Content-Type: text/html; charset=utf-8");

$CONFIG = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents("config.json")), true);

//兼容laytp上传配置 ------ 开始
define('ROOT_PATH', dirname(dirname(dirname(dirname(dirname(__DIR__))))));
$laytp_config        = require_once ROOT_PATH . '/config/laytp.php';
$laytp_upload_config = $laytp_config['upload'];
$arr_mimetype        = explode(',', $laytp_upload_config['mimetype']);
foreach ($arr_mimetype as $k => $v) {
    $arr_mimetype[$k] = '.' . $v;
}
$CONFIG['imageAllowFiles'] = $arr_mimetype;

$typeDict = ['b' => 0, 'k' => 1, 'kb' => 1, 'm' => 2, 'mb' => 2, 'gb' => 3, 'g' => 3];
preg_match('/(\d+)(\w+)/', $laytp_upload_config['maxsize'], $matches);
$type                     = strtolower($matches[2]);
$size                     = (int)$laytp_upload_config['maxsize'] * pow(1024, isset($typeDict[$type]) ? $typeDict[$type] : 0);
$CONFIG['imageMaxSize']   = $size;
$CONFIG['scrawlMaxSize']  = $size;
$CONFIG['catcherMaxSize'] = $size;
$CONFIG['videoMaxSize']   = $size;
$CONFIG['fileMaxSize']    = $size;
//兼容laytp上传配置 ------ 结束

$action = $_GET['action'];

switch ($action) {
    case 'config':
        $result = json_encode($CONFIG);
        break;

    /* 上传图片 */
    case 'uploadimage':
        /* 上传涂鸦 */
    case 'uploadscrawl':
        /* 上传视频 */
    case 'uploadvideo':
        /* 上传文件 */
    case 'uploadfile':
        $result = include("action_upload.php");
        break;

    /* 列出图片 */
    case 'listimage':
        $result = include("action_list.php");
        break;
    /* 列出文件 */
    case 'listfile':
        $result = include("action_list.php");
        break;

    /* 抓取远程文件 */
    case 'catchimage':
        $result = include("action_crawler.php");
        break;

    default:
        $result = json_encode([
            'state' => '请求地址出错',
        ]);
        break;
}

/* 输出结果 */
if (isset($_GET["callback"])) {
    if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
        echo htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
    } else {
        echo json_encode([
            'state' => 'callback参数不合法',
        ]);
    }
} else {
    echo $result;
}