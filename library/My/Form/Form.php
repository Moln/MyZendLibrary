<?php
namespace My\Form;
use My\Captcha\Image;
use Zend_Form, Zend_Form_Element, Zend_Translate, Zend_Controller_Front,
    Zend_Validate;

/**
 * Form
 *
 * @author xiemaomao
 * @version $Id: Form.php 1305 2014-01-28 02:20:34Z maomao $
 */
class Form extends Zend_Form
{
    public function __construct($options = null)
    {
        $this->addPrefixPath('My\Form\Element', 'My/Form/Element', self::ELEMENT);
        $this->addPrefixPath('My\Form\Decorator', 'My/Form/Decorator', self::DECORATOR);

        $this->addElementPrefixPath('My\Validate', 'My/Validate',
            Zend_Form_Element::VALIDATE);
        parent::__construct($options);
    }

    public static function callStatic()
    {
        $translate = new Zend_Translate('array', include ('Form.zh.php'), 'zh');
        Zend_Form::setDefaultTranslator($translate);

        Zend_Validate::addDefaultNamespaces('My\Validate');
        // Zend_Validate_Abstract::setDefaultTranslator($translate);
    }

    /**
     *
     * @return \Zend_Controller_Request_Http
     */
    public static function getRequest()
    {
        return Zend_Controller_Front::getInstance()->getRequest();
    }

    public function getFirstMessage()
    {
        $result = [];
        foreach ($this->getMessages() as $filed => $error) {
            if (!empty($error)) {
                $result[$filed] = is_array($error) ? current($error) : $error;
            }
        }
        return $result;
    }

    public function addCaptcha(array $attrs = array())
    {
        $this->addElement(
            'captcha', 'captcha', $attrs + array(
                'label'   => '验证码',
                'captcha' => Image::create(),
                'decorators' => ['captcha'],
            )
        );
    }
}Form::callStatic();