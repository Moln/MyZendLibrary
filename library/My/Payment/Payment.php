<?php
/**
 * Payment.php
 *
 * @author   maomao
 * @DateTime 12-5-30 下午5:21
 * @version  $Id: Payment.php 790 2013-03-15 08:56:56Z maomao $
 */
namespace My\Payment;

/**
 * 充值服务
 * Class Payment
 * @package My\Payment
 */
class Payment
{
    const RATE = 10;

    const ALIPAY   = 'alipay';
    const YEEPAY   = 'yeepay';
    const PAY19PAY = 'pay19pay';
    const VNETONE  = 'vnetone';
    const VNETONE_PHONE  = 'vnetonePhone';
    const UNTX     = 'untx';
    const TIANXIAFU2     = 'tianxiafu2';

    private static $services
        = array(
            'all'       => [
                self::YEEPAY   => '易宝支付',
                self::ALIPAY   => '支付宝',
                self::PAY19PAY => '19Pay',
                self::VNETONE  => 'V币钱包支付',
                self::TIANXIAFU2  => '天下付',
            ],
            'bank'      => [
                self::ALIPAY   => '支付宝',
                self::YEEPAY   => '易宝支付',
                self::PAY19PAY => '19Pay',
            ],
            'phoneCard' => [
                self::YEEPAY   => '易宝支付',
                self::PAY19PAY => '19Pay',
                self::UNTX     => '新宽联',
                self::VNETONE_PHONE => '盈华讯方-神州行',
            ]
        );

//    public static function factory($adapter, $service)
//    {
//        $className = self::getAdapterClass($adapter);
//        $options   = is_string($service) ? ['service' => $service] : $service;
//        return new $className($options);
//    }

//    public static function getAdapterClass($adapter)
//    {
//        $className = 'My\Payment\Adapter\\' . $adapter;
//        if (class_exists($className)
//            && in_array('My\Payment\Adapter\AdapterInterface', class_implements($className))
//        ) {
//            return $className;
//        } else {
//            throw new \RuntimeException('错误的适配器:' . $adapter);
//        }
//    }

    public static function getSupportChannels($adapter, $service)
    {
        /** @var $className Service\ServiceAbstract */
        $className = self::getServiceLoader()->load($service);
        return $className::getChannels($adapter);
    }


    public static function createOrderId()
    {
        list(, $m) = explode('.', number_format(microtime(true), 3, ".", ""));
        return date('ymdHis') . $m . mt_rand(100, 999);
    }

    /**
     * @static
     *
     * @param string $channel
     *
     * @return array
     * @throws \RuntimeException
     */
    public static function getChannelServices($channel = 'bank')
    {
        if (!isset(self::$services[$channel])) {
            throw new \RuntimeException('未知适配器:' . $channel);
        }
        return self::$services[$channel];
    }

    /**
     * Has service
     *
     * @param string $service
     *
     * @return bool
     */
    public static function hasService($service)
    {
        $className = self::getServiceLoader()->load($service, false);
        $interface = 'My\Payment\Service\ServiceInterface';
        return $className && in_array($interface, class_implements($className));
    }

    public static function getProvider($service)
    {
        $className = self::getServiceLoader()->load($service, false);
        $interface = 'My\Payment\Service\ServiceInterface';
        if ($className && in_array($interface, class_implements($className))) {
            return $className::getName();
        }

        throw new \RuntimeException('不是一个有效供应商:' . $service);
    }

    /**
     *
     * @param string $service
     * @param array  $options
     *
     * @throws \RuntimeException
     * @return Service\ChannelPay|Service\DirectPay
     */
    public static function factoryService($service, $options = array())
    {
        $config = \My\Config\Factory::getConfigs('payment');
        $options += $config[$service];

        $className  = self::getServiceLoader()->load($service);
        $reflection = new \ReflectionClass($className);
        $interface  = 'My\\Payment\\Service\\ServiceInterface';
        if (!$reflection->implementsInterface($interface)) {
            throw new \RuntimeException($className . " does not implement $interface.");
        }
        return new $className($options);
    }


    private static $pluginLoader;

    /**
     * Retreive PluginLoader
     *
     * @return \Zend_Loader_PluginLoader_Interface
     */
    public static function getServiceLoader()
    {
        if (!self::$pluginLoader instanceof \Zend_Loader_PluginLoader_Interface) {
            self::$pluginLoader = new \Zend_Loader_PluginLoader(
                ['\\My\\Payment\\Service\\' => 'My/Payment/Service'], __CLASS__
            );
        }

        return self::$pluginLoader;
    }
}


/*array(
    'icbc' => '中国工商银行',
    'ccb' => '中国建设银行',
    'abc' => '中国农业银行',
    'cmb' => '招商银行',
    'boc' => '中国银行',
    'bcom' => '交通银行',
    'ceb' => '中国光大银行',
    'gdb' => '广东发展银行',
    'post' => '中国邮政储蓄银行',
    'gzrcc' => '广东农村信用社',
    'gzcb' => '广州银行',
    'shrcc' => '上海农村商业银行',
    'spdb' => '浦发银行',
    'bjrcb' => '北京农村商业银行',
    'cmbc' => '中国民生银行',
    'sdb' => '深圳发展银行',
    'citic' => '中信银行',
    'pab' => '平安银行',
    'shb' => '上海银行',
    'bccb' => '北京银行',
    'hxb' => '华夏银行',
    'nbcb' => '宁波银行',
    'njcb' => '南京银行',
    'hzb' => '杭州银行',
    'hsb' => '徽商银行',
    'czb' => '浙商银行',
    'cbhb' => '渤海银行',
    'bea' => 'BEA东亚银行',
    'cib' => '兴业银行',
);*/
