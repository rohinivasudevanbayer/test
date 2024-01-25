<?php
namespace Shorturl\Model;

use Application\Model\AbstractTable;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Predicate;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Db\TableGateway\TableGatewayInterface;
use Shorturl\Model\Shorturl;
use Shorturl\Model\ShorturlsTable;

class ShorturlVisitsTable extends AbstractTable
{
    /** @var string TABLENAME The name of the table */
    const TABLENAME = 'su_visits';

    /**
     * Constructor
     *
     * @param TableGatewayInterface $shorturlsHistoryTableGateway
     * @param ShorturlsTable $shorturlsTable
     */
    public function __construct(
        TableGatewayInterface $shorturlsHistoryTableGateway,
        ShorturlsTable $shorturlsTable
    ) {
        parent::__construct($shorturlsHistoryTableGateway);
        $this->shorturlsTable = $shorturlsTable;
        $this->dbAdapter = $this->tableGateway->getAdapter();
    }

    /**
     * Deletes all shorturl visits for the given shorturl id
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
     * Helper function which creates the JSON for the statistics ajax response
     *
     * @param array $data
     * @return void
     */
    private function buildStatisticsJson(array $data)
    {
        $countData = 0;
        $json = '[';
        foreach ($data as $key => $value) {
            $countData++;

            $json .= '{';
            if (isset($value["label"])) {
                $json .= '"label": ' . '"' . $value["label"] . '",';
            }
            $json .= '"data": [';
            if (!empty($value['data'])) {
                $countVisits = 0;
                foreach ($value['data'] as $visit) {
                    $countVisits++;
                    $json .= '[ ' . $visit . ']';
                    if (count($value['data']) !== $countVisits) {
                        $json .= ',';
                    }
                }
            }
            $json .= ']}';
            if (count($data) !== $countData) {
                $json .= ',';
            }
        }
        $json .= ']';
        return $json;
    }

    /**
     * Converts the db result into a linear result series (with 0 as value instead of missing key/value pair)
     *
     * @param array $dbResult
     * @param string $tickStep Step between values (as string usable in strtotime, e.g. '+1 day')
     * @param int $minTs Starting timestamp (in ms) of the linear series
     * @param int $maxTs Ending timestamp (in ms) of the linear series
     * @return array
     */
    private function normalizeDbResult(array $dbResult, string $tickStep, int $minTs, int $maxTs)
    {
        $result = [];
        foreach ($dbResult as $index => $shorturlData) {
            $result[$index] = [];
            for ($timestamp = $minTs; $timestamp <= $maxTs; $timestamp = strtotime($tickStep, $timestamp)) {
                $visits = empty($shorturlData[$timestamp]) ? 0 : $shorturlData[$timestamp];
                $result[$index]['data'][] = 1000 * $timestamp . ',' . $visits;
            }
            if (isset($shorturlData['label'])) {
                $result[$index]['label'] = $shorturlData['label'];
            }
        }
        return $result;
    }

    /**
     * Get the data for short periods
     *
     * @param array $shorturlIds
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getDayStatistic(array $shorturlIds, string $startDate, string $endDate): array
    {
        $result = [];

        foreach ($shorturlIds as $index => $shorturlId) {
            $result[$index] = [];
            try {
                $shorturlObj = $this->shorturlsTable->getById($shorturlId);
                $result[$index]['label'] = $shorturlObj->short_url;

                $sql = new Sql($this->dbAdapter);
                $select = $sql->select(self::TABLENAME)
                    ->columns([
                        'visits' => new \Laminas\Db\Sql\Expression('COUNT(*)'),
                        'month' => new \Laminas\Db\Sql\Expression('MONTH(visit_date)'),
                        'year' => new \Laminas\Db\Sql\Expression('YEAR(visit_date)'),
                        'day' => new \Laminas\Db\Sql\Expression('DAY(visit_date)'),
                    ])
                    ->where(['shorturl_id' => $shorturlId])
                    ->where(function (Where $where) use ($startDate, $endDate) {
                        $where->addPredicate(
                            new Predicate\Between('visit_date', $startDate, $endDate)
                        );
                    })
                    ->group(['month', 'year', 'day'])
                    ->order(['year', 'month', 'day']);

                $selectString = $sql->buildSqlString($select);
                $rs = $this->dbAdapter->query($selectString, $this->dbAdapter::QUERY_MODE_EXECUTE);

                foreach ($rs as $key => $row) {
                    $timestamp = strtotime($row['year'] . '-' . $row['month'] . '-' . $row['day'] . ' UTC');
                    $result[$index][$timestamp] = (int) $row['visits'];
                }
            } catch (\Exception $e) {
                // ignore - shorturl not found
            }
        }
        $minTs = strtotime($startDate . 'UTC');
        $maxTs = strtotime($endDate . 'UTC');
        $result = $this->normalizeDbResult($result, '+1 day', $minTs, $maxTs);
        return array('jsonData' => $this->buildStatisticsJson($result));
    }

    /**
     * Get the data for medium periods
     *
     * @param array $shorturlIds
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getWeekStatistic(array $shorturlIds, string $startDate, string $endDate): array
    {
        $result = [];
        $minTs = strtotime($startDate . 'UTC');
        $minTs = date('Y', $minTs) . 'W' . date('W', $minTs) . ' UTC';
        $minTs = strtotime($minTs);
        $maxTs = strtotime($endDate . 'UTC');
        $maxTs = date('Y', $maxTs) . 'W' . date('W', $maxTs) . ' UTC';
        $maxTs = strtotime($maxTs);
        foreach ($shorturlIds as $index => $shorturlId) {
            $result[$index] = [];
            try {
                $shorturlObj = $this->shorturlsTable->getById($shorturlId);
                $result[$index]['label'] = $shorturlObj->short_url;

                $sql = new Sql($this->dbAdapter);
                $select = $sql->select(self::TABLENAME)
                    ->columns([
                        'visits' => new \Laminas\Db\Sql\Expression('COUNT(*)'),
                        'week' => new \Laminas\Db\Sql\Expression('WEEK(visit_date)'),
                        'year' => new \Laminas\Db\Sql\Expression('YEAR(visit_date)'),
                    ])
                    ->where(['shorturl_id' => $shorturlId])
                    ->where(function (Where $where) use ($startDate, $endDate) {
                        $where->addPredicate(
                            new Predicate\Between('visit_date', $startDate, $endDate)
                        );
                    })
                    ->group(['week', 'year'])
                    ->order(['year', 'week']);

                $selectString = $sql->buildSqlString($select);
                $rs = $this->dbAdapter->query($selectString, $this->dbAdapter::QUERY_MODE_EXECUTE);

                foreach ($rs as $key => $row) {
                    $timestamp = strtotime($row['year'] . 'W' . sprintf('%02d', $row['week']) . ' UTC');
                    $result[$index][$timestamp] = (int) $row['visits'];
                }
            } catch (\Exception $e) {
                // ignore - shorturl not found
            }
        }
        $result = $this->normalizeDbResult($result, '+1 week', $minTs, $maxTs);
        return array('jsonData' => $this->buildStatisticsJson($result));
    }

    /**
     * Get the data for long periods
     *
     * @param array $shorturlIds
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getMonthStatistic(array $shorturlIds, string $startDate, string $endDate): array
    {
        $result = [];

        foreach ($shorturlIds as $index => $shorturlId) {
            $result[$index] = [];
            try {
                $shorturlObj = $this->shorturlsTable->getById($shorturlId);
                $result[$index]['label'] = $shorturlObj->short_url;

                $sql = new Sql($this->dbAdapter);
                $select = $sql->select(self::TABLENAME)
                    ->columns([
                        'visits' => new \Laminas\Db\Sql\Expression('COUNT(*)'),
                        'month' => new \Laminas\Db\Sql\Expression('MONTH(visit_date)'),
                        'year' => new \Laminas\Db\Sql\Expression('YEAR(visit_date)'),
                    ])
                    ->where(['shorturl_id' => $shorturlId])
                    ->where(function (Where $where) use ($startDate, $endDate) {
                        $where->addPredicate(
                            new Predicate\Between('visit_date', $startDate, $endDate)
                        );
                    })
                    ->group(['month', 'year'])
                    ->order(['year', 'month']);

                $selectString = $sql->buildSqlString($select);
                $rs = $this->dbAdapter->query($selectString, $this->dbAdapter::QUERY_MODE_EXECUTE);

                foreach ($rs as $key => $row) {
                    $timestamp = strtotime($row['year'] . '-' . $row['month'] . '-01 UTC');
                    $result[$index][$timestamp] = (int) $row['visits'];
                }
            } catch (\Exception $e) {
                // ignore - shorturl not found
            }
        }
        $minTs = strtotime($startDate . 'UTC');
        $maxTs = strtotime($endDate . 'UTC');
        $result = $this->normalizeDbResult($result, '+1 month', $minTs, $maxTs);
        return array('jsonData' => $this->buildStatisticsJson($result));
    }
}
