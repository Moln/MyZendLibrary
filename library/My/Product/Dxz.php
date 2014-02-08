<?php
namespace My\Product;
use My\Stdlib\Fragment\Instance;

/**
 * 大侠传
 * @package My\Product
 * @author   maomao
 * @DateTime 12-5-24 下午1:42
 * @version  $Id: Dxz.php 1275 2014-01-23 23:10:26Z maomao $
 */
class Dxz extends Product
{
    use Instance;
    protected $areaNodeClass = 'DxzAreaNode';
    protected $areaListClass = 'DxzAreaList';
    protected $opId, $gameId = 8, $api;

    public function setApi($api)
    {
        $this->api = $api;
        return $this;
    }

    public function getApi()
    {
        return $this->api;
    }

    public function setGameId($gameId)
    {
        $this->gameId = $gameId;
        return $this;
    }

    public function getGameId()
    {
        return $this->gameId;
    }

    public function setOpId($opId)
    {
        $this->opId = $opId;
        return $this;
    }

    public function getOpId()
    {
        return $this->opId;
    }

    public function getStartIndex()
    {
        return $this->getOpId()*1000000 + $this->getGameId()*10000;
    }
}