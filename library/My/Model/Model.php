<?php
namespace My\Model;
use ArrayAccess, IteratorAggregate, ArrayIterator, Zend_Db_Table_Abstract;

/**
 * Model
 *
 * @author maomao
 * @version $Id: Model.php 1348 2014-04-03 18:24:19Z maomao $
 */
abstract class Model implements ArrayAccess, IteratorAggregate
{
    const DUPLICATE = DbTable::DUPLICATE;
    const REPLACE = DbTable::REPLACE;

    protected $dbTable;

    protected $_data = array();

    protected $modifiedFields = array();

    protected $primary;

    public function __construct($options = null)
    {
        if (isset($options['table']) && $options['table'] instanceof DbTable) {
            $this->dbTable = $options['table'];
            $this->_data = $options['data'];
        }

        if (empty($this->_data)) {
            foreach ($this->getTable()->info('cols') as $key) {
                $this->_data[$key] = null;
            }
        }

        if (null !== $options) {
            $this->setFromArray($options);
        }

        $this->init();
    }

    public function init()
    {}

    /**
     * Constructs where statement for retrieving row(s).
     * @return array
     */
    protected function getWhereQuery()
    {
        $where = array();
        $db = $this->getTable()->getAdapter();
        $info = $this->getTable()->info();
        $metadata = $info[Zend_Db_Table_Abstract::METADATA];

        // retrieve recently updated row using primary keys
        $where = array();
        foreach ($this->getPrimary() as $column) {
            $value = $this->__get($column);
            if (empty($value)) continue;

            $type = $metadata[$column]['DATA_TYPE'];
            $columnName = $db->quoteIdentifier($column, true);
            $where[] = $db->quoteInto("{$columnName} = ?", $value, $type);
        }

        return $where;
    }

    /**
     *
     * @param string $columnName
     * @param mixed $value
     *
     * @throws \RuntimeException
     * @return void
     */
    public function __set($columnName, $value)
    {
        if (!array_key_exists($columnName, $this->_data)) {
            throw new \RuntimeException("Specified column \"$columnName\" is not in the row");
        }
        $this->_data[$columnName] = $value;
        $this->modifiedFields[$columnName] = true;
    }

    /**
     *
     * @param string $columnName
     *
     * @throws \RuntimeException
     * @return mixed
     */
    public function __get($columnName)
    {
        if (!array_key_exists($columnName, $this->_data)) {
            throw new \RuntimeException("Specified column \"$columnName\" is not in the row");
        }
        return $this->_data[$columnName];
    }

    /**
     * Test existence of row field
     *
     * @param  string  $columnName   The column key.
     * @return boolean
     */
    public function __isset($columnName)
    {
        return array_key_exists($columnName, $this->_data);
    }

    public function __unset($columnName)
    {
        if (!array_key_exists($columnName, $this->_data)) {
            throw new \RuntimeException("Specified column \"$columnName\" is not in the row");
        }
        unset($this->_data[$columnName]);
        return $this;
    }

    public function getIterator()
    {
        return new ArrayIterator((array) $this->_data);
    }

    /**
     * Proxy to __isset
     * Required by the ArrayAccess implementation
     *
     * @param string $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return $this->__isset($offset);
    }

    /**
     * Proxy to __get
     * Required by the ArrayAccess implementation
     *
     * @param string $offset
     * @return string
     */
     public function offsetGet($offset)
     {
         return $this->__get($offset);
     }

     /**
      * Proxy to __set
      * Required by the ArrayAccess implementation
      *
      * @param string $offset
      * @param mixed $value
      */
     public function offsetSet($offset, $value)
     {
         $this->__set($offset, $value);
     }

     /**
      * Proxy to __unset
      * Required by the ArrayAccess implementation
      *
      * @param string $offset
      * @return \My\Model\Model|void
      */
     public function offsetUnset($offset)
     {
         return $this->__unset($offset);
     }

    /**
     * Returns the column/value data as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->_data;
    }

    protected function getModifiedFields()
    {
        $data = array_intersect_key($this->_data, $this->modifiedFields);
        foreach ($this->getPrimary() as $column) {
            unset($data[$column]);
        }
    }

    /**
     *
     * @param array $data
     *
     * @return Model
     */
    public function setFromArray(array $data)
    {
        $data = array_intersect_key($data, $this->_data);

        foreach ($data as $columnName => $value) {
            $this->__set($columnName, $value);
        }

        return $this;
    }

    /**
     *
     * @return DbTable
     */
    public function getTable()
    {
        if (null === $this->dbTable) {
            $className = str_replace('_Model_', '_Model_DbTable_', get_class($this));
            if (method_exists($className, 'getInstance')) {
                $this->dbTable = $className::getInstance();
            } else {
                $this->dbTable = new $className();
            }
        }
        return $this->dbTable;
    }

    /**
     * Insert or update model.
     *
     * @param int $method
     * @return bool|int
     */
    public function save($method = null)
    {
        if ($method == Model::DUPLICATE) {
            $id = $this->getTable()->insertDuplicate($this->_data);
        } else if ($method == Model::REPLACE) {
            $id = $this->getTable()->replace($this->_data);
        } else {

            $data = array_intersect_key($this->_data, $this->modifiedFields);

            $insert = false;
            foreach ($this->getPrimary() as $primary) {
                if (empty($this->_data[$primary])) {
                    $insert = true;
                    break;
                }
                unset($data[$primary]);
            }

            if ($insert) {
                $id = $this->getTable()->insert($data);
            } else {
                $where = $this->getWhereQuery();
                $this->getTable()->update($data, $where);
                $this->modifiedFields = array();
                return 0;
            }
        }

        if ($id) {
            $primary = $this->getPrimary();
            $this->_data[array_shift($primary)] = $id;
        }

        $this->modifiedFields = array();
        return $id;
    }

    /**
     * @return array
     */
    protected function getPrimary()
    {
        if (!$this->primary) {
            $this->primary = $this->getTable()->info('primary');
        }
        return $this->primary;
    }

    public function delete()
    {
        $where = $this->getWhereQuery();
        if (empty($where)) {
            throw new \RuntimeException('删除失败,未知主键');
        }
        return $this->getTable()->delete($where);
    }

    public function __sleep()
    {
        return ['_data', 'modifiedFields', 'primary'];
    }
}