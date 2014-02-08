<?php
namespace My\Payment\Service;

/**
 * DirectInterface.php
 * @author   maomao
 * @DateTime 12-7-11 下午6:30
 * @version  $Id: DirectPay.php 790 2013-03-15 08:56:56Z maomao $
 */
abstract class DirectPay extends ServiceAbstract
{

    protected $amount;

    abstract public function response();

    /**
     * ip验证、签名验证、游戏区验证、账号验证、订单验证
     * @return bool
     */
    abstract public function isValid();

    /**
     * @abstract
     * @delete
     * @toto delete
     * @return mixed
     */
    abstract public function getProductId();

    /**
     * 订单查询接口
     * @param callback($orderId) $callback
     *
     * @throws \RuntimeException
     */
    public function queryOrder($callback)
    {
        throw new \RuntimeException('uncomplete query order!');
    }

    /**
     * @param callback($account) $callback
     *
     * @throws \RuntimeException
     */
    public function validAccount($callback)
    {
        throw new \RuntimeException('uncomplete query order!');
    }

    public function getAmount()
    {
        $amount = $this->getOption('amount');
        if (!$amount) {
            throw new \RuntimeException('Error amount');
        }

        return $amount;
    }

    public function getRate()
    {
        return $this->rate;
    }
}
