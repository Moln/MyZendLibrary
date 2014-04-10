<?php
namespace My\AntiRobot\Validator;

/**
 * HTTP 头部验证
 *
 * 配置:
 * <code>
 * array(
 *  'validMore' => array(function ($request) {}, function ($request) {}),
 * );
 * </code>
 * @package My\AntiRobot\Validator
 * @author Xiemaomao
 * @version $Id: HttpHead.php 1331 2014-03-17 23:01:04Z maomao $
 */
class HttpHead extends AbstractValidator
{
    const EMPTY_HEAD            = 'EMPTY_HEAD';
    const INVALID_USER_AGENT    = 'INVALID_USER_AGENT';
    const INVALID_CONNECTION    = 'INVALID_CONNECTION';
    const INVALID_CALLBACK      = 'INVALID_CALLBACK';

    protected $messages = array(
        self::EMPTY_HEAD => 'Empty head(%head%)(USER_AGENT: %HTTP_USER_AGENT%)',
        self::INVALID_USER_AGENT => 'Invalid user agent(%HTTP_USER_AGENT%)',
        self::INVALID_CONNECTION => 'Invalid connection(%HTTP_CONNECTION%)(USER_AGENT: %HTTP_USER_AGENT%)',
        self::INVALID_CALLBACK => 'Invalid callback.',
    );

    private $validMore = array();

    /**
     * @param \Closure[] $validMore
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function setValidMore(array $validMore)
    {
        foreach ($validMore as $valid) {
            if (!$valid instanceof \Closure) {
                throw new \InvalidArgumentException('Invalid argument, not closure.');
            }
        }

        $this->validMore = $validMore;
        return $this;
    }

    public function isValid()
    {
        //浏览器通用性, 浏览器的请求这些都会有
        foreach (array('USER_AGENT', 'ACCEPT', 'CONNECTION', /*'ACCEPT_ENCODING',*/ 'ACCEPT_LANGUAGE') as $head) {
            if (empty($_SERVER['HTTP_' . $head])) {
                $this->setError(self::EMPTY_HEAD, array('%head%' => $head));
                return false;
            }
        }

        //USER_AGENT 不能含有 WinHttpRequest
        //USER_AGENT 必须含有 Mozilla
        if (false !== strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'winhttprequest') ||
            false === strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'mozilla')
        ) {
            $this->setError(self::INVALID_USER_AGENT);
            return false;
        }

        //HTTP_CONNECTION 必须为 Keep-Alive
        if (strtolower($_SERVER['HTTP_CONNECTION']) != 'keep-alive'){
            $this->setError(self::INVALID_CONNECTION);
            return false;
        }

        foreach ($this->validMore as $validCallback) {
            if (!$validCallback($this->getRequest())) {
                $this->setError(self::INVALID_CALLBACK);
                return false;
            }
        }
        return true;
    }
}