<?php
/**
 * Config.php
 * @author   maomao
 * @DateTime 12-8-9 下午5:58
 * @version  $Id: Factory.php 1265 2013-09-26 08:35:38Z maomao $
 */
namespace My\Config;

/**
 * Class Factory
 * @package My\Config
 */
class Factory
{

    /**
     * @static
     *
     * @param string $file
     *
     * @return mixed|\Zend_Config_Ini|\Zend_Config_Json|\Zend_Config_Xml|\Zend_Config_Yaml
     * @throws \RuntimeException
     */
    public static function fromFile($file)
    {
        $suffix      = pathinfo($file, PATHINFO_EXTENSION);
        $suffix      = ($suffix === 'dist')
            ? pathinfo(basename($file, ".$suffix"), PATHINFO_EXTENSION)
            : $suffix;

        switch (strtolower($suffix)) {
            case 'ini':
                $config = new \Zend_Config_Ini($file);
                break;

            case 'xml':
                $config = new \Zend_Config_Xml($file);
                break;

            case 'json':
                $config = new \Zend_Config_Json($file);
                break;

            case 'yaml':
            case 'yml':
                $config = new \Zend_Config_Yaml($file);
                break;

            case 'php':
            case 'inc':
                $config = include $file;
                if (!is_array($config)) {
                    throw new \RuntimeException('Invalid configuration file provided; PHP file does not return array value');
                }
                return $config;
                break;

            default:
                throw new \RuntimeException('Invalid configuration file provided; unknown config type');
        }

        return $config->toArray();
    }

    /**
     * 获取 application.ini 下 my 开头的配置
     * @static
     *
     * @param string $class
     * @param string $tag
     *
     * @return array|mixed
     * @throws \RuntimeException
     */
    public static function getConfigs($class, $tag = null)
    {
        $config = \Zend_Registry::get('my.options');

        if (isset($config[$class])) {

            if (empty($tag)) {
                $subConfig = &$config[$class];
            } else if (isset($config[$class][$tag])) {
                $subConfig = &$config[$class][$tag];
            } else {
                throw new \RuntimeException('未知标记:' . $tag);
            }

            if (is_string($subConfig)) {
                if (!file_exists($subConfig)) {
                    throw new \RuntimeException('File not exists：' . $subConfig);
                }
                $subConfig = self::fromFile($subConfig);
            } else if (!is_array($subConfig)) {
                throw new \RuntimeException('配置错误，未知文件或配置不为数组：' . $subConfig);
            }

            \Zend_Registry::set('my.options', $config);

            return $subConfig;
        } else {
            throw new \RuntimeException('未知配置(' . $class . ', ' . $tag . ')');
        }
    }
}
