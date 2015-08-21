<?php
namespace app\dao;

use app\classes\Singleton;
use app\models\ActualNumber;

/**
 * @method static ActualNumberDao me($args = null)
 * @property
 */
class ActualNumberDao extends Singleton
{
    public function collectFromUsages($number = null, $clientId = null)
    {
        $params = [];

        if ($number)
            $params[":number"] = $number;

        if ($clientId)
            $params[":client_id"] = $clientId;


        $data = ActualNumber::getDb()->createCommand("

            SELECT 
                client_id, number, region, call_count, 
                number_type, direction, number7800,
                is_blocked, is_disabled 
            FROM
            ( SELECT 
                a.*, 
                IF (number_type = 'nonumber', IFNULL((SELECT e164 FROM usage_voip u WHERE line7800_id = usage_id AND CAST(NOW() AS  DATE) BETWEEN actual_from AND actual_to LIMIT 1), '') , '') AS number7800
            FROM (
                SELECT 
                    client_id, 
                    e164 AS number, 
                    region, 
                    usage_id,
                    no_of_lines AS call_count, 
                    IF(is_virtual, 'vnumber', IF(LENGTH(e164) > 5,'number','nonumber')) AS number_type,
                    allowed_direction AS direction, 
                    #line7800_id,
                    is_blocked, 
                    voip_disabled AS is_disabled

                FROM (
                        SELECT
                            c.id AS client_id,
                            TRIM(e164) AS e164,
                            u.no_of_lines,
                            u.region,
                            u.id as usage_id,
                            #IFNULL((SELECT an.id FROM usage_voip u7800, actual_number an WHERE u7800.id = u.line7800_id and an.number = u7800.e164), 0) AS line7800_id,
                            c.is_blocked AS is_blocked,
                            IFNULL((
                                SELECT 
                                    is_virtual 
                                FROM 
                                    log_tarif lt, tarifs_voip tv 
                                WHERE 
                                        service = 'usage_voip' 
                                    AND id_service = u.id 
                                    AND id_tarif = tv.id 
                                ORDER BY lt.date_activation DESC, lt.id DESC 
                                LIMIT 1), 0) AS is_virtual,
                            allowed_direction,
                            c.voip_disabled
                        FROM
                            usage_voip u, clients c, client_contract ct
                        WHERE
                            (actual_from <= DATE_FORMAT(now(), '%Y-%m-%d') and actual_to >= DATE_FORMAT(now(), '%Y-%m-%d'))
                            AND u.client = c.client 
                            AND ct.id = c.contract_id
                            AND ((c.status in ('negotiations','work','connecting','testing')) or c.id = 9130 or ct.business_process_status_id in (8, 9, 19))
                            AND LENGTH(e164) > 3
                            ".($number ? "and e164 = :number" : "")."
                            ".($clientId ? "and c.id = :client_id" : "")."
                        ORDER BY u.id
                    )a
                WHERE e164 NOT LIKE '7800%'
                )a
            )a
                    ", $params)->queryAll();

        $d = array();
        foreach($data as $l)
            $d[$l["number"]] = $l;

        return $d;

    }

    public function loadSaved($number = null, $clientId = null)
    {
        $params = [];

        if ($number)
            $params[":number"] = $number;

        if ($clientId)
            $params[":client_id"] = $clientId;

        $data = ActualNumber::getDb()->createCommand("
                SELECT 
                    client_id, number, region, call_count, 
                    number_type, direction, number7800,
                    is_blocked, is_disabled 
                FROM 
                    actual_number a
                WHERE number not like '7800%'
                ".($number ? "and number = :number" : "")."
                ".($clientId ? "and client_id = :client_id" : "")."
                ORDER BY id", $params)->queryAll();

        $d = array();
        foreach($data as $l)
            $d[$l["number"]] = $l;

        return $d;
    }
}
