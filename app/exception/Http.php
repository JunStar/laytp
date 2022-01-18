<?php

namespace app\exception;

use app\model\api\Log;
use think\exception\Handle;
use Throwable;

/**
 * 异常处理接管
 *  解决的问题：
 *      1.记录数据库日志
 *      2.windows环境下，出现异常时，页面输出空白的问题
 * Class Http
 * @package app\exception
 */
class Http extends Handle
{
    /**
     * 不需要记录信息（日志）的异常类列表
     *  定义为空，说明是所有异常全部都记录
     * @var array
     */
    protected $ignoreReport = [];

    /**
     * 记录异常信息（包括日志或者其它方式记录）
     *
     * @access public
     * @param  Throwable $exception
     * @return void
     */
    public function report(Throwable $exception): void
    {
        // 非命令行下，记录错误日志
        if(PHP_SAPI !== 'cli') {
            $request = request();
            // 记录数据库日志
            // 这里必须要try，不然，如果报数据库错误，系统会循环调用异常接管程序，导致500
            try{
                Log::create([
                    'rule' => $request->url(),
                    'request_body' => json_encode(request()->post(), JSON_UNESCAPED_UNICODE),
                    'request_header' => json_encode(request()->header(), JSON_UNESCAPED_UNICODE),
                    'ip' => $request->ip(),
                    'status_code' => 500,
                    'response_body' => json_encode($this->convertExceptionToArray($exception), JSON_UNESCAPED_UNICODE),
                    'create_time' => date('Y-m-d H:i:s'),
                ]);
            }catch (\Exception $e){

            }
        }

        // 使用内置的方式记录异常日志
        parent::report($exception);
    }

    /**
     * 收集异常数据
     *  重写父类此方法，将Server/Request Data进行转码，解决windows环境下，出现异常时，页面输出空白的问题
     * @param Throwable $exception
     * @return array
     */
    protected function convertExceptionToArray(Throwable $exception): array
    {
        $data = parent::convertExceptionToArray($exception);
        $data['tables']['Server/Request Data'] = $this->changeToUtf8($this->app->request->server());

        return $data;
    }

    /**
     * 将获取的服务器信息中的中文编码转为utf-8
     * 修复在开启debug模式时出现的Malformed UTF-8 characters 错误
     * @access protected
     * @param $data array
     * @return array                 转化后的数组
     */
    protected function changeToUtf8(array $data): array
    {
        foreach ($data as $key => $value) {
            $data[$key] =  mb_convert_encoding($value, "UTF-8", "GBK, GBK2312");
        }

        return $data;
    }
}