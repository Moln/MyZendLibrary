<?php
/**
 * Table.php
 *
 * @author   maomao
 * @DateTime 12-6-26 下午2:57
 * @version  $Id: Table.php 790 2013-03-15 08:56:56Z maomao $
 */
namespace My\Form\Decorator;

/**
 * Class Table
 * @package My\Form\Decorator
 */
class Table extends \Zend_Form_Decorator_Abstract
{
    /**
     * Render a label
     *
     * @param  string $content
     *
     * @return string
     */
    public function render($content)
    {
        $form = $this->getElement();
        $form->setDecorators(
            ['FormElements', ['HtmlTag', ['tag' => 'table',] + $this->getOptions()], 'Form']
        );

        foreach ($form as $element) {
            $element->removeDecorator('HtmlTag');
            $element->removeDecorator('Label');
            $element->removeDecorator('Errors');
            $element->addDecorators(
                array(
                    'Errors',
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    array('Label', array('tag' => 'th',)),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                )
            );
        }

        foreach ($form->getDecorators() as $key => $decorator) {
            $decorator->setElement($form);
            $content = $decorator->render($content);
        }

//        $form    = $this->getElement();
//        $view    = $form->getView();
//        if (null === $view) {
//            return $content;
//        }
//
//        $helper        = $this->getHelper();
//        $attribs       = $this->getOptions();
//        $name          = $form->getFullyQualifiedName();
//        $attribs['id'] = $form->getId();
//        return $view->$helper($name, $attribs, $content);

        return $content;
    }
}