<?php
namespace My\Product\Node;

/**
 * 锁定用户接口
 * Class LockInterface
 *
 * @package My\Product\Node
 * @author  Yoyo
 * @version $Id: LockInterface.php 1279 2014-01-24 01:13:41Z maomao $
 */
interface LockInterface{

    /**
     * 用户锁定
     * @param string $area
     * @param string $account
     * @param string $deadline
     *
     * @return mixed
     */
    public function lock($area, $account, $deadline);

    /**
     * 用户解锁
     * @param string $account
     *
     * @return mixed
     */
    public function unlock($account);

    /**
     * 是否锁定
     * @param string $account
     *
     * @return mixed
     */
    public function isLock($account);
}