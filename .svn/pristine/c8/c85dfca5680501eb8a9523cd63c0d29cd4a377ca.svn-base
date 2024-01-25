<?php
namespace Shorturl\Validator;

use Laminas\Validator\AbstractValidator;

class UrlValidator extends AbstractValidator
{
    const ERROR_INVALID_FORMAT = 'notUri'; // value needs to match Laminas\Validator\Uri::NOT_URI to make sure error message is only shown once

    protected $messageTemplates = [
        self::ERROR_INVALID_FORMAT => "URL is not valid",
    ];

    public function isValid($value)
    {
        if ((substr($value, 0, 7) !== "http://"
            && substr($value, 0, 8) !== "https://"
        ) || !strstr($value, '.')
        ) {
            $this->error(self::ERROR_INVALID_FORMAT);
            return false;
        }
        return true;
    }
}
