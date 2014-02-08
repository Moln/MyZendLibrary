<?php
namespace My\Token\DbTable;

/**
 * Matrix
 * @author   maomao
 * @DateTime 12-6-11 下午3:07
 * @version  $Id: Matrix.php 790 2013-03-15 08:56:56Z maomao $
 */
class Matrix extends \Zend_Db_Table_Abstract
{
    protected $_name = 'token_matrix';

    protected $_primary = 'id';

    public function bind($account, $sn, $data)
    {
        $this->insert(['account' => $account, 'sn' => $sn, 'data' => $data]);
    }

    /**
     * @param string $account
     *
     * @return null|\Zend_Db_Table_Row_Abstract
     */
    public function getByAccount($account)
    {
        return $this->fetchRow(['account=?' => $account]);
    }

    public function remove($account)
    {
        return $this->delete(['account=?' => $account]);
    }
}
