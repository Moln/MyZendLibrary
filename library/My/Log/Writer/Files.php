<?php
/**
 * My_Log_Writer_Files
 *
 * @author    mmxie
 * @version   $Id: Files.php 790 2013-03-15 08:56:56Z maomao $
 */
class My_Log_Writer_Files extends Zend_Log_Writer_Abstract
{
    /**
     * Directory
     *
     * @var string
     */
    protected $_dir = null;

    protected $_files = array();

    /**
     * Class Constructor
     *
     * @param string $dir
     *
     * @return void
     * @throws Zend_Log_Exception
     */
    public function __construct($dir)
    {
        if (is_dir($dir) && is_writable($dir)) {
            $this->_dir = $dir;
        } else {
            throw new Zend_Log_Exception("文件夹 '$dir' 不存在或没有写入权限");
        }

        $this->_formatter = new Zend_Log_Formatter_Simple("%timestamp% (%employee%): %message%\n");
    }

    /**
     * Create a new instance of My_Log_Writer_Files
     *
     * @param  array|Zend_Config $config
     *
     * @return My_Log_Writer_Files
     */
    static public function factory($config)
    {
        $config = self::_parseConfig($config);
        $config = array_merge(
            array(
                'dir' => null,
            ), $config
        );

        return new self($config['dir']);
    }

    /**
     * Close the stream resources.
     *
     * @return void
     */
    public function shutdown()
    {
        foreach ($this->_files as $fp) {
            fclose($fp);
        }
    }

    /**
     * Write a message to the log.
     *
     * @param  array  $event  event data
     *
     * @return void
     * @throws Zend_Log_Exception
     */
    protected function _write($event)
    {
        $line = $this->_formatter->format($event);

        if (!isset($this->_files[$event['priorityName']])) {
            $file = rtrim($this->_dir, "\\/") . "/{$event['priorityName']}.log";
            if (!($this->_files[$event['priorityName']] = @fopen($file, 'a', false))) {
                throw new Zend_Log_Exception("\"$file\" cannot be opened with mode \"a\"");
            }
        }

        if (false === @fwrite($this->_files[$event['priorityName']], $line)) {
            throw new Zend_Log_Exception("文件无法写入");
        }
    }
}