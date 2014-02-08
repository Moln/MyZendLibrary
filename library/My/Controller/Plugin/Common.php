<?php
/**
 * 公共插件
 * @author: maomao
 * @version: $Id: Common.php 790 2013-03-15 08:56:56Z maomao $
 */
namespace My\Controller\Plugin;

use My\Process\Process,
    Zend_Controller_Plugin_Abstract,
    Zend_Auth,
    Zend_Controller_Request_Abstract,
    Zend_Validate,
    Zend_Controller_Front;

/**
 * Class Common
 * @package My\Controller\Plugin
 */
class Common extends Zend_Controller_Plugin_Abstract
{
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        $front = Zend_Controller_Front::getInstance();
        $moduleName = $request->getModuleName();
        $modules = $front->getParam('bootstrap')->getResource('modules');
        if (isset($modules[$moduleName])) {
            $bootstrap = $modules[$moduleName];

            $loads = array_filter(get_class_methods($bootstrap), function ($val) {
                    return 'load' == substr($val, 0, 4);
                });
            foreach ($loads as $method) {
                $bootstrap->$method();
            }
        }
    }

    public function dispatchLoopShutdown() {
        if(Process::hasTask()) {
            $response = $this->getResponse();
            if($response->isException()) return;
            $response->setHeader('Connection', 'close');
            $response->setHeader('Content-length', strlen($response->getBody()));
            $response->sendResponse();
            ob_end_flush();
            flush();
            if (\Zend_Session::isStarted()) {
                \Zend_Session::writeClose();
            }
            Process::run();
            Zend_Controller_Front::getInstance()->returnResponse(true);
        }
    }

    public function __destruct()
    {
        if(Process::hasTask()) {
            $response = $this->getResponse();
            if($response->isException()) return;
            if (!headers_sent()) {
                $response->setHeader('Connection', 'close');
                $response->setHeader('Content-length', ob_get_length());
                $response->sendHeaders();
                ob_end_flush();
                flush();
            }
            if (\Zend_Session::isStarted()) {
                \Zend_Session::writeClose();
            }
            Process::run();
        }
    }
}