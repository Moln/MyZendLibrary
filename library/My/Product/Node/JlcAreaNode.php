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
 * @version $Id: JlcAreaNode.php 1312 2014-02-07 22:22:16Z maomao $
 * @property \My\Product\Jlc $product
 *
 */
class JlcAreaNode extends AreaNode implements LoginInterface
{

    /**
     * 登陆接口
     * @param Login\Info $info
     * @throws \RuntimeException
     * @return bool|string
     */
    public function login(Login\Info $info)
    {
        $authParams = array(
            'sid'     => $this->getIndex(),
            'uid'     => $info->getId(), //用户id
            'time'    => time(),
            'indulge' => 'n'

        );

        $auth   = base64_encode($this->getAuth($authParams));
        $params = array(
            'auth' => $auth,
            'sign' => md5($auth . $this->product->getKey())
        );
        return $this->product->getApi() . '?' . http_build_query($params);


    }

    /**
     * @param $authParams
     * @return string
     */
    public function getAuth($authParams)
    {
        $query = '';
        foreach ($authParams as $key => $val) {
            $query .= "&$key =$val";

        }
        return ltrim($query, '&');

    }

    /**
     * 充值接口
     * @param string $account
     * @param int $gold
     * @param \My\Payment\Data\Consume $consume
     * @param \My\Payment\Data\OrderResult $order
     * @param null $more
     *
     * @return bool
     */
    public function addGold(
        $account, $gold, Data\Consume $consume, Data\OrderResult $order = null, $more = null
    )
    {
        if (APPLICATION_ENV != 'production') {
            return true;
        }

        $authParams = array(
            'sid'   => $this->getIndex(),
            'uid'   => $consume->getUserId(),
            'oid'   => $consume->getId(),
            'money' => $consume->getGold() / 10.0,
            'gold'  => $consume->getGold(),
            'time'  => time(),

        );
        $auth       = $this->getAuth($authParams);
        $sign       = md5($auth . $this->product->getPayKey());

        $goldUrl = $this->product->getPayApi();
        $authParams += array('sign' => $sign);
        try {
            $client = new Zend_Http_Client($goldUrl);
            $client->setParameterGet($authParams);
            $response = $client->request('GET');
            if ($response->getBody() == '1') {
                return true;
            } else {
                Logger::err("Jlc pay error:" . $response->getBody());
                return false;
            }
        } catch (\Exception $e) {
            Logger::err("Jlc pay exception:" . $e->getMessage());
            return false;
        }
    }
}
