<?php
namespace My\Payment\Service\Validate;

use Zend_Http_Client, My\Payment\Service\Alipay as AlipayService;

/**
 * 类名：AlipayNotify
 * 功能：支付宝通知处理类
 * 详细：处理支付宝各接口通知返回
 * 版本：3.2
 * 日期：2011-03-25
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考
 *
 * ************************注意*************************
 * 调试通知返回时，可查看或改写log日志的写入TXT里的数据，来检查通知返回是否正常
 */
class Alipay
{

    /**
     * HTTPS形式消息验证地址
     */
    private $httpsVerifyUrl = 'https://mapi.alipay.com/gateway.do';

    /**
     * HTTP形式消息验证地址
     */
    private $httpVerifyUrl = 'http://notify.alipay.com/trade/notify_query.do';

    /**
     * @var \My\Payment\Service\Alipay
     */
    private $service;

    public function __construct(AlipayService $service)
    {
        $this->service = $service;
    }

    /**
     * @param $key
     *
     * @return string
     */
    private function getOption($key)
    {
        return $this->service->getOption($key);
    }

    /**
     * valid request
     * rename verify
     *
     * @param array $params
     *
     * @return bool
     */
    public function isValid(array $params)
    {
        // 生成签名结果
        $sign = $this->getSign($params);

        // 获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
        $responseTxt = empty($params["notify_id"])
            ? 'true'
            : $this->getResponse($params["notify_id"]);

        // 验证
        // $responseTxt的结果不是true，与服务器设置问题、合作身份者ID、notify_id一分钟失效有关
        // mySign与sign不等，与安全校验码、请求时的参数格式（如：带自定义参数等）、编码格式有关
        return $responseTxt == self::REQUEST_TURE && $sign == $params["sign"];
    }

    /**
     * 根据反馈回来的信息，生成签名结果
     *
     * @param array $params 通知返回来的参数数组
     *
     * @return String 生成的签名结果
     */
    private function getSign($params)
    {
        // 除去待签名参数数组中的空值和签名参数
        $paraFilter = AlipayService::paraFilter($params);

        // 对待签名参数数组排序
        ksort($paraFilter);

        // 生成签名结果
        return self::buildMysign(
            $paraFilter, $this->service->getKey(), strtoupper($this->getOption('sign_type'))
        );
    }

    /**
     * 获取远程服务器ATN结果,验证返回URL
     *
     * @param string $notifyId 通知校验ID
     *
     * @return string 服务器ATN结果
     *         验证结果集：
     *         invalid命令参数不对 出现这个错误，请检测返回处理中partner和key是否为空
     *         true 返回正确信息
     *         false 请检查防火墙或者是服务器阻止端口问题以及验证时间是否超过一分钟
     */
    private function getResponse($notifyId)
    {
        $partner = $this->getOption('partner');
        $url     = $this->isHttps() ? $this->httpsVerifyUrl : $this->httpVerifyUrl;

        $client = new Zend_Http_Client($url, array('timeout' => 40));
        try {
            $client->setParameterPost(
                array(
                    'partner'   => $partner,
                    'notify_id' => urldecode($notifyId),
                    'service'   => 'notify_verify',
                )
            );
            $response = $client->request(Zend_Http_Client::POST);
            return trim($response->getBody());
        } catch (\Zend_Http_Client_Exception $e) {
            return self::REQUEST_FALSE;
        }
    }

    protected function isHttps()
    {
        return strtolower($this->getOption('transport')) == 'https';
    }

    const REQUEST_INVALID = 'invalid';
    const REQUEST_TURE    = 'true';
    const REQUEST_FALSE   = 'false';



    /**
     * 生成签名结果
     *
     * @param Array  $sortPara 要签名的数组
     * @param String $key      支付宝交易安全校验码
     * @param String $signType 签名类型 默认值：MD5
     *
     * @return String 签名结果字符串
     */
    public static function buildMysign($sortPara, $key, $signType = 'MD5')
    {
        // 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $prestr = self::createLinkString($sortPara);
        // 把拼接后的字符串再与安全校验码直接连接起来
        $prestr = $prestr . $key;
        // 把最终的字符串签名，获得签名结果
        $sign = self::sign($prestr, $signType);
        return $sign;
    }


    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
     *
     * @param Array $para 需要拼接的数组
     *
     * @return String 拼接完成以后的字符串
     */
    public static function createLinkString($para)
    {
        $arg = "";
        foreach ($para as $key => $val) {
            $arg .= $key . "=" . $val . "&";
        }
        // 去掉最后一个&字符
        $arg = substr($arg, 0, count($arg) - 2);
        // 如果存在转义字符，那么去掉转义
        if (get_magic_quotes_gpc()) {
            $arg = stripslashes($arg);
        }
        return $arg;
    }


    /**
     * 签名字符串
     *
     * @param String $string   需要签名的字符串
     * @param String $signType 签名类型 默认值：MD5
     *
     * @throws \RuntimeException
     * @return String 签名结果
     */
    public static function sign($string, $signType = 'MD5')
    {
        if ($signType == 'MD5') {
            $sign = md5($string);
        } elseif ($signType == 'DSA') {
            // DSA 签名方法待后续开发
            throw new \RuntimeException('DSA 签名方法待后续开发，请先使用MD5签名方式');
        } else {
            throw new \RuntimeException('支付宝暂不支持(' . $signType . ')类型的签名方式');
        }
        return $sign;
    }
}