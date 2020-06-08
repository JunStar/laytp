<?php
return [
    [
        'name'=>'AccessKeyID'
        ,'tip'=>'阿里云密钥管理中设置的AccessKeyID'
        ,'key'=>'access_key_id'
        ,'type'=>'input'
        ,'content'=>''
    ],
    [
        'name'=>'AccessKeySecret'
        ,'tip'=>'阿里云密钥管理中设置的AccessKeySecret'
        ,'key'=>'access_key_secret'
        ,'type'=>'input'
        ,'content'=>''
    ],
    [
        'name'=>'签名'
        ,'tip'=>'阿里云手机短信中设置的签名'
        ,'key'=>'sign'
        ,'type'=>'input'
        ,'content'=>''
    ],
    [
        'name'=>'模板与事件对应关系'
        ,'tip'=>'模板与事件对应关系'
        ,'key'=>'template'
        ,'type'=>'array'
        ,'content'=>[
            'reg_login' => '阿里云的模板ID'
        ]
    ],
    [
        'name'=>'过期时间'
        ,'tip'=>'过期时间，Unix时间戳，单位秒'
        ,'key'=>'expire_time'
        ,'type'=>'input'
        ,'content'=>''
    ],
    [
        'name'=>'间隔时间'
        ,'tip'=>'对同一手机号，两条短信发送的间隔时间，单位秒'
        ,'key'=>'interval_time'
        ,'type'=>'input'
        ,'content'=>''
    ],
];
