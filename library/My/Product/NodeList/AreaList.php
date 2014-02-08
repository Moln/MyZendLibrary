<?php
namespace My\Product\NodeList;


/**
 * AreaList
 * @author   maomao
 * @DateTime 12-8-9 下午3:52
 * @version  $Id: AreaList.php 1310 2014-02-07 02:27:31Z maomao $
 */
class AreaList extends AbstractList
{
    protected $nodeClass = 'AreaNode';

    /**
     * @param $pointer
     *
     * @return \My\Product\Node\AreaNode
     */
    protected function loadNode($pointer)
    {
        $className = $this->product->getLoader()->load('Node\\' . $this->nodeClass);
        return new $className($this->data[$pointer], $this->product);
    }

    public function setNodeClass($nodeClass)
    {
        $this->nodeClass = $nodeClass;
        return $this;
    }

    public function __call($name, $params)
    {
        foreach ($this as $area) {
            if (method_exists($area, $name)) {
                call_user_func(array($area, $name), $params);
            }
        }
    }
}
