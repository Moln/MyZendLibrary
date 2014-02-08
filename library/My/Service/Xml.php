<?php

namespace My\Service;

/**
 * Xml.php
 * @author   maomao
 * @DateTime 12-7-24 上午11:00
 * @version  $Id: Xml.php 1210 2013-08-08 01:49:52Z maomao $
 */
class Xml extends ProtocolAbstract
{

    protected $charset = 'UTF-8';

    protected $root = 'response';

    /**
     * @param string $root
     * @return $this
     */
    public function setRoot($root)
    {
        $this->root = $root;
        return $this;
    }

    public function handle()
    {
        if (!headers_sent()) {
            header('Content-Type: text/xml');
        }
        echo $this->result2xml($this->callMethod());
    }

    public function callMethod()
    {
        $result = parent::callMethod();

        $reflect   = new \ReflectionClass($this->service);
        $refMethod = $reflect->getMethod($this->method);
        $doc       = new \Zend_Reflection_Docblock($refMethod->getDocComment());
        if ($doc->getTag('serviceXmlRoot')) {
            $this->root = $doc->getTag('serviceXmlRoot')->getDescription();
        }
        return $result;
    }

    public function result2xml($result, $xml = false)
    {
        if ($xml === false) {
            $head = '<?xml version="1.0" encoding="' . $this->charset . '"?>';
            $root = $this->root;
            if (is_bool($result)) {
                $xml = new \SimpleXMLElement("$head<$root>" . intval($result) . "</$root>");
                return $xml->asXML();
            } else if (!is_array($result)) {
                $xml = new \SimpleXMLElement("$head<$root>" . strval($result) . "</$root>");
                return $xml->asXML();
            } else {
                $xml = new \SimpleXMLElement($head . "<$root/>");
            }
        }

        foreach ($result as $key => $value) {
            if (is_array($value)) {
                $this->result2xml($value, $xml->addChild(is_numeric($key) ? 'item' : $key));
            } else {
                $xml->addChild(is_numeric($key) ? 'item' : $key, $value);
            }
        }
        return $xml->asXML();
    }

    public function getCharset()
    {
        return $this->charset;
    }

    public function setCharset($charset)
    {
        $this->charset = $charset;
        return $this;
    }
}
