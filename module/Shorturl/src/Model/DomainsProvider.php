<?php
namespace Shorturl\Model;

/**
 * This class provides access to the available domains. Instead of using a db table like in the model table classes,
 * it makes use of a static array to store data.
 */
class DomainsProvider
{
    /** @var array $domains A static array which stores all data for the available domains (instead of DB) */
    private $domains = [];

    /**
     * Constructor
     *
     * @param array $domains The domains data as array
     * @return void
     */
    public function __construct($domains)
    {
        $this->domains = $domains;
    }

    /**
     * Retrieve id by given domain name
     *
     * @param string $domain
     *            The domain name
     * @return integer
     */
    public function getIdByDomain($domain): int
    {
        $element = $this->searchForFieldValue('domain', $domain);
        if ($element) {
            return $element['id'];
        }
        return 0;
    }

    /**
     * Fetches the domain object by domain
     *
     * @param string $domain
     * @return Domain
     */
    public function getByDomain($domain): Domain
    {
        $element = $this->searchForFieldValue('domain', $domain);
        if ($element) {
            return $this->arrayToObject($element);
        }
        return null;
    }

    /**
     * Fetches the domain by id
     *
     * @param integer $id
     * @return Domain
     */
    public function getById($id): Domain
    {
        $element = $this->searchForFieldValue('id', (int) $id);
        if ($element) {
            return $this->arrayToObject($element);
        }
        return null;
    }

    /**
     * Fetches all domains of the given type
     *
     * @param string $type
     * @return array
     */
    public function getByType($type): array
    {
        $result = array_filter($this->domains, function ($el) use ($type) {
            return $el['type'] === $type;
        });
        array_walk($result, function (&$el) {
            $el = $this->arrayToObject($el);
        });
        return $result;
    }

    /**
     * Fetches all domains
     *
     * @return array
     */
    public function fetchAll(): array
    {
        $result = $this->domains;
        array_walk($result, function (&$el) {
            $el = $this->arrayToObject($el);
        });
        return $result;
    }

    /**
     * Fetches all domains and returns them as array with db ids as array keys
     *
     * @return array
     */
    public function fetchAllWithIdsAsKeys(): array
    {
        $domains = $this->domains;
        $result = [];
        foreach ($domains as $domain) {
            $result[$domain['id']] = $domain;
        }
        return $result;
    }

    /**
     * Returns the elements with the given fieldValue in the field with the given fieldName
     *
     * @param string $fieldName
     * @param string $fieldValue
     * @return string|null
     */
    private function searchForFieldValue($fieldName, $fieldValue)
    {
        foreach ($this->domains as $key => $val) {
            if ($val[$fieldName] === $fieldValue) {
                return $val;
            }
        }
        return null;
    }

    /**
     * Converts an single domain array element into a Domain object
     *
     * @param array $array
     * @return Domain
     */
    private function arrayToObject($array): Domain
    {
        $obj = new Domain();
        $obj->exchangeArray($array);
        return $obj;
    }
}
