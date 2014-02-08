<?php
namespace My\View\Helper;

/**
 *
 * @author maomao
 * @version
 */

/**
 * StringHidden helper
 *
 * @uses viewHelper
 */
class EmailHidden extends \Zend_View_Helper_Abstract
{
    public function emailHidden($email, $start = 1, $end = 0)
    {
        list($account, $domain) = explode('@', $email);
        return $this->view->stringHidden($account, $start, $end) . '@' . $domain;
    }
}
