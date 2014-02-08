<?php
namespace My\Payment\Service;

use My\Payment\Data\OrderResult;

/**
 * Tianxiafu.php
 *
 * @author   maomao
 * @DateTime 12-7-12 下午3:19
 * @version  $Id: Tianxiafu2.php 1266 2013-10-22 10:29:07Z maomao $
 */
class Tianxiafu2 extends ChannelPay
{
    protected static $name = '天下付';
    protected $gateway = "http://pay.tianxiafu.cn/DirectFillAction";

    protected $options = [];
    protected $keyMapper
        = [
            'sn'      => 'merchantId',
            'user_ip' => 'ip',
            'account' => 'user_account',
        ];

    const ERROR_MERCHANT         = 'ERROR_MERCHANT';
    const ERROR_SIGN             = 'ERROR_SIGN';
    const ERROR_ACCOUNT          = 'ERROR_ACCOUNT';
    const ERROR_ORDER_EXISTS     = 'ERROR_ORDER_EXISTS';
    const ERROR_PARAMS           = 'ERROR_PARAMS';
    const ERROR_CARD_TYPE        = 'ERROR_CARD_TYPE';
    const DENY_IP                = 'DENY_IP';
    const ERROR_ORDER_NOT_EXISTS = 'ERROR_ORDER_NOT_EXISTS';

    protected $messages
        = [
            self::ERROR_MERCHANT         => '商户ID错误',
            self::ERROR_SIGN             => '密钥错误',
            self::ERROR_ACCOUNT          => '账号不存在',
            self::ERROR_ORDER_EXISTS     => '订单已存在',
            self::ERROR_PARAMS           => '参数错误',
            self::ERROR_CARD_TYPE        => '卡类型错误',
            self::DENY_IP                => '非法IP',
            self::ERROR_ORDER_NOT_EXISTS => '订单不存在',
        ];

    protected $cardTypes = array(), $product, $key2, $key3;

    private function isValidParams(array $mustArray)
    {
        foreach ($mustArray as $key) {
            if (empty($_REQUEST[$key])) {
                $this->setError(self::ERROR_PARAMS);
                return false;
            }
        }
        return true;
    }

    public function getCardTypes($type)
    {
        return $this->cardTypes[$type];
    }

    public function setCardTypes($types)
    {
        $this->cardTypes = $types;
        return $this;
    }

    public function getKey2()
    {
        return $this->key2;
    }

    public function setKey2($key2)
    {
        $this->key2 = $key2;
        return $this;
    }

    public function setKey3($key3)
    {
        $this->key3 = $key3;
        return $this;
    }

    public function getKey3()
    {
        return $this->key3;
    }

    /**
     * 跳转时传递参数
     * @param \My\Payment\Data\OrderResult $order
     *
     * @return array
     */
    public function getRequestParams(OrderResult $order)
    {
        $params = [
            'merchant_no'  => $this->sn,
            'product_id'   => $this->getCardTypes($order->getAmount()),
            'charge_amt'   => $order->getAmount(),
            'num'          => $this->getOption('num') ? : 1,
            'user_account' => $order->getAccount(),
            'order_id'     => $order->getOrderId(),
            'user_ip'      => $order->getIp(),
            'ret_type'     => $this->getOption('ret_type'),
            'url_tag'      => $this->getOption('url_tag'),
            'ext_param'    => $this->getOption('ext_param'),
            'c'            => $this->getOption('c'),
        ];

        $params['sign'] = md5(
            "merchant_no={$params['merchant_no']}||{$this->key}"
                . "&product_id={$params['product_id']}&charge_amt={$params['charge_amt']}"
                . "&num={$params['num']}&user_account={$params['user_account']}"
                . "&order_id={$params['order_id']}&user_ip={$params['user_ip']}&ret_type="
                . "{$params['ret_type']}||" . $this->getKey2()
        );
        return $params;
    }

    public function redirectPay(OrderResult $order)
    {
        $params = $this->getRequestParams($order);
//        $params = array_filter($params);

        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
        echo '<form action="' . $this->gateway . '" method="post">';
        foreach ($params as $name => $value) {
            echo '<input type="hidden" name="' . $name . '" value="' . $value . '" />';
        }

        echo'<noscript><input type="submit" value="提交">'
            . '您的浏览器不支持或未开启JAVASCRIPT，请手动点击“提交”按钮</noscript>';
        echo '</form>';
        echo '<script>document.getElementsByTagName("form")[0].submit()</script>';
        exit;
    }

    /**
     * @return bool
     */
    public function isValidServer()
    {
        if (!$this->isAllowIp()) {
            $this->setError(self::DENY_IP);
            return false;
        }

        if (!$this->isValidParams(
            ['orderid', 'tag', 'trade_no', 'validate', 'face_value', 'validate2']
        )) {
            $this->setError(self::ERROR_PARAMS);
            return false;
        }

        $request   = $this->getRequest();
        $orderId   = $request->getParam('orderid');
        $tag       = $request->getParam('tag');
        $tradeNo   = $request->getParam('trade_no');
        $validate  = $request->getParam('validate');
        $validate2 = $request->getParam('validate2');
        $faceValue = $request->getParam('face_value');

        $sign1 = md5("orderid=$orderId&tag=$tag&trade_no=$tradeNo" . $this->key3);
        $sign2 = md5("orderid=$orderId&validate=$validate&face_value=$faceValue" . $this->key3);

        if ($sign1 != $validate || $sign2 != $validate2) {
            $this->setError(self::ERROR_SIGN);
            return false;
        }

        if ($tag != '1') {
            $this->setError(self::ERROR_PARAMS);
            return false;
        }

        $orderResult = $this->loadOrderResult($orderId);
        if (!$orderResult) {
            $this->setError(self::ERROR_ORDER_NOT_EXISTS);
            return false;
        }

        if ($orderResult->isCompleted()) {
            $this->setError(self::ERROR_ORDER_EXISTS);
            return false;
        }

        $this->setOption('pay_id', $tradeNo);

        return true;
    }

    public function serverResponse()
    {
        if (!$this->getError()) {
            echo "1";
        } else if ($this->getError() == self::ERROR_ORDER_NOT_EXISTS) {
            echo "2";
        } else if ($this->getError() == self::ERROR_ORDER_EXISTS) {
            echo "3";
        } else {
            echo "0";
        }
        exit;
    }
}