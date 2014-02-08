<?php
/**
 * 支付宝方式充值
 * @author   maomao
 * @DateTime 12-7-4 上午10:47
 * @version  $Id: Alipay.php 790 2013-03-15 08:56:56Z maomao $
 */
namespace My\Payment\Adapter;

use My\Payment\Payment;

/**
 * Class Alipay
 * @package My\Payment\Adapter
 */
class Alipay implements AdapterInterface
{
    private $service;

    public function __construct($options = [])
    {
        unset($options['service']);
        $this->service = Payment::factoryService('alipay', $options);
    }

    public function getService()
    {
        return $this->service;
    }

}
