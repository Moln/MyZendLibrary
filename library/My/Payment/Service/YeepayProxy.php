<?php
namespace My\Payment\Service;
use My\Payment\Data\OrderResult;
use My\Payment\MessageException;

/**
 * Yeepay service
 *
 * @author  maomao
 * @version $Id: YeepayProxy.php 790 2013-03-15 08:56:56Z maomao $
 */
class YeepayProxy extends ChannelPay
{
    protected static $name = '易宝支付(代理)';

    protected $gateway = "https://www.yeepay.com/app-merchant-proxy/command.action";

    protected $options
        = [
            'currency'     => 'CNY', //交易币种,固定值"CNY".
            'amount'       => null,
            'productName'  => null, //商品名称
            'order'        => null,
            'category'     => null,
            'describe'     => null,
            'p8_Url'       => null,
            'p9_SAF'       => '0',
            'pa_MP'        => null,
            'payCode'      => null,
            'needResponse' => 1,
        ];

    protected $keyMapper
        = [
            'sn'     => 'p1_MerId',
            'pay_id' => 'r2_TrxId',
            'bank'   => 'pd_FrpId',
        ];

    private static $cmd = 'ChargeCardDirect';

    protected static $channels
        = [
            'bank' => [
                'JUNNET'   => '骏网一卡通',
                'SNDACARD' => '盛大卡',
                'SZX'      => '神州行',
                'ZHENGTU'  => '征途卡',
                'QQCARD'   => 'Q币卡',
                'UNICOM'   => '联通卡',
                'JIUYOU'   => '久游卡',
                'YPCARD'   => '易宝e卡通',
                'NETEASE'  => '网易卡',
                'WANMEI'   => '完美卡',
                'SOHU'     => '搜狐卡',
                'TELECOM'  => '电信卡',
                'ZONGYOU'  => '纵游一卡通',
                'TIANXIA'  => '天下一卡通',
                'TIANHONG' => '天宏一卡通',
            ],
        ];

    private $errors
        = array(
            '-1'   => '签名较验失败或未知错误',
            '2'    => '卡密成功处理过或者提交卡号过于频繁',
            '5'    => '卡数量过多，目前最多支持10张卡',
            '11'   => '订单号重复',
            '66'   => '支付金额有误',
            '95'   => '支付方式未开通',
            '112'  => '业务状态不可用，未开通此类卡业务',
            '8001' => '卡面额组填写错误',
            '8002' => '卡号密码为空或者数量不相等',
        );

    private $codeStatus
        = array(
            '0'     => '销卡成功，订单成功',
            '1'     => '销卡成功，订单失败',
            '7'     => '卡号卡密或卡面额不符合规则',
            '1002'  => '本张卡密您提交过于频繁，请您稍后再试',
            '1003'  => '不支持的卡类型',
            '1004'  => '密码错误或充值卡无效',
            '1006'  => '充值卡无效',
            '1007'  => '卡内余额不足',
            '1008'  => '余额卡过期',
            '1010'  => '此卡正在处理中',
            '10000' => '未知错误',
            '2005'  => '此卡已使用',
            '2006'  => '卡密在系统处理中',
            '2007'  => '该卡为假卡',
            '2008'  => '该卡种正在维护',
            '2009'  => '浙江省移动维护',
            '2010'  => '江苏省移动维护',
            '2011'  => '福建省移动维护',
            '2012'  => '辽宁省移动维护'
        );

    /**
     * 签名函数生成签名串
     * @param array $params
     *
     * @return string
     */
    protected function getReqHmacString(array $params)
    {
        return Yeepay::hmacMd5(implode($params), $this->key);
    }

    const VALID_ERROR = 'validError';
    const ORDER_ERROR = 'orderError';
    const CODE_ERROR  = 'codeError';

    public function isValidServer()
    {
        $params = array(
            'r0_Cmd'           => $_REQUEST['r0_Cmd'],
            'r1_Code'          => $_REQUEST['r1_Code'],
            'p1_MerId'         => $_REQUEST['p1_MerId'],
            'p2_Order'         => $_REQUEST['p2_Order'],
            'p3_Amt'           => $_REQUEST['p3_Amt'],
            'p4_FrpId'         => $_REQUEST['p4_FrpId'],
            'p5_CardNo'        => $_REQUEST['p5_CardNo'],
            'p6_confirmAmount' => $_REQUEST['p6_confirmAmount'],
            'p7_realAmount'    => $_REQUEST['p7_realAmount'],
            'p8_cardStatus'    => $_REQUEST['p8_cardStatus'],
            'p9_MP'            => $_REQUEST['p9_MP'],
            'pb_BalanceAmt'    => $_REQUEST['pb_BalanceAmt'],
            'pc_BalanceAct'    => $_REQUEST['pc_BalanceAct'],
        );

        if (!Yeepay::checkHmac($this->getOption('sn'), $this->key, $_REQUEST['hmac'], $params)) {
            $this->setError(self::VALID_ERROR);
            return false;
        }

        $this->setOption('order', $params['p2_Order']);
        $this->setOption('pay_id', $params['p2_Order']);

        $orderResult = $this->loadOrderResult($params['p2_Order']);
        if (!$orderResult || $orderResult->isCompleted()) {
            $this->setError(self::ORDER_ERROR);
            return false;
        }

        if ($params['r1_Code'] != '1') {
            $this->setError(self::CODE_ERROR);
            if ($orderResult) {
                $orderResult->paymentFailed(
                    $orderResult->getDescription() . "\n" . "卡状态:" . $params['p8_cardStatus'] . "\n"
                        . ($params['pb_BalanceAmt'] ? "支付余额:" . $params['pb_BalanceAmt'] : "")
                        . ($params['pc_BalanceAct'] ? "余额卡号:" . $params['pc_BalanceAct'] : "")
                );
            }
            return false;
        }

        $orderResult->setDescription(
            $orderResult->getDescription() . "\n" . "卡状态:" . $params['p8_cardStatus'] . "\n"
                . ($params['pb_BalanceAmt'] ? "支付余额:" . $params['pb_BalanceAmt'] : "")
                . ($params['pc_BalanceAct'] ? "余额卡号:" . $params['pc_BalanceAct'] : "")
        );

        return true;
    }

    public function serverResponse()
    {
        echo
        $this->getError() == self::VALID_ERROR || $this->getError() == self::ORDER_ERROR ? 'failed'
            : 'success';
        exit;
    }

    public function redirectPay(OrderResult $order)
    {
        $cardAmt = $this->getOption('cardAmt') ? self::arrToString(
            (array)$this->getOption('cardAmt')
        ) : $order->getAmount();
        $params  = array(
            'p0_Cmd'          => self::$cmd, //支付请求，固定值"Buy" .
            'p1_MerId'        => $this->getOption('sn'),
            'p2_Order'        => $order->getOrderId(),
            'p3_Amt'          => $order->getAmount(),
            'p4_verifyAmt'    => $this->getOption('p4_verifyAmt'),
            'p5_Pid'          => $this->getOption('p5_Pid'),
            'p6_Pcat'         => $this->getOption('p6_Pcat'),
            'p7_Pdesc'        => $this->getOption('p7_Pdesc'),
            'p8_Url'          => $this->getOption('p8_Url'),
            'pa_MP'           => $this->getOption('pa_MP'),
            'pa7_cardAmt'     => $cardAmt,
            'pa8_cardNo'      => self::arrToString((array)$this->getOption('cardNo')),
            'pa9_cardPwd'     => self::arrToString((array)$this->getOption('cardPwd')),
            'pd_FrpId'        => $this->getChannel(),
            'pr_NeedResponse' => $this->getOption('pr_NeedResponse'),
            'pz_userId'       => $order->getAccount(),
        );
        if ($this->charset == 'utf-8') {
            $params = array_map(
                function ($value) {
                    return iconv('utf-8', 'gbk', $value);
                }, $params
            );
        }

        $params['hmac'] = $this->getReqHmacString($params);

        try {
            $client = new \Zend_Http_Client($this->gateway);
            $client->setParameterPost($params);
            $response = $client->request('POST');
            if ($response->getStatus() !== 200) {
                throw new MessageException("接口服务器连接失败");
            }

            $result = array();
            foreach (explode("\n", trim($response->getBody())) as $item) {
                list($key, $value) = explode("=", $item);
                $result[$key] = $value;
            }
            if ($result['r1_Code'] == '1') {
                header('Location: ' . $this->getOption('redirect'));
                exit;
            } else {
                throw new MessageException(
                    "错误({$result['r1_Code']}):" . $this->errors[$result['r1_Code']]);
            }
        } catch (\Zend_Http_Exception $e) {
            throw new MessageException('接口链接失败:' . $e->getMessage());
        }
    }

    public static function arrToString($arr, $separators = ',')
    {
        $returnString = "";
        foreach ($arr as $value) {
            $returnString = $returnString . $value . $separators;
        }
        return substr($returnString, 0, strlen($returnString) - strlen($separators));
    }

    /**
     * 跳转时传递参数
     *
     * @param \My\Payment\Data\OrderResult $order
     *
     * @return array
     */
    public function getRequestParams(OrderResult $order)
    {
    }

    public function isValidClient()
    {
        $this->setMessage('提交成功!');
        return true;
    }
}