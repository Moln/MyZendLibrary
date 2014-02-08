<?php
namespace My\AntiRobot\Validator;

/**
 * Class AbstractLimitValidator
 * @package My\AntiRobot\Validator
 * @author Xiemaomao
 * @version $Id: AbstractLimitValidator.php 1296 2014-01-27 19:20:23Z maomao $
 */
abstract class AbstractLimitValidator extends AbstractValidator
{
    protected static $cache;
    protected $lockTime = 86400,
        $limit = 50,
        $lifetime = 3600,
        $keyPrefix;

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


    protected function loopIp(\Closure $call)
    {
        $request = $this->getRequest();
        $ips[] = $request->getServer('REMOTE_ADDR');
        if ($ipx = $request->getServer('HTTP_X_FORWARDED_FOR')) {
            array_merge($ips, array_filter(array_map('ip2long', array_map('trim', explode(',', $ipx)))));
        }
        if ($ipx = $request->getServer('HTTP_CLIENT_IP')) {
            array_merge($ips, $ipx);
        }

        foreach ($ips as $ip) {
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