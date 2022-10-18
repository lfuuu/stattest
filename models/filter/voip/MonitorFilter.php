<?php

namespace app\models\filter\voip;

use app\classes\Form;
use app\classes\validators\FormFieldValidator;
use app\helpers\DateTimeZoneHelper;
use app\models\billing\CallsCdr;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\db\Expression;
use yii\db\QueryBuilder;

class MonitorFilter extends Form
{
//    public $range;
    public $number_a;
    public $number_b;

    public $date_from;
    public $date_to;

    public $orig_account;
    public $term_account;

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
            [['number_a', 'number_b'], 'string'],
            [['number_a', 'number_b'], FormFieldValidator::class],
            ['is_with_session_time', 'integer'],
            [['orig_account', 'term_account'], 'integer'],
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

            'orig_account' => 'ЛС номера А',
            'term_account' => 'ЛС номера B',

            'number_a' => 'Номера А',
            'number_b' => 'Номера В',

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

        $this->orig_account = preg_replace('/[^\d]/', '', $this->orig_account);
        $this->term_account = preg_replace('/[^\d]/', '', $this->term_account);

        $this->number_a = preg_replace('/[^\d]/', '', $this->number_a);
        $this->number_b = preg_replace('/[^\d]/', '', $this->number_b);

        $srcCdrNumberSql = '';
        $numbersA = [];
        if ($this->orig_account) {
            $numbersA = AccountTariff::find()
                ->where([
                    'client_account_id' => $this->orig_account,
                    'service_type_id' => ServiceType::ID_VOIP
                ])
                ->distinct()
                ->select('voip_number')
                ->column() ?: [];
        }

        if ($this->number_a) {
            $numbersA[] = $this->number_a;
        }

        if ($numbersA) {
            $numbersWithout7 = array_map(function ($number) {
                return substr($number, 1);
            }, $numbersA);
            $numbersA = array_merge($numbersA, $numbersWithout7);
            $srcCdrNumberSql = ' AND (src_number like \'' . implode('%\' OR src_number like \'', $numbersA) . '%\')';
        }

        $dstCdrNumberSql = '';
        $numbersB = [];
        if ($this->term_account) {
            $numbersB = AccountTariff::find()
                ->where([
                    'client_account_id' => $this->term_account,
                    'service_type_id' => ServiceType::ID_VOIP
                ])
                ->distinct()
                ->select('voip_number')
                ->column();
        }

        if ($this->number_b) {
            $numbersB[] = $this->number_b;
        }

        if ($numbersB) {
            $numbersWithout7 = array_map(function ($number) {
                return substr($number, 1);
            }, $numbersB);
            $numbersB = array_merge($numbersB, $numbersWithout7);
            $dstCdrNumberSql = ' AND (dst_number like \'' . implode('%\' OR dst_number like \'', $numbersB) . '%\')';
        }


        $query = <<< SQL
with cdr as (
    SELECT *
    FROM "calls_cdr"."cdr"
    WHERE
        ("connect_time" BETWEEN '{$fromStr}' AND '{$toStr}')
        {$srcCdrNumberSql}
        {$dstCdrNumberSql}
    ORDER BY "connect_time" DESC
), calls as (
    select raw.*
    from calls_raw.calls_raw raw,
         cdr
    where raw.server_id = cdr.server_id
      and raw.cdr_id = cdr.id
      and (raw."connect_time" BETWEEN '{$fromStr}' AND '{$toStr}')
)

select cdr.server_id, cdr.id as cdr_id, 
       cdr.src_number as cdr_num_a, cdr.dst_number  as cdr_num_b, cdr.connect_time + interval '3 hours' as cdr_connect_time, cdr.setup_time, cdr.session_time, src_route, dst_route
, c_orig.src_number as orig_num_a, c_orig.dst_number as orig_num_b, c_orig.account_id as orig_account
, c_term.src_number as term_num_a, c_term.dst_number as term_num_b, c_term.account_id as term_account
from cdr
left join calls c_orig on cdr.server_id = c_orig.server_id and cdr.id = c_orig.cdr_id and c_orig.orig
left join calls c_term on cdr.server_id = c_term.server_id and cdr.id = c_term.cdr_id and not c_term.orig
WHERE True
SQL;


        if ($this->orig_account && !$numbersA) {
            $query .= " AND c_orig.account_id = '" . $this->orig_account . "'";
        }

        if ($this->term_account && !$numbersB) {
            $query .= " AND c_term.account_id = '" . $this->term_account . "'";
        }

        if ($this->number_a) {
            $query .= " AND COALESCE(c_orig.src_number, c_term.src_number) = " . $this->number_a . "::bigint";
        }

        if ($this->number_b) {
            $query .= " AND COALESCE(c_orig.dst_number, c_term.dst_number) = '" . $this->number_b . "'::bigint";
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
