<?php
namespace My\Payment\Data;

/**
 * 消息接口
 * @author Xiemaomao
 * @DateTime 12-9-15 上午11:03
 * @version $Id: Consume.php 790 2013-03-15 08:56:56Z maomao $
 */
interface Consume
{
    public function getId();
    public function getUserId();
    public function getGold();
    public function getOrderId();
    public function getTime();
}
