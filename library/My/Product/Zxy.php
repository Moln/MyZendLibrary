<?php
namespace My\Product;
use My\Stdlib\Fragment\Instance;

/**
 * 醉西游
 * Class Zxy
 * @package My\Product
 * @author Xiemaomao
 * @version $Id: Zxy.php 1275 2014-01-23 23:10:26Z maomao $
 */
class Zxy extends Product
{
    use Instance;
    protected $areaNodeClass = 'ZxyAreaNode';

    protected $serverKey, $payKey;

    public function setServerKey($key)
    {
        $this->serverKey = $key;
        return $this;
    }

    public function getServerKey()
    {
        return $this->serverKey;
    }

    public function setPayKey($payKey)
    {
        $this->payKey = $payKey;
        return $this;
    }

    public function getPayKey()
    {
        return $this->payKey;
    }

}
