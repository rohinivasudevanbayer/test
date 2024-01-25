<?php
namespace Shorturl\Model;

use Application\Model\AbstractTable;

class Shorturls2UsersTable extends AbstractTable
{
    /** @var string TABLENAME The name of the table */
    const TABLENAME = 'su_shorturls2users';

    /**
     * Fetches a list of shared shorturl ids
     *
     * @param integer $userId
     *            The id of the current user
     * @return array
     */
    public function getShortUrlIdsByUser(int $userId): array
    {
        $ids = [];
        $rowset = $this->tableGateway->select(['user_id' => $userId]);
        foreach ($rowset as $row) {
            $ids[] = $row->shorturl_id;
        }
        return $ids;
    }

    /**
     * Returns if the given shorturl is shared to the given user
     *
     * @param integer $shorturlId
     * @param integer $userId
     * @return boolean
     */
    public function isShare(int $shorturlId, int $userId): bool
    {
        $rowset = $this->tableGateway->select(['user_id' => $userId, 'shorturl_id' => $shorturlId]);
        return 0 < count($rowset);
    }

    /**
     * Deletes the share defined by the given url and user
     *
     * @param integer $shorturlId
     * @param integer $userId
     * @return boolean
     */
    public function removeShare(int $shorturlId, int $userId): bool
    {
        return $this->tableGateway->delete(['shorturl_id' => $shorturlId, 'user_id' => $userId]);
    }

    /**
     * Deletes all shares for the given shorturl id
     *
     * @param integer $shorturlId
     * @return integer
     */
    public function deleteByShorturlId(int $shorturlId): int
    {
        return $this->tableGateway->delete(['shorturl_id' => $shorturlId]);
    }
}
