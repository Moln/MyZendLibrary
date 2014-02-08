<?php
namespace My\Product\NodeList;

use My\Product\Exception\ErrorAreaException;
use My\Product\Product;
use My\Product\Node\AbstractNode;

/**
 * AbstractList
 * @author   maomao
 * @DateTime 12-8-9 下午3:52
 * @version  $Id: AbstractList.php 1205 2013-08-07 10:52:02Z maomao $
 */
abstract class AbstractList implements \SeekableIterator, \Countable, \ArrayAccess
{
    protected $data = array();
    protected $keyPair = array();
    private $pointer, $count;
    protected $product;

    public function __construct(array $list, Product $product)
    {
        $this->data    = $list;
        $this->count   = count($list);
        $this->product = $product;
        foreach ($list as $key => $item) {
            if (!isset($item['id'])) {
                throw new \RuntimeException('Error config, unknown list id');
            }
            $this->keyPair[$item['id']] = $key;

            if (isset($item['index'])) {
                $this->keyPair[$item['index']] = $key;
            }
        }
    }

    /**
     * @param $id
     *
     * @throws \My\Product\Exception\ErrorAreaException
     * @return \My\Product\Node\AbstractNode
     */
    public function get($id)
    {
        if (!isset($this->keyPair[$id])) {
            throw new ErrorAreaException('Unknown area: ' . $id);
        }
        return $this->offsetGet($this->keyPair[$id]);
    }

    public function has($id)
    {
        return isset($this->keyPair[$id]);
    }

    public function getKeyPair()
    {
        $keyPair = [];
        foreach ($this->data as $item) {
            $keyPair[$item['id']] = $item['name'];
        }

        return $keyPair;
    }

    /**
     * @param $pointer
     *
     * @return AbstractNode
     */
    abstract protected function loadNode($pointer);

    /**
     *
     * @return AbstractNode
     */
    public function current()
    {
        if ($this->valid() === false) {
            return null;
        }

        if (!$this->data[$this->pointer] instanceof AbstractNode) {
            $this->data[$this->pointer] = $this->loadNode($this->pointer);
        }

        return $this->data[$this->pointer];
    }

    /**
     * @return void
     */
    public function next()
    {
        ++$this->pointer;
    }

    /**
     *
     * @return int
     */
    public function key()
    {
        return $this->pointer;
    }

    /**
     * @return boolean
     */
    public function valid()
    {
        return $this->pointer >= 0 && $this->pointer < $this->count;
    }

    /**
     *
     * @return void
     */
    public function rewind()
    {
        $this->pointer = 0;
    }

    /**
     *
     * @param int $offset
     *
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->data[(int) $offset]);
    }

    /**
     *
     * @param int $offset
     *
     * @throws \OutOfRangeException
     * @return AbstractNode
     */
    public function offsetGet($offset)
    {
        $offset = (int) $offset;
        if ($offset < 0 || $offset >= $this->count) {
            throw new \OutOfRangeException("Illegal index $offset");
        }
        $this->pointer = $offset;

        return $this->current();
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
    }

    /**
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
    }

    /**
     *
     * @return int
     */
    public function count()
    {
        return $this->count;
    }

    /**
     *
     * @param int $position
     *
     * @throws \OutOfRangeException
     * @return void
     */
    public function seek($position)
    {
        $position = (int) $position;
        if ($position < 0 || $position >= $this->count()) {
            throw new \OutOfRangeException("Illegal index $position");
        }
        $this->pointer = $position;
    }

    public function toArray()
    {
        return $this->data;
    }
}
