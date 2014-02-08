<?php

namespace My\Log;

use Zend_Log,
Zend_Log_Writer_Null;

/**
 * @author maomao
 * @version $Id: Logger.php 790 2013-03-15 08:56:56Z maomao $
 * @method static void err(string $message, array $extras = array())
 * @method static void emerg(string $message, array $extras = array())
 * @method static void crit(string $message, array $extras = array())
 * @method static void warn(string $message, array $extras = array())
 * @method static void notice(string $message, array $extras = array())
 * @method static void info(string $message, array $extras = array())
 * @method static void debug(string $message, array $extras = array())
 */
class Logger
{
    /**
     * \Zend_Log
     * @var \Zend_Log
     */
    static protected $logger;


    /**
     * @param string $method
     * @param array $params
     * @return mixed
     */
    public static function __callstatic($method, $params)
    {
        return call_user_func_array(array(self::get(), $method), $params);
    }


    /**
     * @return \Zend_Log
     */
    public static function get()
    {
        if (!self::$logger) {
            self::$logger = new Zend_Log(new Zend_Log_Writer_Null());
        }
        return self::$logger;
    }

    /**
     * @param Zend_Log $logger
     */
    public static function set(Zend_Log $logger)
    {
        self::$logger = $logger;
    }
}
