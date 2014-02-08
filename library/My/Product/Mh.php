<?php
/**
 * Mh.php
 * @DateTime 12-12-13 下午5:19
 */
namespace My\Product;
use My\Stdlib\Fragment\Instance;

/**
 * 新梦幻之城
 * Class Mh
 * @package My\Product
 * @author Xiemaomao
 * @version $Id: Mh.php 1275 2014-01-23 23:10:26Z maomao $
 */
class Mh extends Product
{
    use Instance;
    protected $areaNodeClass = 'MhAreaNode';

    protected $genid, $areaStart;

    public function setAreaStart($areaStart)
    {
        $this->areaStart = $areaStart;
        return $this;
    }

    public function getAreaStart()
    {
        return $this->areaStart;
    }

    public function setGenid($genid)
    {
        $this->genid = $genid;
        return $this;
    }

    public function getGenid()
    {
        return $this->genid;
    }
}