<?php
namespace app\api\controller;

use controller\Api;

/**
* 示例接口
*/
class Demo extends Api
{
    // 无需无需登录的接口，*表示全部
    public $no_need_login = ['test1'];

    /**
     * @ApiTitle    (需要登录的接口)
     * @ApiSummary  (需要登录的接口详细描述)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/demo/test)
     * @ApiHeaders  (name="token", type="string", required="true", description="请求的Token")
     * @ApiParams   (name="id", type="integer", required="true", description="会员ID")
     * @ApiParams   (name="name", type="string", required="true", description="用户名")
     * @ApiReturnParams   (name="err_code", type="integer", description="错误码.0=没有错误，表示操作成功；1=常规错误码，客户端仅需提示msg；其他错误码与具体业务相关，其他错误码举例：10401。前端需要跳转至登录界面。")
     * @ApiReturnParams   (name="msg", type="string", description="返回描述")
     * @ApiReturnParams   (name="time", type="integer", description="请求时间，Unix时间戳，单位秒")
     * @ApiReturnParams   (name="data", type="object", description="返回的数据对象")
     * @ApiReturnParams   (name="data.id", type="string", description="参数id的值")
     * @ApiReturnParams   (name="data.name", type="string", description="参数name的值")
     * @ApiReturn
({
    "err_code": 0,
    "msg": "返回成功",
    "time": 1591168410,
    "data": {
        "id": "",
        "name": ""
    }
})
    */
    public function test(){
        $this->success('返回成功', $this->request->param());
    }

    /**
     * @ApiTitle    (无需登录的接口)
     * @ApiSummary  (无需登录的接口详细描述)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/demo/test1)
     * @ApiReturnParams   (name="err_code", type="integer", description="错误码.0=没有错误，表示操作成功；1=常规错误码，客户端仅需提示msg；其他错误码与具体业务相关，其他错误码举例：10401。前端需要跳转至登录界面。")
     * @ApiReturnParams   (name="msg", type="string", description="返回描述")
     * @ApiReturnParams   (name="time", type="integer", description="请求时间，Unix时间戳，单位秒")
     * @ApiReturnParams   (name="data", type="object", description="返回的数据对象")
     * @ApiReturnParams   (name="data.action", type="string", description="固定返回test1")
     * @ApiReturn
({
    "err_code": 0,
    "msg": "返回成功",
    "time": 1591168410,
    "data": {
        "action": "test1"
    }
})
     */
    public function test1(){
        $this->success('返回成功', ['action' => 'test1']);
    }
}