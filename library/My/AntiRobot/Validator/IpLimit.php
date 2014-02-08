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
 * @version $Id: IpLimit.php 1279 2014-01-24 01:13:41Z maomao $
 */
class IpLimit extends AbstractLimitValidator implements FormResultInterface
{
    private $skipSession = false, $lock;

    private function getLock()
    {
        if ($this->lock === null) {
            $this->loopIp(
                function ($ip, $lockId) {
                    $lockId .= '_LOCK';
                    $this->lock = (bool)$this->getCache()->load($lockId);
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

    /**
     *
     * @return bool
     */
    public function isValid()
    {
        $lock = $this->getLock();

        if ($lock && $this->skipSession && \Zend_Session::sessionExists()) {
            return true;
        }

        return !$lock;
    }

    /**
     * @param bool $formValidResult
     * @return void
     */
    public function setFormValidResult($formValidResult)
    {
        if ($formValidResult || $this->getLock()) {
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