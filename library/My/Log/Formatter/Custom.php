<?php
/**
 * IG_Log_Formatter_Custom
 *
 * @author mmxie
 * @version $Id: Custom.php 790 2013-03-15 08:56:56Z maomao $
 */
class My_Log_Formatter_Custom extends Zend_Log_Formatter_Abstract
{
    public function format($event)
    {
        $text = '';
        unset($event['priority'], $event['priorityName']);
        foreach ($event as $key => $val) {
            $val = is_array($val) ? print_r($val, true) : $val;
            $text .= "$key: " . $val . PHP_EOL;
        }
        return $text . PHP_EOL;
    }

    /**
     * Factory for Zend_Log_Formatter_Simple classe
     *
     * @param array|Zend_Config $options
     * @return Zend_Log_Formatter_Simple
     */
    public static function factory($options)
    {
        return new self();
    }
}