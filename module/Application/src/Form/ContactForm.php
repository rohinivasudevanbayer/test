<?php
namespace Application\Form;

use Laminas\Form\Form;

class ContactForm extends Form
{
    public function __construct()
    {
        parent::__construct('contact-form');
        $this->setAttribute('class', 'form-horizontal')
            ->setAttribute('id', 'contact-form');

        $this->addElements();
        $this->addInputFilters();
    }

    protected function addElements()
    {
        $this->add([
            'type' => 'text',
            'name' => 'name',
        ]);

        $this->add([
            'type' => 'email',
            'name' => 'email',
            'attributes' => [
                'required' => true,
            ],
        ]);

        $this->add([
            'type' => 'text',
            'name' => 'subject',
            'attributes' => [
                'required' => true,
            ],
        ]);

        $this->add([
            'type' => 'textarea',
            'name' => 'message',
            'attributes' => [
                'required' => true,
            ],
        ]);

        $this->add([
            'type' => 'submit',
            'name' => 'submit',
        ]);
    }

    protected function addInputFilters()
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
            'name' => 'subject',
            'required' => true,
        ]);

        $inputFilter->add([
            'name' => 'message',
            'required' => true,
        ]);
    }
}
