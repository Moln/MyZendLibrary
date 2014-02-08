<?php
namespace My\Payment\Service;
use My\Payment\Data\OrderResult;

/**
 * Yeepay service
 *
 * @author  maomao
 * @version $Id: Yeepay.php 852 2013-04-12 05:39:44Z maomao $
 */
class Yeepay extends ChannelPay
{
    protected static $name = '易宝支付';

    protected $gateway = "https://www.yeepay.com/app-merchant-proxy/node";

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

    private static $cmd = 'Buy';

    protected static $channels
        = [
            'bank'         => [
                'ICBC-NET'     => '工商银行',
                'CMBCHINA-NET' => '招商银行',
                'ABC-NET'      => '农业银行',
                'CCB-NET'      => '建设银行',
                'CCB-PHONE'    => '建设银行WAP',
                'BCCB-NET'     => '北京银行',
                'BOCO-NET'     => '交通银行',
                'CIB-NET'      => '兴业银行',
                'NJCB-NET'     => '南京银行',
                'CMBC-NET'     => '民生银行',
                'CEB-NET'      => '光大银行',
                'BOC-NET'      => '中国银行',
                'PAB-NET'      => '平安银行',
                'HKBEA-NET'    => '东亚银行',
                'NBCB-NET'     => '宁波银行',
                'SDB-NET'      => '深圳发展银行',
                'GDB-NET'      => '广东发展银行',
                'SPDB-NET'     => '上海浦东发展银行',
                'POST-NET'     => '中国邮政',
                'BJRCB-NET'    => '北京农村商业银行',
                'GNXS-NET'     => '广州市农信社',
                '1000000-NET'  => '易宝会员支付',
            ],
            'bankMap'      => [
                'icbc'  => 'ICBC-NET',
                'ccb'   => 'CCB-NET',
                'abc'   => 'ABC-NET',
                'cmb'   => 'CMBCHINA-NET',
                'boc'   => 'BOC-NET',
                'post'  => 'POST-NET',
                'bcom'  => 'BOCO-NET',
                'ceb'   => 'CEB-NET',
                'gdb'   => 'GDB-NET',
                'gzrcc' => 'GNXS-NET',
//            'gzcb' => '广州银行',
//            'shrcc' => '上海农村商业银行',
                'spdb'  => 'SPDB-NET',
                'bjrcb' => 'BJRCB-NET',
                'cmbc'  => 'CMBC-NET',
                'sdb'   => 'SDB-NET',
//            'citic' => '中信银行',
                'pab'   => 'PAB-NET',
//            'shb' => '上海银行',
                'bccb'  => 'BCCB-NET',
//            'hxb' => '华夏银行',
                'nbcb'  => 'NBCB-NET',
                'njcb'  => 'NJCB-NET',
//            'hzb' => '杭州银行',
//            'hsb' => '徽商银行',
//            'czb' => '浙商银行',
//            'cbhb' => '渤海银行',
                'bea'   => 'HKBEA-NET',
                'cib'   => 'CIB-NET',
            ],
            'phoneCard'    => [
                'SZX-NET'     => '神州行充值卡',
                'UNICOM-NET'  => '联通充值卡',
                'TELECOM-NET' => '电信行充值卡',
            ],
            'phoneCardMap' => [
                'szx'     => 'SZX-NET',
                'unicom'  => 'UNICOM-NET',
                'telecom' => 'TELECOM-NET',
            ]
        ];

    public function getRequestParams(OrderResult $order)
    {
        $params = array(
            'p0_Cmd'          => self::$cmd, //支付请求，固定值"Buy" .
            'p1_MerId'        => $this->getOption('sn'),
            'p2_Order'        => $this->getOption('order'),
            'p3_Amt'          => $this->getOption('amount'),
            'p4_Cur'          => $this->getOption('currency'),
            'p5_Pid'          => $this->getOption('productName'),
            'p6_Pcat'         => $this->getOption('category'),
            'p7_Pdesc'        => $this->getOption('describe'),
            'p8_Url'          => $this->getOption('p8_Url'),
            'p9_SAF'          => $this->getOption('p9_SAF'),
            'pa_MP'           => $this->getOption('pa_MP'),
            'pd_FrpId'        => $this->getChannel(),
            'pr_NeedResponse' => $this->getOption('needResponse'),
        );
        if ($this->charset == 'utf-8') {
            $params = array_map(
                function ($value) {
                    return iconv('utf-8', 'gbk', $value);
                }, $params
            );
        }

        $params['hmac'] = $this->getReqHmacString(
            $params['p2_Order'], $params['p3_Amt'], $params['p4_Cur'], $params['p5_Pid'],
            $params['p6_Pcat'], $params['p7_Pdesc'], $params['p8_Url'], $params['pa_MP'],
            $params['pd_FrpId'], $params['pr_NeedResponse']
        );

        return $params;
    }

    /**
     * 签名函数生成签名串
     * @param string $orderId       商户订单号
     * @param string $amount        支付金额
     * @param string $currency      交易币种
     * @param string $name          商品名称
     * @param string $category      商品分类
     * @param string $desc          商品描述
     * @param string $successUrl    商户接收支付成功数据的地址
     * @param string $moreInfo      商户扩展信息
     * @param string $payCode       支付通道编码
     * @param string $needResponse  是否需要应答机制
     *
     * @return string
     */
    protected function getReqHmacString(
        $orderId, $amount, $currency, $name, $category, $desc, $successUrl, $moreInfo, $payCode,
        $needResponse
    )
    {
        // 行签名处理，一定按照文档中标明的签名顺序进行
        $sbOld = self::$cmd // 入业务类型
            . $this->getOption('sn') // 入商户编号
            . $orderId // 入商户订单号
            . $amount // 入支付金额
            . $currency // 入交易币种
            . $name // 入商品名称
            . $category // 入商品分类
            . $desc // 入商品描述
            . $successUrl // 入商户接收支付成功数据的地址
            . $this->getOption('p9_SAF') // 入送货地址标识
            . $moreInfo // 入商户扩展信息
            . $payCode // 入支付通道编码
            . $needResponse // 入是否需要应答机制
        ;

        // 需要配置环境支持iconv，否则中文参数不能正常处理

        //         self::logstr($orderId, $sbOld, HmacMd5($sbOld, self::$_merchantKey));
        return self::hmacMd5($sbOld, $this->key);
    }

    const VALID_ERROR = 'validError';
    const ORDER_ERROR = 'orderError';
    const ORDER_COMPLETE = 'orderComplete';
    const REDIRECT    = 'REDIRECT';

    public function isValidServer()
    {
        $request = $this->getRequest();
        $params = array(
            'r0_Cmd'   => $request->getParam('r0_Cmd'),
            'r1_Code'  => $request->getParam('r1_Code'),
            'r2_TrxId' => $request->getParam('r2_TrxId'),
            'r3_Amt'   => $request->getParam('r3_Amt'),
            'r4_Cur'   => $request->getParam('r4_Cur'),
            'r5_Pid'   => $request->getParam('r5_Pid'),
            'r6_Order' => $request->getParam('r6_Order'),
            'r7_Uid'   => $request->getParam('r7_Uid'),
            'r8_MP'    => $request->getParam('r8_MP'),
            'r9_BType' => $request->getParam('r9_BType'),
        );

//        foreach ($params as $key => $value) {
//            if (empty($value)) {
//                throw new \RuntimeException('缺少键:' . $key);
//            }
//        }

        if (!self::checkHmac($this->getOption('sn'), $this->key, $request->getParam('hmac'), $params)) {
            $this->setError(self::VALID_ERROR);
            return false;
        }

        $this->setOption('order', $request->getParam('r6_Order'));
        $this->setOption('pay_id', $request->getParam('r2_TrxId'));

        $orderResult = $this->loadOrderResult($request->getParam('r6_Order'));
        if (!$orderResult) {
            $this->setError(self::ORDER_ERROR);
            return false;
        } else if ($orderResult->isCompleted()) {
            $this->setError(self::ORDER_COMPLETE);
            return false;
        } else if ($request->getParam('r9_BType') == '1') {
            $this->setError(self::REDIRECT);
            return false;
        }

        return true;
    }

    public function isValidClient()
    {
        $msg = $this->getRequest()->getParam('msg');
        if ($msg != self::REDIRECT) {
            $this->setError($msg);
            return false;
        }
        return true;
    }

    public function serverResponse()
    {
        $request = $this->getRequest();
        if ($request->getParam('r9_BType') == '2') {
            if ($this->getError() == self::ORDER_COMPLETE) {
                echo 'success';
            } else {
                echo $this->getError() ? 'fail' : 'success';
            }
        } else if ($request->getParam('r9_BType') == '1') {
            header('Location: ' . $this->getOption('redirect') . '?msg=' . $this->getError());
        }
        exit;
    }

    /**
     * 取得返回串中的所有参数
     * @param string $r0_Cmd
     * @param string $r1_Code
     * @param string $r2_TrxId
     * @param string $r3_Amt
     * @param string $r4_Cur
     * @param string $r5_Pid
     * @param string $r6_Order
     * @param string $r7_Uid
     * @param string $r8_MP
     * @param string $r9_BType
     * @param string $hmac
     *
     * @return null
     */
    public static function getCallBackValue(
        &$r0_Cmd, &$r1_Code, &$r2_TrxId, &$r3_Amt, &$r4_Cur, &$r5_Pid, &$r6_Order, &$r7_Uid,
        &$r8_MP, &$r9_BType, &$hmac
    )
    {
        $r0_Cmd   = $_REQUEST['r0_Cmd'];
        $r1_Code  = $_REQUEST['r1_Code'];
        $r2_TrxId = $_REQUEST['r2_TrxId'];
        $r3_Amt   = $_REQUEST['r3_Amt'];
        $r4_Cur   = $_REQUEST['r4_Cur'];
        $r5_Pid   = $_REQUEST['r5_Pid'];
        $r6_Order = $_REQUEST['r6_Order'];
        $r7_Uid   = $_REQUEST['r7_Uid'];
        $r8_MP    = $_REQUEST['r8_MP'];
        $r9_BType = $_REQUEST['r9_BType'];
        $hmac     = $_REQUEST['hmac'];
        return null;
    }

    /**
     *
     * @param string $merchantId
     * @param string $key
     * @param string $hmac
     * @param array  $params
     *  keys:
     *
     * @return boolean
     */
    public static function checkHmac($merchantId, $key, $hmac, $params)
    {
        return $hmac == self::hmacMd5($merchantId . implode($params), $key);
    }

    public static function hmacMd5($data, $key)
    {
        // RFC 2104 HMAC implementation for php.
        // Creates an md5 HMAC.
        // Eliminates the need to install mhash to compute a HMAC
        // Hacked by Lance Rushing(NOTE: Hacked means written)
        // 需要配置环境支持iconv，否则中文参数不能正常处理
        $key  = iconv("gbk", "UTF-8", $key);
        $data = iconv("gbk", "UTF-8", $data);

        $b = 64; // byte length for md5
        if (strlen($key) > $b) {
            $key = pack("H*", md5($key));
        }
        $key    = str_pad($key, $b, chr(0x00));
        $ipad   = str_pad('', $b, chr(0x36));
        $opad   = str_pad('', $b, chr(0x5c));
        $k_ipad = $key ^ $ipad;
        $k_opad = $key ^ $opad;
        return md5($k_opad . pack("H*", md5($k_ipad . $data)));
    }
}