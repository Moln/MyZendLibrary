<?php
namespace My\Payment\Service;
use My\Payment\Data\OrderResult;

/**
 * Untx.php
 * @author   maomao
 * @DateTime 12-7-26 下午1:24
 * @version  $Id: Untx.php 1263 2013-09-17 06:24:37Z maomao $
 */
class Untx extends ChannelPay
{
    protected static $name = '新宽联';
    protected $gateway = 'http://shengg.pm.91ka.com/pay/interface_index.php';
    private $queryApi = 'http://shengg.if.91ka.com/if/interface/auto_interface_third_query_order.php';

    protected $options
        = [
            'orderid'     => null,
            'origin'      => null,
            'chargemoney' => null,
            'channelid'   => null,
            'paytype'     => null,
            'bankcode'    => null,
            'cardno'      => null,
            'cardpwd'     => null,
            'cardamount'  => null,
            'fronturl'    => null,
            'bgurl'       => null,
            'version'     => '2.0.1',
            'ext1'        => null,
            'ext2'        => null,
        ];

    protected $keyMapper
        = array(
            'sn'     => 'origin',
            'order'  => 'orderid',
            'amount' => 'chargemoney',
            'pay_id' => 'systemno',
        );


    protected static $channels
        = [
            'bank'    => [
                'BOCB2C' => '中国银行',
                '1001'   => '招商银行',
                '1002'   => '工商银行',
                '1003'   => '建设银行',
                '1004'   => '浦发银行',
                '1005'   => '农业银行',
                '1006'   => '民生银行',
                '1008'   => '深圳发展银行',
                '1009'   => '兴业银行',
                '1020'   => '交通银行',
                '1022'   => '光大银行',
                '1032'   => '北京银行',
                'GDB'    => '广发银行',
                'BILL'   => '快钱支付',
                'ALIPAY' => '支付宝支付',
                'YP'     => '易宝支付',
                'PSBC'   => '邮政银行',
                'TENPAY' => '财付通支付',
                'CMPAY'  => '中国移动手机支付',
                'JUNNET' => '骏网一卡通',
            ],
            'bankMap' => [
                'icbc'  => '1002',
                'ccb'   => '1003',
                'abc'   => '1005',
                'cmb'   => '1001',
                'boc'   => 'BOCB2C',
                'ceb'   => '1022',
                'gdb'   => 'GDB',
                'spdb'  => '1004',
                'cmbc'  => '1006',
                'sdb'   => '1008',
                'bccb'  => '1032',
                'cib'   => '1009',
                'bcom'  => '1020',
            ],
            'phoneCard' => [
                'szx'     => '神州行',
                'unicom'  => '联通卡',
                'telecom' => '电信卡',
            ],
            'phoneCardMap' => [
                'szx'     => 'szx',
                'unicom'  => 'unicom',
                'telecom' => 'telecom',
            ],
        ];

    const DENY_IP                = 'DENY_IP';
    const ERROR_SIGN             = 'ERROR_SIGN';
    const ERROR_ORDER_EXISTS     = 'ERROR_ORDER_EXISTS';
    const ERROR_ORDER_NOT_EXISTS = 'ERROR_ORDER_NOT_EXISTS';
    const ERROR_ORDER_COMPLETED  = 'ERROR_ORDER_COMPLETED';
    const ERROR_RESULT           = 'ERROR_RESULT';

    protected $messages
        = [
            self::ERROR_SIGN             => '密钥错误',
            self::ERROR_ORDER_EXISTS     => '订单已存在',
            self::ERROR_ORDER_NOT_EXISTS => '订单不存在',
            self::ERROR_ORDER_COMPLETED  => '订单已支付',
            self::ERROR_RESULT           => '支付失败',
        ];

    /**
     * 跳转时传递参数
     * @param \My\Payment\Data\OrderResult $order
     *
     * @return array
     */
    public function getRequestParams(OrderResult $order)
    {
        $chanelId = $this->getChannelId();
        $params = [
            'orderid'     => $this->getOption('orderid'),
            'origin'      => $this->sn,
            'chargemoney' => $this->getOption('chargemoney'),
            'channelid'   => $chanelId,
            'paytype'     => $chanelId == '1' ? '' : $this->getOption('paytype'),
            'bankcode'    => $chanelId == '1' ? $this->getChannel() : '',
            'cardno'      => $this->getOption('paytype') == '2' ? '':'', //todo encode
            'cardpwd'     => $this->getOption('paytype') == '2' ? '':'', //todo encode
            'cardamount'  => $this->getOption('cardamount'),
            'fronturl'    => $this->getOption('fronturl'),
            'bgurl'       => $this->getOption('bgurl'),
            'version'     => '2.0.1',
            'ext1'        => $this->getOption('ext1'),
            'ext2'        => $this->getOption('ext2'),
        ];

        $params['validate'] = $this->getSign($params);
        return $params;
    }

    public function redirectPay(OrderResult $order)
    {
        $params = $this->getRequestParams($order);
//        $params = array_filter($params);

        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
        echo '<form action="' . $this->gateway . '" method="post">';
        foreach ($params as $name => $value) {
            echo '<input type="hidden" name="' . $name . '" value="' . $value . '" />';
        }

        echo'<noscript><input type="submit" value="提交">'
            . '您的浏览器不支持或未开启JAVASCRIPT，请手动点击“提交”按钮</noscript>';
        echo '</form>';
        echo '<script>document.getElementsByTagName("form")[0].submit()</script>';
        exit;
    }

    /**
     * 文档:异步通知
     * @return bool
     */
    public function isValidServer()
    {
        $request = $this->getRequest();
        $params  = [
            'orderid'     => $request->getParam('orderid'),
            'chargemoney' => $request->getParam('chargemoney'),
            'systemno'    => $request->getParam('systemno'),
            'channelid'   => $request->getParam('channelid'),
            'status'      => $request->getParam('status'),
            'ext1'        => $request->getParam('ext1'),
            'ext2'        => $request->getParam('ext2'),
        ];
        $sign = $request->getParam('validate');
        if ($this->getSign($params) != $sign) {
            $this->setError(self::ERROR_SIGN);
            return false;
        }

        $orderResult = $this->loadOrderResult($params['orderid']);
        if (!$orderResult) {
            $this->setError(self::ERROR_ORDER_NOT_EXISTS);
            return false;
        }
        if ($orderResult->isCompleted()) {
            $this->setError(self::ERROR_ORDER_COMPLETED);
            return false;
        }

        if ($params['status'] != '1') {
            $this->setError(self::ERROR_RESULT);
            return false;
        }

        $this->setOption('order', $params['orderid']);
        $this->setOption('pay_id', $params['systemno']);

        return true;
    }

    /**
     * 文档:同步通知
     * @return bool
     */
    public function isValidClient()
    {
        $request = $this->getRequest();
        $params  = [
            'orderid'   => $request->getParam('orderid'),
            'channelid' => $request->getParam('channelid'),
            'systemno'  => $request->getParam('systemno'),
            'payprice'  => $request->getParam('payprice'),
            'status'    => $request->getParam('status'),
            'ext1'      => $request->getParam('ext1'),
            'ext2'      => $request->getParam('ext2'),
        ];
        $sign = $request->getParam('validate');
        if ($this->getSign($params) != $sign) {
            $this->setError(self::ERROR_SIGN);
            return false;
        }

        $orderResult = $this->loadOrderResult($params['orderid']);
        if (!$orderResult) {
            $this->setError(self::ERROR_ORDER_NOT_EXISTS);
            return false;
        }

        if ($params['status'] != '1') {
            $this->setError(self::ERROR_RESULT);
            return false; //
        }

        $this->setOption('order', $params['orderid']);
        $this->setOption('pay_id', $params['systemno']);

        return true;
    }

    public function serverResponse()
    {
        if (!$this->getError()) {
            echo '1';
        } else if ($this->getError() == self::ERROR_SIGN) {
            echo '2';
        } else if ($this->getError() == self::ERROR_ORDER_NOT_EXISTS) {
            echo '3';
        } else if ($this->getError() == self::ERROR_ORDER_COMPLETED) {
            echo '4';
        } else {
            echo '0';
        }
        exit;
    }

    private function getSign(array $params)
    {
        $str = '';
        foreach ($params as $key => $val) {
            if ($key == 'version') {
                continue;
            }
            $str .= '&' . $key . '=' . $val;
        }
        $str .= $this->key;
        return substr(md5(ltrim($str, '&')), 8, 16);
    }

    private function getChannelId()
    {
        switch ($this->getChannel()) {
            case 'unicom':
                return 4;
            case 'telecom':
                return 3;
            case 'szx':
                return 2;
            default:
                return 1;
        }
    }
}
