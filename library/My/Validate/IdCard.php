<?php
namespace My\Validate;
use Zend_Validate_Abstract;

/**
 * 身份证验证
 *
 * @author maomao
 * @version $Id: IdCard.php 790 2013-03-15 08:56:56Z maomao $
 */
class IdCard extends Zend_Validate_Abstract
{
    const INVALID = 'idCardInvalid';
    const ERROR_FORMAT = 'errorFormat';

    protected $_messageTemplates = array(
        self::INVALID      => '输入参数错误',
        self::ERROR_FORMAT => '身份证格式不正确',
    );

    public function isValid($value)
    {
        if (!is_string($value)) {
            $this->_error(self::INVALID, $this->_messageTemplates[self::INVALID]);
            return false;
        }

        $area = [11, 12, 13, 14, 15, 21, 22, 23, 31, 32, 33, 34, 35, 36, 37, 41,
                 42, 43, 44, 45, 46, 50, 51, 52, 53, 54, 61, 62, 63, 64, 65, 71,
                 81, 82, 91, ];

        if (!in_array(substr($value, 0, 2), $area)) {
            $this->_error(self::ERROR_FORMAT, $this->_messageTemplates[self::ERROR_FORMAT]);
            return false;
        }

        switch (strlen($value)) {
            case 15:
                $y = substr($value, 6, 2)+1900;
                $l = ($y%4 == 0 || $y%400 == 0) ? 9 : 8;

                $reg = '/^[1-9][0-9]{5}[0-9]{2}((01|03|05|07|08|10|12)(0[1-'
                     . '9]|[1-2][0-9]|3[0-1])|(04|06|09|11)(0[1-9]|[1-2][0-'
                     . "9]|30)|02(0[1-9]|1[0-9]|2[0-$l]))[0-9]{3}$/";

                if (!preg_match($reg, $value)) {
                    $this->_error(self::ERROR_FORMAT, $this->_messageTemplates[self::ERROR_FORMAT]);
                    return false;
                }

                /* $m = substr($value, 8, 2);
                $d = substr($value, 10, 2);
                $age = date('y')-$y;
                if ($m < date('m') || ($m == date('m') && $d < date('d'))) {
                    $age--;
                } */

                break;
            case 18:
                $y = substr($value, 6, 4);
                $l = ($y%4 == 0 || $y%400 == 0) ? 9 : 8;

                $reg = '/^[1-9][0-9]{5}(18|19|20)[0-9]{2}((01|03|05|07|08|10|'
                     . '12)(0[1-9]|[1-2][0-9]|3[0-1])|(04|06|09|11)(0[1-9]|[1'
                     . "-2][0-9]|30)|02(0[1-9]|1[0-9]|2[0-$l]))[0-9]{3}[0-9Xx"
                     . ']$/';

                if (!preg_match($reg, $value)) {
                    $this->_error(self::ERROR_FORMAT, $this->_messageTemplates[self::ERROR_FORMAT]);
                    return false;
                }
                break;
            default:
                $this->_error(self::ERROR_FORMAT, $this->_messageTemplates[self::ERROR_FORMAT]);
                return false;
                break;
        }

        return true;
    }
}