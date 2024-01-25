<?php
namespace Auth\Form;

use Laminas\Form\Form;

class LoginForm extends Form
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct('login-form');

        $this->setAttribute('method', 'post');

        $this->addElements();
        $this->addInputFilter();
    }

    protected function addElements()
    {
        $this->add([
            'type' => 'text',
            'name' => 'email',
            'required' => true,
        ]);

        $this->add([
            'type' => 'password',
            'name' => 'password',
            'required' => true,
        ]);

        $this->add([
            'type' => 'hidden',
            'name' => 'redirect_url',
        ]);

        $this->add([
            'type' => 'csrf',
            'name' => 'csrf',
            'options' => [
                'csrf_options' => [
                    'timeout' => 600,
                ],
            ],
        ]);

        $this->add([
            'type' => 'submit',
            'name' => 'submit',
        ]);
    }

    private function addInputFilter()
    {
        $inputFilter = $this->getInputFilter();

        $inputFilter->add([
            'name' => 'email',
            'required' => true,
            'filters' => [
                ['name' => 'StringTrim'],
            ],
            'validators' => [
                [
                    'name' => 'EmailAddress',
                    'options' => [
                        'allow' => \Laminas\Validator\Hostname::ALLOW_DNS,
                        'useMxCheck' => false,
                    ],
                ],
            ],
        ]);

        $inputFilter->add([
            'name' => 'password',
            'required' => true,
            'filters' => [
            ],
            'validators' => [
                [
                    'name' => 'StringLength',
                    'options' => [
                        'min' => 5,
                        'max' => 64,
                    ],
                ],
            ],
        ]);

        $inputFilter->add([
            'name' => 'redirect_url',
            'required' => false,
            'filters' => [
                ['name' => 'StringTrim'],
            ],
            'validators' => [
                [
                    'name' => 'StringLength',
                    'options' => [
                        'min' => 0,
                        'max' => 2048,
                    ],
                ],
            ],
        ]);
    }
}
