<?php
namespace My\Payment\Adapter;

use My\Payment\Service\ServiceAbstract,
    My\Payment\Service\Alipay,
    My\Payment\Payment;

/**
 * 网银支付方式
 * @author   maomao
 * @DateTime 12-5-21 下午2:32
 * @version  $Id: Bank.php 790 2013-03-15 08:56:56Z maomao $
 */
class Bank implements AdapterInterface
{
    public function __construct(array $options = [])
    {
        if (isset($options['service'])) {
            $this->setService($options['service']);
        }
        unset($options['service']);
        $this->setOptions($options);
    }

    /**
     * @var ServiceAbstract
     */
    private $service;
    private $options;

    public function setService($service)
    {
        if (is_string($service)) {
            $className = Payment::getServiceLoader()->load($service);
            $options = isset($className::$bankAdapterOptions) ? $className::$bankAdapterOptions : [];

            $service = Payment::factoryService($service, $options);
        }
        if (!$service instanceof ServiceAbstract) {
            throw new \RuntimeException('错误类型: ' . $service);
        }
        $this->service = $service;
        return $this;
    }

    /**
     * @return ServiceAbstract
     */
    public function getService()
    {
        return $this->service;
    }

    public function setOptions(array $options)
    {
        return $this->getService()->mergeOptions($options);
    }
}