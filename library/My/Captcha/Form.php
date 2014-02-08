<?php
namespace My\Captcha;

use My\Form\Form as MyForm;
/**
 * 验证码
 * @author maomao
 *
 */
abstract class Form extends MyForm
{
    /**
     * @var \Zend_Cache_Core
     */
    private $cache;
    private $count;
    private $cacheId;
    protected $tags = [], $overNumber = 4, $lifetime = 3600;

    protected function initFlagCount($prefix, $id)
    {
        $this->cache   = \Zend_Registry::get('application.cache');
        $this->cacheId = $prefix . md5($id);

        if (!($this->count = $this->cache->load($this->cacheId))) {
            $this->count = 1;
        }
        if ($this->count > $this->overNumber) {
            $this->addCaptcha();
        }
    }

    public function hasCaptcha()
    {
        return $this->count > $this->overNumber;
    }

    public function plusFlagCount()
    {
        if ($this->count == $this->overNumber) {
            $this->addCaptcha();
        }
        $this->cache->save(++$this->count, $this->cacheId, $this->tags, $this->lifetime);
    }

    public function addCaptcha()
    {
        $this->addElement(
            'captcha', 'captcha', array(
                'label'   => '验证码',
                'captcha' => Image::create(),
                'ignore'  => true,
                'decorators' => ['captcha'],
            )
        );
    }

    public function clearFlagCount()
    {
        $this->removeElement('captcha');
        $this->cache->remove($this->cacheId);
    }
}