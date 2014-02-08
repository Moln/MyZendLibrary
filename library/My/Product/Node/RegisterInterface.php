<?php
namespace My\Product\Node;

/**
 * 注册接口
 * @package My\Product\Node
 * @author Xiemaomao
 * @version $Id: RegisterInterface.php 790 2013-03-15 08:56:56Z maomao $
 */
interface RegisterInterface
{
    public function changePassword($account, $password);

    public function register(array $userData);
}
