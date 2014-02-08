<?php
namespace My\Product\Node;
use My\Payment\Data;
use My\Log\Logger;
use Zend_Http_Client;
use My\Payment\Payment;

/**
 * 醉西游节点
 * @author Xiemaomao
 * @DateTime 12-11-13 上午11:29
 * @version $Id: ZxyAreaNode.php 1312 2014-02-07 22:22:16Z maomao $
 *
 * @property \My\Product\Zxy $product
 */
class ZxyAreaNode extends AreaNode implements LoginInterface
{

    protected $hidden;

    public function setHidden($hidden)
    {
        $this->hidden = (bool)$hidden;
        return $this;
    }

    public function isHidden()
    {
        return $this->hidden;
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

        $url    = 'http://' . $this->getHost() . '/user/pay.php';
        $params = array(
            'Mode'      => 1,
            'PayNum'    => $consume->getId(),
            'PayToUser' => $account,
            'PayMoney'  => $consume->getGold() / Payment::RATE,
            'PayGold'   => $consume->getGold(),
            'PayTime'   => time(),
        );

        $params['ticket']   = md5($this->product->getPayKey() . implode($params));
        $params['serverid'] = $this->getId();

        try {
            $client = new Zend_Http_Client($url);
            $client->setParameterGet($params);
            $response = $client->request('GET');
            if ($response->getBody() == 'true') {
                return true;
            } else {
                Logger::err("Zxy pay error:" . $response->getBody());
                return false;
            }
        } catch (\Exception $e) {
            Logger::err("Zxy pay exception:" . $e->getMessage());
            return false;
        }
    }

    public function login(Login\Info $info)
    {
        $url    = 'http://' . $this->getHost() . '/user/start.php?';
        $params = array(
            'accid'   => '0',
            'accname' => $info->getAccount(),
            'tstamp'  => time(),
        );

        $params['ticket']  = md5(implode($params) . $this->product->getServerKey());
        $params['serverid']  = $this->getId();

        if ($info->isEnableAntiAddiction()) {
            $params['ktc'] = 1;
            $params['fcm'] = $info->getIsAdult() ? 1 : 2;
        }
        return $url . http_build_query($params);
    }

    public function queryRole($account)
    {
        $time   = time();
        $params = array(
            'username' => $account,
            'time'     => $time,
            'serverid' => $this->getIndex(),
            'ticket'   => md5($time . $this->product->getServerKey()),
        );

        $url    = 'http://' . $this->getHost() . '/user/role_info.php';
        $client = new \Zend_Http_Client($url, ['timeout' => 5]);
        $client->setParameterGet($params);

        try {
            $response = $client->request();
            if ($response->getStatus() == 200) {

                $result = json_decode($response->getBody(), true);
                if (is_array($result)) {
                    return array('result' => true, 'data' => $result);
                } else {
                    $errors = array(
                        -1 => '参数缺少',
                        -2 => '验证失败',
                        -3 => '查询不到该用户信息',
                    );
                    return array('result' => false, 'message' => $errors[$result]);
                }
            } else {
                Logger::emerg(
                    '角色查询接口调用失败(' . $response->getStatus() . '):' . $response->getBody(),
                    ['url' => $url]
                );
                return false;
            }
        } catch (\Zend_Http_Client_Exception $e) {
            Logger::emerg('角色查询接口调用失败:' . $e->getMessage(), ['url' => $url]);
            return false;
        }
    }
}
