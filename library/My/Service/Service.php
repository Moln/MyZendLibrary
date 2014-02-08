<?php
namespace My\Service;

/**
 * ServiceFactory.php
 * @author   maomao
 * @DateTime 12-7-23 下午2:33
 * @version  $Id: Service.php 790 2013-03-15 08:56:56Z maomao $
 */
class Service
{
    public static function factory($protocol, $serviceName)
    {
        $array = explode('-', $protocol);
        $protocol = array_shift($array);
        $adapter  = array_shift($array);

        $className = 'My\\Service\\' . ucfirst($protocol);
        if (!in_array('My\\Service\\ProtocolInterface',  class_implements($className))) {
            throw new \RuntimeException('错误协议：' . $protocol);
        }

        /** @var $service ProtocolAbstract */
        $service = new $className($serviceName);

        if (strtolower($protocol) == 'serialize' && !empty($adapter)) {
            $service->setAdapter($adapter);
        }

        return $service;
    }
}
