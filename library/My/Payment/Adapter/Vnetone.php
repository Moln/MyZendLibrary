<?php
namespace My\Payment\Adapter;

use My\Payment\Payment;
/**
 * Vnetone.php
 * @author   maomao
 * @DateTime 12-7-4 下午6:19
 * @version  $Id: Vnetone.php 790 2013-03-15 08:56:56Z maomao $
 */
class Vnetone implements AdapterInterface
{
    private $service;

    public function __construct($options = [])
    {
        $this->service = Payment::factoryService('vnetone');
    }

    public function getService()
    {
        return $this->service;
    }
}