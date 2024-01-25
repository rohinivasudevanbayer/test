<?php
namespace Shorturl\Model;

class Admin2Domain
{
    public $id;
    public $user_id;
    public $domain_id;

    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->user_id = !empty($data['user_id']) ? $data['user_id'] : 0;
        $this->domain_id = !empty($data['domain_id']) ? $data['domain_id'] : 0;
    }

}
