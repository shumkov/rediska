<?php

class Form_Post extends Zend_Form
{
    public function init()
    {
        $this->addElement('textarea', 'text', array(
            'label'    => 'Message',
            'required' => true,
            'rows' => 6,
            'validators' => array(
                array('StringLength', true, array(0, 5000)),
            ),
        ));
        
        $this->addElement('submit', 'submit', array('label' => 'Add'));
    }
}