<?php
/**
 * HTML5 input[type=Number]
 * @author   maomao
 * @DateTime 12-7-10 上午11:03
 * @version  $Id: Number.php 790 2013-03-15 08:56:56Z maomao $
 */
namespace My\Form\Element;

/**
 * Class Number
 * @package My\Form\Element
 */
class Number extends \Zend_Form_Element_Xhtml
{
    /**
     * Default form view helper to use for rendering
     * @var string
     */
    public $helper = 'formNumber';

    public function init()
    {
        $this->addValidator('int');
    }
}