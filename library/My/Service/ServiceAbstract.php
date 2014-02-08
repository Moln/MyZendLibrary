<?php
namespace My\Service;

/**
 * ServiceAbstract.php
 * @author   maomao
 * @DateTime 12-7-24 上午11:24
 * @version  $Id: ServiceAbstract.php 1163 2013-07-26 05:30:12Z maomao $
 */
abstract class ServiceAbstract
{
    protected $key = 'bHojkq1Vn8NckcObhuc0';

    protected $messages = [];

    /**
     * @var \Zend_Db_Adapter_Abstract
     */
    protected $db;

    protected function db()
    {
        if (!$this->db) {
            if (class_exists('Zend_Db_Table', false)) {
                $this->db = \Zend_Db_Table::getDefaultAdapter();
            } else {
                $config = \Zend_Registry::get('application.options')['resources']['db']['params'];;
                $this->db = new \PDO(
                    "mysql:dbname=" . $config['dbname'] . ";host=" . $config['host'],
                    $config['username'], $config['password']
                );
            }
        }
        return $this->db;
    }

    protected function validSign(array $args, $key = null)
    {
        if (md5(implode($args) . ($key ? : $this->key)) != strtolower($_REQUEST['sign'])) {
            return ['code' => -100, 'msg' => '签名校验失败']
            + (APPLICATION_ENV != 'production' ? ['s' => md5(implode($args) . ($key ? : $this->key))] : []);
        }
        return true;
    }

    protected function result($code, $ext = [])
    {
        $result = ['code' => $code];
        if (isset($this->messages[$code])) {
            $result += ['msg' => $this->messages[$code]];
        }
        return $result + $ext;
    }

    protected function success($ext = [])
    {
        return ['code' => 1] + $ext;
    }

    /**
     * @return \Zend_Controller_Request_Http
     */
    protected function getRequest()
    {
        return \Zend_Controller_Front::getInstance()->getRequest();
    }
}
