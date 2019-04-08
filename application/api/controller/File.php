<?php
namespace app\api\controller;

use controller\Api;

/**
 * 测试文件上传
 */
class File extends Api{
    /**
     * @ApiTitle    (测试文件上传)
     * @ApiSummary  (测试文件上传)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/file/index)
     * @ApiParams   (name="file", type="file", required=true, description="文件")
     * @ApiReturnParams   (name="code", type="integer", required=true, sample="0")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="返回成功")
     * @ApiReturnParams   (name="data", type="object", sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}", description="扩展数据返回")
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