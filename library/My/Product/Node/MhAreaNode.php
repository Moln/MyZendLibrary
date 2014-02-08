<?php
namespace My\Product\Node;
use My\Payment\Data;
use My\Log\Logger;
use Zend_Http_Client;
use My\Payment\Payment;

/**
 * 醉西游服务器节点
 * Class MhAreaNode
 * @package My\Product\Node
 * @author Xiemaomao
 * @version $Id: MhAreaNode.php 1312 2014-02-07 22:22:16Z maomao $
 *
 * @property \My\Product\Mh $product
 */
class MhAreaNode extends AreaNode implements LoginInterface
{
    private function getGateWay($api)
    {
        return 'http://mhapi.lezi.com/' . $this->product->getGenid() . '/' . $api . '.php';
    }

    /**
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
        if (APPLICATION_ENV != 'production') {
            return true;
        }

        $url = $this->getGateWay('deal/pay');

        $params = array(
            'genid'   => $this->product->getGenid(),
            'area'    => $this->product->getAreaStart()+$this->getIndex(),
            'user'    => $account,
            'transno' => $consume->getId(),
            'counts'  => $consume->getGold(),
            'time'    => time(),
        );

        $params['sign'] = md5(implode('|', $params) . '|' . $this->product->getKey());

        try {
            $client = new Zend_Http_Client($url);
            $client->setParameterGet($params);
            $response = $client->request('GET');
            if ($response->getBody() === '0') {
                return true;
            } else {
                Logger::err("Mh pay error:" . $response->getBody());
                return false;
            }
        } catch (\Exception $e) {
            Logger::err("Mh pay exception:" . $e->getMessage());
            return false;
        }
    }


    public function login(Login\Info $info)
    {
        $url    = $this->getGateWay('login');
        $params = array(
            'genid'     => $this->product->getGenid(),
            'server_id' => $this->product->getAreaStart()+$this->getIndex(),
            'user'      => $info->getAccount(),
            'pass'      => md5($info->getAccount() . $this->product->getGenid()),
            'time'      => time(),
        );

        $params['sign'] = md5(implode('|', $params) . '|' . $this->product->getKey());
        $this->sync($info->getAccount());
        return $url . '?' . http_build_query($params);
    }

    /**
     * 同步账号信息
     * @param $account
     *
     * @return bool
     */
    public function sync($account)
    {
        $url    = $this->getGateWay('deal/syn');
        $index  = $this->product->getAreaStart()+$this->getIndex();
        $params = array(
            'genid' => $this->product->getGenid(),
            'area'  => $index,
            'user'  => $account,
            'pass'  => md5($account . $this->product->getGenid()),
            'time'  => time(),
        );

        $params['sign']  = md5(implode('|', $params) . '|' . $this->product->getKey());
        $params['adult'] = 1;

        try {
            $client = new Zend_Http_Client($url);
            $client->setParameterGet($params);
            $response = $client->request('GET');
            if ($response->getBody() === '0') {
                return true;
            } else {
                Logger::err("Mh sync error:" . $response->getBody());
                return false;
            }
        } catch (\Exception $e) {
            Logger::err("Mh sync exception:" . $e->getMessage());
            return false;
        }
    }
}
