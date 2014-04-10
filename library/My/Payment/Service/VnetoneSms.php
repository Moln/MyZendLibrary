<?php
/**
 * platform VnetoneSms.php
 * @DateTime 13-3-18 下午1:54
 */

namespace My\Payment\Service;

use My\Payment\Data\OrderResult;

/**
 * 盈华讯方短信支付
 * Class VnetoneSms
 * @package My\Payment\Service
 * @author Xiemaomao
 * @version $Id: VnetoneSms.php 1334 2014-03-18 18:10:38Z maomao $
 */
class VnetoneSms extends ChannelPay
{
    protected static $name = '盈华讯方-短信';
    protected $gateway = 'http://ydzf.vnetone.com/Default_mo.aspx';

    const ORDER_NOT_FOUND   = 'ORDER_NOT_FOUND';
    const ORDER_COMPLETED   = 'ORDER_COMPLETED';
    const VALID_ERROR       = 'VALID_ERROR';
    const DENY_IP           = 'DENY_IP';

    /**
     * 跳转时传递参数
     *
     * @param \My\Payment\Data\OrderResult $order
     *
     * @return array
     */
    public function getRequestParams(OrderResult $order)
    {

        $params = array(
            'sp'    => $this->sn,
            'od'    => $order->getOrderId(),
            'mz'    => $order->getAmount(),
            'spzdy' => $order->getIp(),
            'uid'   => $order->getAccount(),
            'spreq' => $this->getOption('spreq'),
            'spsuc' => $this->getOption('spsuc'),
        );


        $params['md5'] = strtoupper(md5(
            $params['sp'] . $params['od'] . $this->getKey() . $params['mz'] . $params['spreq']
                . $params['spsuc']
        ));

        return $params;
    }


    /**
     * @return bool
     */
    public function isValidServer()
    {
        //IP
        if (!$this->isAllowIp()) {
            $this->setError(self::DENY_IP);
            return false;
        }

        $rq = $this->getRequest();
        $params = array(
            'oid' => $rq->getParam('oid'),
            'sporder' => $rq->getParam('sporder'),
            'spid' => $rq->getParam('spid'),
            'mz' => $rq->getParam('mz'),
        );

        //校验
        $sign = md5(implode($params) . $this->getKey());
        if ($sign != strtolower($rq->getParam('md5'))) {
            $this->setError(self::VALID_ERROR);
            return false;
        }

        //订单验证
        $orderResult = $this->loadOrderResult($params['sporder']);
        if (!$orderResult) {
            $this->setError(self::ORDER_NOT_FOUND);
            return false;
        }
        if ($orderResult->isCompleted()) {
            $this->setError(self::ORDER_COMPLETED);
            return false;
        }

        $this->setOption('order', $params['sporder']);
        $this->setOption('pay_id', $params['oid']);

        return true;
    }

    public function serverResponse()
    {
        if (!$this->getError() || $this->getError() == self::ORDER_COMPLETED) {
            echo 'okydzf';
        } else if ($this->getError()) {
            echo 'failydzf';
            $this->logger($this->getError(), $_REQUEST);
        }
        exit;
    }
}