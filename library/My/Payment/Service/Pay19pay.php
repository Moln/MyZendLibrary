<?php
namespace My\Payment\Service;

use My\Payment\Data\OrderResult;

class Pay19pay extends ChannelPay
{
    protected static $name = '19Pay';

    protected $gateway = 'https://pay.19pay.com/page/bussOrder.do', $bankGateway = 'https://pay.19pay.com/page/bussOrder.do', $phoneCardGateWay = 'https://exchange.19ego.cn/page/bussOrder.do';

    protected $options
        = array(
            'version_id'   => '2.00',
            'merchant_id'  => null,
            'order_date'   => null,
            'order_id'     => null,
            'amount'       => null,
            'currency'     => 'RMB',
            'returl'       => null,
            'pm_id'        => null,
            'pc_id'        => null,
            'merchant_key' => null,
            'order_pdesc'  => null,
            'order_pname'  => null,
            'user_mobile'  => null,
            'user_phone'   => null,
            'user_name'    => null,
            'user_email'   => null,
            'verifystring' => null
        );

    protected $keyMapper
        = array(
            'sn'      => 'merchant_id',
            'order'   => 'order_id',
            'bank'    => 'pc_id',
            'account' => 'user_name',
            'pay_id'  => 'pay_sq',
        );

    protected static $channels
        = [
            'bank'         => [
                'BC00060001' => '招商银行(借记卡)',
                'BC00060007' => '北京银行(借记卡)',
                'BC00010002' => '深圳发展银行',
//                'BC00010003' => '中国工商银行',
                'BC00010004' => '中国民生银行',
                'BC00010005' => '中国建设银行',
                'BC00010008' => '中国农业银行',
                'BC00010009' => '中国交通银行',
                'BC00010011' => '浦东发展银行',
                'BC00010012' => '华夏银行',
                'BC00010015' => '中国光大银行',
                'BC00010016' => '中国银行',
                'BC00010024' => '中国邮政储蓄银行',
                'BC00060010' => '中信银行(借记卡)',

//                'BC00010008' => '农业银行',
//                'BC00010003' => '工商银行',
//                'BC00060001' => '招商银行(借记卡)',
//                'BC00010005' => '建设银行',
//                'BC00010009' => '交通银行',
//                'BC00010007' => '北京银行',
//                'BC00010004' => '民生银行',
//                'BC00010002' => '深圳发展银行',
//                'BC00010010' => '中信银行',
//                'BC00010011' => '浦东发展银行',
//                'BC00010013' => '兴业银行',
//                'BC00010006' => '邮政储蓄网汇通',
//                'BC00010015' => '光大银行',
//                'BC00010016' => '中国银行',
//                'BC00010017' => '广东发展银行',
//                'BC00010018' => '平安银行',
//                'BC00010019' => '上海银行',
//                'BC00010020' => '杭州银行',
//                'BC00010021' => '北京农村商业银行',
//                'BC00010022' => '浙商银行',
            ],
            'bankMap'      => [
//                'icbc'  => 'BC00010003',
                'ccb'   => 'BC00010005',
                'abc'   => 'BC00010008',
                'cmb'   => 'BC00060001',
                'boc'   => 'BC00010016',
                'post'  => 'BC00010024',
                'bcom'  => 'BC00010009',
                'ceb'   => 'BC00010015',
//                'gdb'   => 'BC00010017',
                'spdb'  => 'BC00010011',
//                'bjrcb' => 'BC00010021',
                'cmbc'  => 'BC00010004',
                'sdb'   => 'BC00010002',
                'citic' => 'BC00060010',
//                'pab'   => 'BC00010018',
//                'shb'   => 'BC00010019',
                'bccb'  => 'BC00060007',
//                'hzb'   => 'BC00010020',
//                'cib'   => 'BC00010013',
//                'czb'   => 'BC00010022',
                'hxb'   => 'BC00010012',
            ],
            'phoneCard'    => [
                'CMJFK00010001' => '全国移动充值卡',
                'CMJFK00010112' => '浙江移动缴费券',
                'CMJFK00010111' => '江苏移动充值卡',
                'CMJFK00010014' => '福建移动呱呱通充值卡',
                'CMJFK00010102' => '辽宁移动电话交费卡',
                'LTJFK00020000' => '联通充值卡',
                'DXJFK00010001' => '电信行充值卡',
            ],
            'phoneCardMap' => [
                'szx'     => 'CMJFK00010001',
                'unicom'  => 'LTJFK00020000',
                'telecom' => 'DXJFK00010001',
            ]
        ];

    public function getPmId($pcId)
    {
        $this->gateway = $pcId{0} == 'B' ? $this->bankGateway : $this->phoneCardGateWay;
        if ($pcId{0} == 'B') { // BC开头银行支付
            return 'BC';
        } else if ($pcId{0} == 'D') { //DXJFK 开头电信充值卡
            return 'DXJFK';
        } else if ($pcId{0} == 'L') { //LTJFK 开头联通充值卡
            return 'LTJFK';
        } else if ($pcId{0} == 'C') { //CMJFK 开头移动充值卡
            return 'CMJFK';
        } else {
            throw new \Exception('未知通道:' . $pcId);
        }
    }

    /*
    *
    * @see My\Payment.AbstractService::getRequestParams()
    */
    public function getRequestParams(OrderResult $order)
    {
        $params          = [
            'order_date'  => date('Ymd'),
            'pc_id'       => $this->getChannel(),
            'version_id'  => $this->getOption('version_id'),
            'currency'    => $this->getOption('currency'),
            'merchant_id' => $this->sn,
            'notify_url'  => $this->getOption('notify_url'),
            'order_id'    => $order->getOrderId(),
            'order_pdesc' => $this->getOption('order_pdesc'),
            'order_pname' => $this->getOption('order_pname'),
            'returl'      => $this->getOption('returl'),
            'user_name'   => $this->getOption('user_name'),
            'amount'      => $this->getOption('amount'),
        ];
        $params['pm_id'] = $this->getPmId($params['pc_id']);
        $params          = array_filter($params);

        foreach ($params as &$val) {
            $val = iconv('utf-8', 'gbk', $val);
        }

        $oriStr = 'version_id=' . $params['version_id'] . '&merchant_id=' . $params['merchant_id']
            . '&order_date=' . $params['order_date'] . '&order_id=' . $params['order_id']
            . '&amount=' . $params['amount'] . '&currency=' . $params['currency'] . '&returl='
            . $params['returl'] . '&pm_id=' . $params['pm_id'] . '&pc_id=' . $params['pc_id']
            . '&merchant_key=' . $this->key;

        $params['verifystring'] = md5($oriStr);

        return $params;
    }

    const PARAMS_ERROR    = 'paramsError';
    const VALID_ERROR     = 'validError';
    const SYSTEM_ERROR    = 'systemError';
    const ORDER_ERROR     = 'orderError';
    const ORDER_COMPLETED = 'orderCompleted';

    private function isValidParams()
    {
        $params = [
            'version_id',
            'merchant_id',
            'verifystring',
            'order_id',
            'result',
            'amount',
            'currency',
            'pay_sq',
            'pay_date',
            'pm_id',
            'pc_id'
        ];

        foreach ($params as $key) {
            if (empty($_REQUEST[$key])) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return bool
     */
    public function isValidServer()
    {

        if (!$this->isValidParams()) {
            $this->setError(self::PARAMS_ERROR);
            return false;
        }

        $versionId    = $_REQUEST["version_id"];
        $merchantId   = $_REQUEST["merchant_id"];
        $verifyString = $_REQUEST["verifystring"];
        $orderDate    = $_REQUEST["order_date"];
        $orderId      = $_REQUEST["order_id"];
        $result       = $_REQUEST["result"];
        $amount       = $_REQUEST["amount"];
        $currency     = $_REQUEST["currency"];
        $paySq        = $_REQUEST["pay_sq"];
        $payDate      = $_REQUEST["pay_date"];
        $pmId         = $_REQUEST["pm_id"];
        $pcId         = $_REQUEST["pc_id"];

        //验证串的原串
        $ori = "version_id=" . $versionId . "&merchant_id=" . $merchantId . "&order_id=" . $orderId
            . "&result=" . $result . "&order_date=" . $orderDate . "&amount=" . $amount
            . "&currency=" . $currency . "&pay_sq=" . $paySq . "&pay_date=" . $payDate . "&pc_id="
            . $pcId . "&merchant_key=" . $this->key;

        if (md5($ori) != $verifyString) {
            $this->setError(self::VALID_ERROR);
            return false;
        }

        $orderResult = $this->loadOrderResult($orderId);
        if (!$orderResult) {
            $this->setError(self::ORDER_ERROR);
            return false;
        }
        if ($orderResult->isCompleted()) {
            $this->setError(self::ORDER_COMPLETED);
            return false;
        }

        $this->mergeOptions($_REQUEST);

        return true;
    }

    public function serverResponse()
    {
        $this->logger($this->getError());
        if (!$this->getError()) {
            echo 'Y';
        } else if (self::ORDER_COMPLETED == $this->getError()) {
            echo 'Y';
        } else {
            echo 'N';
        }
    }

    public function isValidClient()
    {
        if (!$this->isValidParams()) {
            $this->setError(self::PARAMS_ERROR);
            return false;
        }

        $versionId    = $_REQUEST["version_id"];
        $merchantId   = $_REQUEST["merchant_id"];
        $verifyString = $_REQUEST["verifystring"];
        $orderDate    = $_REQUEST["order_date"];
        $orderId      = $_REQUEST["order_id"];
        $amount       = $_REQUEST["amount"];
        $currency     = $_REQUEST["currency"];
        $paySq        = $_REQUEST["pay_sq"];
        $payDate      = $_REQUEST["pay_date"];
        $pmId         = $_REQUEST["pm_id"];
        $pcId         = $_REQUEST["pc_id"];
        $result       = $_REQUEST["result"];

        //注意:为了安全,先验证数据订单的合法性!
        $ori
            =
            "version_id=" . $versionId . "&merchant_id=" . $merchantId . "&order_date=" . $orderDate
                . "&order_id=" . $orderId . "&amount=" . $amount . "&currency=" . $currency
                . "&pay_sq=" . $paySq . "&pay_date=" . $payDate . "&pc_id=" . $pcId . "&result="
                . $result . "&merchant_key=" . $this->key;

        if (md5($ori) != $verifyString) {
            $this->getError(self::VALID_ERROR);
            return false;
        }

        return true;
    }
}