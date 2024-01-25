<?php
namespace Shorturl\Model;

use Application\Model\AbstractTable;

class Admins2DomainsTable extends AbstractTable
{
    /** @var string TABLENAME The name of the table */
    const TABLENAME = 'su_admins2domains';

    /**
     * Retrieve list of domain ids user is admin for
     *
     * @param integer $userId
     *            The id of the current logged in user
     * @return array
     */
    public function getAdminDomainIds(int $userId): array
    {
        $ids = [];
        $rowset = $this->tableGateway->select(['user_id' => $userId]);
        foreach ($rowset as $row) {
            $ids[] = $row->domain_id;
        }
        return $ids;
    }

    /**
     * Returns if the given user is admin for the given domain
     *
     * @param integer $userId
     * @param integer $domainId
     * @return boolean
     */
    public function isAdmin(int $userId, int $domainId): bool
    {
        $rowset = $this->tableGateway->select(['user_id' => $userId, 'domain_id' => $domainId]);
        return 0 < count($rowset);
    }

    /**
     * Delete all domain admin assignments for the given user
     *
     * @param integer $userId
     * @return void
     */
    public function deleteByUserId(int $userId)
    {
        return $this->tableGateway->delete(['user_id' => $userId]);
    }

    /**
     * Find an admin domain assignment by user id and domain id
     *
     * @param integer $userId
     * @param integer $domainId
     * @throws RuntimeException if not found
     * @return Admin2Domain
     */
    public function getByUserIdAndDomainId(int $userId, int $domainId): Admin2Domain
    {
        $rowset = $this->tableGateway->select(['user_id' => $userId, 'domain_id' => $domainId]);
        $row = $rowset->current();
        if (!$row) {
            throw new \RuntimeException(sprintf(
                'Could not find row with identifiers %d %d',
                $userId,
                $domainId
            ));
        }

        return $row;
    }

    /**
     * Delete an admin domain assignment by user id and domain id
     *
     * @param integer $userId
     * @param integer $domainId
     * @throws RuntimeException if not found
     * @return int number of deleted rows
     */
    public function deleteByUserIdAndDomainId(int $userId, int $domainId): int
    {
        return $this->tableGateway->delete(['user_id' => $userId, 'domain_id' => $domainId]);
    }
}
