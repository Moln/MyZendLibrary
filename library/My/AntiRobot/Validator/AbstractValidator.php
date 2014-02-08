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
 * @version $Id: AbstractValidator.php 1279 2014-01-24 01:13:41Z maomao $
 */
abstract class AbstractValidator implements ValidatorInterface
{
    use Option;

    private static $eventManager;

    public static function getEventManager()
    {
        if (!self::$eventManager) {
            self::$eventManager = new \Zend_EventManager_EventManager(__CLASS__);
        }
        return self::$eventManager;
    }

    public function __construct(array $options)
    {
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