<?php
/**
 * platform GiftDbTable.php
 * @DateTime 13-8-7 下午4:09
 */

namespace My\Product\Gift;

/**
 * Class GiftDbTable
 * @package My\Product\Node\Gift
 * @author Xiemaomao
 * @version $Id: GiftDbTable.php 1279 2014-01-24 01:13:41Z maomao $
 */
class GiftDbTable extends \Zend_Db_Table
{
    /**
     * @param $cdkey
     * @return null|\Zend_Db_Table_Row_Abstract
     */
    public function queryCdkey($cdkey)
    {
        return $this->select()->from($this->_name)->where('cdkey=?', $cdkey)->query()->fetch();
    }

    /**
     * @param $account
     * @param $cdkey
     * @param $info
     * @return bool
     */
    public function activeCdkey($account, $cdkey, $info)
    {
        return (bool) $this->update(
            array(
                'account' => $account,
                'info'    => $info,
                'status'  => 1,
            ),
            array(
                'cdkey=?'   => $cdkey,
                'status=?' => 0,
            )
        );
    }

    public function addCdkey($cdkey)
    {
        $this->insert(array('cdkey' => $cdkey));
    }

    /**
     * @param $account
     * @param $keyFront
     * @return bool|string
     */
    public function isActive($account, $keyFront)
    {
        $result = $this->select()
            ->from($this->_name, 'status')
            ->where('cdkey like ?', $keyFront . '%')
            ->where('account=?', $account)
            ->where('status=?', 1)
            ->query()->fetchAll();

        return count($result);
    }

    public function getTotal($keys)
    {
        $select = $this->select()->from(
            $this->_name,
            array('total' => 'count(1)', 'used' => 'count(if(status = 1, 1, null))')
        );
        foreach ($keys as $key) {
            $select->orWhere('cdkey like ?', $key . '%');
        }
        return $select->query()->fetch();
    }
}