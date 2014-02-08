<?php
namespace My\Controller\Action\Helper;

/**
 * JSON格式返回消息
 *
 * @author   maomao
 * @DateTime 12-6-5 下午2:05
 * @version  $Id: Message.php 790 2013-03-15 08:56:56Z maomao $
 */
class Message extends \Zend_Controller_Action_Helper_Abstract
{
    public function direct($message, $code = 0)
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->getActionController()->getHelper('json')->sendJson(
                [
                    'code' => $code,
                    'msg'  => $message
                ]
            );
        }

        $request = $this->getRequest();
        $this->getActionController()->view->message = $message;
        $this->getActionController()->view->messageCode = $code;
        $request->setControllerName('error');
        $request->setModuleName('default');
        $request->setActionName('message');
        $request->setDispatched(false);
    }
}
