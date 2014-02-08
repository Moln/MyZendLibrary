<?php
/**
 * platform AccountStatusInterface.php
 * @DateTime 13-7-24 下午5:10
 */

namespace My\Product;

/**
 * 验证账号状态接口
 * @package My\Product
 * @author Xiemaomao
 * @version $Id: ValidLoginApiInterface.php 1279 2014-01-24 01:13:41Z maomao $
 */
interface ValidLoginApiInterface {

    /**
     * @param array $user 账号
     * @param int $code
     * @param string $message
     * @param array $ext
     * @return bool
     */
    public function isValidAccount($user, &$code = null, &$message = null, &$ext = []);
}