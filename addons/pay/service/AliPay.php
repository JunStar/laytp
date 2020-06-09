<?php
/**
 * 支付宝服务
 */
namespace addons\pay\service;
use service\Service;
use think\facade\Config;
use think\facade\Env;

class AliPay extends Service
{
    protected static $instance;
    protected $_config = null;
    protected $_aop = null;

    /**
     * 单例
     * @return static
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function init()
    {
        require_once Env::get('root_path') . 'addons/pay/library/alipay/aop/AopClient.php';
        require_once Env::get('root_path') . 'addons/pay/library/alipay/aop/request/AlipayTradeAppPayRequest.php';
        $this->_aop = new \AopClient();
        $this->_config = Config::get('alipay.');
        $this->_aop->gatewayUrl         = $this->_config['gatewayUrl'];
        $this->_aop->appId              = $this->_config['appId'];
        $this->_aop->rsaPrivateKey      = $this->_config['rsaPrivateKey'];
        $this->_aop->format             = $this->_config['format'];
        $this->_aop->postCharset        = $this->_config['postCharset'];
        $this->_aop->signType           = $this->_config['signType'];
        $this->_aop->alipayrsaPublicKey = $this->_config['alipayrsaPublicKey'];
    }

    //手机支付
    public function app_pay($data){
        $request = new \AlipayTradeAppPayRequest();
        $request->setBizContent("{"
            . "\"out_trade_no\":\"{$data['out_trade_no']}\","
            . "\"product_code\":\"QUICK_MSECURITY_PAY\","
            . "\"total_amount\":{$data['total_amount']},"
            . "\"subject\":\"{$data['name']}\""
            . "}"
        );
        $request->setNotifyUrl($this->_config['notify_url']);
        $result = $this->_aop->sdkExecute($request);
        return $result;
    }
}