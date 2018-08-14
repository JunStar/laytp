<?php
Route::rule('hello/:id','index/index/hello');

//return [
//    '__pattern__' => [
//        'name' => '\w+',
//    ],
//    '[hello]'     => [
//        ':id'   => ['index/index/hello', ['method' => 'get'], ['id' => '\d+']],
//        ':name' => ['index/index/hello', ['method' => 'post']],
//    ],
//
//];
