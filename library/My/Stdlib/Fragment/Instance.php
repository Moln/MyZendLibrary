<?php
namespace My\Stdlib\Fragment;

/**
 * 通用单例
 * Trait Instance
 * @package My\Stdlib\Fragment
 * @author Xiemaomao
 * @version $Id: Instance.php 1275 2014-01-23 23:10:26Z maomao $
 */
trait Instance {
    protected static $instance;

    /**
     * Singleton instance
     *
     * @return static
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            $class = new \ReflectionClass(__CLASS__);
            self::$instance = $class->newInstanceArgs(func_get_args());
        }

        return self::$instance;
    }
}