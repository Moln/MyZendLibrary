<?php
namespace My\Product\Node\Login;

/**
 * 登录信息
 * Class Info
 * @package My\Product\Node\Login
 * @author Xiemaomao
 * @version $Id: Info.php 790 2013-03-15 08:56:56Z maomao $
 */
class Info
{
    public function __construct($id, $account, $gameTime = null, $isAdult = false)
    {
        $this->id = $id;
        $this->account = $account;
        $this->gameTime = $gameTime;
        $this->isAdult = $isAdult;
    }
    private $id, $account, $gameTime, $isAdult, $enableAntiAddiction = false;

    public function getAccount()
    {
        return $this->account;
    }

    public function setEnableAntiAddiction($enableAntiAddiction)
    {
        $this->enableAntiAddiction = (bool) $enableAntiAddiction;
        return $this;
    }

    public function isEnableAntiAddiction()
    {
        return $this->enableAntiAddiction;
    }

    public function getGameTime()
    {
        return $this->gameTime;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getIsAdult()
    {
        return $this->isAdult;
    }

    public function getLoginIp()
    {
        return \Zend_Controller_Front::getInstance()->getRequest()->getClientIp(CLIENT_IP_PROXY);
    }
}
