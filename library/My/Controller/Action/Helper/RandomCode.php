<?php
namespace My\Controller\Action\Helper;

/**
 * Class RandomCode
 * @package My\Controller\Action\Helper
 * @author Xiemaomao
 * @version $Id$
 */
class RandomCode extends \Zend_Controller_Action_Helper_Abstract
{

    public function direct($length = 8)
    {
        return substr(str_shuffle('123456789abcdefghijkmnpqrstuvwxyz'), 0, $length);
    }
}