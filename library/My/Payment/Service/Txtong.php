<?php
namespace My\Payment\Service;

use My\Product\Product;

/**
 * Txtong.php
 *
 * @author   maomao
 * @DateTime 12-7-12 下午2:13
 * @version  $Id: Txtong.php 1266 2013-10-22 10:29:07Z maomao $
 */
class Txtong extends DirectPay
{
    protected static $name = '天下通';

    protected $keyMapper = [];

    const SUCCESS           = '000';
    const KEY_ERROR         = '101';
    const ACCOUNT_NOT_FOUND = '102';
    const TYPE_ERROR        = '103';
    const CARD_ERROR        = '104';
    const ORDER_EXISTS      = '105';
    const DENY_IP           = '106';
    const SYSTEM_ERROR      = '201';

    protected $gateway = 'null';
    protected $sn;

    public function response()
    {
        $code = $this->getError() ? : ($this->getOrderResult() ? self::SUCCESS : self::SYSTEM_ERROR);
        $sign = md5(
            'code=' . $code . '&transid=' . $this->getOption('order') . '&key=' . $this->key
        );

        echo 'code=' . $code . '&transid=' . $this->getOption('order') . '&sign=' . $sign;
        exit;
    }

    public function isValid()
    {
        if (!$this->isAllowIp()) {
            $this->setError(self::DENY_IP);
            return false;
        }

        $params = [
            'userid'    => null,
            'gameId'    => null,
            'serverId'  => null,
            'chargeNum' => null,
            'orderId'   => null,
            'loginId'   => null,
        ];

        //参数&签名验证
        foreach ($params as $key => &$val) {
            if (!isset($_REQUEST[$key])) {
                $this->setError(self::SYSTEM_ERROR);
                $this->logger('Param not found : ' . $key);
                return false;
            }
            $val = $_REQUEST[$key];
        }

        $sign   = strtolower($this->getRequest()->getParam('sign'));
        $mySign = md5(
            'userId=' . $params['userid'] . '&gameId=' . $params['gameId'] . '&serverId='
            . $params['serverId'] . '&chargeNum=' . $params['chargeNum'] . '&orderId='
            . $params['orderId'] . '&loginId=' . $params['loginId'] . '&key=' . $this->key
        );

        if ($params['loginId'] != $this->sn) {
            $this->setError(self::SYSTEM_ERROR);
            $this->logger('Txtong sn error');
            return false;
        }

        if ($sign != $mySign) {
            $this->setError(self::KEY_ERROR);
            $this->logger(
                'Key error: ' . $mySign,
                array(
                    'SignString' => 'userId=' . $params['userid'] . '&gameId='
                    . $params['gameId'] . '&serverId='
                    . $params['serverId'] . '&chargeNum=' . $params['chargeNum'] . '&orderId='
                    . $params['orderId'] . '&loginId=' . $params['loginId'] . '&key=' . $this->key
                )
            );
            return false;
        }

        //充值金额 一定要10的倍数
        if (substr($params['chargeNum'], -1) !== '0') {
            $this->setError(self::CARD_ERROR);
            return false;
        }

        //验证游戏区服
        if (!Product::hasProduct($params['gameId'])) {
            $this->setError(self::SYSTEM_ERROR);
            $this->logger('Txtong gameId error');
            return false;
        }
        $product = Product::factory($params['gameId']);
        if (!$product->hasArea($params['serverId'])) {
            $this->setError(self::SYSTEM_ERROR);
            $this->logger('Txtong serverId error');
            return false;
        }

        //账号验证
        if (!($call = $this->validAccountCallback) || !$call($params['userid'])) {
            $this->setError(self::ACCOUNT_NOT_FOUND);
            return false;
        }

        //验证订单是否存在
        $orderResult = $this->loadOrderResultByPay($params['orderId']);
        if ($orderResult && $orderResult->isCompleted()) {
            $this->setError(self::ORDER_EXISTS);
            return false;
        }

        $this->mergeOptions(
            [
                'account' => $params['userid'],
                'pay_id'  => $params['orderId'],
                'amount'  => $params['chargeNum'],
            ]
        );
        return true;
    }

    public function getProductId()
    {
        return [
            'product' => $this->getRequest()->getParam('gameId'),
            'server'  => $this->getRequest()->getParam('serverId')
        ];
    }
}
