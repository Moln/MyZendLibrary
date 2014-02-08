<?php
/**
 * platform SourceUrl.php
 * @DateTime 13-12-16 下午1:47
 */

namespace My\View\Helper;

/**
 * Class SourceUrl
 * @package My\View\Helper
 * @author Xiemaomao
 * @version $Id: SourceUrl.php 1279 2014-01-24 01:13:41Z maomao $
 */
class SourceUrl extends \Zend_View_Helper_Abstract
{
    public function sourceUrl($url)
    {
        return trim($this->view->staticUrl, '/') . '/' . trim($url, '/');
    }
}