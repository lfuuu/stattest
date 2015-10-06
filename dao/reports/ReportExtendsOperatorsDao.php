<?php

namespace app\dao\reports;

use Yii;
use DateTime;
use yii\db\Expression;
use yii\db\Query;
use app\classes\Singleton;
use app\classes\operators\Operators;

class ReportExtendsOperatorsDao extends Singleton
{

    public
        $operator,
        $dateFrom,
        $dateTo;

    public function setOperator(Operators $operator)
    {
        $this->operator = $operator;
        return $this;
    }

    public function getReportResult($dateFrom, $dateTo, $mode = '', $promo = '')
    {
        $this->prepateDates($dateFrom, $dateTo);

        $query = new Query;

        $query->select([
            't.id AS trouble_id',
            't.bill_no',
            't.problem',
            'req_no',
            'fio',
            'phone',
            'address',
            'date_creation',
            new Expression('(
                SELECT `date_start`
                FROM `tt_stages` s, `tt_doers` d
                WHERE
                    s.`stage_id` = d.`stage_id`
                    AND s.`trouble_id` = t.`id`
                ORDER BY
                    s.`stage_id` DESC
                LIMIT 1
            ) AS date_delivered'),
            new Expression('(
                SELECT GROUP_CONCAT(`serial` SEPARATOR ", ") FROM `g_serials` s WHERE s.`bill_no` = t.`bill_no`
            ) AS serials'),
            new Expression('(
                SELECT CONCAT(`coupon`) FROM `onlime_order` oo WHERE oo.`bill_no` = t.`bill_no`
            ) AS coupon'),
            'i.comment1 AS date_deliv',
            'i.comment2 AS fio_oper',
        ]);
        $this->prepareProducts($query);

        $query->from([
            'tt_troubles t',
            'tt_stages s',
            'newbills_add_info i',
            'newbills b',
        ]);

        if ($promo) {
            $query->leftJoin('onlime_order oo', 'oo.bill_no = b.bill_no');
        }

        $query->where('i.bill_no = t.bill_no');
        $query->andWhere('t.bill_no = b.bill_no');
        $query->andWhere(['t.client' => $this->operator->operatorClient]);

        switch ($promo) {
            case 'promo':
                $query->andWhere(['!=', 'coupon', '']);
                break;
            case 'no_promo':
                $query->andWhere('coupon != "" OR coupon IS NULL');
                break;
            default:
                break;
        }

        $query->groupBy('s.`trouble_id`');
        $query->orderBy('date_creation ASC');

        $modes = $this->operator->requestModes;
        $items = [];

        if (!array_key_exists($mode, $modes)) {
            return $items;
        }

        $mode = $modes[ $mode ];
        if (array_key_exists('queryModify', $mode) && method_exists($this->operator, $mode['queryModify'])) {
            $this->operator->{$mode['queryModify']}($query, $this);
        }

        $items = $query->createCommand()->queryAll();

        foreach ($items as &$item) {
            if (array_key_exists('date_deliv', $item) && $item['date_deliv']) {
                @list(, $item['date_deliv']) = explode(': ', $item['date_deliv']);
            }

            if (array_key_exists('fio_oper', $item) && $item['fio_oper']) {
                @list(, $item['fio_oper']) = explode(': ', $item['fio_oper']);
            }

            $item['address'] = $this->prepareAddress($item['address']);
            $item['phone'] = $this->preparePhone($item['phone']);

            $item['stages'] = $this->getTroubleStages($item['trouble_id']);
        }

        return $items;
    }

    public function getTroubleStages($troubleId)
    {
        $query = new Query;

        $query->select([
            's.*',
            new Expression('IF(s.`date_edit` = 0, NULL, `date_edit`) AS date_edit'),
            'tts.name AS state_name',
        ]);

        $query->from('tt_stages s');
        $query->innerJoin('tt_states tts', 'tts.id = state_id');

        $query->where(['trouble_id' => $troubleId]);

        $query->orderBy('stage_id ASC');

        $stages = $query->createCommand()->queryAll();

        foreach ($stages as &$stage) {
            $query = new Query;

            $query->select([
                'td.doer_id',
                'cr.name',
                'cr.depart',
            ]);

            $query->from('tt_doers td');
            $query->leftJoin('courier cr', 'cr.id = td.doer_id');

            $query->where(['td.stage_id' => $stage['stage_id']]);

            $query->orderBy('cr.depart ASC, cr.name ASC');

            $stage['doers'] = $query->createCommand()->queryAll();
        }

        return $stages;
    }

    private function prepateDates($dateFrom, $dateTo)
    {
        $this->dateFrom = new DateTime($dateFrom);
        $this->dateFrom = $this->dateFrom->setTime(0, 0, 0);
        $this->dateFrom = $this->dateFrom->format('Y-m-d H:i:s');

        $this->dateTo = new DateTime($dateTo);
        $this->dateTo = $this->dateTo->setTime(23, 59, 59);
        $this->dateTo = $this->dateTo->format('Y-m-d H:i:s');
    }

    private function prepareProducts(Query $query)
    {
        $products = [];
        foreach ($this->operator->products as $key => $product) {
            if (is_string($key)) {
                $key = 'count_' . $key;
            }
            else {
                $key = 'count_' . ($key + 1);
            }

            $products[] = new Expression("
                (
                    SELECT SUM(`amount`)
                    FROM `newbill_lines` nl
                    WHERE
                        `item_id` IN ('" . implode('\',\'', (array) $product['id_1c']) . "')
                        AND nl.`bill_no` = t.`bill_no`
                ) AS " . $key
            );
        }
        $query->select = array_merge($query->select, $products);
    }

    private function prepareAddress($address)
    {
        if (strpos($address, '^') !== false) {
            list($street, $home, $bild, $porch, $floor, $flat, $intercom) = explode(' ^ ', $address . ' ^  ^  ^  ^  ^  ^  ^  ^ ');
            $a = $street;

            if ($home) {
                $a .= ', д.' . $home;
            }
            if ($bild) {
                $a .= ' стр.' . $bild;
            }
            if ($porch) {
                $a .= ', подъезд ' . $porch;
            }
            if ($floor) {
                $a .= ', этаж ' . $floor;
            }
            if ($flat) {
                $a .= ', кв.' . $flat;
            }
            if ($intercom) {
                $a .= ' (домофон: ' . $intercom . ')';
            }

            return $a;
        }

        return $address;
    }

    private function preparePhone($phone)
    {
        if (strpos($phone, '^') !== false) {
            list($home, $mob, $work) = explode(' ^ ', $phone . ' ^  ^  ^ ');
            $p = array();

            if ($home) {
                $p[] = 'Домашний: ' . $home;
            }
            if ($mob) {
                $p[] = 'Сотовый: ' . $mob;
            }
            if ($work) {
                $p[] = 'Рабочий: ' . $work;
            }

            return implode('<br />', $p);
        }

        return $phone;
    }

}