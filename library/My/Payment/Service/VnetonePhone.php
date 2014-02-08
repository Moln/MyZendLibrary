<?php
namespace My\Payment\Service;
use My\Payment\Data\OrderResult;

/**
 * 盈华讯方-神州行手机充值卡
 * Class VnetonePhone
 * @package My\Payment\Service
 * @author Xiemaomao
 * @version $Id: VnetonePhone.php 1266 2013-10-22 10:29:07Z maomao $
 */
class VnetonePhone extends ChannelPay
{
    protected static $name = '盈华讯方-神州行';
    protected $gateway = 'http://sj.vnetone.com/Default.aspx';

    protected $options
        = array(
            'spid'       => null,
            'longstring' => null,
            'sporderid'  => null,
            'mz'         => null,
            'ip'         => null,
            'uid'        => null,
            'spreq'      => null,
            'sprev'      => null,
            'md5x'       => null,
        );

    protected $keyMapper
        = array(
            'sn'      => 'spid',
            'order'   => 'sporderid',
            'amount'  => 'mz',
            'account' => 'uid',
            'pay_id'  => 'sid',
        );

    protected static $channels = [
        'phoneCard' => [
            'szx' => '神州行'
        ],
        'phoneCardMap' => [
            'szx' => 'szx',
        ],
    ];

    public function getRequestParams(OrderResult $order)
    {
        $params = [
            'spid'       => $this->getOption('spid'),
            'longstring' => $this->getOption('longstring'),
            'sporderid'  => $this->getOption('sporderid'),
            'mz'         => $this->getOption('mz'),
            'ip'         => $this->getRequest()->getClientIp(CLIENT_IP_PROXY),
            'uid'        => $this->getOption('uid'),
            'spreq'      => $this->getOption('spreq'),
            'sprev'      => $this->getOption('sprev'),
        ];

        $str            = $params['sporderid'] . $params['spid'] . $this->key . $params['mz'];
        $params['md5x'] = strtoupper(md5($str));

        return $params;
    }

    public function redirectPay(OrderResult $order)
    {
        $params = $this->getRequestParams($order);
        $params = array_filter($params);

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

    const ORDER_ERROR  = 'orderError';
    const VALID_ERROR  = 'validError';
    const PARAMS_ERROR = 'paramsError';
    const SYSTEM_ERROR = 'systemError';
    const STATUS_ERROR = 'statusError';
    const FROM_FRONT   = 'fromFront';
    const ORDER_COMPLETED = 'ORDER_COMPLETED';


    /**
     * @return bool
     */
    public function isValidServer()
    {
        $params = [];
        if (!$this->isValid($params)) {
            if ($this->getError() == self::ORDER_ERROR && $params['flag'] == '2') {
                $this->setError(self::FROM_FRONT);
                return false;
            }
            return false;
        }

        if ($params['flag'] == '2') {
            $this->setError(self::FROM_FRONT);
            return false;
        }

        if ($params['flag'] != '1') {
            $this->setError(self::STATUS_ERROR);
            return false;
        }

        $this->setOption('order', $params['sporderid']);
        $this->setOption('pay_id', $params['sid']);

        return true;
    }

    private function isValid(&$params)
    {
        $request = $this->getRequest();
        $params  = array(
            'spid'      => $request->getParam('spid'),
            'md5'       => $request->getParam('md5'),
            'sid'       => $request->getParam('sid'),
            'sporderid' => $request->getParam('sporderid'),
            'money'     => $request->getParam('money'),
            'ntime'     => $request->getParam('ntime'),
            'flag'      => $request->getParam('flag'),
            'uid'       => $request->getParam('uid'),
        );

        $sign = md5(
            $params['spid'] . $this->key . $params['sid'] . $params['sporderid'] . $params['money']
                . $params['flag']
        );

        if ($sign != strtolower($params['md5'])) {
            $this->setError(self::VALID_ERROR);
            return false;
        }

        $orderResult = $this->loadOrderResult($params['sporderid']);
        if (!$orderResult) {
            $this->setError(self::ORDER_ERROR);
            return false;
        } else if ($orderResult->isCompleted()) {
            $this->setError(self::ORDER_COMPLETED);
            return false;
        }

        return true;
    }

    public function serverResponse()
    {
        if (!$this->getError() || $this->getError() == self::ORDER_COMPLETED) {
            echo 1;
        } else if ($this->getError() == self::FROM_FRONT) {
            header('Location: ' . $this->getOption('spreq'));
        } else {
            $this->logger("vnetone error:" . $this->getError());
        }
        exit;
    }
}