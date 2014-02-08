<?php
namespace My\Product\Node;
use My\Product\Product;

/**
 * 抽象节点
 * Class AbstractNode
 * @package My\Product\Node
 *
 * @property string $id
 * @property string $name
 * @property string $host
 * @author   maomao
 * @DateTime 12-8-9 下午3:30
 * @version  $Id: AbstractNode.php 790 2013-03-15 08:56:56Z maomao $
 */
abstract class AbstractNode extends \My\Model\Model
{
    protected $product;

    public function __construct($options = null, Product $product)
    {
        parent::__construct($options);
        $this->product = $product;
    }

    protected $_data = array(
        'id'   => null,
        'name' => null,
        'host' => null,
    );

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    public function getProduct()
    {
        return $this->product;
    }
}