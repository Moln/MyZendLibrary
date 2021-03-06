<?php
namespace My\View\Helper;

/**
 * FormDate.php
 * @author   maomao
 * @DateTime 12-6-26 下午12:26
 * @version  $Id: FormDate.php 790 2013-03-15 08:56:56Z maomao $
 */
class FormDate extends \Zend_View_Helper_FormElement
{
    public function formDate($name, $value = null, $attribs = null)
    {
//        $this->view->jQuery();

        $info = $this->_getInfo($name, $value, $attribs);
        extract($info); // name, value, attribs, options, listsep, disable

        // build the element
        $disabled = '';
        if ($disable) {
            // disabled
            $disabled = ' disabled="disabled"';
        }

        // XHTML or HTML end tag?
        $endTag = ' />';
        if (($this->view instanceof \Zend_View_Abstract) && !$this->view->doctype()->isXhtml()) {
            $endTag= '>';
        }

        $xhtml = '<input type="date"'
            . ' name="' . $this->view->escape($name) . '"'
            . ' id="' . $this->view->escape($id) . '"'
            . ' value="' . $this->view->escape($value) . '"'
            . $disabled
            . $this->_htmlAttribs($attribs)
            . $endTag;

        return $xhtml;
    }
}