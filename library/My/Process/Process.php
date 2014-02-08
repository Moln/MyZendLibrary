<?php
namespace My\Process;

/**
 * Class Process
 * @package My\Process
 * @author Xiemaomao
 * @version $Id: Process.php 790 2013-03-15 08:56:56Z maomao $
 */
class Process
{
    private static $processlist = array();

    public static function register($call, $params = array())
    {
        if (!is_callable($call)) {
            throw new \RuntimeException('不是一个调用函数');
        }

        self::$processlist[] = array($call, $params);
    }

    public static function run() {
        $list = self::$processlist;
        self::$processlist = array();
        foreach($list as $func) {
            call_user_func_array($func[0], $func[1]);
        }
    }

    public static function hasTask()
    {
        return (bool) count(self::$processlist);
    }
}