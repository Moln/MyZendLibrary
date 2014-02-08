<?php
/**
 * Number.php
 * @author   maomao
 * @DateTime 12-7-10 上午11:03
 * @version  $Id: Email.php 790 2013-03-15 08:56:56Z maomao $
 */
namespace My\Form\Element;

/**
 * HTML5 input[type=email]
 * Class Email
 * @package My\Form\Element
 */
class Email extends \Zend_Form_Element_Xhtml
{
    /**
     * Default form view helper to use for rendering
     * @var string
     */
    public $helper = 'formEmail';

    public function init()
    {
        $this->addValidator('EmailAddress');
        $this->addFilters(['StringTrim', 'StringToLower']);
    }
}