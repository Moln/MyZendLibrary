<?php
namespace My\Payment\Data;

/**
 * 订单支付结果接口
 * @author   maomao
 * @DateTime 12-8-13 下午4:59
 * @version  $Id: OrderResult.php 790 2013-03-15 08:56:56Z maomao $
 */
interface OrderResult
{
    const UNPAID    = 0;
    const COMPLETED = 1;
    const PROGRESS  = 2;
    const FAILED    = 3;

    public function setAccount($account);

    public function getAccount();

    public function setOrderId($orderId);

    public function getOrderId();

    public function setOrderTime($orderTime);

    public function getOrderTime();

    public function setStatus($status);

    public function getStatus();

    public function setAmount($amount);

    public function getAmount();

    public function getIp();

    public function isCompleted();

    public function isUnpaid();

    public function isProgress();

    public function isFailed();

    public function getDescription();

    public function setDescription($description);

    public function toArray();

    /**
     * 支付失败动作
     * @param string $message
     */
    public function paymentFailed($message);
}
