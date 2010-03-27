<?php

class Form_UserLogin extends Zend_Form
{
    public function init()
    {
        $this->addElement('text', 'login', array(
            'label'    => 'Ник',
            'required' => true,
            'validators'  => array(
                array('Alnum', true, array(true)),
                array('StringLength', true, array(2, 40)),
             ),
            'filters' => array('StringTrim'),
        ));

        $this->addElement('password', 'password', array(
            'label'      => 'Пароль',
            'required'   => true,
        ));
    }
}   