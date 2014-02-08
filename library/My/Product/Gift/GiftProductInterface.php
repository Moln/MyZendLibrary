<?php
/**
 * platform GiftProductInterface.php
 * @DateTime 13-8-8 上午10:21
 */

namespace My\Product\Gift;


interface GiftProductInterface {

    /**
     * 生成A类礼品卡
     *
     * A 0 VVVV VVVVVVVV 14位长度
     * @param int $giftId 礼品卡ID
     * @param int $number 数量
     * @return array 卡数据
     */
    public function giftGenerateA($giftId, $number);

    /**
     * 生成B类礼品卡
     *
     * B 0 VVVV VVVVVVVV 14位长度
     * @param int $giftId 礼品卡ID
     * @param string $account 账号
     *
     * @return int 卡号
     */
    public function giftGenerateB($giftId, $account);

    /**
     * 卡状态查询
     * @param string$cdkey
     * @return array|null
     */
    public function queryCdkey($cdkey);

    /**
     * 卡规则验证
     * @param $cdkey
     * @return bool
     */
    public function isValidCdkey($cdkey);

    public function giftTotal($keys);
}