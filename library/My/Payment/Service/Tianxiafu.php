<?php
namespace My\Payment\Service;


/**
 * Tianxiafu.php
 *
 * @author   maomao
 * @DateTime 12-7-12 下午3:19
 * @version  $Id: Tianxiafu.php 1266 2013-10-22 10:29:07Z maomao $
 */
class Tianxiafu extends DirectPay
{
    protected static $name = '天下付';
    protected $gateway = "null";

    protected $keyMapper
        = [
            'sn'      => 'merchantId',
            'order'   => 'order_id',
            'user_ip' => 'ip',
            'account' => 'user_account'
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

    protected $cardTypes = array(), $product, $key2;

    public function response()
    {
        if (!$this->getError()) {
            $code = 0;
        } else {
            switch ($this->getError()) {
                case self::ERROR_ORDER_EXISTS:
                    $code = 3;
                    break;
                default:
                    $code = 1;
                    break;
            }
        }
        $this->send(['ret_code' => $code, 'user_account' => $_REQUEST['user_account']]);
    }

    public function isValid()
    {
        $result = $this->isValidWay(2);
        if ($result) {
            $this->setOption('time_fee', $_REQUEST['time_fee']);
        }
        return $result;
    }

    /**
     *
     * @param int $way
     *
     * @throws \RuntimeException
     * @return bool
     */
    public function isValidWay($way)
    {
        $request = $this->getRequest();
        if (intval($request->getParam('way')) != $way) {
            throw new \RuntimeException('Error way');
        }

        if ($request->getParam('merchant_id') != $this->sn) {
            $this->setError(self::ERROR_MERCHANT);
            return false;
        }
        if (!$this->isAllowIp()) {
            $this->setError(self::DENY_IP);
            return false;
        }

        switch ($way) {
            case 1:
                if (!$this->isValidParams(['merchant_id', 'user_account', 'time_stamp'])) {
                    return false;
                }

                $params = [
                    'merchant_id'  => $request->getParam('merchant_id'),
                    'user_account' => $request->getParam('user_account'),
                    'time_stamp'   => $request->getParam('time_stamp'),
                ];

                $sign   = strtolower($request->getParam('sign'));
                $result = $sign == md5($this->queryBuild($params) . '||' . $this->key);
                if (!$result) {
                    $this->setError(self::ERROR_SIGN);
                    $this->logger('Error sign', ['way' => 1]+$params);
                    return false;
                }

                if (!($call = $this->validAccountCallback)
                    || !$call(
                        strtolower(urldecode($params['user_account']))
                    )
                ) {
                    $this->setError(self::ERROR_ACCOUNT);
                    return false;
                }

                return $result;

            case 2:
                $validParams = $this->isValidParams(
                    ['user_account', 'prod_id', 'num', 'order_id', 'ip', 'time_fee', 'sign']
                );
                if (!$validParams) {
                    return false;
                }

                $params = [
                    'merchant_id'  => $_REQUEST['merchant_id'],
                    'user_account' => $_REQUEST['user_account'],
                    'prod_id'      => $_REQUEST['prod_id'],
                ];

                $params2 = [
                    'order_id' => $_REQUEST['order_id'],
                    'ip'       => $_REQUEST['ip'],
                    'time_fee' => $_REQUEST['time_fee'],
                ];

                $mySign = md5(
                    $this->queryBuild($params) . '||' . $this->key . '&'
                        . $this->queryBuild($params2) . '||' . $this->getKey2()
                );
                $result  = $request->getParam('sign') == $mySign;

                if (!$result) {
                    $this->setError(self::ERROR_SIGN);
                    $this->logger('Error sign', ['way' => 2]+$params+$params2);
                    return false;
                }

                $orderResult = $this->loadOrderResult($params2['order_id']);
                if (!$orderResult) {
                    $this->setError(self::ERROR_ORDER_NOT_EXISTS);
                    return false;
                } else if ($orderResult->isCompleted()) {
                    $this->setError(self::ERROR_ORDER_EXISTS);
                    return false;
                }

                return $result;

            case 3:

                $params = [
                    'merchant_id'  => $request->getParam('merchant_id'),
                    'user_account' => $request->getParam('user_account'),
                    'order_id'     => $request->getParam('order_id'),
                ];

                if (!$this->isValidParams(['merchant_id', 'user_account', 'order_id', 'sign'])) {
                    return false;
                }

                if ($_REQUEST['sign'] != md5($this->queryBuild($params) . '||' . $this->key)) {
                    $this->setError(self::ERROR_SIGN);
                    $this->logger('Error sign', ['way' => 3]+$params);
                    return false;
                }

                return true;

            case 4:

                $validParams = [
                    'merchant_id', 'way', 'user_account', 'prod_id', 'num', 'order_id', 'ip', 'sign'
                ];
                if (!$this->isValidParams($validParams)) {
                    return false;
                }

                $params = [
                    'merchant_id'  => $request->getParam('merchant_id'),
                    'user_account' => strtolower($request->getParam('user_account')),
                    'prod_id'      => $request->getParam('prod_id'),
                    'order_id'     => $request->getParam('order_id'),
                    'ip'           => $request->getParam('ip'),
                ];

                $mySign = md5($this->queryBuild($params) . '||' . $this->key);
                if ($request->getParam('sign') != $mySign) {
                    $this->setError(self::ERROR_SIGN);
                    $this->logger('Error sign: ' . $mySign, ['way' => 4]+$params);
                    return false;
                }

                if (!isset($this->cardTypes[$params['prod_id']])) {
                    $this->setError(self::ERROR_CARD_TYPE);
                    return false;
                }

                if (!($call = $this->validAccountCallback) ||
                    !$call(urldecode($params['user_account']))) {
                    $this->setError(self::ERROR_ACCOUNT);
                    return false;
                }

                $orderResult = $this->loadOrderResultByPay($params['order_id']);
                if ($orderResult && $orderResult->isCompleted()) {
                    $this->setError(self::ERROR_ORDER_EXISTS);
                    return false;
                }

                return true;

            default:
                return false;
        }
    }

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

    public function validAccount($validCallback)
    {
        $this->setValidAccountCallback($validCallback);
        $this->isValidWay(1);

        $this->send(
            [
                'ret_code'     => $this->getError() ? 1 : 0,
                'user_account' => $_REQUEST['user_account'],
            ]
        );
    }

    /**
     * @param callback($account, $amount, $orderId, $ip, $rate) $callback
     *
     * @throws \RuntimeException
     */
    public function createOrder($callback)
    {
        if (!is_callable($callback)) {
            throw new \RuntimeException('callback 参数类型错误');
        }

        $request = $this->getRequest();

        $num       = $request->getParam('num');
        $extends   = $request->getParam('extends');
        $account   = strtolower(urldecode($request->getParam('user_account')));
        $prodId    = $request->getParam('prod_id');
        $orderId   = $request->getParam('order_id');
        $ip        = $request->getParam('ip');
        $cpOrderId = time() . mt_rand(000, 999);

        if ($this->isValidWay(4)) {
            $amount = $num * $this->getCardTypes($prodId);
            $this->setOption('amount', $amount);
            $cpOrderId = $callback($account, $amount, $orderId, $ip, $this->getRate(), $extends);
        }

        if ($this->getError() == self::ERROR_ORDER_EXISTS) {
            $code = 3;
        } else {
            $code = $this->getError() ? 1 : 0;
        }

        $this->send(
            [
                'ret_code' => $code, 'cp_order_id' => $cpOrderId, 'user_account' => $account,
            ]
        );
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

    public function queryOrder($callback)
    {
        $params = [
            'ret_code' => 1, 'user_account' => $this->getRequest()->getParam('user_account')
        ];
        if ($this->isValidWay(3)) {
            /** @var $orderResult \My\Payment\Data\OrderResult */
            $orderResult = $callback($this->getRequest()->getParam('order_id'));
            if ($orderResult) {
                if ($orderResult->isCompleted()) { //充值成功
                    $params['ret_code'] = 0;
                } else if ($orderResult->isUnpaid()) { //商户接收，但未充值
                    $params['ret_code'] = 2;
                } else if ($orderResult->isProgress()) {
                    $params['ret_code'] = 4;
                }
            } else {
                $this->setError(self::ERROR_ORDER_NOT_EXISTS);
            }
        }

        $this->send($params);
    }

    private function send($params)
    {
        $signFilterParams = ['ret_msg', 'extends'];

        if (!headers_sent()) {
            header('Content-Type: text/xml; charset=gbk');
        }
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="GBK"?><response/>');

        $params = ['merchant_id' => $this->sn] + $params;
        foreach ($params as $key => $value) {
            $xml->addChild($key, $value);
        }

        foreach ($signFilterParams as $key) {
            unset($params[$key]);
        }

        $xml->addChild('sign', md5($this->queryBuild($params) . '||' . $this->key));

        if ($this->getMessage()) {
            $xml->addChild('ret_msg', '{message}');
            $message = iconv($this->charset, 'gbk', $this->getMessage());
            $result  = str_replace('{message}', $message, $xml->asXML());
        } else {
            $xml->addChild('ret_msg', '');
            $result = $xml->asXML();
        }

        echo $result;
        exit;
    }

    public function getProductId()
    {
        return ['product' => $this->getProduct(),];
    }

    public function getProduct()
    {
        return $this->product;
    }

    public function setProduct($product)
    {
        $this->product = $product;
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
}