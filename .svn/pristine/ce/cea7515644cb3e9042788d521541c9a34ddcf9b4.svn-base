<?php
class DomainsData
{
    const TYPE_INTRANET = 'intranet';
    const TYPE_INTERNET = 'internet';

    const DOMAINS = [
        ['id' => 2, 'domain' => 'go.cnb', 'type' => self::TYPE_INTRANET],
        ['id' => 3, 'domain' => 'go.bayer.com', 'type' => self::TYPE_INTERNET],
    ];

    public function __invoke()
    {
        return self::DOMAINS;
    }
}
$domainsData = new DomainsData;
return $domainsData();
