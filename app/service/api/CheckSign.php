<?php

namespace app\service\api;

use app\service\ConfServiceFacade;
use laytp\traits\Error;
use think\facade\Config;
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
        $request = Request::instance();
        $requestTime = $request->header('request-time');
        $sign = $request->header('sign');
        $signKey = ConfServiceFacade::get('system.basic.signKey');
        $backendSign = strtoupper(md5(md5($requestTime).md5($signKey)));
        if($sign != $backendSign){
            $this->setError($backendSign);
            return false;
        }

        return true;
    }
}