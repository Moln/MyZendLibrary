<?php
/**
 * platform AbstractFilter.php
 * @DateTime 14-1-9 下午4:11
 */

namespace My\AntiRobot\Validator;

use My\Stdlib\Fragment\Option;

/**
 * Class AbstractFilter
 * @package My\AntiRobot\Validator
 * @author Xiemaomao
 * @version $Id: AbstractValidator.php 1329 2014-03-13 00:02:24Z maomao $
 */
abstract class AbstractValidator implements ValidatorInterface
{
    use Option;

    protected $message, $error, $messages = array(), $msgVars = array();

    public function setError($error, $vars = array())
    {
        $this->error = $error;
        if (isset($this->messages[$error])){
            $msgVars = $this->getMsgVars() + $vars;
            $this->message = str_replace(array_keys($msgVars), $msgVars, $this->messages[$error]);
        }
        return $this;
    }

    private function getMsgVars()
    {
        if (empty($this->msgVars)){
            return array();
        }
        $keys = array_keys($this->msgVars);
        if ($keys[0][0] == '%') {
            return $this->msgVars;
        }

        foreach ($this->msgVars as $key => $val) {
            $this->msgVars['%' . $key . '%'] = $val;
            unset($this->msgVars[$key]);
        }

        return $this->msgVars;
    }

    public function getError()
    {
        return $this->error;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function __construct(array $options)
    {
        $this->msgVars = $_SERVER;
        $this->setOptions($options);
    }

    /**
     * @return \Zend_Controller_Request_HTTP
     */
    public function getRequest()
    {
        return \Zend_Controller_Front::getInstance()->getRequest();
    }
}