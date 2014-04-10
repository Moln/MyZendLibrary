<?php
namespace My\AntiRobot\Validator;

/**
 * Class AbstractLimitValidator
 * @package My\AntiRobot\Validator
 * @author Xiemaomao
 * @version $Id: AbstractLimitValidator.php 1330 2014-03-16 23:58:59Z maomao $
 */
abstract class AbstractLimitValidator extends AbstractValidator
{
    protected static $cache;
    protected $lockTime = 86400,
        $limit = 50,
        $lifetime = 3600,
        $keyPrefix;
    private $ipList;

    public function __construct(array $options)
    {
        $name = isset($options['robotName']) ? $options['robotName'] : 'default';
        $this->keyPrefix = $name . '_' . str_replace('\\', '_', get_class($this));

        parent::__construct($options);
    }

    /**
     * 限定时间内不能超过的数量
     * @param int $limit
     * @return self
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * 限定时间
     * @param int $lifetime
     * @return self
     */
    public function setLifetime($lifetime)
    {
        $this->lifetime = $lifetime;
        return $this;
    }

    /**
     * 锁定时间
     * @param int $lockTime
     * @return self
     */
    public function setLockTime($lockTime)
    {
        $this->lockTime = $lockTime;
        return $this;
    }

    protected function getIpList()
    {
        if (!$this->ipList) {
            $request = $this->getRequest();
            $ips[] = $request->getServer('REMOTE_ADDR');

            $ip1 = $request->getServer('HTTP_X_FORWARDED_FOR');
            $ip2 = $request->getServer('HTTP_CLIENT_IP');
            if ($ip1 || $ip2) {
                $ipx = $ip1 . ',' .  $ip2;
                $ipx = array_filter(array_map('trim', explode(',', $ipx)), function ($ip) {
                    return (bool)ip2long($ip);
                });
                $ips = array_merge($ips, $ipx);
            }
            $this->ipList = array_unique($ips);
        }

        return $this->ipList;
    }


    protected function loopIp(\Closure $call)
    {
        $ipList = $this->getIpList();
        foreach ($ipList as $ip) {
            if ($call($ip, $this->keyPrefix . dechex(ip2long($ip))) === false) {
                break;
            }
        }
    }

    /**
     * @throws \RuntimeException
     * @return \Zend_Cache_Core
     */
    public static function getCache()
    {
        if (!self::$cache) {
            self::setCache(\Zend_Registry::get('application.cache'));
            if (!self::$cache) {
                throw new \RuntimeException('No register cache.');
            }
        }

        return self::$cache;
    }

    /**
     * @param \Zend_Cache_Core $cache
     * @return self
     */
    public static function setCache(\Zend_Cache_Core $cache)
    {
        self::$cache = $cache;
    }
}