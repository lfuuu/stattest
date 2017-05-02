<?php

namespace app\modules\uu\models\traits;

use app\models\City;
use app\models\ClientAccount;
use app\models\Region;
use app\modules\uu\models\AccountLogPeriod;
use app\modules\uu\models\AccountLogResource;
use app\modules\uu\models\AccountLogSetup;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffLog;
use app\modules\uu\models\AccountTariffResourceLog;
use app\modules\uu\models\Resource;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\TariffPeriod;
use yii\db\ActiveQuery;

/**
 * @property ClientAccount $clientAccount
 * @property ServiceType $serviceType
 * @property \app\modules\uu\models\Resource[] $resources
 * @property Region $region
 * @property City $city
 * @property \app\models\Number $number
 * @property AccountTariff $prevAccountTariff  Основная услуга
 * @property AccountTariff[] $nextAccountTariffs   Пакеты
 * @property AccountTariffLog[] $accountTariffLogs
 * @property AccountTariffResourceLog[] $accountTariffResourceLogs
 * @property TariffPeriod $tariffPeriod
 *
 * @property AccountLogSetup[] $accountLogSetups
 * @property AccountLogPeriod[] $accountLogPeriods
 * @property AccountLogResource[] $accountLogResources
 *
 * @method ActiveQuery hasMany($class, array $link) see [[BaseActiveRecord::hasMany()]] for more info
 * @method ActiveQuery hasOne($class, array $link) see [[BaseActiveRecord::hasOne()]] for more info
 */
trait AccountTariffRelationsTrait
{
    /**
     * @return ActiveQuery
     */
    public function getTariffPeriod()
    {
        return $this->hasOne(TariffPeriod::className(), ['id' => 'tariff_period_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getClientAccount()
    {
        return $this->hasOne(ClientAccount::className(), ['id' => 'client_account_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getPrevAccountTariff()
    {
        return $this->hasOne(self::className(), ['id' => 'prev_account_tariff_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getNextAccountTariffs()
    {
        return $this->hasMany(self::className(), ['prev_account_tariff_id' => 'id'])
            ->indexBy('id');
    }

    /**
     * @return ActiveQuery
     */
    public function getRegion()
    {
        return $this->hasOne(Region::className(), ['id' => 'region_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getCity()
    {
        return $this->hasOne(City::className(), ['id' => 'city_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getNumber()
    {
        return $this->hasOne(\app\models\Number::className(), ['number' => 'voip_number']);
    }

    /**
     * @return ActiveQuery
     */
    public function getServiceType()
    {
        return $this->hasOne(ServiceType::className(), ['id' => 'service_type_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getResources()
    {
        return $this->hasMany(Resource::className(), ['service_type_id' => 'service_type_id'])
            ->indexBy('id')
            ->orderBy(['id' => SORT_ASC]);
    }

    /**
     * @return ActiveQuery
     */
    public function getAccountLogSetups()
    {
        return $this->hasMany(AccountLogSetup::className(), ['account_tariff_id' => 'id'])
            ->indexBy('id')
            ->orderBy(['id' => SORT_ASC]);
    }

    /**
     * @return ActiveQuery
     */
    public function getAccountLogPeriods()
    {
        return $this->hasMany(AccountLogPeriod::className(), ['account_tariff_id' => 'id'])
            ->indexBy('id')
            ->orderBy(['id' => SORT_ASC]);
    }

    /**
     * @return ActiveQuery
     */
    public function getAccountLogResources()
    {
        return $this->hasMany(AccountLogResource::className(), ['account_tariff_id' => 'id'])
            ->indexBy('id')
            ->orderBy(['id' => SORT_ASC]);
    }

    /**
     * @return ActiveQuery
     */
    public function getAccountTariffLogs()
    {
        return $this->hasMany(AccountTariffLog::className(), ['account_tariff_id' => 'id'])
            ->orderBy(['actual_from_utc' => SORT_DESC, 'id' => SORT_DESC])
            ->indexBy('id');
    }

    /**
     * @param int $resourceId
     * @return ActiveQuery
     */
    public function getAccountTariffResourceLogs($resourceId = null)
    {
        return $this->hasMany(AccountTariffResourceLog::className(), ['account_tariff_id' => 'id'])
            ->andWhere($resourceId ? ['resource_id' => $resourceId] : [])
            ->orderBy(
                [
                    'resource_id' => SORT_ASC,
                    'actual_from_utc' => SORT_DESC,
                    'id' => SORT_DESC,
                ])
            ->indexBy('id');
    }
}