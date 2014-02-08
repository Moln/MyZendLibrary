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
 * @version $Id: HttpHead.php 1279 2014-01-24 01:13:41Z maomao $
 */
class HttpHead extends AbstractValidator
{
    const INVALID = 'invalid';
    const INVALID_STRING = 'invalidString';
    const EMPTY_HEAD = 'emptyHead';

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
        if (empty($_SERVER['HTTP_USER_AGENT']) ||
            empty($_SERVER['HTTP_ACCEPT']) ||
            empty($_SERVER['HTTP_CONNECTION']) ||
            empty($_SERVER['HTTP_ACCEPT_ENCODING']) ||
            empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            return false;
        }

        //USER_AGENT 不能含有 WinHttpRequest
        //USER_AGENT 必须含有 Mozilla
        //HTTP_CONNECTION 必须为 Keep-Alive
        if (false !== strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'WinHttpRequest') ||
            false === strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'mozilla') ||
            strtolower($_SERVER['HTTP_CONNECTION']) != 'keep-alive'
        ) {
            return false;
        }

        foreach ($this->validMore as $validCallback) {
            if (!$validCallback($this->getRequest())) {
                return false;
            }
        }
        return true;
    }
}