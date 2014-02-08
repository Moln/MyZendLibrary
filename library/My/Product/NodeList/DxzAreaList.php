<?php
/**
 * DaxiazhuanAreaList
 * @author Xiemaomao
 * @DateTime 13-3-14 下午2:09
 * @version $Id: DxzAreaList.php 1174 2013-08-01 03:03:04Z maomao $
 */

namespace My\Product\NodeList;


/**
 * Class DaxiazhuanAreaList
 * @package My\Product\NodeList
 * @property \My\Product\Dxz $product
 */
class DxzAreaList extends AreaList
{
    /**
     * 重写方法, 兼容用 整数ID(212080001) 和 普通ID(s1)
     * @param $id
     *
     * @return \My\Product\Node\AbstractNode
     * @throws \My\Product\Exception\ErrorAreaException
     */
    public function get($id)
    {
        if (is_numeric($id)) {
            $id = 's' . ($id-$this->product->getStartIndex());
        }
        return parent::get($id);
    }

    public function has($id)
    {
        if (is_numeric($id)) {
            $id = 's' . ($id-$this->product->getStartIndex());
        }
        return parent::has($id);
    }
}