<?php
/**
 * platform FormElementLimit.php
 * @DateTime 14-1-15 下午5:38
 */

namespace My\AntiRobot\Validator;

/**
 * 表单元素频繁请求验证, 一般 用于同一账号多次请求错误
 *
 * 配置:
 * <code>
 * array(
 *  'prefix' => null,
 *  'value' => 'account',
 *  'limit'   => 5,
 *  'lifetime' => 3600,
 * );
 * </code>
 * 未使用 lockTime 属性
 *
 * @package My\AntiRobot\Validator
 * @author Xiemaomao
 * @version $Id: ValueLimit.php 1279 2014-01-24 01:13:41Z maomao $
 */
class ValueLimit extends AbstractLimitValidator implements FormResultInterface
{
    private $value, $count, $prefix;

    protected $limit = 5;

    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
        return $this;
    }

    private function setCount($count)
    {
        $this->count = $count;
        $this->getCache()->save($count, $this->getId(), array('anti'), $this->lifetime);
        return $this;
    }

    private function getCount()
    {
        if ($this->count === null) {
            $this->count = $this->getCache()->load($this->getId()) ? : 0;
        }
        return $this->count;
    }

    public function setValue($value)
    {
        if ($value instanceof \Closure) {
            $value = $value($this->getRequest());
        } else if (is_string($value)) {
            $value = $this->getRequest()->getParam($value);
        } else {
            throw new \InvalidArgumentException('Must be a string type.');
        }

        $this->value = $value;
        return $this;
    }

    private function getId()
    {
        return $this->keyPrefix . $this->prefix . md5($this->value);
    }

    /**
     * 表单验证结果
     * @param bool $formValidResult
     * @throws \InvalidArgumentException
     * @return void
     */
    public function setFormValidResult($formValidResult)
    {
        $id = $this->getId();

        if ($formValidResult) {
            $this->getCache()->remove($id);
        } else {
            $count = $this->getCount();
            $this->setCount($count+1);
        }
    }

    public function isValid()
    {
        return $this->getCount() < $this->limit;
    }
}