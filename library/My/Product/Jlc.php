<?php
namespace My\Product;
use My\Stdlib\Fragment\Instance;

/**
 *
 * @author   maomao
 * @DateTime 12-5-24 下午1:42
 * @version  $Id: Jlc.php 1312 2014-02-07 22:22:16Z maomao $
 */
class Jlc extends Product
{
    use Instance;

    protected $areaNodeClass = 'JlcAreaNode';
    protected $opId, $gameId, $api, $payKey, $payApi;

    public function setPayApi($payApi)
    {
        $this->payApi = $payApi;
    }

    public function getPayApi()
    {
        return $this->payApi;
    }

    public function setPayKey($payKey)
    {
        $this->payKey = $payKey;
    }

    public function getPayKey()
    {
        return $this->payKey;
    }

    public function setGameId($gameId)
    {
        $this->gameId = $gameId;
    }

    public function getGameId()
    {
        return $this->gameId;
    }

    public function setOpId($opId)
    {
        $this->opId = $opId;
    }

    public function getOpId()
    {
        return $this->opId;
    }

    public function setApi($api)
    {
        $this->api = $api;
    }

    public function getApi()
    {
        return $this->api;
    }


}