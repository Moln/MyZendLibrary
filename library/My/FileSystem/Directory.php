<?php
/**
 * Directory.php
 * @author   maomao
 * @DateTime 12-8-2 下午5:06
 * @version  $Id: Directory.php 790 2013-03-15 08:56:56Z maomao $
 */
namespace My\FileSystem;

/**
 * Class Directory
 * @package My\FileSystem
 */
class Directory
{
    public static function mkdirs($dir, $mode = 0777, $recursive = true)
    {
        if (($dir === null) || $dir === '') {
            return false;
        }
        if (is_dir($dir) || $dir === '/') {
            return true;
        }
        if (self::mkdirs(dirname($dir), $mode, $recursive)) {
            return mkdir($dir, $mode);
        }
        return false;
    }
}
