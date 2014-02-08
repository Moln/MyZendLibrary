<?php
namespace My\Payment\Service;
use My\Payment\Data\OrderResult;
use My\Log\Logger;

/**
 * @author  maomao
 * @version $Id: ServiceAbstract.php 1269 2013-11-11 03:51:17Z maomao $
 */
abstract class ServiceAbstract implements ServiceInterface
{
    protected static $name;

    protected $gateway,
        $sn,
        $key,
        $options = array(),
        $charset = 'utf-8',
        $rate = 1,
        $error,
        $messages = [],
        $allowIp;

    protected $keyMapper
        = array(
            'sn'     => 'sn',
            'order'  => 'order',
            'amount' => 'amount',
            'pay_id' => 'pay_id',
        );

    public function __construct(array $config)
    {
        if (empty($this->sn)) {
            if (empty($config['sn'])) {
                throw new \RuntimeException('未知渠道商的序列号');
            } else {
                $this->sn = $config['sn'];
                if (isset($this->keyMapper['sn'])) {
                    $config['options']['sn'] = $config['sn'];
                }
            }
        }

        if (empty($config['key'])) {
            throw new \RuntimeException('未知渠道商的密钥');
        } else {
            $this->key = $config['key'];
        }

        if (empty($config['gateway'])) {
            if (empty($this->gateway)) {
                throw new \RuntimeException('未知渠道商的网关');
            }
        } else {
            $this->gateway = $config['gateway'];
        }

        unset($config['key'], $config['gateway']);

        $this->setConfig($config);
        $this->init();
    }

    public function init()
    {
    }

    public function setConfig($config)
    {
        if (isset($config['options'])) {
            $this->mergeOptions($config['options']);
            unset($config['options']);
        }
        foreach ($config as $key => $value) {
            $method = 'set' . ucfirst($key);
            method_exists($this, $method) && $this->$method($value);
        }
    }

    public function getKey()
    {
        return $this->key;
    }

    /**
     * set option
     * @param string $key
     * @param mixed $value
     * @return ServiceAbstract
     */
    public function setOption($key, $value)
    {
        $key                 = isset($this->keyMapper[$key]) ? $this->keyMapper[$key] : $key;
        $this->options[$key] = $value;
        return $this;
    }

    /**
     * get options
     *
     * @param array $options
     *
     * @return mixed
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * merge options
     * @param array $options
     * @return ServiceAbstract
     */
    public function mergeOptions(array $options)
    {
        foreach ($options as $key => $value) {
            $this->setOption($key, $value);
        }
        return $this;
    }

    /**
     * get options
     * @return mixed:
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * get a option
     * @param string $key
     * @return mixed
     */
    public function getOption($key)
    {
        if (isset($this->keyMapper[$key]) && isset($this->options[$this->keyMapper[$key]])) {
            return $this->options[$this->keyMapper[$key]];
        }

        if (isset($this->options[$key])) {
            return $this->options[$key];
        }
        return null;
    }

    public static function getName()
    {
        return static::$name;
    }

    public static function setName($name)
    {
        static::$name = $name;
    }

    public function getError()
    {
        return $this->error;
    }

    public function setError($error)
    {
        $this->error = $error;
        if (isset($this->messages[$error])) {
            $this->setMessage($this->messages[$error]);
        }
        return $this;
    }

    /**
     * @var OrderResult
     */
    protected $orderResult;

    /**
     * @return OrderResult
     */
    public function getOrderResult()
    {
        return $this->orderResult;
    }

    /**
     * @param $payResult
     *
     * @return ServiceAbstract
     */
    public function setOrderResult(OrderResult $payResult)
    {
        $this->orderResult = $payResult;
        return $this;
    }

    protected $message;

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    public function logger($message, $extra = [])
    {
        Logger::info(
            $message,
            $extra + array(
                'Type' => 'Payment',
                'Service' => str_replace(__NAMESPACE__, '', get_class($this)),
                'ServerIp' => $this->getRequest()->getClientIp(CLIENT_IP_PROXY),
            )
        );
    }

    abstract public function getRate();

    protected function setRate($rate)
    {
        $this->rate = $rate;
        return $this;
    }

    /**
     * @return null|\Zend_Controller_Request_Http
     */
    public function getRequest()
    {
        return \Zend_Controller_Front::getInstance()->getRequest();
    }

    protected $validAccountCallback;

    /**
     * @param callback($account) $callback
     */
    public function setValidAccountCallback($callback)
    {
        $this->validAccountCallback = $callback;
    }

    protected $orderResultCallback;

    public function setOrderResultCallback($callback)
    {
        $this->orderResultCallback = $callback;
        return $this;
    }

    /**
     * @param $order
     *
     * @throws \RuntimeException
     * @return null|\My\Payment\Data\OrderResult
     */
    public function loadOrderResult($order)
    {
        $call = $this->orderResultCallback;
        if (!is_callable($call)) {
            throw new \RuntimeException('Unknown orderResult callback');
        }
        return $call($order);
    }

    protected $orderResultByPayCallback;

    public function setOrderResultByPayCallback($callback)
    {
        $this->orderResultByPayCallback = $callback;
        return $this;
    }

    /**
     * @param $order
     *
     * @throws \RuntimeException
     * @return null|\My\Payment\Data\OrderResult
     */
    public function loadOrderResultByPay($order)
    {
        $call = $this->orderResultByPayCallback;
        if (!is_callable($call)) {
            throw new \RuntimeException('Unknown orderResultByPay callback');
        }
        return $call($order);
    }

    protected function isAllowIp()
    {
        /** @var $request \Zend_Controller_Request_Http */
        $request = \Zend_Controller_Front::getInstance()->getRequest();
        return strpos($this->allowIp, $request->getClientIp(CLIENT_IP_PROXY)) !== false;
    }

    protected function setAllowIp($allowIp)
    {
        $this->allowIp = $allowIp;
    }

    public static function queryBuild(array $params, $encode = false)
    {
        $str = '';
        if ($encode) {
            foreach ($params as $key => $value) {
                $str .= '&' . $key . '=' . urlencode($value);
            }
        } else {
            foreach ($params as $key => $value) {
                $str .= '&' . $key . '=' . $value;
            }
        }

        return substr($str, 1);
    }
}