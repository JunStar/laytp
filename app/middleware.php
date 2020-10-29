<?php
/**
 * 将此文件放到config目录下，设置开启session不生效，必须放到app目录下，TP的BUG
 * 开启session的目的是为了使用验证码
 */
return [
    \think\middleware\SessionInit::class
];
