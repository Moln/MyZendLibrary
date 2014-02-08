<?php
namespace My\Product\Node;
use My\Log\Logger;

/**
 * 大侠传
 * @package My\Product\Node
 * @author watt
 * @DateTime 12-9-27 下午6:20
 * @version $Id: DxzAreaNode.php 1266 2013-10-22 10:29:07Z maomao $
 * @property \My\Product\Dxz $product
 */
class DxzAreaNode extends UuzuAreaNode implements LoginInterface
{
    const GAME_ID = 8;

    /**
     * 排行
     * @param string $type  type:rw（人物）,zl(战力),sw(声望),yp,jj（天梯）,bh（帮会）
     * @param int $limit
     *
     * @return bool
     */
    public function rank($type, $limit)
    {

        $authParams = array(
            'op_id'      => $this->product->getOpId(),
            'sid'        => $this->getSid(),
            'game_id'    => static::GAME_ID,
            'type'       => $type,
            'limit'      => $limit,
        );

        $url    = $this->getUrl('rank', $authParams);
        $result = json_decode(file_get_contents($url), true);

        if ($result['status'] == '0') {
            return $result['data'];
        } else {
            Logger::emerg('Dxz rank error!', $result);
            return false;
        }
    }



}

