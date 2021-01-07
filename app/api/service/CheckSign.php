<?php

namespace app\api\service;

use laytp\traits\Error;
use think\facade\Env;
use think\facade\Request;

/**
 * Api验证签名服务实现者
 * Class CheckSign
 * @package app\api\service
 */
class CheckSign
{
    use Error;
    protected $_noNeedCheckSign = [];//无需验证签名的方法名数组

    /**
     * header部分需要进行签名的参数
     * @var array
     */
    protected $_header = ['channel', 'language', 'device-number', 'os', 'timestamp', 'version', 'token'];

    /**
     * 不需要进行签名的值
     * 默认，当传递的参数的值为空字符串或者不传值时，不进行签名
     */
    protected $_unSignVal = ['', null];

    /**
     * 设置无需验证签名的方法名数组
     * @param array $noNeedCheckSign
     */
    public function setNoNeedCheckSign($noNeedCheckSign = [])
    {
        $this->_noNeedCheckSign = $noNeedCheckSign;
    }

    /**
     * 获取无需验证签名的方法名数组
     * @return array
     */
    public function getNoNeedCheckSign()
    {
        return $this->_noNeedCheckSign;
    }

    /**
     * 当前节点是否需要验证签名
     * @param bool $noNeedCheckSign
     * @return bool true:需要验证签名,false:不需要验证签名
     */
    public function needCheckSign($noNeedCheckSign = false)
    {
        $noNeedCheckSign === false && $noNeedCheckSign = $this->getNoNeedCheckSign();
        $noNeedCheckSign = is_array($noNeedCheckSign) ? $noNeedCheckSign : explode(',', $noNeedCheckSign);
        //为空表示所有方法都需要验证签名，返回true
        if (!$noNeedCheckSign) {
            return true;
        }
        $noNeedCheckSign = array_map('strtolower', $noNeedCheckSign);
        $request         = Request::instance();
        //判断当前请求的操作名是否存在于不需要验证签名的方法名数组中，如果存在，表明不需要验证签名，返回false
        if (in_array(strtolower($request->action()), $noNeedCheckSign) || in_array('*', $noNeedCheckSign)) {
            return false;
        }

        //默认为需要验证签名
        return true;
    }

    /**
     * 验证签名
     */
    public function check()
    {
        if ('local' === Env::get('APP_ENV')) {
            return true;
        }

        $request = Request::instance();

        $sign    = $request->header('sign');
        $signArr = $request->post();

        foreach ($this->_header as $header) {
            $signArr[$header] = $request->header($header);
        }

        ksort($signArr);

        $signStr = '';
        foreach ($signArr as $key => $value) {
            if (!in_array($value, $this->_unSignVal)) {
                $signStr = $signStr . $key . '=' . $value . '&';
            }
        }

        $signStr = $signStr . 'key=' . env('SIGN_KEY');

        $signVal = strtoupper(md5($signStr));

        if ($sign != $signVal) {
            if ('test' === Env::get('APP_ENV')) {
                $this->setError('签名错误，应该传递：' . $signVal . '，但是你传递的是：' . $sign);
                return false;
            }
            $this->setError('非法请求');
            return false;
        }

        return true;
    }
}