<?php
namespace My\Product\Node;
use My\Payment\Data;

/**
 * 充值接口
 * @package My\Product\Node
 * @author Xiemaomao
 * @version $Id: PaymentInterface.php 790 2013-03-15 08:56:56Z maomao $
 */
interface PaymentInterface
{
    /**
     * @param string                       $account
     * @param int                          $gold
     * @param \My\Payment\Data\Consume     $consume
     * @param \My\Payment\Data\OrderResult $order
     * @param null                         $more
     *
     * @return bool
     */
    public function addGold(
        $account, $gold, Data\Consume $consume, Data\OrderResult $order = null, $more = null
    );
}
