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

        $sTypes = OperatorOnlime::$requestModes;

        $list = [];
        if (isset($sTypes[ $filter['mode'] ])) {
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

                $l['stages'] = $this->getTroubleStages($l['trouble_id']);
            }
        }

        return $list;
    }

    public function getTroubleStages($troubleId)
    {
        $stages = Yii::$app->getDb()->createCommand("
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
            ':trouble_id' => $troubleId,
        ])->queryAll();

        foreach ($stages as &$stage) {
            $stage['doers'] = Yii::$app->getDb()->createCommand("
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
                ':stage_id' => $stage['stage_id'],
            ])->queryAll();
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