<?php
/**
 * LayTp安装程序
 *
 * 安装完成后建议删除此文件
 */
// error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
// ini_set('display_errors', '1');
// 定义目录分隔符
define('DS', DIRECTORY_SEPARATOR);

// 定义根目录
define('ROOT_PATH', __DIR__ . DS . '..' . DS);

// 定义应用目录
define('APP_PATH', ROOT_PATH . 'application' . DS);

// 安装包目录
define('INSTALL_PATH', APP_PATH . 'admin' . DS . 'command' . DS . 'Install' . DS);

// 判断文件或目录是否有写的权限
function is_really_writable($file)
{
    if (DIRECTORY_SEPARATOR == '/' AND @ ini_get("safe_mode") == FALSE) {
        return is_writable($file);
    }
    if (!is_file($file) OR ($fp = @fopen($file, "r+")) === FALSE) {
        return FALSE;
    }

    fclose($fp);
    return TRUE;
}

$sitename = "LayTp";

$link = array(
    'qqun'  => "http://shang.qq.com/wpa/qunwpa?idkey=feb89577b675eaea3b45e4cbc86f38a1024ef759430b4b0bc2ae3d7fce110892",
    'gitee' => 'https://gitee.com/junstar/laytp/issues',
    'home'  => 'http://www.laytp.com',
    'forum' => 'http://www.laytp.com',
    'doc'   => 'http://www.laytp.com',
);

// 检测目录是否存在
$checkDirs = [
    'thinkphp',
    'vendor'
];
//缓存目录
$runtimeDir = APP_PATH . 'runtime';

//错误信息
$errInfo = '';

//数据库配置文件
$dbConfigFile = ROOT_PATH . DS . 'config' . DS . 'database.php';

// 锁定的文件
$lockFile = INSTALL_PATH . 'install.lock';
if (is_file($lockFile)) {
    $errInfo = "当前已经安装{$sitename}，如果需要重新安装，请手动移除application/admin/command/Install/install.lock文件";
} else if (version_compare(PHP_VERSION, '5.5.0', '<')) {
    $errInfo = "当前版本(" . PHP_VERSION . ")过低，请使用PHP5.5以上版本";
} else if (!extension_loaded("PDO")) {
    $errInfo = "当前未开启PDO，无法进行安装";
} else if (!is_really_writable($dbConfigFile)) {
    $errInfo = '当前权限不足，无法写入配置文件application/config.php/database.php';
} else {
    $dirArr = [];
    foreach ($checkDirs as $k => $v) {
        if (!is_dir(ROOT_PATH . $v)) {
            $errInfo = '当前代码仅包含核心代码，请前往官网下载完整包或资源包覆盖后再尝试安装';
            break;
        }
    }
}
// 当前是POST请求
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($errInfo) {
        echo $errInfo;
        exit;
    }
    $err = '';
    $mysqlHostname = isset($_POST['mysqlHost']) ? $_POST['mysqlHost'] : '127.0.0.1';
    $mysqlHostport = 3306;
    $hostArr = explode(':', $mysqlHostname);
    if (count($hostArr) > 1) {
        $mysqlHostname = $hostArr[0];
        $mysqlHostport = $hostArr[1];
    }
    $mysqlUsername = isset($_POST['mysqlUsername']) ? $_POST['mysqlUsername'] : 'root';
    $mysqlPassword = isset($_POST['mysqlPassword']) ? $_POST['mysqlPassword'] : '';
    $mysqlDatabase = isset($_POST['mysqlDatabase']) ? $_POST['mysqlDatabase'] : 'laytp';
    $mysqlPrefix = isset($_POST['mysqlPrefix']) ? $_POST['mysqlPrefix'] : 'lt_';
    $adminUsername = isset($_POST['adminUsername']) ? $_POST['adminUsername'] : 'admin';
    $adminPassword = isset($_POST['adminPassword']) ? $_POST['adminPassword'] : '123456';
    $adminPasswordConfirmation = isset($_POST['adminPasswordConfirmation']) ? $_POST['adminPasswordConfirmation'] : '123456';

    if ($adminPassword !== $adminPasswordConfirmation) {
        echo "两次输入的密码不一致";
        exit;
    } else if (!preg_match("/^\w+$/", $adminUsername)) {
        echo "用户名只能输入字母、数字、下划线";
        exit;
    } else if (!preg_match("/^[\S]+$/", $adminPassword)) {
        echo "密码不能包含空格";
        exit;
    } else if (strlen($adminUsername) < 3 || strlen($adminUsername) > 12) {
        echo "用户名请输入3~12位字符";
        exit;
    } else if (strlen($adminPassword) < 5 || strlen($adminPassword) > 16) {
        echo "密码请输入5~16位字符";
        exit;
    }
    try {
        //检测能否读取安装文件
        $sql = @file_get_contents(INSTALL_PATH . 'laytp.sql');
        if (!$sql) {
            throw new Exception("无法读取application/admin/command/Install/laytp.sql文件，请检查是否有读权限");
        }
        $sql = str_replace("`lt_", "`{$mysqlPrefix}", $sql);
        $pdo = new PDO("mysql:host={$mysqlHostname};port={$mysqlHostport}", $mysqlUsername, $mysqlPassword, array(
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
        ));

        //检测是否支持innodb存储引擎
        $pdoStatement = $pdo->query("SHOW VARIABLES LIKE 'innodb_version'");
        $result = $pdoStatement->fetch();
        if (!$result) {
            throw new Exception("当前数据库不支持innodb存储引擎，请开启后再重新尝试安装");
        }

        $pdo->query("CREATE DATABASE IF NOT EXISTS `{$mysqlDatabase}` CHARACTER SET utf8 COLLATE utf8_general_ci;");

        $pdo->query("USE `{$mysqlDatabase}`");

        $pdo->exec($sql);

        $config = @file_get_contents($dbConfigFile);
        $callback = function ($matches) use ($mysqlHostname, $mysqlHostport, $mysqlUsername, $mysqlPassword, $mysqlDatabase, $mysqlPrefix) {
            $field = ucfirst($matches[1]);
            $replace = ${"mysql{$field}"};
            if ($matches[1] == 'hostport' && $mysqlHostport == 3306) {
                $replace = '';
            }
            return "'{$matches[1]}'{$matches[2]}=>{$matches[3]}Env::get('database.{$matches[1]}', '{$replace}'),";
        };
        $config = preg_replace_callback("/'(hostname|database|username|password|hostport|prefix)'(\s+)=>(\s+)Env::get\((.*)\)\,/", $callback, $config);

        //检测能否成功写入数据库配置
        $result = @file_put_contents($dbConfigFile, $config);

        if (!$result) {
            throw new Exception("无法写入数据库信息到application/database.php文件，请检查是否有写权限");
        }

        //检测能否成功写入lock文件
        $result = @file_put_contents($lockFile, 1);
        if (!$result) {
            throw new Exception("无法写入安装锁定到application/admin/command/Install/install.lock文件，请检查是否有写权限");
        }

        $newPassword = password_hash($adminPassword, PASSWORD_DEFAULT);
        $pdo->query("UPDATE {$mysqlPrefix}admin_user SET username = '{$adminUsername}', password = '{$newPassword}' WHERE username = 'admin'");
        echo "success";
    } catch (PDOException $e) {
        $err = $e->getMessage();
    } catch (Exception $e) {
        $err = $e->getMessage();
    }
    echo $err;
    exit;
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>安装<?php echo $sitename; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1">
    <meta name="renderer" content="webkit">

    <style>
        body {
            background: #fff;
            margin: 0;
            padding: 0;
            line-height: 1.5;
        }

        body, input, button {
            font-family: 'Open Sans', sans-serif;
            font-size: 16px;
            color: #7E96B3;
        }

        .container {
            max-width: 515px;
            margin: 0 auto;
            padding: 20px;
            text-align: center;
        }

        a {
            color: #18bc9c;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        h1 {
            margin-top: 0;
            margin-bottom: 10px;
        }

        h2 {
            font-size: 28px;
            font-weight: normal;
            color: #3C5675;
            margin-bottom: 0;
        }

        form {
            margin-top: 40px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group .form-field:first-child input {
            border-top-left-radius: 4px;
            border-top-right-radius: 4px;
        }

        .form-group .form-field:last-child input {
            border-bottom-left-radius: 4px;
            border-bottom-right-radius: 4px;
        }

        .form-field input {
            background: #EDF2F7;
            margin: 0 0 1px;
            border: 2px solid transparent;
            transition: background 0.2s, border-color 0.2s, color 0.2s;
            width: 100%;
            padding: 15px 15px 15px 180px;
            box-sizing: border-box;
        }

        .form-field input:focus {
            border-color: #18bc9c;
            background: #fff;
            color: #444;
            outline: none;
        }

        .form-field label {
            float: left;
            width: 160px;
            text-align: right;
            margin-right: -160px;
            position: relative;
            margin-top: 18px;
            font-size: 14px;
            pointer-events: none;
            opacity: 0.7;
        }

        button, .btn {
            background: #3C5675;
            color: #fff;
            border: 0;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
            padding: 15px 30px;
            -webkit-appearance: none;
        }

        button[disabled] {
            opacity: 0.5;
        }

        #error, .error, #success, .success {
            background: #D83E3E;
            color: #fff;
            padding: 15px 20px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        #success {
            background: #3C5675;
        }

        #error a, .error a {
            color: white;
            text-decoration: underline;
        }
    </style>
</head>

<body>
<div class="container">
    <h2>安装 <?php echo $sitename; ?></h2>
    <div>

        <p>若你在安装中遇到麻烦可点击
            <a href="<?php echo $link['doc']; ?>" target="_blank">安装文档</a>
            <a href="<?php echo $link['forum']; ?>" target="_blank">交流社区</a>
            <a href="<?php echo $link['qqun']; ?>" target="_blank">QQ交流群</a>
        </p>

        <form method="post">
            <?php if ($errInfo): ?>
                <div class="error">
                    <?php echo $errInfo; ?>
                </div>
            <?php endif; ?>
            <div id="error" style="display:none"></div>
            <div id="success" style="display:none"></div>

            <div class="form-group">
                <div class="form-field">
                    <label>MySQL 数据库地址</label>
                    <input type="text" name="mysqlHost" value="127.0.0.1" required="">
                </div>

                <div class="form-field">
                    <label>MySQL 数据库名</label>
                    <input type="text" name="mysqlDatabase" value="laytp" required="">
                </div>

                <div class="form-field">
                    <label>MySQL 用户名</label>
                    <input type="text" name="mysqlUsername" value="root" required="">
                </div>

                <div class="form-field">
                    <label>MySQL 密码</label>
                    <input type="password" name="mysqlPassword">
                </div>

                <div class="form-field">
                    <label>MySQL 数据表前缀</label>
                    <input type="text" name="mysqlPrefix" value="lt_">
                </div>
            </div>

            <div class="form-group">
                <div class="form-field">
                    <label>管理者用户名</label>
                    <input name="adminUsername" value="admin" required=""/>
                </div>

                <div class="form-field">
                    <label>管理者密码</label>
                    <input type="password" name="adminPassword" required="">
                </div>

                <div class="form-field">
                    <label>重复密码</label>
                    <input type="password" name="adminPasswordConfirmation" required="">
                </div>
            </div>

            <div class="form-buttons">
                <button type="submit" <?php echo $errInfo ? 'disabled' : '' ?>>点击安装</button>
            </div>
        </form>

        <script src="https://cdn.bootcss.com/jquery/2.1.4/jquery.min.js"></script>
        <script>
            $(function () {
                $('form :input:first').select();

                $('form').on('submit', function (e) {
                    e.preventDefault();

                    var $button = $(this).find('button')
                        .text('安装中...')
                        .prop('disabled', true);

                    $.post('', $(this).serialize())
                        .done(function (ret) {
                            if (ret === 'success') {
                                $('#error').hide();
                                $("#success").text("安装成功！开始你的<?php echo $sitename; ?>之旅吧！").show();
                                $('<a class="btn" href="./">访问首页</a> <a class="btn" href="/admin" style="background:#18bc9c">访问后台</a>').insertAfter($button);
                                $button.remove();
                                localStorage.setItem("laytp", "installed");
                            } else {
                                $('#error').show().text(ret);
                                $button.prop('disabled', false).text('点击安装');
                                $("html,body").animate({
                                    scrollTop: 0
                                }, 500);
                            }
                        })
                        .fail(function (data) {
                            $('#error').show().text('发生错误:\n\n' + data.responseText);
                            $button.prop('disabled', false).text('点击安装');
                            $("html,body").animate({
                                scrollTop: 0
                            }, 500);
                        });

                    return false;
                });
            });
        </script>
    </div>
</div>
</body>
</html>