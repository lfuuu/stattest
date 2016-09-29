<?php

use app\helpers\DateTimeZoneHelper;

class m160218_150650_virtpbx_stat_missdata extends \app\classes\Migration
{
    public function up()
    {
        return; // она разово запустилась, выполнилась и хватит. А на тесте она не нужна
        $dates = Yii::$app->db->createCommand('
            SELECT
                missed.*,
                vs3.date AS end_period_date,
                vs3.use_space AS end_use_space,
                vs3.numbers AS end_numbers,
                ABS(TO_DAYS(vs3.date) - TO_DAYS(missed.start_period_date)) - 1 AS period
            FROM (
                SELECT
                   vs1.client_id,
                   vs1.usage_id,
                   vs1.date AS start_period_date,
                   vs1.use_space AS start_use_space,
                   vs1.numbers AS start_numbers,
                   vs1.ext_did_count
                FROM
                    virtpbx_stat vs1
                        LEFT JOIN virtpbx_stat vs2
                            ON
                                vs1.client_id = vs2.client_id
                                AND vs1.usage_id = vs2.usage_id
                                AND vs1.date + INTERVAL 1 day = vs2.date
                WHERE
                    vs2.date IS NULL
            ) missed
                LEFT JOIN virtpbx_stat vs3
                    ON
                        vs3.client_id = missed.client_id
                        AND vs3.usage_id = missed.usage_id
                        AND vs3.date > missed.start_period_date
            WHERE
                vs3.date IS NOT NULL
            GROUP BY
                missed.start_period_date,
                missed.client_id,
                missed.usage_id
        ')->queryAll();

        $insert = [];

        foreach ($dates as $record) {
            $date = (new DateTime($record['start_period_date']));
            $useSpace =
                (
                $record['start_use_space'] == $record['end_use_space']
                    ? $record['start_use_space']
                    : round(abs($record['end_use_space'] - $record['start_use_space']) / $record['period'])
                );
            $numbers =
                (
                $record['start_numbers'] == $record['end_numbers']
                    ? $record['start_numbers']
                    : abs($record['end_numbers'] - $record['start_numbers']) / $record['period']
                );

            for ($i = 0, $s = $record['period']; $i < $s; $i++) {
                $useSpaceI = $record['start_use_space'] + ($useSpace * ($i + 1));
                $useNumbersI = round($record['start_numbers'] + ($numbers * $i + 1));

                $insert[] = [
                    $record['client_id'],
                    $record['usage_id'],
                    $date->modify('+1 day')->format(DateTimeZoneHelper::DATE_FORMAT),
                    ($useSpaceI < $record['end_use_space'] ? $useSpaceI : $record['end_use_space']),
                    ($useNumbersI < $record['end_numbers'] ? $useNumbersI : $record['end_numbers']),
                    $record['ext_did_count'],
                ];
            }
        }

        if (count($insert)) {
            $chunks = array_chunk($insert, 1000);

            foreach ($chunks as $chunk) {
                $this->batchInsert(
                    'virtpbx_stat',
                    [
                        'client_id',
                        'usage_id',
                        'date',
                        'use_space',
                        'numbers',
                        'ext_did_count'
                    ],
                    $chunk
                );
            }
        }
    }

    public function down()
    {
    }
}