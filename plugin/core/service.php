<?php
//需要注入全局的服务类数组，只有如下一种编写规则，将完整类名，一行一个写入数组中进行返回
//安装脚本会将/config/service.php与当前文件进行合并
return [
    '\app\common\service\admin\AuthService',
    '\app\common\service\admin\UserService',
];