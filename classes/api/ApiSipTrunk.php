<?php

namespace app\classes\api;

use app\classes\HttpClient;
use app\classes\Singleton;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ResourceModel;
use app\modules\uu\models\ServiceType;
use Yii;
use yii\base\InvalidConfigException;

/**
 * Class ApiSipTrunk
 *
 * @method static ApiSipTrunk me($args = null)
 */
class ApiSipTrunk extends Singleton
{
    /**
     * @return bool
     */
    public function isAvailable()
    {
        return (bool)$this->_getHost() && (bool)$this->_getApiAuthorization();
    }

    /**
     * @return string
     */
    private function _getHost()
    {
        return isset(Yii::$app->params['PHONE_SERVER']) ? Yii::$app->params['PHONE_SERVER'] : '';
    }

    /**
     * @return bool
     */
    private function _getUrl()
    {
        $phoneHost = $this->_getHost();
        return $phoneHost ? 'https://' . $phoneHost . '/phone/api/' : '';
    }

    /**
     * @return array
     */
    private function _getApiAuthorization()
    {
        return isset(Yii::$app->params['VPBX_API_AUTHORIZATION']) ? Yii::$app->params['VPBX_API_AUTHORIZATION'] : [];
    }

    /**
     * Отправить данные
     *
     * @param string $action
     * @param array $data
     * @return mixed
     * @throws InvalidConfigException
     */
    public function exec($action, $data)
    {
        if (!$this->isAvailable()) {
            throw new InvalidConfigException('API for SIP-trunk is not configured');
        }

        return (new HttpClient)
            ->createRequest()
            ->setMethod('post')
            ->setData($data)
            ->setUrl($this->_getUrl() . $action)
            ->auth($this->_getApiAuthorization())
            ->getResponseDataWithCheck();
    }

    /**
     * Создать SIP-Trunk
     *
     * @param AccountTariff $accountTariff
     * @return int
     */
    public function create(AccountTariff $accountTariff)
    {
        return $this->exec('create_sip_trunk', $this->_getDataByAccountTariff($accountTariff));
    }

    /**
     * Обновить SIP-Trunk
     *
     * @param AccountTariff $accountTariff
     * @return string
     * @throws InvalidConfigException
     */
    public function update(AccountTariff $accountTariff)
    {
        return $this->exec('edit_sip_trunk', $this->_getDataByAccountTariff($accountTariff));
    }

    /**
     * Удалить SIP-Trunk
     *
     * @param AccountTariff $accountTariff
     * @return string
     * @throws InvalidConfigException
     */
    public function remote(AccountTariff $accountTariff)
    {
        $data = [
            'account_id' => $accountTariff->client_account_id,
            'stat_product_id' => $accountTariff->id,
        ];

        return $this->exec('delete_sip_trunk', $data);
    }


    /**
     * Синхронизировать
     *
     * @param array $data
     * @return int|string
     * @throws InvalidConfigException
     */
    public function sync($data)
    {
        $accountTariffId = isset($data['account_tariff_id']) ? $data['account_tariff_id'] : false;
        $isCreate = isset($data['is_create']) ? $data['is_create'] : true;

        if (
            !$accountTariffId ||
            !($accountTariff = AccountTariff::findOne(['id' => $accountTariffId])) ||
            $accountTariff->service_type_id != ServiceType::ID_SIPTRUNK
        ) {
            throw new \InvalidArgumentException('SyncSipTrunk. Неправильный параметр ' . $accountTariffId);
        }

        if (!$accountTariff->tariff_period_id) {
            // выключить
            return $this->remote($accountTariff);
        }

        if ($isCreate) {
            // включить
            return $this->create($accountTariff);
        }

        // обновить
        return $this->update($accountTariff);
    }

    /**
     * Создание структуры данных для синхронизации
     *
     * @param AccountTariff $accountTariff
     * @return array
     */
    private function _getDataByAccountTariff(AccountTariff $accountTariff)
    {
        $resources = $accountTariff->tariffPeriod->tariff->tariffResourcesIndexedByResourceId;

        return [
            'account_id' => $accountTariff->client_account_id,
            'stat_product_id' => $accountTariff->id,
            'region_id' => $accountTariff->region_id,
            'call_limit' => (int)$accountTariff->getResourceValue(ResourceModel::ID_CALLLIMIT),
            'allow_diversion' => (bool)$accountTariff->getResourceValue(ResourceModel::ID_ALLOW_DIVERSION),
            'name' => /* trim($accountTariff->comment) ?: */ 'SIP-Trunk #' . $accountTariff->id,
        ];
    }

}
