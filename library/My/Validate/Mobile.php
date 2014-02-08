<?php
namespace My\Validate;

/**
 * 手机验证
 * Class Mobile
 * @package My\Validate
 * @author Xiemaomao
 * @version $Id: Mobile.php 790 2013-03-15 08:56:56Z maomao $
 */
class Mobile extends \Zend_Validate_Abstract
{
    const INVALID = 'mobileInvalid';
    const NOT_MOBILE = 'notMobile';

    protected $_messageTemplates = array(
        self::INVALID    => '手机号码必须为11位纯数字',
        self::NOT_MOBILE => '手机号码格式不正确',
    );

    public function isValid($value)
    {
        if (!is_numeric($value) || strlen($value) != 11) {
            $this->_error(self::INVALID);
            return false;
        }

        if (strval($value)[0] != '1') {
            $this->_error(self::NOT_MOBILE);
            return false;
        }

        return true;
    }
}