<?php
namespace My\Product\Node;

/**
 * 抽象区节点
 * Area
 * @package My\Product\Node
 * @property string $openDate
 * @property string $index
 * @property string $enabled
 * @property string $disabledMessage
 * @property string $maintainTime
 * @author   maomao
 * @DateTime 12-8-9 下午3:29
 * @version  $Id: AreaNode.php 1247 2013-08-22 01:52:14Z maomao $
 */
abstract class AreaNode extends AbstractNode implements PaymentInterface
{
    protected $_data = array(
        'id'    => null,
        'index' => null,
        'name' => null,
        'host' => null,
        'openDate' => null,
        'enabled' => true,
        'disabledMessage' => null,
        'maintainTime' => array(),
    );

    public function setIndex($index)
    {
        $this->index = $index;
        return $this;
    }

    public function getIndex()
    {
        return $this->index;
    }

    /**
     * 设置开服时间
     * @param $openDate
     *
     * @return $this
     */
    public function setOpenDate($openDate)
    {
        $this->openDate = $openDate;
        return $this;
    }

    /**
     * 开服时间
     * @return string
     */
    public function getOpenDate()
    {
        return $this->openDate;
    }

    /**
     * 是否开服
     * @return bool
     */
    public function isOpened()
    {
        if (empty($this->openDate)) {
            return true;
        } else {
            return time() >= strtotime($this->openDate);
        }
    }

    public function isEnabled()
    {
        if (!$this->enabled) {
            return false;
        }

        if (!empty($this->maintainTime)) {

            $s = is_numeric($this->maintainTime[0]) ? $this->maintainTime[0] : strtotime($this->maintainTime[0]);
            $e = is_numeric($this->maintainTime[1]) ? $this->maintainTime[1] : strtotime($this->maintainTime[1]);
            if ($s < time() && $e > time()) {
                return false;
            }
        }
        return true;
    }

    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * 获取区的ID路径
     * @return string
     */
    public function getIdPath()
    {
        return $this->product->getId() . '@' . $this->getId();
    }

    /**
     * 获取区全名
     * @return string
     */
    public function getNamePath()
    {
        return $this->product->getName() . '@' . $this->getName();
    }

    public function setDisabledMessage($disabledMessage)
    {
        $this->disabledMessage = $disabledMessage;
        return $this;
    }

    public function getDisabledMessage()
    {
        return $this->disabledMessage;
    }

    /**
     * @param array $maintainTime
     * @return AreaNode
     */
    public function setMaintainTime(array $maintainTime)
    {
        $this->maintainTime = $maintainTime;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMaintainTime()
    {
        return $this->maintainTime;
    }
}