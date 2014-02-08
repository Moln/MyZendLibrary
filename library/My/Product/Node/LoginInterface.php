<?php
namespace My\Product\Node;

/**
 * 游戏登录接口
 * Class LoginInterface
 * @package My\Product\Node
 * @author Xiemaomao
 * @version $Id: LoginInterface.php 790 2013-03-15 08:56:56Z maomao $
 */
interface LoginInterface
{
    /**
     * 登录
     * @param Login\Info $info
     *
     * @return bool|string
     */
    public function login(Login\Info $info);
}