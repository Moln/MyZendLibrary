<?php
/**
 * Platform Date.php
 * @author   maomao
 * @DateTime 12-6-26 下午1:06
 * @version  $Id: Date.php 790 2013-03-15 08:56:56Z maomao $
 */
namespace My\Form\Element;

/**
 * HTML5 input[type=date]
 * Class Date
 * @package My\Form\Element
 */
class Date extends \Zend_Form_Element_Xhtml
{
    /**
     * Default form view helper to use for rendering
     * @var string
     */
    public $helper = 'formDate';
}