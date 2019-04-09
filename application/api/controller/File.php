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
        try{
            $file = $this->request->file('file'); // 获取上传的文件
            $info = $file->move('uploads'); // 移动文件到指定目录 没有则创建
            $this->success('上传成功',['data'=>'/uploads/'.$info->getSaveName()]);
        }catch (Exception $e){
            $this->error('上传失败',['data'=>$e->getMessage()]);
        }
    }
}