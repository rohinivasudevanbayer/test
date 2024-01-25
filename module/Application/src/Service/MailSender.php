<?php
namespace Application\Service;

use Laminas\Mail\Message;
use Laminas\Mail\Transport\Smtp as SmtpTransport;
use Laminas\Mail\Transport\SmtpOptions;

class MailSender
{
    protected $printInsteadOfSending = false;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function sendMail($sender, $recipient, $replyTo, $subject, $text)
    {
        $result = false;

        if (is_array($this->config['mail']) && !empty($this->config['mail']['print_insteadof_sending'])) {
            echo "<pre>
Mail with following content has NOT been sent:
from: ${sender['name']} &lt;${sender['email']}&gt;
to: ${recipient['name']} &lt;${recipient['email']}&gt;";
            if (is_array($replyTo) && isset($replyTo['name']) && isset($replyTo['email'])) {
                echo "replyTo: ${replyTo['name']} &lt;${replyTo['email']}&gt;";
            }
            echo "subject: $subject
message: <div style='margin-left: 2rem'>$text</div>
</pre>";
            return true;
        }

        try {
            $mail = new Message();
            $mail->getHeaders()->addHeaderLine('Content-Type', 'text/html; charset=UTF-8');
            $mail->setFrom([$sender['email'] => $sender['name']]);
            if (is_array($replyTo) && isset($replyTo['name']) && isset($replyTo['email'])) {
                $mail->setReplyTo([$replyTo['email'] => $replyTo['name']]);
            }
            $mail->addTo([$recipient['email'] => $recipient['name']]);
            $mail->setSubject($subject);
            $mail->setBody($text);

            $transport = new SmtpTransport();
            $options = new SmtpOptions([
                'name' => $this->config['smtp']['host'],
                'host' => $this->config['smtp']['host'],
                'port' => $this->config['smtp']['port'],
                'connection_class' => 'login',
                'connection_config' => [
                    'username' => $this->config['smtp']['username'],
                    'password' => $this->config['smtp']['password'],
                    'ssl' => $this->config['smtp']['ssl']
                ],
            ]);
            $transport->setOptions($options);
            $transport->send($mail);
            $result = true;
        } catch (\Exception $e) {
            $result = false;
        }

        return $result;
    }
}
