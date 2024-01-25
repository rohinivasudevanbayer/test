<?php
declare(strict_types = 1);

namespace Shorturl\Cronjob;

require_once 'Base.php';

class SendNotFound extends Base
{

    public function run()
    {
        $this->_sendNotFound();
    }
    
    private function _sendNotFound()
    {
        $shortUrlsString = '';
        if ($shortUrls = $this->dbh->query('SELECT DISTINCT(`short_url`) FROM `su_notfound` 
                                            WHERE `mail_sent_date` = 0')) {
            foreach ($shortUrls as $shortUrl) {
                    $shortUrlsString .= '<li>' . $shortUrl['short_url'] . '</li>';
            }
            $this->dbh->query('UPDATE `su_notfound`
                               SET `mail_sent_date` = NOW()
                               WHERE `mail_sent_date` = 0');
        }
        if (!empty($shortUrlsString)) {
            $shortUrlsString = '<ul>' . $shortUrlsString . '</ul>';
            $users = $this->dbh->query('SELECT * FROM `su_users` 
                                        WHERE `notifynotfound` = 1');
            foreach ($users as $user) {
                $this->_prepareAndSendNotification($user, 'Notification-MailSubject-ShortUrl-Not-Found', 'Notification-MailMessage-ShortUrl-Not-Found', $shortUrlsString, 0);
            }
        }
    }
}

$sendNotFound = new SendNotFound();
$sendNotFound->run();