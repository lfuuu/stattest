<?php

namespace app\dao\reports;

use Yii;
use DateTime;
use app\classes\Singleton;
use app\classes\operators\OperatorOnlime;

class ReportOnlimeDao extends Singleton
{

    private
        $dateFrom,
        $dateTo;

    /*
    public function getCount($dateFrom, $dateTo, $filter = [])
    {


        $deliveryList = Yii::$app->getDb()->createCommand($query = "
            SELECT
                t.`id` AS trouble_id, req_no, fio, phone, t.bill_no, date_creation
            FROM
                (
                    SELECT
                        s.`trouble_id`, MAX(s.`stage_id`) AS max_stage_id
                    FROM
                        `tt_stages` s, `tt_doers` d,
                        (
                            SELECT
                                t.`id` AS trouble_id
                            FROM
                                `tt_troubles` t, `tt_stages` s, `tt_doers` d
                            WHERE
                                `date_start` BETWEEN :dateFrom AND :dateTo
                                AND t.`id` = s.`trouble_id`
                                AND t.`client` = :client
                                AND d.`stage_id` = s.`stage_id`
                            GROUP BY
                                t.`id`
                        ) ts
                    WHERE
                        s.`stage_id` = d.`stage_id`
                        AND s.`trouble_id` = ts.`trouble_id`
                    GROUP BY
                        s.`trouble_id`
                ) m, `tt_stages` s, `tt_stages` s2, `tt_troubles` t, `newbills_add_info` a
            WHERE
                m.`max_stage_id` = s.`stage_id`
                AND s.`date_start` BETWEEN :dateFrom AND :dateTo
                AND t.`id` = m.`trouble_id`
                AND t.`bill_no` = a.`bill_no`
                AND s2.`stage_id` = t.`cur_stage_id`
                AND s2.`state_id` IN (2, 20)
        ", [
            ':client' => self::OPERATOR_CLIENT,
            ':dateFrom' => $dateFrom,
            ':dateTo' => $dateTo,
        ])->queryAll();

        $addWhere = $addJoin = '';

        if ($filter['action'] == 'promo') {
            $addWhere = "AND coupon != ''";
            $addJoin = "LEFT JOIN `onlime_order` oo ON (oo.`bill_no` = b.`bill_no`)";
        }
        else if ($filter['action'] == 'no_promo') {
            $addWhere = "AND (coupon = '' OR coupon IS NULL)";
            $addJoin = "LEFT JOIN `onlime_order` oo ON (oo.`bill_no` = b.`bill_no`)";
        }

        $productsFilter = [];
        foreach (self::getGroups() as $groupName => $products) {
            $productsFilter[] = "
                (
                    SELECT SUM(`amount`)
                    FROM `newbill_lines` nl
                    WHERE
                        `item_id` IN ('" . implode('\',\'', $products) . "')
                        AND nl.`bill_no` = t.`bill_no`
                ) AS " . $groupName;
        }

        $result = Yii::$app->getDb()->createCommand($query = "
            SELECT
                SUM(`is_rollback`) AS rollback,
                SUM(IF(`is_rollback` =0 AND `state_id` IN (2,20), 1, 0)) AS close,
                SUM(IF(`is_rollback` = 0 AND `state_id` = 21, 1, 0)) AS reject,
                SUM(IF(`is_rollback` = 0 AND `state_id` NOT IN (2, 20, 21), 1, 0)) AS work,
                COUNT(1) AS count
            FROM
                `tt_troubles` t, `tt_stages` s, `newbills` b
                " . $addJoin . "
            WHERE
                t.`client` = :client
                AND b.`bill_no` = t.`bill_no`
                AND s.`stage_id` = t.`cur_stage_id`
                AND `date_creation` BETWEEN :dateFrom AND :dateTo
                " . $addWhere
        , [
            ':client' => self::OPERATOR_CLIENT,
            ':dateFrom' => $dateFrom,
            ':dateTo' => $dateTo,
        ])->queryAll() + ['delivery' => count($deliveryList)];

        $result['close'] = count($closeList);

        return [$result, $closeList, $deliveryList];
    }
    */

    public function getDeliveryList($dateFrom, $dateTo)
    {
        return Yii::$app->getDb()->createCommand("
            SELECT
                t.`id` AS trouble_id, req_no, fio, phone, t.bill_no, date_creation
            FROM
                (
                    SELECT
                        s.`trouble_id`, MAX(s.`stage_id`) AS max_stage_id
                    FROM
                        `tt_stages` s, `tt_doers` d,
                        (
                            SELECT
                                t.`id` AS trouble_id
                            FROM
                                `tt_troubles` t, `tt_stages` s, `tt_doers` d
                            WHERE
                                `date_start` BETWEEN :dateFrom AND :dateTo
                                AND t.`id` = s.`trouble_id`
                                AND t.`client` = :client
                                AND d.`stage_id` = s.`stage_id`
                            GROUP BY
                                t.`id`
                        ) ts
                    WHERE
                        s.`stage_id` = d.`stage_id`
                        AND s.`trouble_id` = ts.`trouble_id`
                    GROUP BY
                        s.`trouble_id`
                ) m, `tt_stages` s, `tt_stages` s2, `tt_troubles` t, `newbills_add_info` a
            WHERE
                m.`max_stage_id` = s.`stage_id`
                AND s.`date_start` BETWEEN :dateFrom AND :dateTo
                AND t.`id` = m.`trouble_id`
                AND t.`bill_no` = a.`bill_no`
                AND s2.`stage_id` = t.`cur_stage_id`
                AND s2.`state_id` IN (2, 20)
        ", [
            ':client' => OperatorOnlime::OPERATOR_CLIENT,
            ':dateFrom' => $dateFrom,
            ':dateTo' => $dateTo,
        ])->queryAll();
    }

    public function getClosedList($dateFrom, $dateTo, $filter = [])
    {
        $this->prepateDates($dateFrom, $dateTo);

        $addWhere = $addJoin = '';

        if ($filter['action'] == 'promo') {
            $addWhere = "AND coupon != ''";
            $addJoin = "LEFT JOIN `onlime_order` oo ON (oo.`bill_no` = b.`bill_no`)";
        }
        else if ($filter['action'] == 'no_promo') {
            $addWhere = "AND (coupon = '' OR coupon IS NULL)";
            $addJoin = "LEFT JOIN `onlime_order` oo ON (oo.`bill_no` = b.`bill_no`)";
        }

        return Yii::$app->getDb()->createCommand("
            SELECT
                t.`id` AS trouble_id, `req_no`, `fio`, `phone`, `address`, t.`bill_no`, `date_creation`,
                (
                    SELECT `date_start`
                    FROM `tt_stages` s, `tt_doers` d
                    WHERE s.`stage_id` = d.`stage_id` AND s.`trouble_id` = t.`id`
                    ORDER BY s.`stage_id` DESC LIMIT 1) AS date_delivered,
                    " . implode(', ', $this->prepareProducts()) . ",
                    a.`comment1` AS date_deliv,
                    a.`comment2` AS fio_oper
            FROM
                `tt_stages` s, `tt_troubles` t, `newbills_add_info` a, `newbills` b
                " . $addJoin . "
            WHERE
                s.`trouble_id` = t.`id`
                AND s.`date_start` BETWEEN :dateFrom AND :dateTo
                AND t.`client` = :client
                AND s.`state_id` IN (2,20)
                AND t.`bill_no` = a.`bill_no`
                AND b.`bill_no` = t.`bill_no`
                AND `is_rollback` = 0
                " . $addWhere . "
            GROUP BY s.`trouble_id`
        ", [
            ':client' => OperatorOnlime::OPERATOR_CLIENT,
            ':dateFrom' => $this->dateFrom,
            ':dateTo' => $this->dateTo,
        ])->queryAll();
    }

    public function getList($dateFrom, $dateTo, $filter = [])
    {
        $this->prepateDates($dateFrom, $dateTo);
        $addWhere = $addJoin = '';

        if ($filter['action'] == 'promo') {
            $addWhere = "AND coupon != ''";
            $addJoin = "LEFT JOIN `onlime_order` oo ON (oo.`bill_no` = b.`bill_no`)";
        }
        else if($filter['action'] == "no_promo") {
            $addWhere = "AND (coupon = '' OR coupon IS NULL)";
            $addJoin = "LEFT JOIN `onlime_order` oo ON (oo.`bill_no` = b.`bill_no`)";
        }

        $sTypes = [
            'work'   => [
                'sql' => '`is_rollback` = 0 AND `state_id` NOT IN (2,20,21)',
                'title' => 'В Обработке',
            ],
            'close'  => [
                'sql' => '`is_rollback` = 0 AND `state_id` IN (2,20)',
                'title' => 'Доставлен'
            ],
            'reject' => [
                'sql' => '`is_rollback` = 0 AND `state_id` = 21',
                'title' => 'Отказ',
            ],
            'delivery' => [
                'title' => 'Доставка',
            ],
            'rollback' => [
                'sql' => '`is_rollback` = 1',
                'title' => 'Возврат',
            ],
        ];

        $list = [];
        if (isset($sTypes[ $filter['mode'] ])) {
            if($filter['mode'] == 'close') {
                $list = $this->getClosedList($dateFrom, $dateTo, $filter);
            }
            else if($filter['mode'] == 'delivery') {
                $list = $this->getDeliveryList($dateFrom, $dateTo, $filter);
            }
            else {
                $list = Yii::$app->getDb()->createCommand("
                    SELECT
                        t.`id` AS trouble_id,
                        t.`bill_no`,
                        t.`problem`,
                        `req_no`, `fio`, `phone`, `address`, `date_creation`,
                        " . implode(', ', $this->prepareProducts()) . ",
                        (
                            SELECT `date_start`
                            FROM `tt_stages` s, `tt_doers` d
                            WHERE
                                s.`stage_id` = d.`stage_id`
                                AND s.`trouble_id` = t.`id`
                            ORDER BY
                                s.`stage_id` DESC
                            LIMIT 1
                        ) AS date_delivered,
                        i.`comment1` AS date_deliv,
                        i.`comment2` AS fio_oper
                    FROM
                        `tt_troubles` t, `tt_stages` s, `newbills_add_info` i, `newbills` b
                        " . $addJoin . "
                    WHERE
                        t.`client` = :client
                        AND s.`stage_id` = t.`cur_stage_id`
                        AND `date_creation` BETWEEN :dateFrom AND :dateTo
                        AND i.`bill_no` = t.`bill_no`
                        AND t.`bill_no` = b.`bill_no`
                        AND " . $sTypes[ $filter['mode'] ]['sql'] . "
                        " . $addWhere . "
                    ORDER BY
                        date_creation
                ", [
                    ':client' => OperatorOnlime::OPERATOR_CLIENT,
                    ':dateFrom' => $this->dateFrom,
                    ':dateTo' => $this->dateTo,
                ])->queryAll();
            }
            print_r($list);

            foreach($list as &$l)
            {
                if (isset($l['date_deliv']) && $l['date_deliv']) {
                    @list(, $l['date_deliv']) = explode(': ', $l['date_deliv']);
                }

                if (isset($l['fio_oper']) && $l['fio_oper']) {
                    @list(, $l['fio_oper']) = explode(': ', $l['fio_oper']);
                }

                $l['address'] = $this->prepareAddress($l['address']);
                $l['phone'] = $this->preparePhone($l['phone']);

                $l['stages'] = Yii::$app->getDb()->createCommand("
                    SELECT
                        S.*,
                        IF(S.`date_edit` = 0, NULL, `date_edit`) AS date_edit,
                        tts.`name` AS state_name
                    FROM
                        `tt_stages` S INNER JOIN `tt_states` tts ON tts.`id` = `state_id`
                    WHERE
                        `trouble_id` = :trouble_id
                    ORDER BY
                        `stage_id` ASC
                ",[
                    ':trouble_id' => $l['trouble_id'],
                ])->queryAll();

                foreach($l['stages'] as &$s) {
                    $s['doers'] = Yii::$app->getDb()->createCommand("
                        SELECT
                            `td`.`doer_id`,
                            `cr`.`name`,
                            `cr`.`depart`
                        FROM
                            `tt_doers` `td` LEFT JOIN `courier` `cr` ON `cr`.`id` = `td`.`doer_id`
                        WHERE
                            `td`.`stage_id` = :stage_id
                        ORDER BY
                            `cr`.`depart`,
                            `cr`.`name`
                    ", [
                        ':stage_id' => $s['stage_id'],
                    ])->queryAll();
                }
            }
        }

        return $list;
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

    private function prepareProducts()
    {
        $productsFilter = [];
        $count = 1;
        foreach (OperatorOnlime::$requestProducts as $product) {
            $productsFilter[] = "
                (
                    SELECT SUM(`amount`)
                    FROM `newbill_lines` nl
                    WHERE
                        `item_id` IN ('" . implode('\',\'', (array) $product['id_1c']) . "')
                        AND nl.`bill_no` = t.`bill_no`
                ) AS group_" . $count;
            $count++;
        }
        return $productsFilter;
    }

    private function prepareAddress($address)
    {
        if (strpos($address, '^') !== false) {
            list($street, $home, $bild, $porch, $floor, $flat, $intercom) = explode(' ^ ', $address . ' ^  ^  ^  ^  ^  ^  ^  ^ ');
            $a = $street;
            if ($home)
                $a .= ', д.' . $home;
            if ($bild)
                $a .= ' стр.' . $bild;
            if ($porch)
                $a .= ', подъезд ' . $porch;
            if ($floor)
                $a .= ', этаж ' . $floor;
            if ($flat)
                $a .= ', кв.' . $flat;
            if ($intercom)
                $a .= ' (домофон: ' . $intercom . ')';
            return $a;
        }

        return $address;
    }

    private function preparePhone($phone)
    {
        if (strpos($phone, '^') !== false) {
            list($home, $mob, $work) = explode(' ^ ', $phone . ' ^  ^  ^ ');
            $p = array();

            if($home)
                $p[] = 'Домашний: ' . $home;
            if($mob)
                $p[] = 'Сотовый: ' . $mob;
            if($work)
                $p[] = 'Рабочий: ' . $work;

            return implode('<br />', $p);
        }

        return $phone;
    }

}