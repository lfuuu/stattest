<?php
/**
 * Model for number filling report (see at /voip/filling)
 */

namespace app\models\voip;

use Psr\Log\InvalidArgumentException;
use Yii;
use yii\base\Model;
use yii\data\SqlDataProvider;

class Filling extends Model
{
    const PAGE_SIZE = 10;

    public $number = null;
    public $dateStart = null;
    public $dateEnd = null;
    public $date = '';

    public function rules()
    {
        return [
            [['dateStart', 'dateEnd', 'date'], 'trim'],
            [['dateStart', 'dateEnd'], 'date', 'format' => 'php:Y-m-d'],
            [['number', 'date'], 'string']
        ];
    }

    /**
     * Loading class properties from GET-params
     *
     * @param array $get
     * @return bool
     */
    public function load(array $get)
    {
        if (isset($get['date'], $get['number']) && strpos($get['date'], ':') !== false) {
            list($get['dateStart'], $get['dateEnd']) = explode(':', $get['date']);
            parent::load($get, '');
            if ($this->validate()) {
                return true;
            } else {
                throw new InvalidArgumentException('Не все параметры введены корректно');
            }
        }
        return true;
    }

    /**
     * Getting report data
     *
     * @return SqlDataProvider
     */
    public function getFilling()
    {
        return new SqlDataProvider([
            'db' => 'dbPgSlave',
            'sql' => "SELECT
                        gs.gs || ' - ' || gs.gs + interval '1 hour' AS interval,
                        sn.lines_count,
                        round(SUM (cc.session_time) / 60, 0) AS minutes_count,
                        round(SUM (cc.session_time) / (60 * 60 * sn.lines_count), 3) || ' Эрл' AS filling
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
                         (sn.did = cc.src_number
                                OR
                            sn.did = cc.dst_number)
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
                        gs.gs",
            'params' => [
                ':number' => $this->number,
                ':date_start' => $this->dateStart,
                ':date_end' => $this->dateEnd
            ],
            'pagination' => false
        ]);
    }
}