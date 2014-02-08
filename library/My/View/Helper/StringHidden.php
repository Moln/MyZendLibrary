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
class StringHidden extends \Zend_View_Helper_Abstract
{
    /**
     * @param string $string
     * @param int $start
     * @param int $end
     * @return string
     */
    public function stringHidden($string, $start = 1, $end = 0, $charset = null) {
        if (!$string) {
            return '';
        }

        if ($charset) {
            return iconv_substr($string, 0, $start, $charset)
                . str_repeat('*', iconv_strlen($string, $charset)-$start-$end)
                . ($end ? iconv_substr($string, -$end, $end, $charset) : '');
        } else {
            return substr($string, 0, $start)
                . str_repeat('*', strlen($string)-$start-$end)
                . ($end ? substr($string, -$end) : '');
        }
    }
}
