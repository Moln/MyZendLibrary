<?php
namespace My\Token;

/**
 * Class TokenAbstract
 * @package My\Token
 * @author Xiemaomao
 * @version  $Id: TokenAbstract.php 790 2013-03-15 08:56:56Z maomao $
 */
abstract class TokenAbstract
{
    protected $message;
    protected $messages;
    protected $result;

    public abstract function auth($account, $password);

    public abstract function bind($account, $password, $sn);

    public abstract function unbind($account, $password, $sn);

    public abstract function delete($account, $sn = '');

    public abstract function replace($account, $sn, $password, $newSn, $newPassword);

    /*
    public abstract function queryUser($sn);
    public abstract function querySn($account);
    public abstract function queryStatus($account);
    */

    /**
     * 返回消息
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    protected function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }
}