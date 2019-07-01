<?php
namespace app\api\controller;

use controller\Api;

/**
 * 测试分组
 */
class Test extends Api{
    /**
     * @ApiTitle    (测试名称)
     * @ApiSummary  (测试描述信息)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/test/index)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams   (name="param1", type="integer", required=true, description="参数1")
     * @ApiParams   (name="param2", type="string", required=true, description="参数2")
     * @ApiParams   (name="param3", type="object", sample="{'id':'int','username':'string','profile':{'email':'string','age':'integer'}}", description="参数3")
     * @ApiReturnParams   (name="code", type="integer", required=true, sample="0")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="返回成功")
     * @ApiReturnParams   (name="data", type="object", sample="{'id':'int','username':'string','profile':{'email':'string','age':'integer'}}", description="扩展数据返回")
     * @ApiReturn
({
    'code':'1',
    'msg':'返回成功'
})
     */
    public function index()
    {
        $this->success("返回成功", $this->request->request());
    }
}