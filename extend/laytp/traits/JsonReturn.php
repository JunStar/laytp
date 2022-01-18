<?php
declare (strict_types=1);

namespace laytp\traits;

use app\model\api\Log;

/**
 * 所有接口success和error返回数据格式定义
 */
trait JsonReturn
{
    /**
     * 操作成功返回数据
     * @param string $msg
     * @param null $data
     * @return false|string|\think\response\Json
     */
    public function success($msg = '', $data = null)
    {
        $result = [
            'code' => 0,
            'msg'  => $msg,
            'time' => time(),
            'data' => $data ?? new \stdClass(),
        ];
        //response返回的Content-Type:text/html; charset=utf-8，使用浏览器单独访问接口地址方便调试，
        //但是注意在javascript进行ajax请求时，要设置dataType: "json"，这样,javascript得到字符串后，会自动使用JSON.parse对字符串进行解析
//        return json_encode($result,JSON_UNESCAPED_UNICODE);

        //使用json方法进行返回的好处：
        // 1.客户端在进行ajax请求时，不需要设置dataType: "json"，依旧会认为得到的数据是json
        // 2.APP_DEBUG = true，客户端得到的也仅仅是json部分数据，不会夹杂trace等debug代码
//        return json($result);

        //记录Api请求日志
        if (app('http')->getName() === 'api') {
            Log::create([
                'rule'           => request()->url(),
                'request_body'   => json_encode(request()->post(), JSON_UNESCAPED_UNICODE),
                'request_header' => json_encode(request()->header(), JSON_UNESCAPED_UNICODE),
                'ip'             => request()->ip(),
                'status_code'    => 200,
                'response_body'  => json_encode($result, JSON_UNESCAPED_UNICODE),
                'create_time'    => date('Y-m-d H:i:s'),
            ]);
        }

        //最终做如下处理：在测试环境且传入的debug=1的参数，就返回html，否则返回json数据
        //在测试环境可以单独用浏览器打开接口地址，传入debug=1的参数对接口进行调试
        if (env('APP_DEBUG') && request()->param('debug')) {
            return json_encode($result, JSON_UNESCAPED_UNICODE);
        } else {
            return json($result);
        }
    }

    /**
     * 操作失败返回数据
     * @param string $msg
     * @param int $code 错误码，为0表示没有错误，为1表示常规错误，前端仅需提示msg，其他值有具体含义，比如10401为未登录，前端需要跳转至登录界面
     * @param array $data
     * @return \think\response\Json
     */
    public function error($msg = '', $code = 1, $data = null)
    {
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'time' => time(),
            'data' => $data ?? new \stdClass(),
        ];

        //记录Api请求日志
        if (app('http')->getName() === 'api') {
            Log::create([
                'rule'           => request()->url(),
                'request_body'   => json_encode(request()->post(), JSON_UNESCAPED_UNICODE),
                'request_header' => json_encode(request()->header(), JSON_UNESCAPED_UNICODE),
                'ip'             => request()->ip(),
                'status_code'    => 200,
                'response_body'  => json_encode($result, JSON_UNESCAPED_UNICODE),
                'create_time'    => date('Y-m-d H:i:s'),
            ]);
        }

        if (env('APP_DEBUG') && request()->param('debug')) {
            return json_encode($result, JSON_UNESCAPED_UNICODE);
        } else {
            return json($result);
        }
    }

    /**
     * 捕获异常后返回数据
     * @param $e
     * @return \think\response\Json
     */
    public function exceptionError($e){
        $data = [];
        if(env('APP_DEBUG')){
            $data = [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ];
        }
        return $this->error($e->getMessage(), 1, $data);
    }
}
