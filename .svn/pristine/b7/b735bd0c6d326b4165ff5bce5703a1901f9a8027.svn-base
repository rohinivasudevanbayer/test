<?php
namespace Shorturl\Form;

use Laminas\Form\Form;
use Laminas\Validator\Uri;
use Shorturl\Validator\ShorturlValidator;
use Shorturl\Validator\UrlValidator;

class ShorturlForm extends Form
{

    protected $domains = [];

    /**
     * do not change the construct
     *
     * @param null $options
     */
    public function __construct($options = null)
    {
        parent::__construct('shorturl-form');
        $this->setAttribute('class', 'form-horizontal')
            ->setAttribute('id', 'shorturl-form');

        if (!empty($options['domains']) && is_iterable($options['domains'])) {
            $this->domains = $options['domains'];
        }

        $this->addElements();
        $this->addInputFilters();
    }

    protected function addElements()
    {
        $this->add([
            'type' => 'text',
            'name' => 'target_url',
            'options' => [
                'label' => 'Target Url',
            ],
            'attributes' => [
                'required' => true,
                'placeholder' => 'http://',
            ],
        ]);

        $domainOptions = [];
        foreach ($this->domains as $domain) {
            $domainOptions['' . $domain['id']] = $domain['domain'] . ' (' . ucfirst($domain['type']) . ')';
        };

        $this->add([
            'type' => 'select',
            'name' => 'domains',
            'options' => [
                'label' => 'Domain',
                'value_options' => $domainOptions,
            ],
            'attributes' => [
                'required' => true,
            ],
        ]);

        $this->add([
            'type' => 'text',
            'name' => 'url_code',
            'options' => [
                'label' => 'Shorturl',
            ],
            'attributes' => [
                'required' => true,
            ],
        ]);

        $this->add([
            'type' => 'text',
            'name' => 'expiry_date',
            'options' => [
                'label' => 'Expiry Date',
            ],
            'attributes' => [
                'required' => true,
            ],
        ]);

        $this->add([
            'type' => 'select',
            'name' => 'status',
            'options' => [
            ],
            'options' => [
                'label' => 'Status',
                'value_options' => [
                    '1' => 'active',
                    '0' => 'inactive',
                ],
            ],
            'attributes' => [
                'required' => true,
            ],
        ]);

        $this->add([
            'type' => 'submit',
            'name' => 'cancel',
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
            'name' => 'target_url',
            'required' => true,
            'validators' => [
                [
                    'name' => Uri::class,
                    'options' => [
                        'allowRelative' => false,
                        'allowAbsolute' => true,
                        'messageTemplates' => [
                            Uri::INVALID => 'URL is not valid',
                            Uri::NOT_URI => 'URL is not valid',
                        ],
                    ],
                ],
                [
                    'name' => UrlValidator::class,
                ],
            ],
        ]);

        $inputFilter->add([
            'name' => 'domains',
            'required' => true,
        ]);

        $inputFilter->add([
            'name' => 'url_code',
            'required' => true,
            'validators' => [
                [
                    'name' => ShorturlValidator::class,
                    'options' => [
                        'domains' => $this->domains,
                    ],
                ],
            ],
        ]);

        $inputFilter->add([
            'name' => 'expiry_date',
            'required' => true,
        ]);

        $inputFilter->add([
            'name' => 'status',
            'required' => true,
        ]);

    }

}
