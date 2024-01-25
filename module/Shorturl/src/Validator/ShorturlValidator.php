<?php
namespace Shorturl\Validator;

use Laminas\Validator\AbstractValidator;

class ShorturlValidator extends AbstractValidator
{
    const ERROR_INVALID_CHARACTERS = 'invalidFormat';
    const ERROR_TARGETURL_IDENTICAL = 'targetUrlIdentical';

    protected $messageTemplates = [
        self::ERROR_INVALID_CHARACTERS => "Short-URL contains invalid character",
        self::ERROR_TARGETURL_IDENTICAL => "Short-URL and Target-URL can not be identical",
    ];

    protected function containsNoSlash($value)
    {
        if (strstr($value, '/')) {
            $this->error(self::ERROR_INVALID_CHARACTERS);
            return false;
        }
        return true;
    }

    protected function isNotIdenticalWithTargetUrl($value, $context)
    {
        $domains = $this->getOption('domains');
        if (
            !empty($domains) && is_array($domains) && !empty($context['domains']) &&
            !empty($domains[$context['domains']]) && is_array($domains[$context['domains']]) &&
            !empty($domains[$context['domains']]['domain'])
        ) {
            $selectedDomain = $domains[$context['domains']]['domain'];

            $newShorturl = $selectedDomain . '/' . $value;
            if ($context['target_url'] === 'http://' . $newShorturl || $context['target_url'] === 'https://' . $newShorturl) {
                $this->error(self::ERROR_TARGETURL_IDENTICAL);
                return false;
            }
            return true;
        }
    }

    public function isValid($value, $context = null)
    {
        return $this->containsNoSlash($value) && $this->isNotIdenticalWithTargetUrl($value, $context);
    }
}
