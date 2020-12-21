<?php
declare (strict_types = 1);

namespace laytp\traits;

/**
 * 所有接口success和error返回数据格式定义
 */
trait JsonReturn
{
    /**
     * 操作成功返回数据
     * @param string $msg
     * @param null $data
     * @return \think\response\Json
     */
    public function success($msg = '', $data = null){
        $result = [
            'code' => 0,
            'msg'  => $msg,
            'time' => time(),
            'data' => $data
        ];
        //使用json函数，http的response返回的Content-Type:application/json; charset=utf-8，当代码中使用dump函数进行调试时，使用浏览器单独访问接口地址不会友好的展示dump的html
        return json($result, JSON_UNESCAPED_UNICODE);
        //response返回的Content-Type:text/html; charset=utf-8，使用浏览器单独访问接口地址方便调试，
        //但是注意在javascript进行ajax请求时，要设置dataType: "json"，
        //这样,javascript得到字符串后，会自动使用JSON.parse对字符串进行解析
//        return response(json_encode($result, JSON_UNESCAPED_UNICODE));
    }

    /**
     * 操作失败返回数据
     * @param string $msg
     * @param int $code 错误码，为0表示没有错误，为1表示常规错误，前端仅需提示msg，其他值有具体含义，比如10401为未登录，前端需要跳转至登录界面
     * @param array $data
     * @return \think\response\Json
     */
    public function error($msg = '', $code = 1, $data = []){
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'time' => time(),
            'data' => $data
        ];
        return json($result, JSON_UNESCAPED_UNICODE);
//        return response(json_encode($result, JSON_UNESCAPED_UNICODE));
    }
}
