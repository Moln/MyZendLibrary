<?php
namespace My\Controller\Action\Helper;

/**
 * 404 NotFound
 * @author   maomao
 * @DateTime 12-6-12 下午4:51
 * @version  $Id: NotFound.php 790 2013-03-15 08:56:56Z maomao $
 */
class NotFound extends \Zend_Controller_Action_Helper_Abstract
{
    public function direct($message = 'Page not found')
    {
        throw new \Zend_Controller_Action_Exception($message, 404);
    }
}