<?php
/**
 * 表单提交验证, 防止重复提交
 * @author Xiemaomao
 * @DateTime 12-9-14 下午3:24
 * @version $Id: Verify.php 856 2013-04-17 09:31:53Z maomao $
 */
namespace My\Form\Element;
use Zend_Session_Namespace, Zend_Validate_Callback, Zend_View_Interface;

/**
 * Class Verify
 * @package My\Form\Element
 */
class Verify extends \Zend_Form_Element_Xhtml
{
    private static $sessionName = 'My_Form_Element_Verify_Random';
    /**
     * Default form view helper to use for rendering
     * @var string
     */
    public $helper = 'formHidden';

    private $session;

    public function init()
    {
        $this->session = new Zend_Session_Namespace(self::$sessionName);

        $this->addValidator(
            'callback', true, [
                'callback' => function ($value) {
                    $result = !empty($this->session->code) && $this->session->code == $value;
                    unset($this->session->code);
                    return $result;
                }
            ]
        );
        $this->setErrorMessages(
            [Zend_Validate_Callback::INVALID_CALLBACK => '表单已过期，请勿重复提交。']
        );
    }

    public function render(Zend_View_Interface $view = null)
    {
        $this->session->code = md5(mt_rand());
        $this->setValue($this->session->code);
        return parent::render($view);
    }
}
