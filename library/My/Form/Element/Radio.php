<?php
namespace My\Form\Element;

/**
 * Radio form element
 *
 * @category   Zend
 * @package    Zend_Form
 * @subpackage Element
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Radio.php 790 2013-03-15 08:56:56Z maomao $
 */
class Radio extends \Zend_Form_Element_Multi
{
    /**
     * Use formRadio view helper by default
     * @var string
     */
    public $helper = 'formRadio';
}
