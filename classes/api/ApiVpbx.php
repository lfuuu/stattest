<?php
namespace app\classes\api;

use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\Resource;
use app\classes\uu\model\ServiceType;
use Yii;
use app\classes\JSONQuery;
use yii\base\Exception;
use app\models\UsageVirtpbx;
use app\models\ClientAccount;
use app\models\Region;

class ApiVpbx
{
    public static function isAvailable()
    {
        return self::getPhoneHost() && self::getApiUrl();
    }

    public static function getPhoneHost()
    {
        return isset(\Yii::$app->params['PHONE_SERVER']) ? \Yii::$app->params['PHONE_SERVER'] : false;
    }

    public static function getVpbxHost()
    {
        return isset(\Yii::$app->params['VPBX_SERVER']) ? \Yii::$app->params['VPBX_SERVER'] : false;
    }

    public static function getApiUrl()
    {
        return isset(\Yii::$app->params['VIRTPBX_URL']) && \Yii::$app->params['VIRTPBX_URL'] ? \Yii::$app->params['VIRTPBX_URL'] : false;
    }

    public static function exec($action, $data, $toServer = 'phone', $isSendPost = true)
    {
        $url = self::getApiUrl();
        $host = null;

        if ($toServer == 'phone') {
            $host = self::getPhoneHost();
        }elseif ($toServer == 'vpbx') {
            $host = self::getVpbxHost();
        }

        if (!$url || !$host) {
            throw new Exception('API Vpbx was not configured');
        }

        $url = strtr($url, array("[address]" => $host, "[action]" => $action));

        $result = JSONQuery::exec($url, $data, $isSendPost);

        if (isset($result["errors"]) && $result["errors"]) {
            $msg = !isset($result['errors'][0]["message"]) && isset($result['errors'][0])
                ? "Текст ошибки не найден! <br>\n" . var_export($result['errors'][0], true)
                : '';
            throw new Exception($msg ?: $result["errors"][0]["message"], $result["errors"][0]["code"]);
        }

        return $result;
    }

    public static function create($clientId, $usageId, $billerVersion = ClientAccount::VERSION_BILLER_USAGE)
    {
        $tariff = null;

        switch ($billerVersion) {

            case ClientAccount::VERSION_BILLER_USAGE: {
                $tariff = self::getTariff($usageId);
                break;
            }

            case ClientAccount::VERSION_BILLER_UNIVERSAL: {
                $tariff = self::getTariffUniversal($usageId);
                break;
            }
        }

        if (!$tariff) {
            throw new \Exception('bad tariff');
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
                "region" => $tariff['region']
            ]
        );
    }

    public static function transfer(
        $fromAccountId,
        $fromUsageId,
        $toAccountId,
        $toUsageId
    ) {
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
                "client_id" => (int)$clientId,
                "stat_product_id" => (int)$usageId,
            ]
        );
    }

    public static function update($clientId, $usageId, $regionId, $billerVersion = ClientAccount::VERSION_BILLER_USAGE)
    {
        if ($billerVersion == ClientAccount::VERSION_BILLER_USAGE) {
            $tariff = self::getTariff($usageId);
        } elseif ($billerVersion == ClientAccount::VERSION_BILLER_UNIVERSAL) {
            $tariff = self::getTariffUniversal($usageId);
        } else {
            throw new \Exception('bad biller version');
        }

        if (!$tariff) {
            throw new \Exception('bad tariff');
        }

        ApiVpbx::exec(
            'update',
            [
                "client_id" => (int)$clientId,
                "stat_product_id" => (int)$usageId,
                "phones" => $tariff["num_ports"],
                "faxes" => $tariff["is_fax"] ? 5 : 0,
                "record" => (bool)$tariff["is_record"],
                "enable_web_call" => (bool)$tariff["is_web_call"],
                "region" => (int)$regionId,
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

    /**
     * @param \DateTime $date
     * @return mixed JSON array
     * @throws Exception
     */
    public static function getResourceStatistics(\DateTime $date)
    {
        return ApiVpbx::exec('get_resource_usage_per_day', ['date' => $date->format('Y-m-d')]);
    }

    /**
     * @param int $clientAccountId
     * @param int $usageVpbxId
     * @param \DateTimeImmutable $date
     * @return []. int_number_amount=>... или errors=>...
     */
    public static function getResourceVoipLines($clientAccountId, $usageVpbxId, \DateTimeImmutable $date)
    {
        return ApiVpbx::exec('get_int_number_usage', [
            'client_id' => $clientAccountId,
            'stat_product_id' => $usageVpbxId,
            'date' => $date->format('Y-m-d'),
        ]);
    }

    public static function getStatistic(
        $clientId,
        $usageId,
        $date,
        $statisticFunction = "get_total_space_usage",
        $statisticField = "total"
    ) {
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

    /**
     * Возвращает подготовленное описание для синхронизации тарифа ВАТС. "Старые" услуги.
     * @param $usageId
     * @return array|false
     */
    public static function getTariff($usageId)
    {
        $command =
            Yii::$app->db->createCommand("
                SELECT
                    t.num_ports,
                    t.space,
                    t.is_record,
                    t.is_fax,
                    t.is_web_call,
                    region
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
                        ) as tarif_id,
                        u.region
                    FROM usage_virtpbx u
                    WHERE u.id = :usageId ) u
                LEFT JOIN tarifs_virtpbx t ON (t.id = u.tarif_id)
            ", [':usageId' => $usageId]);

        return $command->queryOne();
    }

    /**
     * Возвращает список подключенных телефонных номеров на платформе
     * @return array
     */
    public static function getPhoneServices()
    {
        return  self::exec('services/phone/', [], 'vpbx', false);
    }

    /**
     * Возвращает список подключенных телефонных номеров на платформе
     * @return array
     */
    public static function getVpbxServices()
    {
        return  self::exec('services/vpbx/', [], 'vpbx', false);
    }

    /**
     * Возвращает подготовленное описание для синхронизации тарифа ВАТС. Универсальные услуги.
     * @param $usageId
     * @return array
     * @throws \Exception
     */
    public static function getTariffUniversal($usageId)
    {
        $accountTariff = AccountTariff::findOne(['id' => $usageId]);

        if (!$accountTariff || !$accountTariff->tariffPeriod || $accountTariff->service_type_id != ServiceType::ID_VPBX) {
            throw new \Exception('bad tariff');
        }

        $data = [
            "num_ports" => 0,
            "space" => 0,
            "is_record" => 0,
            "is_fax" => 0,
            "is_web_call" => 0,
            "region" => $accountTariff->region_id
        ];

        foreach ($accountTariff->tariffPeriod->tariff->tariffResources as $tariffResource) {
            switch ($tariffResource->resource_id) {

                case Resource::ID_VPBX_ABONENT: {
                    $data['num_ports'] = $tariffResource->amount;
                    break;
                }

                case Resource::ID_VPBX_DISK: {
                    $data['space'] = $tariffResource->amount;
                    break;
                }

                case Resource::ID_VPBX_RECORD: {
                    $data['is_record'] = $tariffResource->amount ? 1 : 0;
                    break;
                }

                case Resource::ID_VPBX_FAX: {
                    $data['is_fax'] = $tariffResource->amount ? 1 : 0;
                    break;
                }
            }
        }

        return $data;
    }
}
