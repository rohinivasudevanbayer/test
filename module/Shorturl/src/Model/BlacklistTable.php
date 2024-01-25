<?php
namespace Shorturl\Model;

use Application\Model\AbstractTable;
use Laminas\Db\Sql\Select;

class BlacklistTable extends AbstractTable
{
    /** @var string TABLENAME The name of the table */
    const TABLENAME = 'su_blacklist';

    public function findByUrlCodeAndDomainId($urlCode, $domainId)
    {
        $selectFactory = function (Select $select) use ($urlCode, $domainId) {
            $select->where(['url_code' => $urlCode, 'domain_id' => $domainId]);
        };
        return $this->tableGateway->select($selectFactory);
    }
}
