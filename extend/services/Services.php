<?php
/**
 * Service基类
 */

namespace services;

class Services
{
    public static function success($msg = '', $data = null){
        $result = [
            'code' => 1,
            'msg'  => $msg,
            'data' => $data,
        ];
        return $result;
    }

    public static function error($msg = ''){
        $result = [
            'code' => 0,
            'msg'  => $msg
        ];
        return $result;
    }
}