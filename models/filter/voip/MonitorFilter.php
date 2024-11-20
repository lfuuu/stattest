<?php

namespace app\models\filter\voip;

use app\classes\Form;
use app\classes\validators\FormFieldValidator;
use app\helpers\DateTimeZoneHelper;
use app\models\billing\CallsCdr;
use app\models\voip\StateServiceVoip;
use yii\data\ArrayDataProvider;

class MonitorFilter extends Form
{
//    public $range;
    public $number;

    public $date_from;
    public $date_to;

    public $account;

    public $is_with_session_time = 1;

    public function init()
    {
        $this->date_from = date('Y-m-d\TH:i', strtotime('+3 hours', strtotime('-5 minute')));
        $this->date_to = date('Y-m-d\TH:i', strtotime('+3 hours'));
    }


    public function rules()
    {
        global $fixclient_data;

        return [
//            ['range', 'required'],
//            ['range', 'in', 'range' => $this->getRanges()],
//            [['date_from', 'date_to'], 'require'],
            [['date_from', 'date_to'], 'datetime'],
            [['number'], 'string'],
            [['number'], FormFieldValidator::class],
            ['is_with_session_time', 'integer'],
            [['account'], 'integer'],
//            ['orig_account', 'default', 'value' => $fixclient_data['id'] ?? null],

        ];
    }

    public function attributeLabels()
    {
        return [
            'charge_time' => 'Дата/время',
            'src_number' => 'Номера А',
            'dst_number' => 'Номера В',
            'dst_route' => 'Исходящий транк',
            'cost' => 'Стоимость',
            'cost_gr' => 'Цена',
            'rate' => 'Ставка',
            'count' => 'Кол-во частей',

            'connect_time' => 'Время',
            'session_time' => 'Длительность',
            'is_with_session_time' => 'Звонки с длительностью',

            'account' => 'ЛС номера А/B',

            'number' => 'Номера А/B',

            'date_from' => 'Период начала звонка "С" (Мск)',
            'date_to' => 'Период начала звонка "По" (Мск)',

            'cdr_connect_time' => 'Время соединения (Мск)',
        ];
    }

    public function search()
    {
        $from = \DateTime::createFromFormat('Y-m-d\TH:i', $this->date_from)->modify('-3 hours');
        $to = \DateTime::createFromFormat('Y-m-d\TH:i', $this->date_to)->modify('-3 hours');

        $fromStr = $from->format(DateTimeZoneHelper::DATETIME_FORMAT);
        $toStr = $to->format(DateTimeZoneHelper::DATETIME_FORMAT);

        $this->account = preg_replace('/[^\d]/', '', $this->account);

        $this->number = preg_replace('/[^\d]/', '', $this->number);

        $cdrNumberSql = '';
        $numbers = [];
        $ranges = [];

        if ($this->number) {
            $numbers[] = $this->number;
        }

        if ($this->account) {
            $ranges = array_map(fn(StateServiceVoip $s) => [
                'number' => $s->e164,
                'activation_dt' => $s->activation_dt,
                'expire_dt' => $s->expire_dt
            ], StateServiceVoip::findAll(['client_id' => $this->account]));

            $numbers = array_unique(array_map(fn($n) => $n['number'], $ranges));

            if ($this->number) {
                $numbers = array_filter($numbers, fn($n) => $n == $this->number);
            }
        }

        if ($numbers) {
            foreach ($numbers as $number) {
                $numbersWithout7 = substr($number, 1);
                $_numbers = [$number, $numbersWithout7];
                $numberSql = ' src_number like \'' . implode('%\' OR src_number like \'', $_numbers) . '%\'';
                $numberSql .= ' OR  dst_number like \'' . implode('%\' OR dst_number like \'', $_numbers) . '%\' ';

                if ($ranges) {
                    $rangeSql = "";

                    $numberRanges = array_filter($ranges, fn(array $range) => $range['number'] == $number);

                    if ($numberRanges) {
                        $rangeSql = implode(" OR ",
                            array_map(fn(array $range) => "connect_time > '{$range['activation_dt']}'" .
                                ($range['expire_dt'] ? " AND connect_time < '{$range['expire_dt']}'" : '')
                                , $numberRanges));
                    }

                    if ($rangeSql) {
                        $numberSql = " ($numberSql) AND ($rangeSql) ";
                    }
                }
                $cdrNumberSql .= PHP_EOL . ($cdrNumberSql ? " OR " : "    ") . " ($numberSql) ";
            }
        }

        !$cdrNumberSql && $cdrNumberSql = " true ";


        $query = <<< SQL
WITH cdr AS (
    SELECT *
    FROM "calls_cdr"."cdr"
    WHERE
        ("connect_time" BETWEEN '{$fromStr}' AND '{$toStr}')
        AND ({$cdrNumberSql})
    ORDER BY "connect_time" DESC
    LIMIT 1000000
), calls AS (
    SELECT raw.*
    FROM calls_raw.calls_raw raw,
         cdr
    WHERE raw.server_id = cdr.server_id
      AND raw.cdr_id = cdr.id
      AND (raw."connect_time" BETWEEN '{$fromStr}' AND '{$toStr}')
)

SELECT cdr.server_id, cdr.id as cdr_id, cdr.mcn_callid,
       cdr.src_number as cdr_num_a, cdr.dst_number  as cdr_num_b, cdr.connect_time + INTERVAL '3 hours' as cdr_connect_time, cdr.setup_time, cdr.session_time, src_route, dst_route
, c_orig.src_number as orig_num_a, c_orig.dst_number as orig_num_b, c_orig.account_id as orig_account
, c_term.src_number as term_num_a, c_term.dst_number as term_num_b, c_term.account_id as term_account
FROM cdr
LEFT JOIN calls c_orig on cdr.server_id = c_orig.server_id and cdr.id = c_orig.cdr_id and c_orig.orig
LEFT JOIN calls c_term on cdr.server_id = c_term.server_id and cdr.id = c_term.cdr_id and not c_term.orig
WHERE True
SQL;


        if ($this->account && !$numbers) {
            $query .= " AND (c_orig.account_id = '" . $this->account . "' OR c_term.account_id = '" . $this->account . "')";
        }

        if ($this->number) {
            $query .= " AND (COALESCE(c_orig.src_number, c_term.src_number) = " . $this->number . "::bigint OR COALESCE(c_orig.dst_number, c_term.dst_number) = '" . $this->number . "'::bigint)";
        }

        if ($this->is_with_session_time) {
            $query .= ' AND cdr.session_time > 0';
        }

        $query .= PHP_EOL . "ORDER BY cdr.connect_time DESC";

//        echo "<pre>";
//        print_r($query);
//        echo "</pre>";

        return new ArrayDataProvider([
            'allModels' => CallsCdr::getDb()->createCommand($query)->queryAll(),
        ]);
    }


}
