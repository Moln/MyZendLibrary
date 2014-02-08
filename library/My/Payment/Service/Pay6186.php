<?php
namespace My\Payment\Service;
use My\Product\Product;
use My\Payment\Payment;

/**
 * Pay6186
 * @author Xiemaomao
 * @DateTime 12-11-7 上午10:08
 * @version $Id: Pay6186.php 1266 2013-10-22 10:29:07Z maomao $
 */
class Pay6186 extends DirectPay
{
    protected $gateway = 'null';
    protected static $name = '6186';

    const ERROR_ORDER   = '-201';
    const ERROR_IP      = '-100';
    const ERROR_PARAMS  = '-101';
    const ERROR_SIGN    = '-102';
    const ERROR_PRODUCT = '-112';
    const ERROR_AREA    = '-113';
    const ERROR_ACCOUNT = '-110';
    const ORDER_EXISTS  = '-103';
    const SUCCESS       = '1';
    const ERROR_SYSTEM  = '-114';

    protected $messages
        = array(
//        '1' => '直充成功',
            self::ERROR_IP      => '充值ip错误',
            self::ERROR_PARAMS  => '参数不完整',
            self::ERROR_SIGN    => 'MD5验证错误',
            self::ORDER_EXISTS  => '单据号重复',
            self::ERROR_ACCOUNT => '游戏账户错误',
            '-111'              => '游戏金额错误',
            self::ERROR_PRODUCT => '游戏代码型错误',
            self::ERROR_AREA    => '区域代码型错误',
            self::ERROR_SYSTEM  => '服务器代码型错误',
        );

    public function response()
    {
        $request = $this->getRequest();
        $orderResult = $this->getOrderResult();
        if ($orderResult) {
            $params  = array(
                'result'              => $orderResult->isCompleted() ?
                    self::SUCCESS : self::ERROR_SYSTEM,
                'userorder'           => $request->getParam('userorder'),
                'officialorder'       => $orderResult->getOrderId(),
                'officialgamemoney'   => $orderResult->getAmount(),
                'officialgameaccount' => $orderResult->getAccount(),
            );
        } else {
            $params  = array(
                'result'              => $this->getError(),
                'userorder'           => $request->getParam('userorder'),
                'officialorder'       => Payment::createOrderId(),
                'officialgamemoney'   => $request->getParam('gamemoney'),
                'officialgameaccount' => $request->getParam('gameaccount'),
            );

            if ($this->getError() == self::ERROR_PRODUCT || $this->getError() == self::ERROR_AREA) {
                $this->logger(
                    '6186 gameid error', $_REQUEST+['officialorder' => $params['officialorder']]
                );
            }
        }

        $params['sign'] = md5($this->queryBuild($params));
//        $params['message'] = $this->getMessage();

        echo $this->queryBuild($params);
        exit;
    }

    public function isValid()
    {
        if (!$this->isAllowIp()) {
            $this->setError(self::ERROR_IP);
            return false;
        }

        $params  = array(
            'userid'      => null,
            'userorder'   => null,
            'gamemoney'   => null,
            'gameaccount' => null,
            'gameid'      => null,
//            'areaid'      => null,
            'serverid'    => null,
        );
        $request = $this->getRequest();
        foreach ($params as $key => &$val) {
            if (!$request->has($key)) {
                $this->setError(self::ERROR_PARAMS);
                return false;
            }
            $val = $request->getParam($key);
        }

        $sign = md5($this->queryBuild($params + array('key' => $this->key)));
        if ($request->getParam('sign') != $sign) {
            $this->setError(self::ERROR_SIGN);
            return false;
        }

        //验证游戏区服
        try {
            $product = Product::factory($params['gameid']);
        } catch (\Exception $e) {
            $this->setError(self::ERROR_PRODUCT);
            return false;
        }

        if (!$product->hasArea($params['serverid'])) {
            $this->setError(self::ERROR_AREA);
            return false;
        }

        $this->product = $product;
        $this->area = $product->getArea($params['serverid']);

        //账号验证
        if ($this->validAccountCallback && ($call = $this->validAccountCallback)
            && !$call($params['gameaccount'])
        ) {
            $this->setError(self::ERROR_ACCOUNT);
            return false;
        }

        //验证订单是否存在
        $orderResult = $this->loadOrderResultByPay($params['userorder']);
        if ($orderResult) {
            $this->setError(self::ORDER_EXISTS);
            return false;
        }

        $this->mergeOptions(
            [
                'pay_id'  => $params['userorder'],
                'account' => $params['gameaccount'],
                'amount'  => $params['gamemoney'],
            ]
        );

        return true;
    }

    /**
     * @var Product;
     */
    private $product, $area;

    /**
     * @delete
     * @toto delete
     * @return mixed
     */
    public function getProductId()
    {
        return [
            'product' => $this->product->getId(), 'area' => $this->area->getId()
        ];
    }

    public function queryOrder($callback)
    {
        $order = $this->getRequest()->getParam('userorder');
        $userId = $this->getRequest()->getParam('userid');
        $result = array(
            'officialorder' => null,
            'officialgamemoney' => null,
            'officialgameaccount' => null,
        );
        if (!$this->isAllowIp()) {
            $this->setError(self::ERROR_IP);
        }
        if (!$this->getError() && $this->sn != $userId) {
            $this->setError(self::ERROR_PARAMS);
        }
        if (!$this->getError()) {

            $sign = md5($this->queryBuild(array(
                        'userid' => $userId, 'userorder' => $order, 'key' => $this->key,
                    )));

            if ($this->getRequest()->getParam('sign') != $sign) {
                $this->setError(self::ERROR_SIGN);
            }
        }

        if (!$this->getError()) {
            /** @var $orderResult \My\Payment\Data\OrderResult */
            $orderResult = $callback($order);
            if (!$orderResult) {
                $this->setError(self::ERROR_ORDER);
            } else {
                $result = array(
                    'officialorder' => $orderResult->getOrderId(),
                    'officialgamemoney' => $orderResult->getAmount(),
                    'officialgameaccount' => $orderResult->getAccount(),
                );
            }
        }

        $params = array(
            'result' => $this->getError() ? : self::SUCCESS,
            'userorder' => $order,
        ) + $result;

        $params['sign'] = md5($this->queryBuild($params));

        echo $this->queryBuild($params);
        exit;
    }
}
