<?php
namespace My\Product\Node;
use My\Payment\Data;
use Zend_Controller_Front;
use Zend_Http_Response;
use My\Log\Logger;

/**
 * Class UuzuAreaNode
 * @package My\Product\Node
 * @author Yoyo
 * @version $Id: UuzuAreaNode.php 1312 2014-02-07 22:22:16Z maomao $
 */
abstract class UuzuAreaNode extends AreaNode
{
    protected $_data
        = array(
            'id'       => null,
            'index'    => null,
            'name'     => null,
            'host'     => null,
            'openDate' => null,
            'enabled'  => true,
        );

    public function getSid()
    {
        return $this->product->getOpId() * 1000000 + static::GAME_ID * 10000 + $this->getIndex();
    }

    /**
     * 登陆接口
     * @param Login\Info $info
     *
     * @throws \RuntimeException
     * @return bool|string
     */
    public function login(Login\Info $info)
    {
        $authParams = array(
            'op_id'      => $this->product->getOpId(),
            'sid'        => $this->getSid(),
            'game_id'    => static::GAME_ID,
            'account'    => $info->getAccount(),
            'ip'         => $info->getLoginIp(),
            'adult_flag' => (int)!$info->isEnableAntiAddiction(),
            'game_time'  => $info->getGameTime(),
            'ad_info'    => null,
        );

        $urlData          = parse_url($this->getUrl('Login', $authParams));
        $urlData['port']  = isset($urlData['port']) ? $urlData['port'] : 80;
        $urlData['query'] = isset($urlData['query']) ? '?' . $urlData['query'] : null;

        $fp = @fsockopen($urlData['host'], $urlData['port'], $errno, $errstr, 5);
        if (!$fp) {
            throw new \RuntimeException($this->product->getId() . "login error($errno):" . $errstr);
        } else {
            $out = "GET {$urlData['path']}{$urlData['query']} HTTP/1.1\r\n";
            $out .= "Host: {$urlData['host']}\r\n";
            $out .= "Connection: Close\r\n\r\n";

            fwrite($fp, $out);
            $content = '';
            while (!feof($fp)) {
                $content .= fgets($fp, 1024);
            }
            fclose($fp);
        }

        $response = Zend_Http_Response::fromString($content);
        if ($location = $response->getHeader('Location')) {
            return (string)$location;
        } else {
            Logger::emerg(
                $this->product->getId() . 'login error:' . $response->getBody(), $authParams
            );
            return false;
        }
    }

    protected function getUrl($method, array $authParams)
    {
        $api = 'http://up.uuzu.com/api/commonAPI/';
        $authParams += array('time' => time());

        $query = '';
        foreach ($authParams as $key => $val) {
            $query .= "&$key=$val";
        }

        $auth   = base64_encode(ltrim($query, '&'));
        $params = array(
            'auth'   => $auth,
            'verify' => md5($auth . $this->product->getKey()),
        );

        return $api . $method . '?' . http_build_query($params);
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
        if (APPLICATION_ENV != 'production') {
            return true;
        }

        $authParams = array(
            'op_id'      => $this->product->getOpId(),
            'sid'        => $this->getSid(),
            'game_id'    => static::GAME_ID,
            'account'    => $account,
            'order_id'   => $consume->getId(),
            'game_money' => $consume->getGold(),
            'u_money'    => $consume->getGold() / 10,
        );

        $url    = $this->getUrl('charge', $authParams);
        $result = json_decode(file_get_contents($url), true);

        if ($result['status'] == '0') {
            return true;
        } else {
            Logger::emerg($this->product->getId() . 'add gold error!' . $url, $result);
            return false;
        }
    }
}

