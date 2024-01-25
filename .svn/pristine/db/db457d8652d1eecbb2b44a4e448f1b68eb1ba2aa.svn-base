<?php
namespace Shorturl\Model;

class Shorturl2User
{
    public $id;
    public $user_id;
    public $shorturl_id;

    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->user_id = !empty($data['user_id']) ? $data['user_id'] : 0;
        $this->shorturl_id = !empty($data['shorturl_id']) ? $data['shorturl_id'] : 0;
    }

}
