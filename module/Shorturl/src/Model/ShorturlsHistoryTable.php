<?php
namespace Shorturl\Model;

use Application\Model\AbstractTable;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\Sql\Select;
use Laminas\Paginator\Adapter\DbSelect;
use Laminas\Paginator\Paginator;
use Shorturl\Model\Shorturl;

class ShorturlsHistoryTable extends AbstractTable
{
    /** @var string TABLENAME The name of the table */
    const TABLENAME = 'su_shorturls_history';

    /**
     * Deletes all shorturl history entries for the given shorturl id
     *
     * @param integer $shorturlId
     * @return integer
     */
    public function deleteByShorturlId(int $shorturlId): int
    {
        $shorturlId = (int) $shorturlId;
        return $this->tableGateway->delete(['shorturl_id' => $shorturlId]);
    }

    /**
     * Returns all history entries for the given shorturl
     *
     * @param integer $id
     * @return Paginator
     */
    public function getByShorturlIdPaginated(int $id): Paginator
    {
        $id = (int) $id;

        $select = new Select($this->tableGateway->getTable());
        $select->where(['shorturl_id' => $id])
            ->order('id desc'); // do not order by updated_by because restored revisions do not get a new updated_by

        $resultSetPrototype = new ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new Shorturl());

        $paginatorAdapter = new DbSelect(
            $select,
            $this->tableGateway->getAdapter(),
            $resultSetPrototype
        );

        $paginator = new Paginator($paginatorAdapter);
        return $paginator;
    }

}
