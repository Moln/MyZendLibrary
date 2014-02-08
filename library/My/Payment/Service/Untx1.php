<?php
namespace My\Payment\Service;
use My\Payment\Data\OrderResult;
use My\Payment\MessageException;
use RuntimeException;

/**
 * Untx1.php
 * @author Xiemaomao
 * @DateTime 12-10-18 上午10:56
 * @version $Id: Untx1.php 1266 2013-10-22 10:29:07Z maomao $
 */
class Untx1 extends ChannelPay
{
    const ERROR_SIGN = "ERROR_SIGN";
    const ERROR_ORDER = "ERROR_ORDER";
    const ERROR_ORDER_COMPLETED = "ERROR_ORDER_COMPLETED";
    const ERROR_STATUS = "ERROR_STATUS";
    protected static $name = "新宽联CASS";
    protected $gateway = 'http://cass.gotogame.com.cn/cass/interface/protal.php';

    protected $keyMapper = [
        'sn'   => 'mch_id',
        'bank' => 'pay_type',
        'amount' => 'pay_fee',
        'order' => 'mch_order',
    ];
    private $cryptKey, $payApi;

    public function setPayApi($payApi)
    {
        $this->payApi = $payApi;
        return $this;
    }

    public function setCryptKey($cryptKey)
    {
        $this->cryptKey = substr($cryptKey, 0, 24);
        return $this;
    }

    public function redirectPay(OrderResult $order)
    {
        $params = $this->getRequestParams($order);
        if ($params['request_type'] == 1) {
            try {
                $client = new \Zend_Http_Client($this->gateway);
                $client->setParameterPost($params);
                $response = $client->request('POST');
                parse_str($response->getBody(), $result);

                if (count($result) == 1) {
                    throw new MessageException(iconv('gbk', 'utf-8', $response->getBody()));
                }

                if ($result['status'] == '1') {
                    $client2 = new \Zend_Http_Client($this->payApi);
                    $client2->setParameterGet($result);
                    $response2 = $client2->request('POST');
                    if ($response2->getBody() == 'success') {
                        header('Location: ' . $this->getOption('return_url'));
                        exit;
                    } else {
                        throw new RuntimeException('Payment untx1 error!');
                    }
                } else {
                    throw new MessageException(iconv('gbk', 'utf-8', $result['result']));
                }
            } catch (\Zend_Http_Exception $e) {
                throw new MessageException('接口链接失败');
            }
        } else {
            header('Location: ' . $this->gateway . '?' . http_build_query($params));
            exit;
        }
    }

    /**
     * 跳转时传递参数
     *
     * @param \My\Payment\Data\OrderResult $order
     *
     * @throws \RuntimeException
     * @return array
     */
    public function getRequestParams(OrderResult $order)
    {
        $payType = $this->getOption('pay_type');
        $returnUrl = $notifyUrl = "";
        switch ($payType) {
            case 1:case 2:
                $requestType = 1;
                $returnUrl = $this->getOption('return_url');
                $notifyUrl = $this->getOption('notify_url');
                break;
            case 3:case 4:case 5:case 6:
                $requestType = 0;
                break;
            default:
                throw new \RuntimeException('错误支付类型:' . $payType);
                break;
        }

        $reserve1 = '';
        $cards = $this->getOption('card');
        if ($payType == 2 && count($cards)) {
            $cardpws = $this->getOption('cardpw');
            $reserve1 = count($cards);
            foreach ($cards as $i => $card) {
                $reserve1 .= "|$card,{$cardpws[$i]}";
            }

            $crypt = new UntxCrypt3Des($this->cryptKey);
            $reserve1 = unpack("H*", $crypt->encrypt($reserve1))[1];
        }

        $params = array(
            'mch_id' => $this->sn,
            'pay_fee' => number_format($order->getAmount(), 2),
            'mch_order' => $order->getOrderId(),
            'time' => time(),
            'referer' => $this->getOption('referer') ? : '',
            'pay_type' => $payType,
            'addition' => $this->getOption('addition') ? : '',
            'reserve1' => $this->getOption('reserve1') ? : $reserve1,
            'reserve2' => $this->getOption('reserve2') ? : '',
            'return_url' => $returnUrl ? : '',
            'notify_url' => $notifyUrl ? : '',
            'request_type' => $requestType,
        );

        $params['sign'] = md5($this->queryBuild($params+['key' => $this->key], true));

        return $params;
    }

    /**
     * @return bool
     */
    public function isValidServer()
    {
        $request = $this->getRequest();
        $params = array(
            'mch_order' => $request->getParam('mch_order'),
            'sys_order' => $request->getParam('sys_order'),
            'time' => $request->getParam('time'),
            'status' => $request->getParam('status'),
            'result' => $request->getParam('result'),
            'pay_fee' => $request->getParam('pay_fee'),
            'pay_type' => $request->getParam('pay_type'),
            'addition' => $request->getParam('addition'),
            'reserve1' => $request->getParam('reserve1'),
            'reserve2' => $request->getParam('reserve2'),
        );

        $sign = md5($this->queryBuild($params+['key' => $this->key], true));

        if ($request->getParam('sign') != $sign) {
            $this->setError(self::ERROR_SIGN);
            return false;
        }

        $orderResult = $this->loadOrderResult($params['mch_order']);
        if (!$orderResult) {
            $this->setError(self::ERROR_ORDER);
            return false;
        }

        if ($params['status'] != 1) {
            $this->setError(self::ERROR_STATUS);
            return false;
        }

        if ($orderResult->isCompleted()) {
            $this->setError(self::ERROR_ORDER_COMPLETED);
            return false;
        }

        $this->setOption('order', $params['mch_order']);
        $this->setOption('pay_id', $params['sys_order']);
        return true;
    }

    public function serverResponse()
    {
        if ($this->getError() == self::ERROR_STATUS) {
            $this->logger('Untx1 response error:' . self::ERROR_STATUS, $_REQUEST);
        }
        echo $this->getError() ? : 'success';
    }
}
