<?php
namespace Shorturl\Model;

class Blacklist
{
    public $id;
    public $short_url;
    public $domain_id;
    public $url_code;
    public $created_at;
    public $created_by;

    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->short_url = !empty($data['short_url']) ? $data['short_url'] : '';
        $this->domain_id = !empty($data['domain_id']) ? $data['domain_id'] : null;
        $this->url_code = !empty($data['url_code']) ? $data['url_code'] : '';
        $this->created_at = !empty($data['created_at']) ? $data['created_at'] : date('Y-m-d H:i:s');
        $this->created_by = !empty($data['created_by']) ? $data['created_by'] : '';
    }

}
