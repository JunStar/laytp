<?php
//有分组模式
//return [
//    'group' => ['微信','支付宝'],
//    'items' => [
//        '微信' => [
//            ['微信app_key','wx_app_key','input'],
//            ['微信secret','wx_secret','input'],
//        ],
//        '支付宝' => [
//            ['支付宝app_key','alipay_app_key','input'],
//            ['支付宝secret','alipay_secret','input'],
//        ],
//    ],
//];

//无分组模式
return [
    [
        'name'=>'单行输入框'
        ,'key'=>'input'
        ,'type'=>'input'
        ,'default'=>''
        ,'content'=>''
    ],
    [
        'name'=>'普通文本域'
        ,'key'=>'textarea'
        ,'type'=>'textarea'
        ,'default'=>''
        ,'content'=>''
    ],
    [
        'name'=>'状态(开关单选)'
        ,'key'=>'status'
        ,'type'=>'radio'
        ,'default'=>'2'
        ,'content'=>[
            '1'=>'关闭'
            ,'2'=>'打开'
        ]
    ],
    [
        'name'=>'单选下拉框'
        ,'key'=>'hero'
        ,'type'=>'select_single'
        ,'default'=>'4'
        ,'content'=>[
            '1'=>'秀逗魔法师'
            ,'2'=>'受折磨的灵魂'
            ,'3'=>'船长'
            ,'4'=>'虚空假面'
            ,'5'=>'幻影刺客'
            ,'6'=>'谜团'
            ,'7'=>'全能骑士'
            ,'8'=>'敌法师'
        ]
    ],
    [
        'name'=>'多选下拉框'
        ,'key'=>'sign'
        ,'type'=>'select_multi'
        ,'default'=>'1,3'
        ,'content'=>[
            '1'=>'热门'
            ,'2'=>'首页'
            ,'3'=>'顶级分类推荐'
            ,'4'=>'二级分类推荐'
            ,'5'=>'特定分类推荐'
            ,'6'=>'轮播图'
            ,'7'=>'置顶'
            ,'8'=>'新闻'
        ]
    ],
    [
        'name'=>'单图'
        ,'key'=>'image'
        ,'type'=>'image_single'
    ],
    [
        'name'=>'多图'
        ,'key'=>'images'
        ,'type'=>'image_multi'
    ],
    [
        'name'=>'单视频'
        ,'key'=>'video'
        ,'type'=>'video_single'
    ],
    [
        'name'=>'多视频'
        ,'key'=>'videos'
        ,'type'=>'video_multi'
    ],
    [
        'name'=>'单文件'
        ,'key'=>'file'
        ,'type'=>'file_single'
    ],
    [
        'name'=>'多文件'
        ,'key'=>'files'
        ,'type'=>'file_multi'
    ],
    [
        'name'=>'数组'
        ,'key'=>'array'
        ,'type'=>'array'
    ],
    [
        'name'=>'富文本编辑器'
        ,'key'=>'ueditor'
        ,'type'=>'ueditor'
    ]
];