<?php
namespace Shorturl\Model;

class ShorturlHistory
{
    public $id;
    public $shorturl_id;
    public $user_id;
    public $short_url;
    public $target_url;
    public $url_code;
    public $domain_id;
    public $status;
    public $visits;
    public $qr_code_settings;
    public $sent_reminder1;
    public $sent_reminder2;
    public $sent_expiration_notification;
    public $created_at;
    public $updated_at;
    public $expiry_date;
    public $updated_by;

    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? (int) $data['id'] : null;
        $this->shorturl_id = !empty($data['shorturl_id']) ? (int) $data['shorturl_id'] : null;
        $this->user_id = !empty($data['user_id']) ? (int) $data['user_id'] : null;
        $this->short_url = !empty($data['short_url']) ? $data['short_url'] : '';
        $this->target_url = !empty($data['target_url']) ? $data['target_url'] : '';
        $this->url_code = !empty($data['url_code']) ? $data['url_code'] : '';
        $this->domain_id = !empty($data['domain_id']) ? (int) $data['domain_id'] : 0;
        $this->status = isset($data['status']) ? (int) $data['status'] : 1;
        $this->visits = !empty($data['visits']) ? (int) $data['visits'] : 0;
        $this->qr_code_settings = !empty($data['qr_code_settings']) ? $data['qr_code_settings'] : null;
        $this->sent_reminder1 = isset($data['sent_reminder1']) ? (int) $data['sent_reminder1'] : 0;
        $this->sent_reminder2 = isset($data['sent_reminder2']) ? (int) $data['sent_reminder2'] : 0;
        $this->sent_expiration_notification = isset($data['sent_expiration_notification']) ? (int) $data['sent_expiration_notification'] : 0;
        $this->created_at = !empty($data['created_at']) ? $data['created_at'] : date('Y-m-d H:i:s');
        $this->updated_at = !empty($data['updated_at']) ? $data['updated_at'] : date('Y-m-d H:i:s');
        $this->expiry_date = !empty($data['expiry_date']) ? $data['expiry_date'] : null;
        $this->updated_by = !empty($data['updated_by']) ? (int) $data['updated_by'] : 0;
    }

    public function newFromShortUrl($shortUrlObject)
    {
        $this->id = null;
        $this->shorturl_id = $shortUrlObject->id;
        $this->user_id = $shortUrlObject->user_id;
        $this->short_url = $shortUrlObject->short_url;
        $this->target_url = $shortUrlObject->target_url;
        $this->url_code = $shortUrlObject->url_code;
        $this->domain_id = $shortUrlObject->domain_id;
        $this->status = $shortUrlObject->status;
        $this->visits = $shortUrlObject->visits;
        $this->qr_code_settings = $shortUrlObject->qr_code_settings;
        $this->sent_reminder1 = $shortUrlObject->sent_reminder1;
        $this->sent_reminder2 = $shortUrlObject->sent_reminder2;
        $this->sent_expiration_notification = $shortUrlObject->sent_expiration_notification;
        $this->created_at = $shortUrlObject->created_at;
        $this->updated_at = $shortUrlObject->updated_at;
        $this->expiry_date = $shortUrlObject->expiry_date;
        $this->updated_by = $shortUrlObject->updated_by;
    }
}
