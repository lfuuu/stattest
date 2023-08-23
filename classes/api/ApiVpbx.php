<?php
namespace app\classes\api;

use app\classes\HttpClient;
use app\classes\Singleton;
use app\helpers\DateTimeZoneHelper;
use app\models\ActualVirtpbx;
use app\models\ClientAccount;
use app\models\UsageVirtpbx;
use app\models\UsageVoip;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ResourceModel;
use app\modules\uu\models\ServiceType;
use Yii;
use yii\base\InvalidCallException;
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
    protected function _exec($action, $data, $isSendPost = true)
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
     * @return mixed
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
                $tariff = $this->_getTariff($usageId);
                break;
            }

            case ClientAccount::VERSION_BILLER_UNIVERSAL: {
                $tariff = $this->_getTariffUniversal($usageId);
                break;
            }
        }

        if (!$tariff) {
            throw new InvalidCallException('bad tariff');
        }

        $account = ClientAccount::findOne(['id' => $clientId]);


        return $this->_exec(
            'create',
            [
                'client_id' => (int)$clientId,
                'account_version' => $account->account_version,
                'stat_product_id' => (int)$usageId,
                'numbers' => [],
                'phones' => (int) $tariff['num_ports'],
                'faxes' => (int)$tariff['is_fax'] ? 5 : 0,
                'ext_did' => (int)$tariff['ext_did'],
                'record' => (bool)$tariff['is_record'],
                'enable_web_call' => (bool)$tariff['is_web_call'],
                'disk_space' => (int)$tariff['space'],
                'timezone' => $account->timezone_name == DateTimeZoneHelper::TIMEZONE_UTC ? DateTimeZoneHelper::TIMEZONE_LONDON : $account->timezone_name,
                'region' => $tariff['region'],
                'enable_geo' => $tariff['enable_geo'],
                'enable_min_price' => $tariff['enable_min_price'],
                'enable_sub_accounts' => $tariff['enable_sub_accounts'],
                'enable_prompter' => $tariff['enable_prompter'] ?? 0,
                'voice_assistant' => $tariff['voice_assistant'] ?? 0,
                'robot_controller' => $tariff['robot_controller'] ?? 0,
                'is_reserv' => $tariff['is_reserv'] ?? false,
                'is_operator_score' => $tariff['is_operator_score'] ?? false,
                'is_external_pbx' => $tariff['is_external_pbx'] ?? false,
                'is_special_autocall' => $tariff['is_special_autocall'] ?? false,
                'is_call_end_management' => $tariff['is_call_end_management'] ?? false,
                'is_transcription' => $tariff['is_transcription'] ?? false,
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
     * @return mixed
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

        return $this->_exec('transfer', $query);
    }

    /**
     * Перенос ВАТС без номеров
     *
     * @param int $fromAccountId
     * @param int $fromUsageId
     * @param int $toAccountId
     * @param int $toUsageId
     * @return mixed
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

        if (UsageVirtpbx::dao()->isVpbxExists($toAccountId) && UsageVoip::dao()->isVoipExists($toAccountId)) {
            return $this->_exec('transfer', $query);
        }

        return $this->_exec('transfer_vpbx_only', $query);
    }

    /**
     * @param int $clientId
     * @param int $usageId
     * @return mixed
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\base\InvalidCallException
     * @throws \yii\base\InvalidConfigException
     */
    public function delete($clientId, $usageId)
    {
        return $this->_exec(
            'delete',
            [
                'client_id' => (int)$clientId,
                'stat_product_id' => (int)$usageId,
            ]
        );
    }

    /**
     * Архивирование ВАТС
     *
     * @param integer $clientId
     * @param integer $usageId
     * @return mixed
     */
    public function archiveVpbx($clientId, $usageId)
    {
        return $this->_exec(
            'archive_vpbx',
            [
                'account_id' => (int)$clientId,
                'stat_product_id' => (int)$usageId,
            ]
        );
    }

    /**
     * Деархивация ВАТС
     *
     * @param integer $clientId
     * @param integer $usageId
     * @return mixed
     */
    public function dearchiveVpbx($clientId, $usageId)
    {
        return $this->_exec(
            'dearchive_vpbx',
            [
                'account_id' => (int)$clientId,
                'stat_product_id' => (int)$usageId,
            ]
        );
    }

    /**
     * @param int $clientId
     * @param int $usageId
     * @param int $regionId
     * @param int $billerVersion
     * @return mixed
     * @throws \Exception
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\base\InvalidCallException
     * @throws \yii\base\InvalidConfigException
     */
    public function update($clientId, $usageId, $regionId, $billerVersion = ClientAccount::VERSION_BILLER_USAGE)
    {
        if ($billerVersion == ClientAccount::VERSION_BILLER_USAGE) {
            $tariff = $this->_getTariff($usageId);
        } elseif ($billerVersion == ClientAccount::VERSION_BILLER_UNIVERSAL) {
            $tariff = $this->_getTariffUniversal($usageId);
        } else {
            throw new InvalidCallException('bad biller version');
        }

        if (!$tariff) {
            throw new InvalidCallException('bad tariff');
        }

        return $this->_exec(
            'update',
            [
                'client_id' => (int)$clientId,
                'account_version' => ClientAccount::findOne(['id' => $clientId])->account_version,
                'stat_product_id' => (int)$usageId,
                'phones' => (int) $tariff['num_ports'],
                'faxes' => $tariff['is_fax'] ? 5 : 0,
                'ext_did' => (int)$tariff['ext_did'],
                'record' => (bool)$tariff['is_record'],
                'disk_space' => (int)$tariff['space'],
                'enable_web_call' => (bool)$tariff['is_web_call'],
                'region' => (int)$regionId,
                'enable_geo' => $tariff['enable_geo'],
                'enable_min_price' => $tariff['enable_min_price'],
                'enable_sub_accounts' => $tariff['enable_sub_accounts'],
                'voice_assistant' => $tariff['voice_assistant'] ?? 0,
                'robot_controller' => $tariff['robot_controller'] ?? 0,
                'enable_prompter' => $tariff['enable_prompter'] ?? 0,
                'is_reserv' => $tariff['is_reserv'] ?? false,
                'is_operator_score' => $tariff['is_operator_score'] ?? false,
                'is_external_pbx' => $tariff['is_external_pbx'] ?? false,
                'is_special_autocall' => $tariff['is_special_autocall'] ?? false,
                'is_call_end_management' => $tariff['is_call_end_management'] ?? false,
                'is_transcription' => $tariff['is_transcription'] ?? false,
            ]
        );
    }

    /**
     * @param \DateTime $date
     * @return mixed JSON array
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\base\InvalidCallException
     * @throws \yii\base\InvalidConfigException
     */
    public function getResourceUsagePerDay(\DateTime $date)
    {
        return $this->_exec('get_resource_usage_per_day', ['date' => $date->format(DateTimeZoneHelper::DATE_FORMAT)]);
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
        if (!$account || !$account->is_blocked || !$this->_isHaveEnabledVPBX($accountId)) {
            return null;
        }

        return $this->_exec('lock_account', ['account_id' => $accountId]);
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
        if (!$account || $account->is_blocked || !$this->_isHaveEnabledVPBX($accountId)) {
            return null;
        }

        return $this->_exec('unlock_account', ['account_id' => $accountId]);
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

    /**
     * Возвращает подготовленное описание для синхронизации тарифа ВАТС. 'Старые' услуги.
     *
     * @param int $usageId
     * @return array|false
     * @throws \yii\db\Exception
     */
    private function _getTariff($usageId)
    {
        $sql = <<<SQL
                SELECT
                    t.num_ports,
                    t.space,
                    t.is_record,
                    t.is_fax,
                    t.ext_did_count as ext_did,
                    t.is_web_call,
                    region,
                    0 as enable_geo,
                    0 as enable_min_price,
                    0 as enable_sub_accounts,
                    0 as is_transcription
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
     * Возвращает подготовленное описание для синхронизации тарифа ВАТС. Универсальные услуги.
     *
     * @param int $usageId
     * @return array
     * @throws \Exception
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\base\InvalidCallException
     * @throws \yii\base\InvalidConfigException
     */
    private function _getTariffUniversal($usageId)
    {
        $accountTariff = AccountTariff::findOne(['id' => $usageId]);

        if (!$accountTariff || !$accountTariff->tariffPeriod || $accountTariff->service_type_id != ServiceType::ID_VPBX) {
            throw new \Exception('bad tariff');
        }

        // Всем примененным ресурсам установить текущую дату синхронизации
        $accountTariff->setResourceSyncTime();

        return [
            'num_ports' => $accountTariff->getResourceValue(ResourceModel::ID_VPBX_ABONENT),
            'space' => 0, // $accountTariff->getResourceValue(Resource::ID_VPBX_DISK) * 1024, // трафик устанавливается не заранее, а по факту
            'is_record' => (int)$accountTariff->getResourceValue(ResourceModel::ID_VPBX_RECORD),
            'is_fax' => (int)$accountTariff->getResourceValue(ResourceModel::ID_VPBX_FAX),
            'ext_did' => (int)$accountTariff->getResourceValue(ResourceModel::ID_VPBX_EXT_DID),
            'is_web_call' => 0, // "звонок-чат". Во-первых, он вообще не из ВАТС, а из отдельной услуги. Во-вторых, он всегда всем включен и не выключается.
            'region' => $accountTariff->region_id,
            'enable_geo' => (int)$accountTariff->getResourceValue(ResourceModel::ID_VPBX_GEO_ROUTE),
            'enable_min_price' => (int)$accountTariff->getResourceValue(ResourceModel::ID_VPBX_MIN_ROUTE),
            'enable_sub_accounts' => (int)$accountTariff->getResourceValue(ResourceModel::ID_VPBX_SUB_ACCOUNT),
            'enable_prompter' => (int)$accountTariff->getResourceValue(ResourceModel::ID_VPBX_PROMPTER),
            'voice_assistant' => (int)$accountTariff->getResourceValue(ResourceModel::ID_VPBX_VOICE_ASSISTANT),
            'robot_controller' => (int)$accountTariff->getResourceValue(ResourceModel::ID_VPBX_ROBOT_CONTROLLER),
            'is_reserv' => (bool)$accountTariff->getResourceValue(ResourceModel::ID_VPBX_RESERV),
            'is_operator_score' => (bool)$accountTariff->getResourceValue(ResourceModel::ID_VPBX_OPERATOR_ASSESSMENT),
            'is_external_pbx' => (bool)$accountTariff->getResourceValue(ResourceModel::ID_VPBX_TRUNK_EXT_VPBX),
            'is_special_autocall' => (bool)$accountTariff->getResourceValue(ResourceModel::ID_VPBX_SPECIAL_AUTOCALL),
            'is_call_end_management' => (bool)$accountTariff->getResourceValue(ResourceModel::ID_VPBX_CALL_END_MANAGEMENT),
            'is_transcription' => (bool)$accountTariff->getResourceValue(ResourceModel::ID_VPBX_TRANSCRIPTION),
        ];
    }
}
