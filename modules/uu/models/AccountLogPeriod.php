<?php

namespace app\modules\uu\models;

use app\classes\model\ActiveRecord;
use app\models\billing\StatNnpPackageMinute;
use app\modules\uu\behaviors\AccountTariffVoipInternet;
use app\modules\uu\behaviors\SyncAccountTariffLight;
use yii\db\ActiveQuery;
use yii\helpers\Url;

/**
 * Предварительное списание (транзакции) абонентской платы
 *
 * @property int $id
 * @property string $date_from
 * @property string $date_to
 * @property int $tariff_period_id  кэш accountTariff -> accountTariffLog -> tariff_period_id
 * @property int $account_tariff_id
 * @property float $period_price кэш tariffPeriod -> price_per_period
 * @property float $coefficient кэш (date_to - date_from) / n
 * @property float $price кэш period_price * coefficient
 * @property string $insert_time
 * @property int $account_entry_id
 *
 * @property-read AccountTariff $accountTariff
 * @property-read TariffPeriod $tariffPeriod
 * @property-read AccountEntry $accountEntry
 * @property-read StatNnpPackageMinute[] $minutesSummary
 *
 * @method static AccountLogPeriod findOne($condition)
 * @method static AccountLogPeriod[] findAll($condition)
 */
class AccountLogPeriod extends ActiveRecord
{
    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits;

    protected $isAttributeTypecastBehavior = true;

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'uu_account_log_period';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['id', 'tariff_period_id', 'account_tariff_id'], 'integer'],
            [['period_price', 'coefficient', 'price'], 'double'],
            [['date_from', 'date_to'], 'string', 'max' => 255],
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                SyncAccountTariffLight::class, // Синхронизировать данные в AccountTariffLight
//                AccountTariffVoipInternet::class, // Синхронизировать данные в MTT
            ]
        );
    }


    /**
     * @return ActiveQuery
     */
    public function getAccountTariff()
    {
        return $this->hasOne(AccountTariff::class, ['id' => 'account_tariff_id']);
    }

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
    public function getAccountEntry()
    {
        return $this->hasOne(AccountEntry::class, ['id' => 'account_entry_id']);
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return Url::to(['/uu/account-log/period', 'AccountLogPeriodFilter[id]' => $this->id]);
    }

    /**
     * Вернуть уникальный Id
     * Поле id хоть и уникальное, но не подходит для поиска нерассчитанных данных при тарификации
     *
     * @return string
     */
    public function getUniqueId()
    {
        return $this->date_from . '_' . $this->tariff_period_id;
    }

    /**
     * Вернуть кол-во потраченных минут по пакету минут
     *
     * @return array [[i_nnp_package_minute_id, i_used_seconds]]
     * @throws \yii\db\Exception
     */
    public function getMinuteStatistic()
    {
        return StatNnpPackageMinute::getDb()
            ->createCommand('SELECT i_nnp_package_minute_id, i_used_seconds FROM billing.used_package_minutes_get(' . $this->id . ')')
            ->queryAll();
    }

    /**
     * Вернуть кол-во потраченных минут по пакету минут
     *
     * @return ActiveQuery
     */
    public function getMinutesSummary()
    {
        return $this->hasMany(StatNnpPackageMinute::class, ['nnp_account_tariff_light_id' => 'id'])
            ->select([
                'nnp_account_tariff_light_id',
                'nnp_package_minute_id',
                'SUM(used_seconds) used_seconds'
            ])
            ->groupBy(['nnp_account_tariff_light_id', 'nnp_package_minute_id']);
    }

    /**
     * Вернуть кол-во потраченных минут по пакету минут
     *
     * @return array
     */
    public function getMinutesSummaryAsArray()
    {
        $result = [];
        /** @var StatNnpPackageMinute $record */
        foreach ($this->minutesSummary as $record) {
            $result[] = [
                'i_nnp_package_minute_id'   => $record->nnp_package_minute_id,
                'i_used_seconds'            => $record->used_seconds,
            ];
        }

        return $result;
    }

    /**
     * @return array
     */
    public static function getHelpConfluence()
    {
        return ['confluenceId' => 7176470, 'message' => 'Абонентская плата'];
    }
}
