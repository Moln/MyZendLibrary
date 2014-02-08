<?php
namespace My\Product\NodeList;
use My\Product\Node\ServerNode;

/**
 * ServerList
 * @author   maomao
 * @DateTime 12-8-9 下午3:52
 * @version  $Id: ServerList.php 790 2013-03-15 08:56:56Z maomao $
 */
class ServerList extends AbstractList
{

    /**
     * @param $pointer
     *
     * @return \My\Product\Node\AreaNode
     */
    protected function loadNode($pointer)
    {
        return new ServerNode($this->data[$pointer]);
    }
}
