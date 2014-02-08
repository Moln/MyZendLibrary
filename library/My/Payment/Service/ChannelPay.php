<?php
namespace My\Payment\Service;
use My\Payment\Data\OrderResult;

/**
 * ChannelPay.php
 * @author   maomao
 * @DateTime 12-7-12 上午10:40
 * @version  $Id: ChannelPay.php 800 2013-03-19 06:50:49Z maomao $
 */
abstract class ChannelPay extends ServiceAbstract
{
    protected static $channels = [];

    public function redirectPay(OrderResult $order)
    {
        $params = $this->getRequestParams($order);
        header('Location: ' . $this->gateway . '?' . http_build_query($params));
        exit;
    }

    public static function getChannels($adapter)
    {
        $channels = [];
        if (isset(static::$channels[$adapter]) && isset(static::$channels[$adapter . 'Map'])) {
            foreach (static::$channels[$adapter . 'Map'] as $key => $val) {
                $channels[$key] = static::$channels[$adapter][$val];
            }
        }
        return $channels;
    }

    /**
     * @throws \RuntimeException
     * @return string
     */
    public function getChannel()
    {
        $channel = $this->getOption('bank');
        if (isset(static::$channels['bankMap'][$channel])) {
            return static::$channels['bankMap'][$channel];
        } else if (isset(static::$channels['phoneCardMap'][$channel])) {
            return static::$channels['phoneCardMap'][$channel];
        } else {
            return $channel;
//            throw new \RuntimeException('未知渠道支付方式:' . $channel);
        }
    }

    /**
     * 跳转时传递参数
     * @abstract
     *
     * @param \My\Payment\Data\OrderResult $order
     *
     * @return array
     */
    abstract public function getRequestParams(OrderResult $order);

    /**
     * 服务端验证
     * @return bool
     */
    abstract public function isValidServer();

    /**
     * 服务端响应
     */
    abstract public function serverResponse();
    public function isValidClient() {return true;}

    public function getRate()
    {
        if (!isset($this->rate['default'])) {
            throw new \RuntimeException('未知默认兑换率');
        }

        $channel = $this->getOption('bank');
        return isset($this->rate[$channel]) ? $this->rate[$channel] : $this->rate['default'];
    }
}
