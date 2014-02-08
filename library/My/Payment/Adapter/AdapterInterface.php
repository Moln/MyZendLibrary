<?php
namespace My\Payment\Adapter;
/**
 * Adapter.php
 * @author   maomao
 * @DateTime 12-5-30 下午5:32
 * @version  $Id: AdapterInterface.php 790 2013-03-15 08:56:56Z maomao $
 */
interface AdapterInterface
{
    /**
     * @abstract
     * @return \My\Payment\Service\ServiceAbstract
     */
    public function getService();
}
