<?php
namespace My\Product;
use My\Stdlib\Fragment\Instance;


/**
 * 大将军
 * Class Djj
 * @package My\Product
 * @author Xiemaomao
 * @version $Id: Djj.php 1275 2014-01-23 23:10:26Z maomao $
 */
class Djj extends Product
{
    use Instance;
    protected $areaNodeClass = 'DjjAreaNode';
    protected $opId, $gameId, $api;

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
}