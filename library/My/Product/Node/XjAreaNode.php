<?php
namespace My\Product\Node;
use My\Payment\Data;
use Zend_Http_Client;
use Zend_Http_Response;
use My\Log\Logger;

/**
 * Class XjAreaNode
 * @package My\Product\Node
 * @author Yoyo
 * @version $Id: XjAreaNode.php 1312 2014-02-07 22:22:16Z maomao $
 * @property \My\Product\Djj $product
 *
 */
class XjAreaNode extends AreaNode implements LoginInterface
{
    protected $_data = array(
        'id' => null,
        'index' => null,
        'name' => null,
        'host' => null,
        'openDate' => null,
        'enabled' => true,
        'sid' => null,
    );

    public function setSid($sid)
    {
        $this->sid = $sid;
        return $this;

    }

    public function getSid()
    {
        return $this->sid;

    }

    /**
     * 登陆接口
     * @param Login\Info $info
     * @throws \RuntimeException
     * @return bool|string
     */
    public function login(Login\Info $info)
    {
        $authParams = array(
            'uid' => $info->getId(), //用户id
            'sno' => $this->getId(), //服务器编号
            'cm' => 0,
            'from' => 0,
            'time' => time(),

        );

        $token = md5(implode($authParams) . $this->product->getKey());
        $authParams += array('token' => $token);
        return $this->product->getApi() . '?' . http_build_query($authParams);

    }

    /**
     * 充值接口
     * @param string                       $account
     * @param int                          $gold
     * @param \My\Payment\Data\Consume     $consume
     * @param \My\Payment\Data\OrderResult $order
     * @param null                         $more
     *
     * @return bool
     */
    public function addGold(
        $account, $gold, Data\Consume $consume, Data\OrderResult $order = null, $more = null
    )
    {
//        if (APPLICATION_ENV != 'production') {
//            return true;
//        }

        $authParams = array(
            'pid' => $this->product->getOpId(),
            'order_id' => $consume->getId(),
            'uid' => $consume->getUserId(),
            'gid' => $this->product->getGameId(),
            'sid' => $this->getSid(),
            'order_amount' => $consume->getGold() / 10,
            'point' => $consume->getGold(),

        );

        $token = md5(implode($authParams) . $this->product->getKey());
        $goldUrl = 'http://pay.cy2009.com/jointoppay/';
        $authParams += array('money' => $consume->getGold() / 10, 'sign' => $token);
        try {
            $client = new Zend_Http_Client($goldUrl);
            $client->setParameterGet($authParams);
            $response = $client->request('GET');
            if ($response->getBody() == '1') {
                return true;
            } else {
                Logger::err("Xj pay error:" . $response->getBody());
                return false;
            }
        } catch (\Exception $e) {
            Logger::err("Xj pay exception:" . $e->getMessage());
            return false;
        }
    }
}
