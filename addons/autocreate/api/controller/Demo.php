<?php

namespace addons\autocreate\api\controller;


use controller\Api;

/**
 * 示例接口
 */
class Demo extends Api
{
    // 无需无需登录的接口，*表示全部
    public $no_need_login = ['test1'];

    /**
     * 需要登录的接口
     *
     * @ApiTitle    (需要登录的接口)
     * @ApiSummary  (需要登录的接口详细描述)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/demo/test)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams   (name="id", type="integer", required=true, description="会员ID")
     * @ApiParams   (name="name", type="string", required=true, description="用户名")
     * @ApiParams   (name="data", type="object", sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}", description="扩展数据")
     * @ApiReturnParams   (name="code", type="integer", required=true, sample="0")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="返回成功")
     * @ApiReturnParams   (name="data", type="object", sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}", description="扩展数据返回")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    })
     */
    public function test()
    {
        $this->success('返回成功', $this->request->param());
    }

    /**
     * 无需登录的接口
     * @ApiRoute    (/api/demo/test1)
     */
    public function test1()
    {
        $this->success('返回成功', ['action' => 'test1']);
    }
}
