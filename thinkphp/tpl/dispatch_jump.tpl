{__NOLAYOUT__}<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
    <title>跳转提示</title>
</head>
<body>
<script src="__STATIC__/library/layui/layui.js" charset="utf-8"></script>
<script src="__STATIC__/library/layui/layui_config.js" charset="utf-8"></script>
<!--
* $msg 待提示的消息
* $url 待跳转的链接
* $time 弹出维持时间（单位秒）
* icon 这里主要有两个layer的表情，5和6，代表（哭和笑）
-->
<script type="text/javascript">
    layui.use(['layTp'],function() {
        var msg = '<?php echo(strip_tags($msg));?>';
        var iurl = '<?php echo($url);?>';
        var wait = '<?php echo($wait);?>';
    <?php
        switch ($code) {
            case 1:
                    ?>
                layui.layer.msg(msg,{icon:"6"});
            <?php
                break;
            case 0:
                    ?>
                layui.layer.msg(msg,{icon:"5"});
            <?php
                break;
        }
            ?>
    });
</script>
</body>
</html>
