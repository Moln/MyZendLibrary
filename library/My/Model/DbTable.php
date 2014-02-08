<?php
namespace My\Model;
use RuntimeException,
    Zend_Db_Table_Abstract,
    Zend_Paginator,
    Zend_Controller_Front,
    Zend_Registry,
    PDO;

/**
 * DbTable
 *
 * @author xiemaomao
 * @version $Id: DbTable.php 790 2013-03-15 08:56:56Z maomao $
 * @method array fetchKeyPair($columns = '*', $where = [], $order = [], $group = [], $limit = null, $offset = null)
 * @method array fetchColumn($columns = '*', $where = [], $order = [], $group = [], $limit = null, $offset = null)
 * @method array fetchAssoc($columns = '*', $where = [], $order = [], $group = [], $limit = null, $offset = null)
 * @method array fetchUnique($columns = '*', $where = [], $order = [], $group = [], $limit = null, $offset = null)
 * @method array fetchGroup($columns = '*', $where = [], $order = [], $group = [], $limit = null, $offset = null)
 */
abstract class DbTable extends Zend_Db_Table_Abstract
{
    const DUPLICATE = 1;
    const REPLACE = 2;

    protected $dbConfig;

    public function __construct($config = array())
    {
        if (isset($this->dbConfig)) {
            $config = ((array) $config) + array(
//                 self::ADAPTER => Zend_Db::factory($iniConfig['adapter'], $iniConfig)
            );
        }
        $rowClass = str_replace('_Model_DbTable_', '_Model_', get_class($this));
        if (class_exists($rowClass)) {
            $this->setRowClass($rowClass);
        }
        parent::__construct($config);
    }

    /**
     * @return array
     */
    public function getPrimary()
    {
        return (array) $this->_primary;
    }

    /**
     * 分页查询
     * @param array|\Zend_Db_Table_Select $where
     * @param array $order
     * @param array|string $group
     * @return Zend_Paginator
     */
    public function fetchPaginator($where = null, $order = null, $group = null)
    {
        if (!($where instanceof \Zend_Db_Table_Select)) {
            $select = $this->select();

            if ($where !== null) {
                $this->_where($select, $where);
            }

            if ($order !== null) {
                $this->_order($select, $order);
            }

            if ($group !== null) {
                $select->group($group);
            }
        } else {
            $select = $where;
        }

        $paginator = Zend_Paginator::factory($select);
        $front     = Zend_Controller_Front::getInstance();
        $request   = $front->getRequest();
        $pageSize  = $request->getParam('numPerPage',
                isset(Zend_Registry::get('my.options')->pagesize) ? Zend_Registry::get('my.options')->pagesize : 20);

        $paginator->setDefaultItemCountPerPage($pageSize);
        $paginator->setCurrentPageNumber($request->getParam('page', 1));
        return $paginator;
    }

    /**
     * fetch model
     *
     * - fetchKeyPair ($columns, $where, $order, $group, $limit, $offset)
     * - fetchColumn  ( ... )
     * - fetchUnique  ( ... )
     * - fetchGroup   ( ... )
     * - fetchBoth    ( ... )
     *
     * @param string $method
     * @param array  $params
     *
     * @throws \RuntimeException
     * @return array
     */
    public function __call($method, $params)
    {
        if (substr($method, 0, 5) == 'fetch') {
            $model = 'PDO::FETCH'
                   . strtoupper(preg_replace('/([A-Z])/', '_$1', substr($method, 5)));

            if (!defined($model)) {
                throw new RuntimeException("未定义的常量($model), 不能使用($method)");
            }

            $select = $this->select();

            if (!empty($params[0])) {
                $select->from($this->_name, $params[0]);
            } else {
                $select->from($this->_name);
            }

            if (!empty($params[1])) {
                $this->_where($select, $params[1]);
            }

            if (!empty($params[2])) {
                $this->_order($select, $params[2]);
            }

            if (!empty($params[3])) {
                $select->group($params[3]);
            }

            if (!empty($params[4])) {
                $select->limit($params[4], empty($params[5]) ? null : $params[5]);
            }

            return $select->query()->fetchAll(constant($model));
        }

        throw new RuntimeException("未定义方法($method)");
    }


    /**
     * 查询表总数
     * @param null $where
     *
     * @return string
     */
    public function fetchCount($where = null)
    {
        $select = $this->select()
                       ->from($this->_name, 'count(1)');

        if ($where !== null) {
            $this->_where($select, $where);
        }

        return $select->query()->fetchColumn();
    }

    /**
     * 执行 INSERT ... on DUPLICATE KEY UPDATE 语句
     * @param array $data
     *
     * @return bool|string
     */
    public function insertDuplicate(array $data)
    {
        $query  = 'INSERT INTO `' . $this->_name . '` SET ';
        $query1 = $query2 = $bind = array();
        foreach ($data as $key => $val) {
            $query1[] = "`$key`=?";
            $query2[] = "`$key`=values(`$key`)";
            $bind[]   = $val;
        }

        $query .= implode(',', $query1)
                . ' ON DUPLICATE KEY UPDATE '
                . implode(',', $query2);

        $sth    = $this->getAdapter()->prepare($query);
        if ($sth->execute($bind)) {
            return $this->getAdapter()->lastInsertId();
        } else {
            return false;
        }
    }

    /**
     * Mysql replace 语句
     * @param $data
     *
     * @return bool|string
     */
    public function replace($data)
    {
        $query  = 'REPLACE `' . $this->_name . '` SET ';
        $query1 = $bind = array();
        foreach ($data as $key => $val) {
            $query1[] = "`$key`=?";
            $bind[]   = $val;
        }

        $query .= implode(',', $query1);
        $sth    = $this->getAdapter()->prepare($query);
        if ($sth->execute($bind)) {
            return $this->getAdapter()->lastInsertId();
        } else {
            return false;
        }
    }

    /**
     *
     * @param string $id
     * @return mixed
     */
    protected function loadCache($id)
    {
        if (null !== $this->_metadataCache) {
            return $this->getMetadataCache()->load(get_class($this) . md5($id));
        }
    }


    /**
     *
     * @param mixed $data
     * @param string $id
     * @return boolean
     */
    protected function saveCache($data, $id)
    {
        if (null !== $this->_metadataCache) {
            return $this->getMetadataCache()->save($data, get_class($this) . md5($id));
        }
    }

    /**
     *
     * @param string $id
     * @return boolean
     */
    protected function removeCache($id)
    {
        if (null !== $this->_metadataCache) {
            return $this->getMetadataCache()->remove(get_class($this) . md5($id));
        }
    }
}