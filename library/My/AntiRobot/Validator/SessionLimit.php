<?php
namespace My\AntiRobot\Validator;

/**
 * 限定每个Session 次数
 *
 * 配置:
 * <code>
 * array(
 *  'lockTime'      => 86400,
 *  'limit'         => 15,
 *  'lifeTime'      => 3600,
 *  'totalOverNumber' => 50
 * );
 * </code>
 *
 * @package My\AntiRobot\Validator
 * @author Xiemaomao
 * @version $Id: SessionLimit.php 1296 2014-01-27 19:20:23Z maomao $
 */
class SessionLimit extends AbstractLimitValidator implements FormResultInterface
{

    private $session,
        $totalOverNumber = 50,
        $lock;
    protected $limit = 15;

    /**
     * 1个IP, SESSION 验证错误请求超过此设置的IP锁定
     * @param int $totalOverNumber
     * @return $this
     */
    public function setTotalOverNumber($totalOverNumber)
    {
        $this->totalOverNumber = $totalOverNumber;
        return $this;
    }

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

    public function __construct(array $options)
    {
        $this->session = new \Zend_Session_Namespace(__CLASS__);
        parent::__construct($options);
    }

    public function isValid()
    {
        if ($this->session->count > $this->limit) {
            return false;
        }

        return !$this->getLock();
    }

    /**
     * 表单验证结果
     * @param bool $formValidResult
     * @return void
     */
    public function setFormValidResult($formValidResult)
    {
        if ($formValidResult) {
            return;
        }

        $this->session->count = $this->session->count ? $this->session->count+1 : 1;

        if ($this->getLock()) {
            return;
        }

        $cache = $this->getCache();
        $this->loopIp(
            function ($ip, $id) use ($cache) {
                $sessions = $cache->load($id) ? : array();
                $sessions[session_id()] = isset($sessions[session_id()]) ? $sessions[session_id()]++ : 1;
                $cache->save($sessions, $id, array('anti'), $this->lifetime);
                if (array_sum($sessions) > $this->totalOverNumber) {
                    $this->setLock($id, true);
                }
            }
        );
    }
}