<?php
/**
 * Model for number workload report (see at /voip/cdr-workload)
 */

namespace app\models\voip\filter;

use Yii;
use yii\base\Model;
use yii\data\SqlDataProvider;
use yii\data\ArrayDataProvider;

class CdrWorkload extends Model
{
    public $number = null;
    public $dateStart = null;
    public $dateEnd = null;
    public $date = null;
    public $period = 'hour';

    /**
     * Rule array
     *
     * @return array
     */
    public function rules()
    {
        return [
            [['dateStart', 'dateEnd', 'date'], 'trim'],
            [['dateStart', 'dateEnd'], 'date', 'format' => 'php:Y-m-d'],
            [['number', 'date'], 'string'],
            [['dateStart', 'dateEnd', 'date'], 'required'],
            ['period', 'in', 'range' => ['hour', 'day', 'week', 'month'], 'strict' => true]
        ];
    }

    /**
     * Loading class properties from GET-params
     *
     * @param array $get
     *
     * @return bool
     */
    public function load(array $get)
    {
        if (!isset($get['date']) || strpos($get['date'], ':') === false) {
            Yii::$app->session->addFlash('error', 'Не задан период или имеет неверный формат');
            return false;
        }

        list($get['dateStart'], $get['dateEnd']) = explode(':', $get['date']);
        $dateStart = new \DateTime($get['dateStart']);
        $dateEnd = new \DateTime($get['dateEnd']);
        $interval = $dateEnd->diff($dateStart);
        if ($interval->m > 1 || ($interval->m && ($interval->i || $interval->d || $interval->h || $interval->s))) {
            Yii::$app->session->addFlash('error', 'Временной период больше одного месяца');
            return false;
        }

        parent::load($get, '');

        if (!$this->validate(['period'])) {
            $this->period = 'hour';
        }

        return $this->validate();
    }

    /**
     * Getting report data
     *
     * @return SqlDataProvider | ArrayDataProvider
     */
    public function getWorkload()
    {
        if ($this->number) {
            return $this->getWorkloadForNumber();
        } else {
            return $this->getWorkloadForDateInterval();
        }
    }

    /**
     * Return data with specified number
     *
     * @return ArrayDataProvider|SqlDataProvider
     */
    protected function getWorkloadForNumber()
    {
        if (!$this->dateStart || !$this->dateEnd) {
            return new ArrayDataProvider(
                [
                    'allModels' => [],
                ]
            );
        }

        $sql = "SELECT
                        gs.gs || ' - ' || gs.gs + interval '1 hour' AS interval,
                        sn.lines_count,
                        SUM (cc.session_time) AS seconds_count,
                        round(SUM (cc.session_time) / (60 * 60 * sn.lines_count), 3) || ' Эрл' AS workload
                    FROM
                        calls_cdr.cdr AS cc
                    RIGHT JOIN
                        generate_series (:date_start::timestamp ,:date_end::timestamp,'1 hour'::interval) AS gs
                        ON
                        cc.connect_time >= gs.gs 
                                AND
                            cc.connect_time <= gs.gs + interval '1 hour'
                    LEFT JOIN
                        billing.service_number AS sn
                        ON
                          sn.did = :number
                            AND
                          sn.activation_dt <= gs.gs 
                              AND
                          sn.expire_dt >= gs.gs + interval '1 hour'
                    WHERE
                        cc.connect_time >= :date_start
                          AND 
                        cc.connect_time <= :date_end
                          AND
                        (cc.src_number = :number
                             OR
                         cc.dst_number = :number)
                    GROUP BY
                        gs.gs,
                        sn.lines_count
                    ORDER BY
                        gs.gs";
        $params = [
            ':number' => $this->number,
            ':date_start' => $this->dateStart,
            ':date_end' => $this->dateEnd
        ];

        return new SqlDataProvider(
            [
                'db' => 'dbPgSlave',
                'sql' => $sql,
                'params' => $params,
                'pagination' => false
            ]
        );
    }

    /**
     * Return data with specified date interval
     *
     * @return ArrayDataProvider|SqlDataProvider
     */
    protected function getWorkloadForDateInterval()
    {
        if (!$this->dateStart || !$this->dateEnd) {
            return new ArrayDataProvider(
                [
                    'allModels' => [],
                ]
            );
        }

        $sql = "SELECT
                        sn.did AS number,
                        sn.lines_count,
                        SUM (cc.session_time) AS seconds_count,
                        round(SUM (cc.session_time) / (60 * 60 * sn.lines_count), 3) || ' Эрл' AS workload
                    FROM
                        billing.service_number AS sn
                    RIGHT JOIN
                        calls_cdr.cdr AS cc
                        ON
                            (cc.src_number = sn.did
                                OR
                            cc.dst_number = sn.did)
                                AND
                            cc.connect_time BETWEEN :date_start AND :date_end
                    WHERE
                        sn.activation_dt BETWEEN :date_start AND :date_end
                             OR
                         sn.expire_dt BETWEEN :date_start AND :date_end
                    GROUP BY
                        did,
                        lines_count
                    ORDER BY
                        lines_count DESC,
                        workload DESC";
        $params = [
            ':date_start' => $this->dateStart,
            ':date_end' => $this->dateEnd,
        ];

        return new SqlDataProvider(
            [
                'db' => 'dbPgSlave',
                'sql' => $sql,
                'params' => $params,
                'pagination' => false
            ]
        );
    }

    /**
     * Return a workload report to API client
     * 
     * @return array
     */
    public function getNumberWorkload()
    {
        if (!$this->dateStart || !$this->dateEnd || !$this->number) {
            return [];
        }

        $sql = "WITH tmp AS (SELECT
                        gs.gs || ' - ' || gs.gs + interval '1 {$this->period}' AS interval,
                        round(SUM (cc.session_time) / (60 * 60 * sn.lines_count), 3) AS workload
                    FROM
                        calls_cdr.cdr AS cc
                    RIGHT JOIN
                        generate_series (:date_start::timestamp , :date_end::timestamp, '1 {$this->period}'::interval) AS gs
                        ON
                        cc.connect_time >= gs.gs 
                          AND
                        cc.connect_time <= gs.gs + interval '1 {$this->period}'
                    LEFT JOIN
                        billing.service_number AS sn
                        ON
                          sn.did = :number
                            AND
                          sn.activation_dt <= gs.gs 
                            AND
                          sn.expire_dt >= gs.gs + interval '1 {$this->period}'
                    WHERE
                        cc.connect_time BETWEEN :date_start AND :date_end
                          AND
                        (cc.src_number = :number
                             OR
                         cc.dst_number = :number)
                    GROUP BY
                        gs.gs,
                        sn.lines_count
                    ORDER BY
                        gs.gs)

                SELECT
                    interval,
                    workload  || ' Эрл' AS workload
                FROM
                    tmp
                UNION
                SELECT
                    :date_start || ' - ' || :date_end,
                    'Минимальное значение: ' || min(workload) || ' Эрл'
                FROM
                    tmp
                UNION
                SELECT
                    :date_start || ' - ' || :date_end,
                    'Максимальное значение: ' || max(workload) || ' Эрл'
                FROM
                    tmp
                UNION
                SELECT
                    :date_start || ' - ' || :date_end,
                    'Среднее значение: ' || round(avg(workload), 3) || ' Эрл'
                FROM
                    tmp
                ORDER BY
                    workload";

        $params = [
            ':number' => $this->number,
            ':date_start' => $this->dateStart,
            ':date_end' => $this->dateEnd,
        ];

        return Yii::$app->dbPgSlave
            ->createCommand($sql, $params)
            ->queryAll();
    }
}
