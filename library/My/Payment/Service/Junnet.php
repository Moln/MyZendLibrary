<?php
namespace My\Payment\Service;

use My\Product\Product, My\Payment\Payment;

/**
 * Junnet.php
 *
 * @author   maomao
 * @DateTime 12-7-11 下午6:03
 * @version  $Id: Junnet.php 1312 2014-02-07 22:22:16Z maomao $
 */
class Junnet extends DirectPay
{
    protected static $name = '骏网';

    protected $gateway = 'null';
    protected $sn      = 'null';

    protected $options
        = [
            'ServerID'   => null,
            'AreaID'     => null,
            'Username'   => null,
            'CardType'   => null,
            'JNetBillID' => null,
            'Sign'       => null,
            'MchBillID'  => null,
        ];

    protected $keyMapper
        = [
            'pay_id'  => 'JNetBillID', 'account' => 'Username', 'order' => 'MchBillID'
        ];

    protected $cardTypes;

    protected $product;

    const SUCCESS             = '000';
    const FAILED              = '111';
    const KEY_ERROR           = '555';
    const AREA_NOT_FOUND      = '002';
    const SERVER_NOT_FOUND    = '003';
    const CARD_TYPE_NOT_FOUND = '006';
    const GAME_NOT_FOUND      = '007';
    const CARD_PASSWORD_ERROR = '008';
    const SN_NOT_FOUND        = '009';
    const ORDER_EXISTS        = '010';
    const DENY_IP             = '015';
    const EMPTY_GOODS         = '016';
    const PARAMS_ERROR        = '401';
    const ACCOUNT_NOT_FOUND   = '402';
    const TIME_OUT            = '403';
    const ACCOUNT_EXCEPTION   = '404';
    const MERCHANT_NOT_FOUND  = '501';
    const MERCHANT_DISABLED   = '502';
    const SYSTEM_ERROR        = '500';
    const SYSTEM_MAINTENANCE  = '999';

    protected $messages = array(
        '000'=> '成功',
        '111'=> '失败',
        '555'=> '数字签名Md5错误',
        '002'=> '大区不存在',
        '003'=> '游戏服不存在',
        '006'=> '卡类型不存在',
        '007'=> '游戏不存在',
        '008'=> '卡密码错误',
        '009'=> '流水号不存在',
        '010'=> '流水号已存在，订单重复提交',
        '015'=> 'IP错误',
        '016'=> '库存不足',
        '401'=> '参数错误',
        '402'=> '玩家账号不存在',
        '403'=> '订单超时',
        '404'=> '玩家账号状态异常',
        '501'=> '经销商帐号不存在',
        '502'=> '经销帐号被禁用',
        '500'=> '未知错误',
        '999'=> '此功能暂时不可用，系统维护中',
    );

    /**
     * @return bool
     */
    public function isValid()
    {
        $params = ['ServerID', 'AreaID', 'Username', 'CardType', 'JNetBillID', 'Sign'];
        //验证参数
        foreach ($params as $key) {
            if (!isset($_REQUEST[$key])) {
                $this->setError(self::PARAMS_ERROR);
                return false;
            }
        }

        //IP
        if (!$this->isAllowIp()) {
            $this->setError(self::DENY_IP);
            return false;
        }

        //验证卡类型
        $amount = $this->getAmountByCardType($_REQUEST["CardType"]);
        if (!$amount) {
            $this->setError(self::CARD_TYPE_NOT_FOUND); //卡类型不存在
            return false;
        }

        //验证游戏区服
        try {
            if ($_REQUEST['ServerID'] == '0') {
                $_REQUEST['ServerID'] = 'tyj';
            }
            $product = Product::factory($_REQUEST['ServerID']);
            $this->setProduct($_REQUEST['ServerID']);
        } catch (\Exception $e) {
            $this->setError(self::SERVER_NOT_FOUND);
            return false;
        }

        if (!$product->hasArea($_REQUEST['AreaID'])) {
            $this->setError(self::AREA_NOT_FOUND);
            return false;
        }

        //签名
        $username     = $_REQUEST["Username"];
        $cardType     = $_REQUEST["CardType"];
        $billID       = $_REQUEST["JNetBillID"];
        $sign         = $_REQUEST["Sign"];
        $verifyString = md5($username . $cardType . $billID . $this->key);

        $username = strtolower($username);
        if ($verifyString != $sign) {
            $this->setError(self::KEY_ERROR);
            return false;
        }

        //账号验证
        if ($this->validAccountCallback && ($call = $this->validAccountCallback)
            && !$call($username)
        ) {
            $this->setError(self::ACCOUNT_NOT_FOUND);
            return false;
        }

        //验证订单是否存在
        $orderResult = $this->loadOrderResultByPay($billID);
        if ($orderResult) {
            $this->setError(self::ORDER_EXISTS);
            return false;
        }

        $this->mergeOptions(
            [
                'pay_id'  => $billID,
                'account' => $username,
                'amount'  => $amount,
            ]
        );
        return true;
    }

    public function getProductId()
    {
        return [
            'product' => $this->getProduct(), 'area'   => $_REQUEST["AreaID"]
        ];
    }

    public function response()
    {
        $params['Return'] = $this->getError()
            ? : ($this->getOrderResult() ? self::SUCCESS : self::FAILED);

        $params          += [
            'ServerID'   => isset($_REQUEST['ServerID']) ? $_REQUEST['ServerID'] : '',
            'AreaID'     => isset($_REQUEST['AreaID']) ? $_REQUEST['AreaID'] : '',
            'Username'   => isset($_REQUEST['Username']) ? $_REQUEST['Username'] : '',
            'CardType'   => isset($_REQUEST['CardType']) ? $_REQUEST['CardType'] : '',
            'JNetBillID' => isset($_REQUEST['JNetBillID']) ? $_REQUEST['JNetBillID'] : '',
            'MchBillID'  => $this->getOption('order') ? : Payment::createOrderId(),
        ];

        $params['Sign']   = md5(
            $params['Username'] . $params['CardType'] . $params['JNetBillID']
                . $params['MchBillID'] . $this->key
        );

        echo http_build_query($params) . '&Message=' .
            iconv('utf-8', 'gb2312', $this->messages[$params['Return']]);
        exit;
    }

    public function getAmountByCardType($type)
    {
        if (empty($this->cardTypes)) {
            throw new \RuntimeException('卡类型未设置');
        }
        return isset($this->cardTypes[$type]['Card_Value'])
            ? $this->cardTypes[$type]['Card_Value'] : null;
    }

    public function setCardTypes($cardTypes)
    {
        $this->cardTypes = $cardTypes;
        return $this;
    }

    public function getProduct()
    {
        return $this->product;
    }

    public function setProduct($product)
    {
        $this->product = $product;
        return $this;
    }

    /**
     * @param $callback
     *
     * @return bool|void
     * @throws \InvalidArgumentException
     */
    public function queryOrder($callback)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('callback 必需是方法');
        }

        $orderId = '';

        $billID  = isset($_REQUEST['JNetBillID']) ? $_REQUEST['JNetBillID'] : '' ;

        if ($this->isValidQueryOrder()) {

            /** @var $orderResult \My\Payment\Data\OrderResult */
            $orderResult = $callback($billID);
            if (!$orderResult) {
                $this->setError(self::SN_NOT_FOUND);
            } else {
                $orderId = $orderResult->getOrderId();
            }
        }

        $return = $this->getError() ? : self::SUCCESS;

        $sign = md5($return . $billID . $orderId . $this->key);
        echo "Return=$return&JNetBillID=$billID&MchBillID=$orderId&Sign=$sign&Message="
            . iconv('utf-8', 'gb2312', $this->messages[$return]);
        exit;
    }

    private function isValidQueryOrder()
    {
        $params = ['JNetBillID', 'Sign'];

        //验证参数
        foreach ($params as $key) {
            if (!isset($_REQUEST[$key])) {
                $this->setError(self::PARAMS_ERROR);
                return false;
            }
        }

        //IP
        if (!$this->isAllowIp()) {
            $this->setError(self::DENY_IP);
            return false;
        }

        //签名
        $billID       = $_REQUEST["JNetBillID"];
        $sign         = $_REQUEST["Sign"];
        $verifyString = md5($billID . $this->key);
        if ($verifyString != $sign) {
            $this->setError(self::KEY_ERROR);
//            echo $verifyString, "<br>\n";
            return false;
        }

        return true;
    }
}
