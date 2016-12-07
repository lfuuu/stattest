<?php
/**
 * Model for number filling report (see at /voip/filling)
 */

namespace app\models\voip;

use \Exception;
use Yii;
use yii\base\Model;
use yii\data\SqlDataProvider;

class FillingException extends Exception { }

class Filling extends Model
{
    public $number = null;
    public $dateStart = null;
    public $dateEnd = null;

    public function rules ()
    {
        return [
            [['dateStart', 'dateEnd'], 'trim'],
            [['dateStart', 'dateEnd'], 'date', 'format' => 'php:Y-m-d'],
            [['number'], 'string']
        ];
    }

    public function load (array $get)
    {
        if ($get['date'] && $get['number'])
        {
            $d = & $get['date'];
            $d = explode(':', $d);
            $get['dateStart'] = $d[0];
            $get['dateEnd'] = $d[1];
            parent::load($get, '');
            if ($this->validate())
            {
                return true;
            }
            else
            {
                throw new FillingException('Не все параметры введены корректно');
            }
        }
        return true;
    }

    public function getFilling ()
    {
        return new SqlDataProvider([
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
                ':date_start'   => $this->dateStart,
                ':date_end'   => $this->dateEnd
            ],
            'pagination' => false,
            'sort' => false]);
    }
}