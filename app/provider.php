<?php
use app\exception\Http;

// 容器Provider定义文件
return [
    'think\exception\Handle' => Http::class,
];
