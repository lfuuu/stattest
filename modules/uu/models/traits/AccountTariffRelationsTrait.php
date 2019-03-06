<?php

namespace app\modules\uu\models\traits;

use app\models\City;
use app\models\ClientAccount;
use app\models\Datacenter;
use app\models\Region;
use app\models\UsageTrunk;
use app\modules\uu\models\AccountLogPeriod;
use app\modules\uu\models\AccountLogResource;
use app\modules\uu\models\AccountLogSetup;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffHeap;
use app\modules\uu\models\AccountTariffLog;
use app\modules\uu\models\AccountTariffResourceLog;
use app\modules\uu\models\AccountTrouble;
use app\modules\uu\models\helper\AccountTariffHelper;
use app\modules\uu\models\Resource;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\TariffPeriod;
use yii\db\ActiveQuery;

/**
 * @property-read ClientAccount $clientAccount
 * @property-read ServiceType $serviceType
 * @property-read \app\modules\uu\models\Resource[] $resources
 * @property-read Region $region
 * @property-read City $city
 * @property-read \app\models\Number $number
 * @property-read AccountTariff $prevAccountTariff  Основная услуга
 * @property-read AccountTariff[] $nextAccountTariffs   Пакеты
 * @property-read AccountTariff $prevUsage  Перенесено из
 * @property-read AccountTariff $nextUsage   Перенесено в
 * @property-read AccountTariffLog[] $accountTariffLogs
 * @property-read AccountTariffResourceLog[] $accountTariffResourceLogs
 * @property-read TariffPeriod $tariffPeriod
 * @property-read Datacenter $datacenter
 * @property-read UsageTrunk $usageTrunk
 * @property-read  AccountTariffHeap $accountTariffHeap
 * @property-read  AccountTrouble[] $accountTroubles
 *
 * @property-read AccountLogSetup[] $accountLogSetups
 * @property-read AccountLogPeriod[] $accountLogPeriods
 * @property-read AccountLogResource[] $accountLogResources
 *
 * @property-read AccountTariffHelper $helper
 *
 * @method ActiveQuery hasMany($class, array $link) see [[BaseActiveRecord::hasMany()]] for more info
 * @method ActiveQuery hasOne($class, array $link) see [[BaseActiveRecord::hasOne()]] for more info
 *
 * @method static AccountTariff findOne($condition)
 * @method static AccountTariff[] findAll($condition)
 */
trait AccountTariffRelationsTrait
{
    /**
     * @return ActiveQuery
     */
    public function getTariffPeriod()
    {
        return $this->hasOne(TariffPeriod::class, ['id' => 'tariff_period_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getClientAccount()
    {
        return $this->hasOne(ClientAccount::class, ['id' => 'client_account_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getPrevAccountTariff()
    {
        return $this->hasOne(self::class, ['id' => 'prev_account_tariff_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getNextAccountTariffs()
    {
        return $this->hasMany(self::class, ['prev_account_tariff_id' => 'id'])
            ->indexBy('id');
    }

    /**
     * @return ActiveQuery
     */
    public function getPrevUsage()
    {
        return $this->hasOne(self::class, ['id' => 'prev_usage_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getNextUsage()
    {
        return $this->hasOne(self::class, ['prev_usage_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getRegion()
    {
        return $this->hasOne(Region::class, ['id' => 'region_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getCity()
    {
        return $this->hasOne(City::class, ['id' => 'city_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getNumber()
    {
        return $this->hasOne(\app\models\Number::class, ['number' => 'voip_number']);
    }

    /**
     * @return ActiveQuery
     */
    public function getServiceType()
    {
        return $this->hasOne(ServiceType::class, ['id' => 'service_type_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getDatacenter()
    {
        return $this->hasOne(Datacenter::class, ['id' => 'datacenter_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getUsageTrunk()
    {
        return $this->hasOne(UsageTrunk::class, ['id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getResources()
    {
        return $this->hasMany(Resource::class, ['service_type_id' => 'service_type_id'])
            ->indexBy('id')
            ->orderBy(['id' => SORT_ASC]);
    }

    /**
     * @return ActiveQuery
     */
    public function getAccountLogSetups()
    {
        return $this->hasMany(AccountLogSetup::class, ['account_tariff_id' => 'id'])
            ->indexBy('id')
            ->orderBy(['id' => SORT_ASC]);
    }

    /**
     * @return ActiveQuery
     */
    public function getAccountLogPeriods()
    {
        return $this->hasMany(AccountLogPeriod::class, ['account_tariff_id' => 'id'])
            ->indexBy('id')
            ->orderBy(['id' => SORT_ASC]);
    }

    /**
     * @return ActiveQuery
     */
    public function getAccountLogResources()
    {
        return $this->hasMany(AccountLogResource::class, ['account_tariff_id' => 'id'])
            ->indexBy('id')
            ->orderBy(['id' => SORT_ASC]);
    }

    /**
     * @return ActiveQuery
     */
    public function getAccountTariffLogs()
    {
        return $this->hasMany(AccountTariffLog::class, ['account_tariff_id' => 'id'])
            ->orderBy(['id' => SORT_DESC])
            ->indexBy('id');
    }

    /**
     * @param int $resourceId
     * @return ActiveQuery
     */
    public function getAccountTariffResourceLogs($resourceId = null)
    {
        return $this->hasMany(AccountTariffResourceLog::class, ['account_tariff_id' => 'id'])
            ->andWhere($resourceId ? ['resource_id' => $resourceId] : [])
            ->orderBy(
                [
                    'resource_id' => SORT_ASC,
                    'id' => SORT_DESC,
                ])
            ->indexBy('id');
    }

    /**
     * @return AccountTariffHelper
     */
    public function getHelper()
    {
        return new AccountTariffHelper($this);
    }

    /**
     * @return ActiveQuery
     */
    public function getAccountTariffHeap()
    {
        return $this->hasOne(AccountTariffHeap::class, ['account_tariff_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getAccountTroubles()
    {
        return $this->hasMany(AccountTrouble::class, ['account_tariff_id' => 'id']);
    }

    /**
     * @return string
     */
    public function getAccountTroublesText()
    {
        return implode(', ', $this->accountTroubles);
    }
}