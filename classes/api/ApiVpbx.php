<?php
namespace app\classes\api;

use Yii;
use app\classes\JSONQuery;
use yii\base\Exception;
use app\models\Usage;
use app\models\UsageVirtpbx;
use app\models\ClientAccount;

class ApiVpbx
{
    public static function isAvailable()
    {
        return self::getVpbxHost() && self::getApiUrl();
    }

    public static function getVpbxHost()
    {
        return defined("PHONE_SERVER") ? PHONE_SERVER : false;
    }

    public static function getApiUrl()
    {
        return defined('VIRTPBX_URL') && VIRTPBX_URL ? VIRTPBX_URL : false;
    }

    public static function exec($action, $data)
    {

        if (!self::isAvailable()) {
            throw new Exception('API Vpbx was not configured');
        }

        $url = self::getApiUrl();
        $host = self::getVpbxHost();

        $url = strtr($url, array("[address]" => $host, "[action]" => $action));

        $result = JSONQuery::exec($url, $data);

        if (isset($result["errors"]) && $result["errors"]) {
            $msg = !isset($result['errors'][0]["message"]) && isset($result['errors'][0])
                ? "Текст ошибки не найден! <br>\n" . var_export($result['errors'][0], true)
                : '';
            throw new Exception($msg ?: $result["errors"][0]["message"], $result["errors"][0]["code"]);
        }

        return $result;
    }

    public static function create($clientId, $usageId)
    {
        $tariff = self::getTariff($usageId);

        $regionId = 99;

        try {
            $u = UsageVirtpbx::findOne($usageId);
            if ($u) {
                $regionId = $u->region;
            }
        } catch (Exception $e) {
        }

        ApiVpbx::exec(
            'create',
            [
                "client_id" => (int)$clientId,
                "stat_product_id" => (int)$usageId,
                "numbers" => [],
                "phones" => $tariff["num_ports"],
                "faxes" => (int)$tariff["is_fax"] ? 5 : 0,
                "record" => (bool)$tariff["is_record"],
                "enable_web_call" => (bool)$tariff["is_web_call"],
                "disk_space" => (int)$tariff["space"],
                "timezone" => ClientAccount::findOne($clientId)->timezone_name,
                "region" => $regionId
            ]
        );
    }

    public static function transfer(
        $fromAccountId, $fromUsageId,
        $toAccountId, $toUsageId
    )
    {
        $query = [
            'from_account_id' => $fromAccountId,
            'from_stat_product_id' => $fromUsageId,
            'to_account_id' => $toAccountId,
            'to_stat_product_id' => $toUsageId
        ];

        ApiVpbx::exec('transfer', $query);
    }

    public static function stop($clientId, $usageId)
    {
        ApiVpbx::exec(
            'delete',
            [
                "client_id"  => (int)$clientId,
                "stat_product_id"  => (int)$usageId,
            ]
        );
    }

    public static function updateConnectionData($clientId, $usageId, $regionId)
    {
        $tariff = self::getTariff($usageId);

        ApiVpbx::exec(
            'update',
            [
                "client_id"         => (int)$clientId,
                "stat_product_id"   => (int)$usageId,
                "phones"            => $tariff["num_ports"],
                "faxes"             => $tariff["is_fax"] ? 5 : 0,
                "record"            => (bool)$tariff["is_record"],
                "enable_web_call"   => (bool)$tariff["is_web_call"],
                "region"            => (int)$regionId,
            ]
        );
    }

    /**
     * Получаем статистику по занятому пространству
     *
     * @return int занятое просторанство
     */
    public static function getUsageSpaceStatistic($clientId, $usageId, $date)
    {
        return self::getStatistic($clientId, $usageId, $date, "get_total_space_usage", "total");
    }

    /**
     * Получаем статистику по количеству используемых портов
     *
     * @return int кол-во используемых портов
     */
    public static function getUsageNumbersStatistic($clientId, $usageId, $date)
    {
        return self::getStatistic($clientId, $usageId, $date, "get_int_number_usage", "int_number_amount");
    }

    public static function getStatistic($clientId, $usageId, $date, $statisticFunction = "get_total_space_usage", $statisticField = "total")
    {
        $result = self::exec(
            $statisticFunction,
            [
                "client_id" => (int)$clientId,
                "stat_product_id" => (int)$usageId,
                "date" => $date,
            ]
        );

        if (!isset($result[$statisticField])) {
            throw new Exception(
                isset($result["errors"]) && $result["errors"]
                    ? implode("; ", $result["error"])
                    : "Ошибка получения статистики"
            );
        }

        return $result[$statisticField];
    }

    public static function getTariff($usageId)
    {
        $command =
            Yii::$app->db->createCommand("
                SELECT
                    t.num_ports,
                    t.space,
                    t.is_record,
                    t.is_fax,
                    t.is_web_call
                FROM (select (
                        select
                            id_tarif
                        from
                            log_tarif
                        where
                                id_service = u.id
                            and service = 'usage_virtpbx'
                            and date_activation < now()
                        ORDER BY
                        date_activation DESC, id DESC LIMIT 1
                        ) as tarif_id
                    FROM usage_virtpbx u
                    WHERE u.id = :usageId ) u
                LEFT JOIN tarifs_virtpbx t ON (t.id = u.tarif_id)
            ", [':usageId' => $usageId]);

        return $command->queryOne();
    }

}
