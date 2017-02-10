<?php
namespace app\classes\api;

use app\classes\HttpClient;
use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\Resource;
use app\classes\uu\model\ServiceType;
use app\helpers\DateTimeZoneHelper;
use app\models\ActualVirtpbx;
use app\models\ClientAccount;
use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;

class ApiVpbx
{
    /**
     * @return bool
     */
    public static function isAvailable()
    {
        return self::getPhoneHost() && self::getApiUrl();
    }

    /**
     * @return string|bool
     */
    public static function getPhoneHost()
    {
        return isset(Yii::$app->params['PHONE_SERVER']) ? Yii::$app->params['PHONE_SERVER'] : false;
    }

    /**
     * @return string|bool
     */
    public static function getVpbxHost()
    {
        return isset(Yii::$app->params['VPBX_SERVER']) ? Yii::$app->params['VPBX_SERVER'] : false;
    }

    /**
     * @return string|bool
     */
    public static function getApiUrl()
    {
        return isset(Yii::$app->params['VIRTPBX_URL']) ? Yii::$app->params['VIRTPBX_URL'] : false;
    }

    /**
     * @return array
     */
    public static function getApiAuthorization()
    {
        return isset(Yii::$app->params['VPBX_API_AUTHORIZATION']) ? Yii::$app->params['VPBX_API_AUTHORIZATION'] : [];
    }

    /**
     * @param string $action
     * @param array $data
     * @param string $toServer
     * @param bool $isSendPost
     * @return mixed
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\base\InvalidCallException
     * @throws \yii\base\InvalidConfigException
     */
    public static function exec($action, $data, $toServer = 'phone', $isSendPost = true)
    {
        $url = self::getApiUrl();
        $host = null;

        switch ($toServer) {
            case 'phone':
                $host = self::getPhoneHost();
                break;
            case 'vpbx':
                $host = self::getVpbxHost();
                break;
        }

        if (!$url || !$host) {
            throw new InvalidConfigException('API Vpbx was not configured');
        }

        $url = strtr($url, ['[address]' => $host, '[action]' => $action]);

        return (new HttpClient)
            ->createJsonRequest()
            ->setMethod($isSendPost ? 'post' : 'get')
            ->setData($data)
            ->setUrl($url)
            ->auth(self::getApiAuthorization())
            ->getResponseDataWithCheck();
    }

    /**
     * @param int $clientId
     * @param int $usageId
     * @param int $billerVersion
     * @throws \Exception
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\base\InvalidCallException
     * @throws \yii\base\InvalidConfigException
     */
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

        self::exec(
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

    /**
     * Перенос ВАТС с номерами
     *
     * @param int $fromAccountId
     * @param int $fromUsageId
     * @param int $toAccountId
     * @param int $toUsageId
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\base\InvalidCallException
     * @throws \yii\base\InvalidConfigException
     */
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

        self::exec('transfer', $query);
    }

    /**
     * Перенос ВАТС без номеров
     *
     * @param int $fromAccountId
     * @param int $fromUsageId
     * @param int $toAccountId
     * @param int $toUsageId
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\base\InvalidCallException
     * @throws \yii\base\InvalidConfigException
     */
    public static function transferVpbxOnly(
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

        self::exec('transfer_vpbx_only', $query);
    }

    /**
     * @param int $clientId
     * @param int $usageId
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\base\InvalidCallException
     * @throws \yii\base\InvalidConfigException
     */
    public static function stop($clientId, $usageId)
    {
        self::exec(
            'delete',
            [
                "client_id" => (int)$clientId,
                "stat_product_id" => (int)$usageId,
            ]
        );
    }

    /**
     * @param int $clientId
     * @param int $usageId
     * @param int $regionId
     * @param int $billerVersion
     * @throws \Exception
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\base\InvalidCallException
     * @throws \yii\base\InvalidConfigException
     */
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

        self::exec(
            'update',
            [
                "client_id" => (int)$clientId,
                "stat_product_id" => (int)$usageId,
                "phones" => $tariff["num_ports"],
                "faxes" => $tariff["is_fax"] ? 5 : 0,
                "record" => (bool)$tariff["is_record"],
                "disk_space" => (int)$tariff["space"],
                "enable_web_call" => (bool)$tariff["is_web_call"],
                "region" => (int)$regionId,
            ]
        );
    }

    /**
     * Получаем статистику по занятому пространству
     *
     * @param int $clientId
     * @param int $usageId
     * @param string $date
     * @return int занятое просторанство
     * @throws \yii\base\Exception
     */
    public static function getUsageSpaceStatistic($clientId, $usageId, $date)
    {
        return self::getStatistic($clientId, $usageId, $date, "get_total_space_usage", "total");
    }

    /**
     * Получаем статистику по количеству используемых портов
     *
     * @param int $clientId
     * @param int $usageId
     * @param string $date
     * @return int кол-во используемых портов
     * @throws \yii\base\Exception
     */
    public static function getUsageNumbersStatistic($clientId, $usageId, $date)
    {
        return self::getStatistic($clientId, $usageId, $date, "get_int_number_usage", "int_number_amount");
    }

    /**
     * @param \DateTime $date
     * @return mixed JSON array
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\base\InvalidCallException
     * @throws \yii\base\InvalidConfigException
     */
    public static function getResourceStatistics(\DateTime $date)
    {
        return self::exec('get_resource_usage_per_day', ['date' => $date->format(DateTimeZoneHelper::DATE_FORMAT)]);
    }

    /**
     * @param int $clientId
     * @param int $usageId
     * @param string $date
     * @param string $statisticFunction
     * @param string $statisticField
     * @return mixed
     * @throws \yii\base\Exception
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\base\InvalidCallException
     * @throws \yii\base\InvalidConfigException
     */
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
                isset($result["errors"]) && $result["errors"] ?
                    implode("; ", $result["error"]) :
                    "Ошибка получения статистики"
            );
        }

        return $result[$statisticField];
    }

    /**
     * Возвращает подготовленное описание для синхронизации тарифа ВАТС. "Старые" услуги.
     *
     * @param int $usageId
     * @return array|false
     * @throws \yii\db\Exception
     */
    public static function getTariff($usageId)
    {
        $command = Yii::$app->db->createCommand("
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
     *
     * @return array
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\base\InvalidCallException
     * @throws \yii\base\InvalidConfigException
     */
    public static function getPhoneServices()
    {
        return self::exec('services/phone/', [], 'vpbx', false);
    }

    /**
     * Возвращает список подключенных телефонных номеров на платформе
     *
     * @return array
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\base\InvalidCallException
     * @throws \yii\base\InvalidConfigException
     */
    public static function getVpbxServices()
    {
        return self::exec('services/vpbx/', [], 'vpbx', false);
    }

    /**
     * Возвращает подготовленное описание для синхронизации тарифа ВАТС. Универсальные услуги.
     *
     * @param int $usageId
     * @return array
     * @throws \Exception
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\base\InvalidCallException
     * @throws \yii\base\InvalidConfigException
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
                    $data['space'] = $tariffResource->amount * 1024;
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

    /**
     * Блокирует клиентский аккаунт в ВАТС
     *
     * @param int $accountId
     * @return array
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\base\InvalidCallException
     * @throws \yii\base\InvalidConfigException
     */
    public static function lockAccount($accountId)
    {
        $account = ClientAccount::findOne(['id' => $accountId]);

        if (
            $account &&
            $account->is_blocked &&
            self::_isHaveEnabledVPBX($accountId)
        ) {
            return self::exec('lock_account/', ['account_id' => $accountId], 'vpbx');
        }

        return null;
    }

    /**
     * Разблокирует клиентский аккаунт в ВАТС
     *
     * @param int $accountId
     * @return array
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\base\InvalidCallException
     * @throws \yii\base\InvalidConfigException
     */
    public static function unlockAccount($accountId)
    {
        $account = ClientAccount::findOne(['id' => $accountId]);
        if (
            $account &&
            !$account->is_blocked &&
            self::_isHaveEnabledVPBX($accountId)
        ) {
            return self::exec('unlock_account/', ['account_id' => $accountId], 'vpbx');
        }

        return null;
    }

    /**
     * Есть ли у ЛС включенные ВАТС
     *
     * @param int $accountId
     * @return bool
     */
    private static function _isHaveEnabledVPBX($accountId)
    {
        return (bool)ActualVirtpbx::find()
            ->where(['client_id' => $accountId])
            ->count();
    }
}
