<?php
namespace app\classes\api;

use app\classes\HttpClient;
use app\classes\Singleton;
use app\helpers\DateTimeZoneHelper;
use app\models\ActualVirtpbx;
use app\models\ClientAccount;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\Resource;
use app\modules\uu\models\ServiceType;
use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;

/**
 * Class ApiVpbx
 *
 * @method static ApiVpbx me($args = null)
 */
class ApiVpbx extends Singleton
{
    /**
     * @return bool
     */
    public function isAvailable()
    {
        return $this->_getHost() && $this->_getApiUrl();
    }

    /**
     * @return string
     */
    private function _getHost()
    {
        return isset(Yii::$app->params['VPBX_SERVER']) ? Yii::$app->params['VPBX_SERVER'] : '';
    }

    /**
     * @return string
     */
    private function _getApiUrl()
    {
        return isset(Yii::$app->params['VIRTPBX_URL']) ? Yii::$app->params['VIRTPBX_URL'] : '';
    }

    /**
     * @return array
     */
    private function _getApiAuthorization()
    {
        return isset(Yii::$app->params['VPBX_API_AUTHORIZATION']) ? Yii::$app->params['VPBX_API_AUTHORIZATION'] : [];
    }

    /**
     * @param string $action
     * @param array $data
     * @param bool $isSendPost
     * @return mixed
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\base\InvalidCallException
     * @throws \yii\base\InvalidConfigException
     */
    private function _exec($action, $data, $isSendPost = true)
    {
        if (!$this->isAvailable()) {
            throw new InvalidConfigException('API Vpbx was not configured');
        }

        $url = $this->_getApiUrl();
        $url = strtr($url, ['[address]' => $this->_getHost(), '[action]' => $action]);

        return (new HttpClient)
            ->createJsonRequest()
            ->setMethod($isSendPost ? 'post' : 'get')
            ->setData($data)
            ->setUrl($url)
            ->auth($this->_getApiAuthorization())
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
    public function create($clientId, $usageId, $billerVersion = ClientAccount::VERSION_BILLER_USAGE)
    {
        $tariff = null;

        switch ($billerVersion) {

            case ClientAccount::VERSION_BILLER_USAGE: {
                $tariff = $this->getTariff($usageId);
                break;
            }

            case ClientAccount::VERSION_BILLER_UNIVERSAL: {
                $tariff = $this->getTariffUniversal($usageId);
                break;
            }
        }

        if (!$tariff) {
            throw new \Exception('bad tariff');
        }

        $this->_exec(
            'create',
            [
                'client_id' => (int)$clientId,
                'stat_product_id' => (int)$usageId,
                'numbers' => [],
                'phones' => $tariff['num_ports'],
                'faxes' => (int)$tariff['is_fax'] ? 5 : 0,
                'record' => (bool)$tariff['is_record'],
                'enable_web_call' => (bool)$tariff['is_web_call'],
                'disk_space' => (int)$tariff['space'],
                'timezone' => ClientAccount::findOne($clientId)->timezone_name,
                'region' => $tariff['region'],
                'enable_geo' => isset($tariff['enable_geo']) ? $tariff['enable_geo'] : 0,
                'enable_min_price' => isset($tariff['enable_min_price']) ? $tariff['enable_min_price'] : 0,
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
    public function transfer(
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

        $this->_exec('transfer', $query);
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
    public function transferVpbxOnly(
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

        $this->_exec('transfer_vpbx_only', $query);
    }

    /**
     * @param int $clientId
     * @param int $usageId
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\base\InvalidCallException
     * @throws \yii\base\InvalidConfigException
     */
    public function stop($clientId, $usageId)
    {
        $this->_exec(
            'delete',
            [
                'client_id' => (int)$clientId,
                'stat_product_id' => (int)$usageId,
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
    public function update($clientId, $usageId, $regionId, $billerVersion = ClientAccount::VERSION_BILLER_USAGE)
    {
        if ($billerVersion == ClientAccount::VERSION_BILLER_USAGE) {
            $tariff = $this->getTariff($usageId);
        } elseif ($billerVersion == ClientAccount::VERSION_BILLER_UNIVERSAL) {
            $tariff = $this->getTariffUniversal($usageId);
        } else {
            throw new \Exception('bad biller version');
        }

        if (!$tariff) {
            throw new \Exception('bad tariff');
        }

        $this->_exec(
            'update',
            [
                'client_id' => (int)$clientId,
                'stat_product_id' => (int)$usageId,
                'phones' => $tariff['num_ports'],
                'faxes' => $tariff['is_fax'] ? 5 : 0,
                'record' => (bool)$tariff['is_record'],
                'disk_space' => (int)$tariff['space'],
                'enable_web_call' => (bool)$tariff['is_web_call'],
                'region' => (int)$regionId,
                'enable_geo' => isset($tariff['enable_geo']) ? $tariff['enable_geo'] : 0,
                'enable_min_price' => isset($tariff['enable_min_price']) ? $tariff['enable_min_price'] : 0,
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
    public function getUsageSpaceStatistic($clientId, $usageId, $date)
    {
        return $this->getStatistic($clientId, $usageId, $date, 'get_total_space_usage', 'total');
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
    public function getUsageNumbersStatistic($clientId, $usageId, $date)
    {
        return $this->getStatistic($clientId, $usageId, $date, 'get_int_number_usage', 'int_number_amount');
    }

    /**
     * @param \DateTime $date
     * @return mixed JSON array
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\base\InvalidCallException
     * @throws \yii\base\InvalidConfigException
     */
    public function getResourceStatistics(\DateTime $date)
    {
        return $this->_exec('get_resource_usage_per_day', ['date' => $date->format(DateTimeZoneHelper::DATE_FORMAT)]);
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
    public function getStatistic(
        $clientId,
        $usageId,
        $date,
        $statisticFunction = 'get_total_space_usage',
        $statisticField = 'total'
    ) {
        $result = $this->_exec(
            $statisticFunction,
            [
                'client_id' => (int)$clientId,
                'stat_product_id' => (int)$usageId,
                'date' => $date,
            ]
        );

        if (!isset($result[$statisticField])) {
            throw new Exception(
                isset($result['errors']) && $result['errors'] ?
                    implode('; ', $result['error']) :
                    'Ошибка получения статистики'
            );
        }

        return $result[$statisticField];
    }

    /**
     * Возвращает подготовленное описание для синхронизации тарифа ВАТС. 'Старые' услуги.
     *
     * @param int $usageId
     * @return array|false
     * @throws \yii\db\Exception
     */
    public function getTariff($usageId)
    {
        $sql = <<<SQL
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
SQL;
        $command = Yii::$app->db->createCommand($sql, [':usageId' => $usageId]);

        return $command->queryOne();
    }

    /**
     * Получить список service_id и account_id с включенной телефонией
     *
     * @return array
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\base\InvalidCallException
     * @throws \yii\base\InvalidConfigException
     */
    public function getPhoneServices()
    {
        return $this->_exec('services/phone', [], $isSendPost = false);
    }

    /**
     * Получить список service_id и account_id с включенными ВАТС
     *
     * @return array
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\base\InvalidCallException
     * @throws \yii\base\InvalidConfigException
     */
    public function getVpbxServices()
    {
        return $this->_exec('services/vpbx', [], $isSendPost = false);
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
    public function getTariffUniversal($usageId)
    {
        $accountTariff = AccountTariff::findOne(['id' => $usageId]);

        if (!$accountTariff || !$accountTariff->tariffPeriod || $accountTariff->service_type_id != ServiceType::ID_VPBX) {
            throw new \Exception('bad tariff');
        }

        return [
            'num_ports' => $accountTariff->getResourceValue(Resource::ID_VPBX_ABONENT),
            'space' => 0, // $accountTariff->getResourceValue(Resource::ID_VPBX_DISK) * 1024, // трафик устанавливается не заранее, а по факту
            'is_record' => (int)$accountTariff->getResourceValue(Resource::ID_VPBX_RECORD),
            'is_fax' => (int)$accountTariff->getResourceValue(Resource::ID_VPBX_FAX),
            'is_web_call' => 0, // ?
            'region' => $accountTariff->region_id,
            'enable_geo' => (int)$accountTariff->getResourceValue(Resource::ID_VPBX_GEO_ROUTE),
            'enable_min_price' => (int)$accountTariff->getResourceValue(Resource::ID_VPBX_MIN_ROUTE),
        ];
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
    public function lockAccount($accountId)
    {
        $account = ClientAccount::findOne(['id' => $accountId]);

        if (
            $account &&
            $account->is_blocked &&
            $this->_isHaveEnabledVPBX($accountId)
        ) {
            return $this->_exec('lock_account', ['account_id' => $accountId]);
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
    public function unlockAccount($accountId)
    {
        $account = ClientAccount::findOne(['id' => $accountId]);
        if (
            $account &&
            !$account->is_blocked &&
            $this->_isHaveEnabledVPBX($accountId)
        ) {
            return $this->_exec('unlock_account', ['account_id' => $accountId]);
        }

        return null;
    }

    /**
     * Есть ли у ЛС включенные ВАТС
     *
     * @param int $accountId
     * @return bool
     */
    private function _isHaveEnabledVPBX($accountId)
    {
        return (bool)ActualVirtpbx::find()
            ->where(['client_id' => $accountId])
            ->count();
    }
}
