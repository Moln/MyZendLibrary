<?php
namespace My\Service;

/**
 * ProtocolAbstract.php
 * @author   maomao
 * @DateTime 12-7-23 下午5:18
 * @version  $Id: ProtocolAbstract.php 1218 2013-08-08 09:51:29Z maomao $
 */
abstract class ProtocolAbstract implements ProtocolInterface
{
    protected $service;
    protected $params;
    protected $method;

    public function __construct($serviceName)
    {
        $this->service = new $serviceName;
        $this->params = $_REQUEST;
    }

    /**
     * @param string $method
     *
     * @return ProtocolAbstract
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    public function callMethod()
    {
        $reflect = new \ReflectionClass($this->service);
        $refMethod = $reflect->getMethod($this->method);

        $realParams = array();
        foreach ($refMethod->getParameters() as $param) {
            if (isset($this->params[$param->getName()])) {
                $realParams[] = $this->params[$param->getName()];
            } else if ($param->isDefaultValueAvailable()) {
                $realParams[] = $param->getDefaultValue();
            } else {
                throw new \InvalidArgumentException('Unknown param:' . $param->getName());
            }
        }

        return call_user_func_array([$this->service, $this->method], $realParams);
    }
}