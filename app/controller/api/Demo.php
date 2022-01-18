<?php

namespace app\controller\api;

use laytp\controller\Api;

/**
 * 示例接口
 */
class Demo extends Api
{
    // 无需无需登录的接口，*表示全部
    public $noNeedLogin = ['test1'];

    /*@formatter:off*/
    /**
     *
     * @ApiTitle    (需要登录的接口需要登录的接口需要登录的接口需要登录的接口)
     * @ApiSummary  (需要登录的接口详细描述)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api.demo/test)
     * @ApiHeaders  (name="token", type="string", required="true", description="请求的Token")
     * @ApiParams   (name="id", type="integer", required="true", description="会员ID")
     * @ApiParams   (name="name", type="string", required="true", description="用户名")
     * @ApiReturnParams   (name="code", type="integer", description="接口返回码.0=常规正确码，表示常规操作成功；1=常规错误码，客户端仅需提示msg；其他返回码与具体业务相关。框架实现了的唯一其他返回码：10401，前端需要跳转至登录界面。在一个复杂的交互过程中，你可能需要自行定义其他返回码")
     * @ApiReturnParams   (name="msg", type="string", description="返回描述")
     * @ApiReturnParams   (name="time", type="integer", description="请求时间，Unix时间戳，单位秒")
     * @ApiReturnParams   (name="data", type="object", description="返回的数据对象")
     * @ApiReturnParams   (name="data.id", type="string", description="参数id的值")
     * @ApiReturnParams   (name="data.name", type="string", description="参数name的值")
     * @ApiReturn
({
    "code": 0,
    "msg": "返回成功",
    "time": 1591168410,
    "data": {
        "id": "",
        "name": ""
    }
})
     */
    /*@formatter:on*/
    public function test()
    {
        return $this->success('返回成功', $this->request->param());
    }

    /*@formatter:off*/
    /**
     * @ApiTitle    (无需登录的接口)
     * @ApiSummary  (无需登录的接口详细描述)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api.demo/test1)
     * @ApiReturnParams   (name="code", type="integer", description="接口返回码.0=常规正确码，表示常规操作成功；1=常规错误码，客户端仅需提示msg；其他返回码与具体业务相关。框架实现了的唯一其他返回码：10401，前端需要跳转至登录界面。在一个复杂的交互过程中，你可能需要自行定义其他返回码")
     * @ApiReturnParams   (name="msg", type="string", description="返回描述")
     * @ApiReturnParams   (name="time", type="integer", description="请求时间，Unix时间戳，单位秒")
     * @ApiReturnParams   (name="data", type="object", description="返回的数据对象")
     * @ApiReturnParams   (name="data.action", type="string", description="固定返回test1")
     * @ApiReturn
({
    "code": 0,
    "msg": "返回成功",
    "time": 1591168410,
    "data": {
        "action": "test1"
    }
})
     */
    /*@formatter:on*/
    public function test1()
    {
        return $this->success('返回成功', ['action' => 'test1']);
    }

    /*@formatter:off*/
    /**
     * @ApiTitle    (参数传递array的接口)
     * @ApiSummary  (参数传递array的接口详细描述)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api.demo/arrayParam)
     * @ApiParams   (name="id", type="string", required="true", description="ID")
     * @ApiParams   (name="name", type="array", required="true", description="数组中的值")
     * @ApiParams   (name="array[key]", type="array", required="true", description="数组中的值")
     * @ApiReturnParams   (name="code", type="integer", description="接口返回码.0=常规正确码，表示常规操作成功；1=常规错误码，客户端仅需提示msg；其他返回码与具体业务相关。框架实现了的唯一其他返回码：10401，前端需要跳转至登录界面。在一个复杂的交互过程中，你可能需要自行定义其他返回码")
     * @ApiReturnParams   (name="msg", type="string", description="返回描述")
     * @ApiReturnParams   (name="time", type="integer", description="请求时间，Unix时间戳，单位秒")
     * @ApiReturnParams   (name="data", type="object", description="返回的数据对象")
     * @ApiReturnParams   (name="data.action", type="string", description="固定返回test1")
     * @ApiReturn
({
    "code": 0,
    "msg": "返回成功",
    "time": 1591168410,
    "data": {
        "action": "test1"
    }
})
     */
    /*@formatter:on*/
    public function arrayParam()
    {
        return $this->success('返回成功', $this->request->param());
    }
}