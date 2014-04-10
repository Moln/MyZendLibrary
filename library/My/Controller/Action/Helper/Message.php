<?php
namespace My\Controller\Action\Helper;

/**
 * JSON格式返回消息
 *
 * @author   maomao
 * @DateTime 12-6-5 下午2:05
 * @version  $Id: Message.php 1335 2014-03-25 17:43:10Z maomao $
 */
class Message extends \Zend_Controller_Action_Helper_Abstract
{
    public function direct($message, $code = 0, array $data = null)
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->getActionController()->getHelper('json')->sendJson(
                [
                    'code' => $code,
                    'msg'  => $message
                ] + ($data ? : [])
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
