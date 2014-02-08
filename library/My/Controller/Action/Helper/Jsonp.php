<?php
namespace My\Controller\Action\Helper;

/**
 * Jquery jsonp
 * @author   maomao
 * @DateTime 12-7-30 上午10:25
 * @version  $Id: Jsonp.php 790 2013-03-15 08:56:56Z maomao $
 */
class Jsonp extends \Zend_Controller_Action_Helper_Abstract
{

    private static $callback;

    public function direct()
    {
        $callback = $this->getRequest()->getParam(self::$callback ? : 'callback');

        if (!preg_match('/^[\w\.]+$/', $callback)) {
            echo 'Error callback!';exit;
        }

        $args = func_get_args();
        $data = $callback . '(' . implode(', ', array_map('json_encode', $args)) . ')';
        $response = $this->getResponse();
        $response->setHeader('Content-Type', 'application/javascript');
        $response->setBody($data);
        $response->sendResponse();
        exit;
    }

    public static function getCallback()
    {
        return self::$callback;
    }

    public static function setCallback($callback)
    {
        self::$callback = $callback;
    }
}
