<?php
namespace My\Product;

use My\Config\Factory as ConfigFactory;
use Zend_Registry;

/**
 * Class Product
 * @package My\Product
 *
 * @author   maomao
 * @DateTime 12-5-24 下午1:36
 * @version  $Id: Product.php 1310 2014-02-07 02:27:31Z maomao $
 */
abstract class Product
{
    private static $config;

    protected $id, $name, $key;

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    public function getKey()
    {
        return $this->key;
    }

    protected $areaNodeClass;

    protected $areaListClass = 'AreaList';

    /**
     * @var NodeList\AreaList
     */
    protected $areas;

    /**
     * @static
     *
     * @param string $product
     * @param array $options
     *
     * @throws Exception\ErrorProductException
     * @return Product
     */
    public static function factory($product, $options = array())
    {
        if (!self::hasProduct($product, true)) {
            throw new Exception\ErrorProductException('未知产品:' . $product);
        }

        $options += ConfigFactory::getConfigs('product', $product);
        $className = self::getLoader()->load($product);
        return $className::getInstance($options);
    }

    private static $pluginLoader;

    /**
     * Retreive PluginLoader
     *
     * @return \Zend_Loader_PluginLoader_Interface
     */
    public static function getLoader()
    {
        if (!self::$pluginLoader instanceof \Zend_Loader_PluginLoader_Interface) {
            $loaders = self::getConfig()['pluginLoader'];
            self::$pluginLoader
                = new \Zend_Loader_PluginLoader(['\\My\\Product\\' => 'My/Product/']+$loaders, __CLASS__);
        }

        return self::$pluginLoader;
    }


    /**
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->setOptions($options);
    }

    /**
     * @param array $options
     *
     * @return Product
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $val) {
            $method = 'set' . ucfirst($key);
            method_exists($this, $method) && $this->$method($val);
        }
        return $this;
    }

    /**
     * @static
     * @return array
     */
    public static function getProducts()
    {
        return self::getConfig()['products'] ? : [];
    }

    public static function hasProduct(&$product, $changeAlias = false)
    {
        $config = self::getConfig();
        if (!empty($config['alias'][$product])) {
            if ($changeAlias) {
                $product = $config['alias'][$product];
            }
            return true;
        } else if (!empty($config['products'][$product])) {
            return true;
        } else if (is_numeric($product) && isset($config['ids'][$product])) {
            if ($changeAlias) {
                $product = $config['ids'][$product];
            }
            return true;
        }
        return false;
    }

    private static function getConfig()
    {
        if (empty(self::$config)) {
            self::$config = ConfigFactory::getConfigs('product', 'product');
        }
        return self::$config;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return NodeList\AreaList|\My\Product\Node\AreaNode[]
     */
    public function getAreas()
    {
        return $this->areas;
    }

    /**
     * @param array $areas
     *
     * @return Product
     */
    protected function setAreas(array $areas)
    {
        $className = self::getLoader()->load('NodeList\\' . $this->areaListClass);
        $this->areas = new $className($areas, $this);
        if ($this->areaNodeClass) {
            $this->areas->setNodeClass($this->areaNodeClass);
        }
        return $this;
    }

    /**
     * @param $area
     *
     * @return \My\Product\Node\AreaNode
     */
    public function getArea($area)
    {
        return $this->areas->get($area);
    }

    /**
     * @param string $area
     *
     * @return bool
     */
    public function hasArea($area)
    {
        return $this->areas->has($area);
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
}