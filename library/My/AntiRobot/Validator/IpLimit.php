<?php
/**
 * platform Ip.php
 * @DateTime 14-1-9 下午4:07
 */

namespace My\AntiRobot\Validator;


/**
 * 限制IP 请求
 *
 * 配置:
 * <code>
 * array (
 *  'skipSession'   => false,
 *
 *  'lockTime'      => 86400,
 *  'limit'         => 50,
 *  'lifeTime'      => 3600,
 * );
 *
 * </code>
 *
 * @package My\AntiRobot\Validator
 * @author Xiemaomao
 * @version $Id: IpLimit.php 1331 2014-03-17 23:01:04Z maomao $
 */
class IpLimit extends AbstractLimitValidator implements FormResultInterface
{
    private $skipSession = false, $lock, $disableLAN = true;

    const INVALID = 'INVALID';
    const INVALID_IP_AREA = 'INVALID_IP_AREA';

    protected $messages = array(
        self::INVALID => 'Lock IP: %lock%',
        self::INVALID_IP_AREA => 'Invalid ip area.',
    );

    private function getLock()
    {
        if ($this->lock === null) {
            $this->loopIp(
                function ($ip, $lockId) {
                    $lockId .= '_LOCK';
                    $this->lock = (bool)$this->getCache()->load($lockId);

                    if ($this->lock) $this->msgVars += array('%lock%' => $ip);

                    return !$this->lock;
                }
            );
        }
        return $this->lock;
    }

    /**
     * @param $lockId
     * @param bool $lock
     * @return $this
     */
    private function setLock($lockId, $lock)
    {
        $this->lock = $this->lock || $lock;
        $lockId .= '_LOCK';
        $this->getCache()->save($lock, $lockId, array('anti'), $this->lockTime);
        return $this;
    }

    /**
     * 如果客户端有传 session id , 则忽略验证.
     * @param bool $skipSession
     * @return $this
     */
    public function setSkipSession($skipSession)
    {
        $this->skipSession = $skipSession;
        return $this;
    }


    public function setDisableLAN($disableLAN)
    {
        $this->disableLAN = $disableLAN;
    }

    /**
     * 私有IP地址验证
     * A	10.0.0.0 – 10.255.255.255	16,777,216	10.0.0.0/8 (255.0.0.0)	24位
     * B	172.16.0.0 – 172.31.255.255	1,048,576	172.16.0.0/12 (255.240.0.0)	20位
     * C	192.168.0.0 – 192.168.255.255	65,536	192.168.0.0/16 (255.255.0.0)	16位
     * E	240.0.0.0 – 255.255.255.255
     */
    public function isValidIP()
    {
        $ipList = $this->getIpList();

        $a = array('min' => ip2long('10.0.0.0'),    'max' => ip2long('10.255.255.255'));
        $b = array('min' => ip2long('172.16.0.0'),  'max' => ip2long('172.31.255.255'));
        $c = array('min' => ip2long('192.168.0.0'), 'max' => ip2long('192.168.255.255'));
        $e = array('min' => ip2long('240.0.0.0'),   'max' => ip2long('255.255.255.255'));
        $l = array('min' => ip2long('127.0.0.0'),   'max' => ip2long('127.0.0.255'));

        if ($this->disableLAN) {
            foreach ($ipList as $ip) {
                $ip = ip2long($ip);

                if (($ip > $a['min'] && $ip < $a['max']) ||
                    ($ip > $b['min'] && $ip < $b['max']) ||
                    ($ip > $c['min'] && $ip < $c['max']) ||
                    ($ip > $e['min'] && $ip < $e['max']) ||
                    ($ip > $l['min'] && $ip < $l['max'])
                ) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     *
     * @return bool
     */
    public function isValid()
    {
        $lock = $this->getLock();

        if (!$this->isValidIP()) {
            $this->setError(self::INVALID_IP_AREA);
            return false;
        }

        if ($lock && $this->skipSession && \Zend_Session::sessionExists()) {
            return true;
        }

        if ($lock) $this->setError(self::INVALID);

        return !$lock;
    }

    /**
     * @param bool $formValidResult
     * @return void
     * @todo IP chain lock
     */
    public function setFormValidResult($formValidResult)
    {
        if ($this->getLock()) {
            return;
        }

        $cache = $this->getCache();

        $this->loopIp(
            function ($ip, $id) use ($cache) {
                $count = $cache->load($id) ? : 1;
                $cache->save(++$count, $id, array('anti'), $this->lifetime);

                if ($count > $this->limit) {
                    $this->setLock($id, true);
                }
            }
        );
    }
}