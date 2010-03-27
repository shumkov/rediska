<?php

class Form_Post extends Zend_Form
{
    public function init()
    {
        $this->addElement('textarea', 'text', array(
            'label'    => 'Текст поста',
            'required' => true,
            'validators' => array(
                array('StringLength', true, array(0, 5000)),
            ),
        ));
        
        $this->addElement('submit', 'submit', array('label' => 'Добавить'));
    }
}