<?php
namespace My\Stdlib\Fragment;

/**
 * Option.php
 * @author   maomao
 * @DateTime 12-8-9 下午6:53
 * @version  $Id: Option.php 1275 2014-01-23 23:10:26Z maomao $
 */
trait Option
{
    /**
     * @param array $options
     *
     * @return static
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $val) {
            $method = 'set' . ucfirst($key);
            method_exists($this, $method) && $this->$method($val);
        }
        return $this;
    }
}
