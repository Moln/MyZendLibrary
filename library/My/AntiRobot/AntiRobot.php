<?php
/**
 * platform Anti.php
 * @DateTime 13-8-14 上午11:29
 */
namespace My\AntiRobot;

/**
 * Class Anti
 * @package My\AntiRobot
 * @author Moln Xie
 * @version $Id: AntiRobot.php 1329 2014-03-13 00:02:24Z maomao $
 */
class AntiRobot
{
    private static $pluginLoader;

    protected $validators = array(), $invalid, $name = 'default';

    /**
     * @param $options
     * @return AntiRobot
     * @throws \InvalidArgumentException
     */
    public static function factory($options)
    {
        if (is_string($options)) {
            $config = \My\Config\Factory::getConfigs('AntiRobot');
            $options = $config[$options] + array('name' => $options);
        }
        if (!is_array($options)) {
            throw new \InvalidArgumentException('Invalid argument type:' . gettype($options));
        }

        $self = new self;

        if (isset($options['name'])) {
            $self->setName($options['name']);
        }
        if (isset($options['validators'])) {
            $self->addValidators($options['validators']);
        }

        return $self;
    }

    public static function getServiceLoader()
    {
        if (!self::$pluginLoader instanceof \Zend_Loader_PluginLoader_Interface) {
            $path = str_replace('\\', DIRECTORY_SEPARATOR, ltrim(__NAMESPACE__, '\\'))
                . DIRECTORY_SEPARATOR . 'Validator';
            self::$pluginLoader = new \Zend_Loader_PluginLoader(
                [__NAMESPACE__ . '\\Validator' => $path], __CLASS__
            );
        }

        return self::$pluginLoader;
    }

    public function addValidators($validators)
    {
        foreach ($validators as $key => $validator) {
            if (is_array($validator)) {
                $validator['name'] = $key;
            }
            $this->addValidator($validator);
        }
    }

    /**
     * @param Validator\ValidatorInterface|array $validator
     * @throws \InvalidArgumentException
     */
    public function addValidator($validator)
    {
        if (is_array($validator)) {
            $className  = self::getServiceLoader()->load($validator['name']);
            $validator = new $className($validator + array('robotName' => $this->getName()));
        } else if (!is_object($validator) || !$validator instanceof Validator\ValidatorInterface) {
            throw new \InvalidArgumentException('Error argument!');
        }

        $name = explode('\\', str_replace('_', '\\', get_class($validator)));
        $this->validators[strtolower(end($name))] = $validator;
    }

    /**
     * @param $name
     * @return null|Validator\AbstractValidator
     */
    public function getValidator($name)
    {
        return isset($this->validators[$name]) ? $this->validators[$name] : null;
    }

    public function removeValidator($name)
    {
        $name = strtolower($name);
        if (isset($this->validators[$name])) {
            unset($this->validators[$name]);
        }
        return $this;
    }

    /**
     *
     * @return Validator\AbstractValidator
     */
    public function getInvalid()
    {
        return $this->invalid;
    }

    public function isValid()
    {
        foreach ($this->getValidators() as $validator) {
            if (!$validator->isValid()) {
                $this->invalid = $validator;
                return false;
            }
        }
        return true;
    }

    /**
     *
     * @return Validator\AbstractValidator[]
     */
    private function getValidators()
    {
        return $this->validators;
    }

    /**
     * @param bool $formValidResult
     * @return $this
     */
    public function setFormValidResult($formValidResult)
    {
        foreach ($this->getValidators() as $validator) {
            if ($validator instanceof Validator\FormResultInterface) {
                $validator->setFormValidResult($formValidResult);
            }
        }
        return $this;
    }

    public function setName($name)
    {
        $this->name = (string) $name;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }
}