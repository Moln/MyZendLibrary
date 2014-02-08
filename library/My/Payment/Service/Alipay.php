<?php
namespace My\Payment\Service;
use My\Payment\Data\OrderResult;

/**
 * 类名：AlipayService
 * 功能：支付宝各接口构造类
 * 详细：构造支付宝各接口请求参数
 * 版本：3.2
 * 日期：2011-03-25
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，
 * 按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。
 */
class Alipay extends ChannelPay
{
    protected static $name = '支付宝';
    protected $gateway = 'https://mapi.alipay.com/gateway.do';

    protected $options
        = array(
            'service'             => 'create_direct_pay_by_user',
            'partner'             => null,
            '_input_charset'      => 'utf-8',
            'sign_type'           => 'MD5',
            'sign'                => null,
            'notify_url'          => null,
            'return_url'          => null,
            'error_notify_url'    => null,
// 	private $_businessParams = array(
            'out_trade_no'        => null,
            'subject'             => null,
            'payment_type'        => null,
            'seller_email'        => null,
            'buyer_email'         => null,
            'seller_id'           => null,
            'buyer_id'            => null,
            'seller_account_name' => null,
            'buyer_account_name'  => null,
            'price'               => null,
            'total_fee'           => null,
            'quantity'            => null,
            'body'                => null,
            'show_url'            => null,
            'paymethod'           => null,
            'enable_paymethod'    => null,
            'defaultbank'         => null,
            'need_ctu_check'      => null,
            'royalty_type'        => null,
        );

    public static $bankAdapterOptions = ['paymethod' => 'bankPay'];

    protected $keyMapper
        = array(
            'sn'     => 'partner',
            'order'  => 'out_trade_no',
            'amount' => 'total_fee',
            'pay_id' => 'trade_no',
            'bank'   => 'defaultbank',
        );

    protected static $channels
        = [
            'bank'          => [
                "BOCB2C"  => "中国银行",
                "ICBCB2C" => "中国工商银行",
                "CMB"     => "招商银行",
                "CCB"     => "中国建设银行",
                "ABC"     => "中国农业银行",
                "SPDB"    => "上海浦东发展银行",
                "CIB"     => "兴业银行",
                "GDB"     => "广东发展银行",
                "SDB"     => "深圳发展银行",
                "CMBC"    => "中国民生银行",
                "COMM"    => "交通银行",
                "CITIC"   => "中信银行",
                "HZCBB2C" => "杭州银行",
                "CEBBANK" => "中国光大银行",
                "SHBANK"  => "上海银行",
                "NBBANK"  => "宁波银行",
                "SPABANK" => "平安银行",
                "BJRCB"   => "北京农村商业银行",
//            "FDB" => "富滇银行",
                "POSTGC"  => "中国邮政储蓄银行",
                //            "abc1003" => "visa",
//            "abc1004" => "master",
                "BJBANK"  => "北京银行",
                "SHRCB"   => "上海农商银行",
//            "WZCBB2C-DEBIT" => "温州银行",
            ],
            'bankMap'       => [
                'icbc'  => 'ICBCB2C',
                'ccb'   => 'CCB',
                'abc'   => 'ABC',
                'cmb'   => 'CMB',
                'boc'   => 'BOCB2C',
                'post'  => 'POSTGC',
                'bcom'  => 'COMM',
                'ceb'   => 'CEBBANK',
                'gdb'   => 'GDB',
                'spdb'  => 'SPDB',
                'bjrcb' => 'BJRCB',
                'cmbc'  => 'CMBC',
                'sdb'   => 'SDB',
                'citic' => 'CITIC',
                'pab'   => 'SPABANK',
                'shb'   => 'SHBANK',
                'bccb'  => 'BJBANK',
                'nbcb'  => 'NBBANK',
                'hzb'   => 'HZCBB2C',
                'cib'   => 'CIB',
                'shrcc' => 'SHRCB',
            ]
        ];

    /**
     * 创建参数
     */
    public function getRequestParams(OrderResult $order)
    {
        $params = [
            'partner'        => $this->sn,
            'out_trade_no'   => $order->getOrderId(),
            '_input_charset' => $this->getOption('_input_charset'),
            'account'        => $order->getAccount(),
            'body'           => $this->getOption('body'),
            'defaultbank'    => $this->getChannel(),
            'input_charset'  => $this->getOption('_input_charset'),
            'notify_url'     => $this->getOption('notify_url'),
            'payment_type'   => $this->getOption('payment_type'),
            'paymethod'      => $this->getOption('paymethod'),
            'return_url'     => $this->getOption('return_url'),
            'seller_email'   => $this->getOption('seller_email'),
            'service'        => 'create_direct_pay_by_user',
            'subject'        => $this->getOption('subject'),
            'total_fee'      => $order->getAmount(),
        ];

        // 除去待签名参数数组中的空值和签名参数
        $params = self::paraFilter($params);

        // 对待签名参数数组排序
        ksort($params);

        $sign = Validate\Alipay::buildMysign($params, $this->key, $this->getOption('sign_type'));

        $params['sign']      = $sign;
        $params['sign_type'] = $this->getOption('sign_type');
        return $params;
    }

    /**
     * 用于防钓鱼，调用接口query_timestamp来获取时间戳的处理函数
     * 注意：该功能PHP5环境及以上支持，因此必须服务器、本地电脑中装有支持DOMDocument、SSL的PHP配置环境。建议本地调试时使用PHP开发软件
     *
     * @return int 时间戳字符串
     */
    private function queryTimestamp()
    {
        $url
            = $this->gateway . 'service=query_timestamp&partner=' . trim($this->options['partner']);

        $doc = new \DOMDocument();
        $doc->load($url);
        $itemEncryptKey = $doc->getElementsByTagName('encrypt_key');
        return $itemEncryptKey->item(0)->nodeValue;
    }

    public function success()
    {
        echo 'success';
    }

    const PARAMS_ERROR = 'paramsError';
    const VALID_ERROR  = 'validError';
    const ORDER_ERROR  = 'orderError';
    const STATUS_ERROR = 'statusError';

    /**
     *
     * @return bool
     */
    public function isValidServer()
    {
        $params = $_POST;

        foreach (['notify_time', 'notify_type', 'notify_id', 'sign_type', 'sign'] as $key) {
            if (empty($params[$key])) {
                $this->setError(self::PARAMS_ERROR);
                return false;
            }
        }

        if (!$this->getValidate()->isValid($params)) {
            $this->setError(self::VALID_ERROR);
            return false;
        }

        $orderResult = $this->loadOrderResult($params['out_trade_no']);
        if (!$orderResult || $orderResult->isCompleted()) {
            $this->setError(self::ORDER_ERROR);
            return false;
        }

        if ($params['trade_status'] != 'TRADE_FINISHED'
            && $params['trade_status'] != 'TRADE_SUCCESS'
        ) {
            $this->setError(self::STATUS_ERROR);
            return false;
        }

        $this->mergeOptions($params);
        return true;
    }

    public function isValidClient()
    {
        $params = $_GET;

        foreach (['notify_time', 'notify_type', 'notify_id', 'sign_type', 'sign'] as $key) {
            if (empty($params[$key])) {
                $this->setError(self::PARAMS_ERROR);
                return false;
            }
        }

        if (!$this->getValidate()->isValid($params)) {
            $this->setError(self::VALID_ERROR);
            return false;
        }

        if ($params['trade_status'] != 'TRADE_FINISHED'
            && $params['trade_status'] != 'TRADE_SUCCESS'
        ) {
            $this->setError(self::STATUS_ERROR);
            return false;
        }

        $this->mergeOptions($params);
        return true;
    }

    /**
     * @var Validate\Alipay
     */
    private $validate;

    /**
     * @throws \RuntimeException
     * @return Validate\Alipay
     */
    public function getValidate()
    {
        if (!$this->validate) {
            $this->validate = new Validate\Alipay($this);
        }

        return $this->validate;
    }

    public function serverResponse()
    {
        echo $this->getError() ? 'fail' : 'success';
    }

    public function clientResponse()
    {
        //echo $this->getError() ? 'fail' : 'success';
    }

    /**
     * 除去数组中的空值和签名参数
     *
     * @param Array $params 签名参数组
     *
     * @return Array 去掉空值与签名参数后的新签名参数组
     */
    public static function paraFilter($params)
    {
        unset($params['sign'], $params['sign_type'], $params['key']);
        return array_filter($params);
    }

    /**
     * 实现多种字符编码方式
     *
     * @param String $input         需要编码的字符串
     * @param String $outputCharset 输出的编码格式
     * @param String $inputCharset  输入的编码格式 return 编码后的字符串
     *
     * @throws \RuntimeException
     * @return String
     */
    public static function charsetEncode($input, $outputCharset, $inputCharset)
    {
        if (!isset($outputCharset)) {
            $outputCharset = $inputCharset;
        }
        if ($inputCharset == $outputCharset || $input == null) {
            $output = $input;
        } elseif (function_exists("mb_convert_encoding")) {
            $output = mb_convert_encoding(
                $input, $outputCharset, $inputCharset
            );
        } elseif (function_exists("iconv")) {
            $output = iconv($inputCharset, $outputCharset, $input);
        } else {
            throw new \RuntimeException("sorry, you have no libs support for charset change.");
        }
        return $output;
    }

    /**
     * 实现多种字符解码方式
     *
     * @param String $input         需要解码的字符串
     * @param String $inputCharset  输入的解码格式
     * @param String $outputCharset 输出的解码格式
     *
     * @throws \RuntimeException
     * @return String 解码后的字符串
     */
    public static function charsetDecode($input, $inputCharset, $outputCharset)
    {
        if ($inputCharset == $outputCharset || $input == null) {
            $output = $input;
        } elseif (function_exists("mb_convert_encoding")) {
            $output = mb_convert_encoding(
                $input, $outputCharset, $inputCharset
            );
        } elseif (function_exists("iconv")) {
            $output = iconv($inputCharset, $outputCharset, $input);
        } else {
            throw new \RuntimeException("sorry, you have no libs support for charset changes.");
        }
        return $output;
    }
}