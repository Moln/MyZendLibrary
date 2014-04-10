<?php
/**
 * platform Hash.php
 * @DateTime 14-1-16 下午3:48
 */

namespace My\AntiRobot\Validator;

/**
 * 传递参数验证
 *
 * 配置:
 * <code>
 * array(
 *  'requestName' => 'sign',
 *  'key' => 'key',
 *  'implodeValues' => array('account', 'password', 'captcha'),
 * );
 * </code>
 * sign = md5(implodeValues+refererHost+key);
 *
 * @package My\AntiRobot\Validator
 * @author Xiemaomao
 * @version $Id: Hash.php 1329 2014-03-13 00:02:24Z maomao $
 */
class Hash extends AbstractValidator
{
    private $requestName = 'sign', $key, $implodeValues = array();

    const INVALID_HASH = 'INVALID_HASH';
    protected $messages = array(
        self::INVALID_HASH => 'Invalid hash.',
    );

    public function setRequestName($requestName)
    {
        $this->requestName = $requestName;
        return $this;
    }

    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    /**
     * 设置要加密的参数
     * @param array $implodeValues
     * @return $this
     */
    public function setImplodeValues(array $implodeValues)
    {
        $this->implodeValues = $implodeValues;
        return $this;
    }

    public function isValid()
    {
        if (!$this->key) {
            throw new \InvalidArgumentException('Unknown key.');
        }

        $signStr = '';
        foreach ($this->implodeValues as $val) {
            $signStr .= $this->getRequest()->getParam($val);
        }

        $url = $this->getRequest()->getHeader('referer');
        $url = parse_url($url) + array('host' => null);

        $sign = md5($signStr . $url['host'] . $this->key);
        if ($this->getRequest()->getParam($this->requestName) == $sign) {
            return true;
        } else {
            $this->setError(self::INVALID_HASH);
            return false;
        }
    }
}