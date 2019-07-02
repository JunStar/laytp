<?php
    if(!function_exists('parse_padding')){
        function parse_padding($source)
        {
            $length  = strlen(strval(count($source['source']) + $source['first']));
            return 40 + ($length - 1) * 8;
        }
    }

    if(!function_exists('parse_class')){
        function parse_class($name)
        {
            $names = explode('\\', $name);
            return '<abbr title="'.$name.'">'.end($names).'</abbr>';
        }
    }

    if(!function_exists('parse_file')){
        function parse_file($file, $line)
        {
            return '<a class="toggle" title="'."{$file} line {$line}".'">'.basename($file)." line {$line}".'</a>';
        }
    }

    if(!function_exists('parse_args')){
        function parse_args($args)
        {
            $result = [];

            foreach ($args as $key => $item) {
                switch (true) {
                    case is_object($item):
                        $value = sprintf('<em>object</em>(%s)', parse_class(get_class($item)));
                        break;
                    case is_array($item):
                        if(count($item) > 3){
                            $value = sprintf('[%s, ...]', parse_args(array_slice($item, 0, 3)));
                        } else {
                            $value = sprintf('[%s]', parse_args($item));
                        }
                        break;
                    case is_string($item):
                        if(strlen($item) > 20){
                            $value = sprintf(
                                '\'<a class="toggle" title="%s">%s...</a>\'',
                                htmlentities($item),
                                htmlentities(substr($item, 0, 20))
                            );
                        } else {
                            $value = sprintf("'%s'", htmlentities($item));
                        }
                        break;
                    case is_int($item):
                    case is_float($item):
                        $value = $item;
                        break;
                    case is_null($item):
                        $value = '<em>null</em>';
                        break;
                    case is_bool($item):
                        $value = '<em>' . ($item ? 'true' : 'false') . '</em>';
                        break;
                    case is_resource($item):
                        $value = '<em>resource</em>';
                        break;
                    default:
                        $value = htmlentities(str_replace("\n", '', var_export(strval($item), true)));
                        break;
                }

                $result[] = is_int($key) ? $value : "'{$key}' => {$value}";
            }

            return implode(', ', $result);
        }
    }
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>系统发生错误</title>
    <meta name="robots" content="noindex,nofollow" />
    <style>
        /* Base */
        body {
            color: #333;
            font: 16px Verdana, "Helvetica Neue", helvetica, Arial, 'Microsoft YaHei', sans-serif;
            margin: 0;
            padding: 0 20px 20px;
        }
        h1{
            margin: 10px 0 0;
            font-size: 28px;
            font-weight: 500;
            line-height: 32px;
        }
        h2{
            color: #4288ce;
            font-weight: 400;
            padding: 6px 0;
            margin: 6px 0 0;
            font-size: 18px;
            border-bottom: 1px solid #eee;
        }
        h3{
            margin: 12px;
            font-size: 16px;
            font-weight: bold;
        }
        abbr{
            cursor: help;
            text-decoration: underline;
            text-decoration-style: dotted;
        }
        a{
            color: #868686;
            cursor: pointer;
        }
        a:hover{
            text-decoration: underline;
        }
        .line-error{
            background: #f8cbcb;
        }

        .echo table {
            width: 100%;
        }

        .echo pre {
            padding: 16px;
            overflow: auto;
            font-size: 85%;
            line-height: 1.45;
            background-color: #f7f7f7;
            border: 0;
            border-radius: 3px;
            font-family: Consolas, "Liberation Mono", Menlo, Courier, monospace;
        }

        .echo pre > pre {
            padding: 0;
            margin: 0;
        }

        /* Exception Info */
        .exception {
            margin-top: 20px;
        }
        .exception .message{
            padding: 12px;
            border: 1px solid #ddd;
            border-bottom: 0 none;
            line-height: 18px;
            font-size:16px;
            border-top-left-radius: 4px;
            border-top-right-radius: 4px;
            font-family: Consolas,"Liberation Mono",Courier,Verdana,"微软雅黑";
        }

        .exception .code{
            float: left;
            text-align: center;
            color: #fff;
            margin-right: 12px;
            padding: 16px;
            border-radius: 4px;
            background: #999;
        }
        .exception .source-code{
            padding: 6px;
            border: 1px solid #ddd;

            background: #f9f9f9;
            overflow-x: auto;

        }
        .exception .source-code pre{
            margin: 0;
        }
        .exception .source-code pre ol{
            margin: 0;
            color: #4288ce;
            display: inline-block;
            min-width: 100%;
            box-sizing: border-box;
        font-size:14px;
            font-family: "Century Gothic",Consolas,"Liberation Mono",Courier,Verdana;
            padding-left: <?php echo (isset($source) && !empty($source)) ? parse_padding($source) : 40;  ?>px;
        }
        .exception .source-code pre li{
            border-left: 1px solid #ddd;
            height: 18px;
            line-height: 18px;
        }
        .exception .source-code pre code{
            color: #333;
            height: 100%;
            display: inline-block;
            border-left: 1px solid #fff;
        font-size:14px;
            font-family: Consolas,"Liberation Mono",Courier,Verdana,"微软雅黑";
        }
        .exception .trace{
            padding: 6px;
            border: 1px solid #ddd;
            border-top: 0 none;
            line-height: 16px;
        font-size:14px;
            font-family: Consolas,"Liberation Mono",Courier,Verdana,"微软雅黑";
        }
        .exception .trace ol{
            margin: 12px;
        }
        .exception .trace ol li{
            padding: 2px 4px;
        }
        .exception div:last-child{
            border-bottom-left-radius: 4px;
            border-bottom-right-radius: 4px;
        }

        /* Exception Variables */
        .exception-var table{
            width: 100%;
            margin: 12px 0;
            box-sizing: border-box;
            table-layout:fixed;
            word-wrap:break-word;
        }
        .exception-var table caption{
            text-align: left;
            font-size: 16px;
            font-weight: bold;
            padding: 6px 0;
        }
        .exception-var table caption small{
            font-weight: 300;
            display: inline-block;
            margin-left: 10px;
            color: #ccc;
        }
        .exception-var table tbody{
            font-size: 13px;
            font-family: Consolas,"Liberation Mono",Courier,"微软雅黑";
        }
        .exception-var table td{
            padding: 0 6px;
            vertical-align: top;
            word-break: break-all;
        }
        .exception-var table td:first-child{
            width: 28%;
            font-weight: bold;
            white-space: nowrap;
        }
        .exception-var table td pre{
            margin: 0;
        }

        /* Copyright Info */
        .copyright{
            margin-top: 24px;
            padding: 12px 0;
            border-top: 1px solid #eee;
        }

        /* SPAN elements with the classes below are added by prettyprint. */
        pre.prettyprint .pln { color: #000 }  /* plain text */
        pre.prettyprint .str { color: #080 }  /* string content */
        pre.prettyprint .kwd { color: #008 }  /* a keyword */
        pre.prettyprint .com { color: #800 }  /* a comment */
        pre.prettyprint .typ { color: #606 }  /* a type name */
        pre.prettyprint .lit { color: #066 }  /* a literal value */
        /* punctuation, lisp open bracket, lisp close bracket */
        pre.prettyprint .pun, pre.prettyprint .opn, pre.prettyprint .clo { color: #660 }
        pre.prettyprint .tag { color: #008 }  /* a markup tag name */
        pre.prettyprint .atn { color: #606 }  /* a markup attribute name */
        pre.prettyprint .atv { color: #080 }  /* a markup attribute value */
        pre.prettyprint .dec, pre.prettyprint .var { color: #606 }  /* a declaration; a variable name */
        pre.prettyprint .fun { color: red }  /* a function name */
    </style>
</head>
<body>
    <div class="echo">
        <?php echo $echo;?>
    </div>
    <?php if(\think\facade\App::isDebug()) { ?>
    <div class="exception">
    <div class="message">

            <div class="info">
                <div>
                    <h2>[<?php echo $code; ?>]&nbsp;<?php echo sprintf('%s in %s', parse_class($name), parse_file($file, $line)); ?></h2>
                </div>
                <div><h1><?php echo nl2br(htmlentities($message)); ?></h1></div>
            </div>

    </div>
	<?php if(!empty($source)){?>
        <div class="source-code">
            <pre class="prettyprint lang-php"><ol start="<?php echo $source['first']; ?>"><?php foreach ((array) $source['source'] as $key => $value) { ?><li class="line-<?php echo $key + $source['first']; ?>"><code><?php echo htmlentities($value); ?></code></li><?php } ?></ol></pre>
        </div>
	<?php }?>
        <div class="trace">
            <h2>Call Stack</h2>
            <ol>
                <li><?php echo sprintf('in %s', parse_file($file, $line)); ?></li>
                <?php foreach ((array) $trace as $value) { ?>
                <li>
                <?php
                    // Show Function
                    if($value['function']){
                        echo sprintf(
                            'at %s%s%s(%s)',
                            isset($value['class']) ? parse_class($value['class']) : '',
                            isset($value['type'])  ? $value['type'] : '',
                            $value['function'],
                            isset($value['args'])?parse_args($value['args']):''
                        );
                    }

                    // Show line
                    if (isset($value['file']) && isset($value['line'])) {
                        echo sprintf(' in %s', parse_file($value['file'], $value['line']));
                    }
                ?>
                </li>
                <?php } ?>
            </ol>
        </div>
    </div>
    <?php } else { ?>
    <style>
        * {-moz-box-sizing:border-box;-webkit-box-sizing:border-box;box-sizing:border-box;}
        html,body,div,span,object,iframe,h1,h2,h3,h4,h5,h6,p,blockquote,pre,abbr,address,cite,code,del,dfn,em,img,ins,kbd,q,samp,small,strong,sub,sup,var,b,i,dl,dt,dd,ol,ul,li,fieldset,form,label,legend,caption,article,aside,canvas,details,figcaption,figure,footer,header,hgroup,menu,nav,section,summary,time,mark,audio,video {margin:0;padding:0;border:0;outline:0;vertical-align:baseline;background:transparent;}
        article,aside,details,figcaption,figure,footer,header,hgroup,nav,section {display:block;}
        html {font-size:16px;line-height:24px;width:100%;height:100%;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;overflow-y:scroll;overflow-x:hidden;}
        img {vertical-align:middle;max-width:100%;height:auto;border:0;-ms-interpolation-mode:bicubic;}
        body {min-height:100%;background:#edf1f4;text-rendering:optimizeLegibility;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;font-family:"Helvetica Neue",Helvetica,"PingFang SC","Hiragino Sans GB","Microsoft YaHei",微软雅黑,Arial,sans-serif;}
        .clearfix {clear:both;zoom:1;}
        .clearfix:before,.clearfix:after {content:"\0020";display:block;height:0;visibility:hidden;}
        .clearfix:after {clear:both;}
        body.error-page-wrapper,.error-page-wrapper.preview {background-position:center center;background-repeat:no-repeat;background-size:cover;position:relative;}
        .error-page-wrapper .content-container {border-radius:2px;text-align:center;box-shadow:1px 1px 1px rgba(99,99,99,0.1);padding:50px;background-color:#fff;width:100%;max-width:560px;position:absolute;left:50%;top:50%;margin-top:-220px;margin-left:-280px;}
        .error-page-wrapper .content-container.in {left:0px;opacity:1;}
        .error-page-wrapper .head-line {transition:color .2s linear;font-size:40px;line-height:60px;letter-spacing:-1px;margin-bottom:20px;color:#777;}
        .error-page-wrapper .subheader {transition:color .2s linear;font-size:32px;line-height:46px;color:#494949;}
        .error-page-wrapper .hr {height:1px;background-color:#eee;width:80%;max-width:350px;margin:25px auto;}
        .error-page-wrapper .context {transition:color .2s linear;font-size:16px;line-height:27px;color:#aaa;}
        .error-page-wrapper .context p {margin:0;}
        .error-page-wrapper .context p:nth-child(n+2) {margin-top:16px;}
        .error-page-wrapper .buttons-container {margin-top:35px;overflow:hidden;}
        .error-page-wrapper .buttons-container a {transition:text-indent .2s ease-out,color .2s linear,background-color .2s linear;text-indent:0px;font-size:14px;text-transform:uppercase;text-decoration:none;color:#fff;background-color:#369;border-radius:99px;padding:8px 0 8px;text-align:center;display:inline-block;overflow:hidden;position:relative;width:45%;}
        .error-page-wrapper .buttons-container a:hover {text-indent:15px;}
        .error-page-wrapper .buttons-container a:nth-child(1) {float:left;}
        .error-page-wrapper .buttons-container a:nth-child(2) {float:right;}
        @media screen and (max-width:580px) {
            .error-page-wrapper {padding:30px 5%;}
            .error-page-wrapper .content-container {padding:37px;position:static;left:0;margin-top:0;margin-left:0;}
            .error-page-wrapper .head-line {font-size:36px;}
            .error-page-wrapper .subheader {font-size:27px;line-height:37px;}
            .error-page-wrapper .hr {margin:30px auto;width:215px;}
        }
        @media screen and (max-width:450px) {
            .error-page-wrapper {padding:30px;}
            .error-page-wrapper .head-line {font-size:32px;}
            .error-page-wrapper .hr {margin:25px auto;width:180px;}
            .error-page-wrapper .context {font-size:15px;line-height:22px;}
            .error-page-wrapper .context p:nth-child(n+2) {margin-top:10px;}
            .error-page-wrapper .buttons-container {margin-top:29px;}
            .error-page-wrapper .buttons-container a {float:none !important;width:65%;margin:0 auto;font-size:13px;padding:9px 0;}
            .error-page-wrapper .buttons-container a:nth-child(2) {margin-top:12px;}
        }
    </style>
    <?php
$debug = function_exists('config') ? config('app_debug') : false;

$lang = [
    'An error occurred' => '发生错误',
    'Home' => '返回主页',
    'Feedback' => '反馈错误',
    'The page you are looking for is temporarily unavailable' => $message,
    'You can return to the previous page and try again' => '你可以返回上一页重试，或直接向我们反馈错误报告'
    ];

    $langSet = '';

    if (isset($_GET['lang'])) {
    $langSet = strtolower($_GET['lang']);
    } elseif (isset($_COOKIE['think_var'])) {
    $langSet = strtolower($_COOKIE['think_var']);
    } elseif (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
    preg_match('/^([a-z\d\-]+)/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches);
    $langSet     = strtolower($matches[1]);
    }
    $langSet = $langSet && in_array($langSet, ['zh-cn', 'en']) ? $langSet : 'zh-cn';
    $langSet == 'en' && $lang = array_combine(array_keys($lang), array_keys($lang));

    ?>
    <div class="error-page-wrapper">
        <div class="content-container">
            <div class="head-line">
                <img src="/static/admin/image/error.svg" alt="" width="120"/>
            </div>
            <div class="subheader">
                <?=$debug?$message:$lang['The page you are looking for is temporarily unavailable']?>
            </div>
            <div class="hr"></div>
            <div class="context">
                <p>
                    <?=$lang['You can return to the previous page and try again']?>
                </p>
            </div>
            <div class="buttons-container">
                <a href="/"><?=$lang['Home']?></a>
                <a href="/"><?=$lang['Feedback']?></a>
            </div>
        </div>
    </div>
    <?php } ?>

    <?php if(!empty($datas)){ ?>
    <div class="exception-var">
        <h2>Exception Datas</h2>
        <?php foreach ((array) $datas as $label => $value) { ?>
        <table>
            <?php if(empty($value)){ ?>
            <caption><?php echo $label; ?><small>empty</small></caption>
            <?php } else { ?>
            <caption><?php echo $label; ?></caption>
            <tbody>
                <?php foreach ((array) $value as $key => $val) { ?>
                <tr>
                    <td><?php echo htmlentities($key); ?></td>
                    <td>
                        <?php
                            if(is_array($val) || is_object($val)){
                                echo htmlentities(json_encode($val, JSON_PRETTY_PRINT));
                            } else if(is_bool($val)) {
                                echo $val ? 'true' : 'false';
                            } else if(is_scalar($val)) {
                                echo htmlentities($val);
                            } else {
                                echo 'Resource';
                            }
                        ?>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
            <?php } ?>
        </table>
        <?php } ?>
    </div>
    <?php } ?>

    <?php if(!empty($tables)){ ?>
    <div class="exception-var">
        <h2>Environment Variables</h2>
        <?php foreach ((array) $tables as $label => $value) { ?>
        <table>
            <?php if(empty($value)){ ?>
            <caption><?php echo $label; ?><small>empty</small></caption>
            <?php } else { ?>
            <caption><?php echo $label; ?></caption>
            <tbody>
                <?php foreach ((array) $value as $key => $val) { ?>
                <tr>
                    <td><?php echo htmlentities($key); ?></td>
                    <td>
                        <?php
                            if(is_array($val) || is_object($val)){
                                echo htmlentities(json_encode($val, JSON_PRETTY_PRINT));
                            } else if(is_bool($val)) {
                                echo $val ? 'true' : 'false';
                            } else if(is_scalar($val)) {
                                echo htmlentities($val);
                            } else {
                                echo 'Resource';
                            }
                        ?>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
            <?php } ?>
        </table>
        <?php } ?>
    </div>
    <?php } ?>

    <?php if(\think\facade\App::isDebug()) { ?>
    <script>
        var LINE = <?php echo $line; ?>;

        function $(selector, node){
            var elements;

            node = node || document;
            if(document.querySelectorAll){
                elements = node.querySelectorAll(selector);
            } else {
                switch(selector.substr(0, 1)){
                    case '#':
                        elements = [node.getElementById(selector.substr(1))];
                        break;
                    case '.':
                        if(document.getElementsByClassName){
                            elements = node.getElementsByClassName(selector.substr(1));
                        } else {
                            elements = get_elements_by_class(selector.substr(1), node);
                        }
                        break;
                    default:
                        elements = node.getElementsByTagName();
                }
            }
            return elements;

            function get_elements_by_class(search_class, node, tag) {
                var elements = [], eles,
                    pattern  = new RegExp('(^|\\s)' + search_class + '(\\s|$)');

                node = node || document;
                tag  = tag  || '*';

                eles = node.getElementsByTagName(tag);
                for(var i = 0; i < eles.length; i++) {
                    if(pattern.test(eles[i].className)) {
                        elements.push(eles[i])
                    }
                }

                return elements;
            }
        }

        $.getScript = function(src, func){
            var script = document.createElement('script');

            script.async  = 'async';
            script.src    = src;
            script.onload = func || function(){};

            $('head')[0].appendChild(script);
        }

        ;(function(){
            var files = $('.toggle');
            var ol    = $('ol', $('.prettyprint')[0]);
            var li    = $('li', ol[0]);

            // 短路径和长路径变换
            for(var i = 0; i < files.length; i++){
                files[i].ondblclick = function(){
                    var title = this.title;

                    this.title = this.innerHTML;
                    this.innerHTML = title;
                }
            }

            // 设置出错行
            var err_line = $('.line-' + LINE, ol[0])[0];
            err_line.className = err_line.className + ' line-error';

            $.getScript('//cdn.bootcss.com/prettify/r298/prettify.min.js', function(){
                prettyPrint();

                // 解决Firefox浏览器一个很诡异的问题
                // 当代码高亮后，ol的行号莫名其妙的错位
                // 但是只要刷新li里面的html重新渲染就没有问题了
                if(window.navigator.userAgent.indexOf('Firefox') >= 0){
                    ol[0].innerHTML = ol[0].innerHTML;
                }
            });

        })();
    </script>
    <?php } ?>
</body>
</html>
