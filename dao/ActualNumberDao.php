<?php

namespace app\dao;

use app\classes\Singleton;
use app\models\ActualNumber;
use app\models\ClientAccount;
use app\modules\uu\models\ServiceType;

/**
 * @method static ActualNumberDao me($args = null)
 */
class ActualNumberDao extends Singleton
{
    public function collectFromUsages($number = null, $clientId = null)
    {
        $params = [];

        if ($number) {
            $params[":number"] = $number;
        }

        if ($clientId) {
            $params[":client_id"] = $clientId;
        }


        $numbersSQL = "

            SELECT 
                client_id, 
                number,
                region,
                call_count, 
                number_type,
                number7800,
                is_blocked,
                is_disabled,
                " . ClientAccount::VERSION_BILLER_USAGE . " as biller_version
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
                            c.voip_disabled
                        FROM
                            usage_voip u, clients c, client_contract ct
                        WHERE
                            #(actual_from <= DATE_FORMAT(now(), '%Y-%m-%d') and actual_to >= DATE_FORMAT(now(), '%Y-%m-%d'))
                            (now() between activation_dt and expire_dt)
                            AND u.client = c.client 
                            AND ct.id = c.contract_id
                            AND LENGTH(e164) > 3
                            " . ($number ? "and e164 = :number" : "") . "
                            " . ($clientId ? "and c.id = :client_id" : "") . "
                        ORDER BY u.id
                    )a
                WHERE e164 NOT LIKE '7800%'
                )a
            )a";

        $uuNumbersSQL = "
            SELECT
                account_tariff.client_account_id AS client_id,
                account_tariff.voip_number AS number,
                COALESCE(number.region, account_tariff.region_id, city.connection_point_id) AS region,
                COALESCE(resource_calls.call_count, 1) AS call_count,
                IF(LENGTH(account_tariff.voip_number) > 5,'number','nonumber') AS number_type,
                '' AS number7800,
                c.is_blocked,
                c.voip_disabled AS is_disabled,
                " . ClientAccount::VERSION_BILLER_UNIVERSAL . " AS biller_version
            FROM
                clients c,
                uu_account_tariff account_tariff
            LEFT JOIN 
                uu_account_tariff_resource_call_count resource_calls 
                ON resource_calls.account_tariff_id = account_tariff.id
            LEFT JOIN
                voip_numbers number
                ON account_tariff.voip_number = number.number
            LEFT JOIN
                city
                ON account_tariff.city_id = city.id
            WHERE
                    account_tariff.tariff_period_id is not null
                AND account_tariff.voip_number is not null
                AND c.id = account_tariff.client_account_id
                " . ($number ? "AND account_tariff.voip_number = :number" : "") . "
                " . ($clientId ? "AND account_tariff.client_account_id = :client_id" : "") . "
                AND c.account_version = " . ClientAccount::VERSION_BILLER_UNIVERSAL . "
            ";


        $where = "";
        if ($number || $clientId) {
            $where = ' AND account_tariff_id IN (SELECT id FROM uu_account_tariff WHERE ';

            if ($clientId) {
                $where .= 'client_account_id = :client_id';
            } elseif ($number) {
                $where .= 'voip_number = :number';
            } else {
                throw new \Exception('Unknown error');
            }

            $where .= ' AND service_type_id = ' . ServiceType::ID_VOIP . ')';
        }


        $db = ActualNumber::getDb();
        $db->createCommand('DROP TEMPORARY TABLE IF EXISTS `uu_account_tariff_resource_call_count`')->execute();
        $db->createCommand("CREATE TEMPORARY TABLE `uu_account_tariff_resource_call_count` (INDEX(account_tariff_id)) AS
            SELECT log.account_tariff_id, ROUND(amount) as call_count FROM (
              SELECT MAX(id) as max_id, account_tariff_id as account_tariff_id_g 
              FROM `uu_account_tariff_resource_log`
              WHERE resource_id = 7 AND actual_from_utc <= UTC_TIMESTAMP()
              {$where} 
              GROUP BY account_tariff_id) a, uu_account_tariff_resource_log log 
              WHERE log.id = a.max_id", $params)->execute();

        $data = $db->createCommand($numbersSQL . ' UNION ' . $uuNumbersSQL, $params)->queryAll();

        $d = [];
        foreach ($data as $l) {
            $d[$l["number"]] = $l;
        }

        return $d;
    }

    public function loadSaved($number = null, $clientId = null)
    {
        $params = [];

        if ($number) {
            $params[":number"] = $number;
        }

        if ($clientId) {
            $params[":client_id"] = $clientId;
        }

        $data = ActualNumber::getDb()->createCommand("
                SELECT 
                    a.client_id, a.number, a.region, a.call_count, 
                    a.number_type, a.number7800,
                    a.is_blocked, a.is_disabled
                FROM 
                    actual_number a
                LEFT JOIN clients c ON (a.client_id = c.id)
                WHERE 
                if (number like '7800%' , c.account_version = 5, true)
                " . ($number ? "and number = :number" : "") . "
                " . ($clientId ? "and client_id = :client_id" : "") . "
                ORDER BY a.id", $params)->queryAll();

        $d = [];
        foreach ($data as $l) {
            $d[$l["number"]] = $l;
        }

        return $d;
    }
}
