<?php

class Form_UserLogin extends Zend_Form
{
    public function init()
    {
        $this->addElement('text', 'login', array(
            'label'    => 'Login',
            'required' => true,
            'validators'  => array(
                array('Alnum', true, array(true)),
                array('StringLength', true, array(2, 40)),
             ),
            'filters' => array('StringTrim'),
        ));

        $this->addElement('password', 'password', array(
            'label'      => 'Password',
            'required'   => true,
        ));
        
        $this->addElement('submit', 'submit', array('label' => 'Submit'));
    }
}   