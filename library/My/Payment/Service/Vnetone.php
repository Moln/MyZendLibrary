<?php
namespace My\Payment\Service;

use My\Payment\Data\OrderResult;

/**
 * 盈华讯方-固定电话
 * Class Vnetone
 * @package My\Payment\Service
 * @author Xiemaomao
 * @version $Id: Vnetone.php 1266 2013-10-22 10:29:07Z maomao $
 */
class Vnetone extends ChannelPay
{
    protected static $name = 'V币电话钱包';
    protected $gateway = 'http://s2.vnetone.com/Default.aspx';

    protected $options
        = array(
            'money'     => null,
            'spcustom'  => null,
            'spid'      => null,
            'spmd5'     => null,
            'spname'    => null,
            'spoid'     => null,
            'sprec'     => null,
            'spreq'     => null,
            'spversion' => 'vpay1001',
            'urlcode'   => 'utf-8',
            'userid'    => null,
            'userip'    => null,
        );

    protected $keyMapper
        = array(
            'sn'      => 'spid',
            'order'   => 'spoid',
            'amount'  => 'money',
            'account' => 'userid',
        );

    private $debug = false;

    public function getRequestParams(OrderResult $order)
    {
        $params = ['userip' => $_SERVER['REMOTE_ADDR']] + $this->getOptions();

        // '网站订单号码+ 请求地址+ 接收地址 + 5位spid+ 18位SP密码+支付的版本号+支付金额
        $str             =
            $params['spoid'] . $params['spreq'] . $params['sprec'] . $params['spid'] . $this->key
            . $params['spversion'] . $params['money'];
        $params['spmd5'] = strtoupper(md5($str));

        $params['spname']   = rawurlencode($params['spname']);
        $params['spcustom'] = rawurlencode($params['spcustom']);

        return $params;
    }

    public function redirectPay(OrderResult $order)
    {
        $params = $this->getRequestParams($order);
        $params = array_filter($params);

        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
        echo '<form action="' . $this->gateway . '" method="post">';
        foreach ($params as $name => $value) {
            echo '<input type="hidden" name="' . $name . '" value="' . $value . '" />';
        }

        echo '<noscript><input type="submit" value="提交">'
            . '您的浏览器不支持或未开启JAVASCRIPT，请手动点击“提交”按钮</noscript>';
        echo '</form>';
        echo '<script>document.getElementsByTagName("form")[0].submit()</script>';
        exit;
    }

    const ORDER_ERROR     = 'orderError';
    const VALID_ERROR     = 'validError';
    const PARAMS_ERROR    = 'paramsError';
    const SYSTEM_ERROR    = 'systemError';
    const STATUS_ERROR    = 'statusError';
    const CARD_ERROR      = 'cardError';
    const ORDER_COMPLETED = 'orderCompleted';


    /**
     * @return bool
     */
    public function isValidServer()
    {
        $request  = $this->getRequest();
        $spid     = $this->getOption('sn'); //商户SP号码 5位
        $sppwd    = $this->key; //商户SP密钥 18位
        $rtmd5    = $request->getParam('v1'); //'V币服务器MD5
        $trka     = $request->getParam('v2'); // 'V币号码15位
        $rtmi     = $request->getParam('v3'); //'V币密码6位 （可能为空 老V币没有密码）
        $rtmz     = $request->getParam('v4'); //'面值 1-999 整数面值
        $rtlx     = $request->getParam('v5'); //'卡的类型1，2，3 。  1:正式卡 2：测试卡 3 ：促销卡
        $rtoid    = $request->getParam('v6'); //盈华服务器的订单
        $rtcoid   = $request->getParam('v7'); //客户端订单
        $rtuserid = $request->getParam('v8'); //用户ID
        $rtcustom = $request->getParam('v9'); //商户自定义字段
        $rtflag   = (int)$request->getParam('v10'); //'返回状态. 1为正常发送回来 2为补单发送回来

        $sign = md5($trka . $rtmi . $rtoid . $spid . $sppwd . $rtcoid . $rtflag . $rtmz);

        if (strtolower($rtmd5) != $sign) {
            $this->setError(self::VALID_ERROR);
            return false;
        }

        if (!$this->debug && $rtlx != '1') {
            $this->setError(self::CARD_ERROR);
            return false;
        }

        if ($rtflag != 1 && $rtflag != 2) {
            $this->setError(self::STATUS_ERROR);
            return false;
        }

        $orderResult = $this->loadOrderResult($rtcoid);
        if (!$orderResult) {
            $this->setError(self::ORDER_ERROR);
            return false;
        }

        if ($orderResult->isCompleted()) {
            $this->setError(self::ORDER_COMPLETED);
            return false;
        }

        $this->setOption('order', $rtcoid);
        $this->setOption('pay_id', $rtoid);

        return true;
    }

    public function serverResponse()
    {
        if (!$this->getError() || $this->getError() == self::ORDER_COMPLETED) {
            header("Data-Received:ok_vpay8");
            exit;
        } else if ($this->getError() == self::ORDER_ERROR) {
            header("Location: /index/success/service/vnetone");
            exit;
        } else {
            $this->logger("vnetone error:" . $this->getError());
        }
    }

    public function setDebug($debug)
    {
        $this->debug = $debug;
        return $this;
    }
}