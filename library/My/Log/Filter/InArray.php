<?php
/**
 * My_Log_Filter_InArray
 *
 * @author mmxie
 * @version $Id: InArray.php 790 2013-03-15 08:56:56Z maomao $
 */
class My_Log_Filter_InArray extends Zend_Log_Filter_Abstract
{
    /**
     * @var integer
     */
    protected $_priorities;

    /**
     * @var bool
     */
    protected $_not;

    /**
     * Filter logging by $priorities.  By default, it will accept any log
     * event whose priorities value is less than or equal to $priorities.
     *
     * @param  array|string  $priorities  priorities
     * @param  bool   $not
     * @return void
     * @throws Zend_Log_Exception
     */
    public function __construct($priorities, $not = false)
    {
        if (is_string($priorities)) {
            $priorities = explode(',', $priorities);
        }

        // Add support for constants
        foreach ((array) $priorities as $key => $val) {
            if (!is_numeric($val) && defined($val)) {
                $priorities[$key] = constant($val);
            }
        }

        $this->_priorities = (array) $priorities;
        $this->_not = $not;
    }

    /**
     * Create a new instance of My_Log_Filter_InArray
     *
     * @param  array|Zend_Config $config
     * @return My_Log_Filter_InArray
     */
    static public function factory($config)
    {
        $config = self::_parseConfig($config);
        $config = array_merge(array(
            'priorities' => null,
            'not' => false,
        ), $config);

        return new self(
            $config['priorities'],
            $config['not']
        );
    }

    /**
     * Returns TRUE to accept the message, FALSE to block it.
     *
     * @param  array    $event    event data
     * @return boolean            accepted?
     */
    public function accept($event)
    {
        return in_array($event['priority'], $this->_priorities) != $this->_not;
    }
}