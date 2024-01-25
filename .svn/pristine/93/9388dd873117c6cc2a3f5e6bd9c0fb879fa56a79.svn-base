<?php
namespace Shorturl\Model;

class ShorturlVisit
{
    public $id;
    public $shorturl_id;
    public $visit_date;

    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->shorturl_id = !empty($data['shorturl_id']) ? $data['shorturl_id'] : null;
        $this->visit_date = !empty($data['visit_date']) ? $data['visit_date'] : null;
    }
}
