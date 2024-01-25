<?php
namespace Shorturl\Model;

class Domain
{
    public $id;
    public $domain;
    public $type;

    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->domain = !empty($data['domain']) ? $data['domain'] : '';
        $this->type = !empty($data['type']) ? $data['type'] : '';
    }

}
