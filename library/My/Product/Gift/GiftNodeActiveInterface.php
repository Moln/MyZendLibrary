<?php
/**
 * platform GiftNodeActiveInterface.php
 * @DateTime 13-8-8 上午10:29
 */

namespace My\Product\Gift;


interface GiftNodeActiveInterface {

    public function giftActive($account, $cdkey);
}