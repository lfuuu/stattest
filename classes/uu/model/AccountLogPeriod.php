<?php

namespace app\classes\uu\model;

use app\classes\behaviors\uu\SyncAccountTariffLight;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
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
 * @property AccountTariff $accountTariff
 * @property TariffPeriod $tariffPeriod
 * @property AccountEntry $accountEntry
 */
class AccountLogPeriod extends ActiveRecord
{
    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits;

    public static function tableName()
    {
        return 'uu_account_log_period';
    }

    public function rules()
    {
        return [
            [['id', 'tariff_period_id', 'account_tariff_id'], 'integer'],
            [['period_price', 'coefficient', 'price'], 'double'],
            [['date_from', 'date_to'], 'string', 'max' => 255],
        ];
    }

    /**
     * @return []
     */
    public function behaviors()
    {
        return [
            'SyncAccountTariffLight' => SyncAccountTariffLight::className(), // Синхронизировать данные в AccountTariffLight
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getAccountTariff()
    {
        return $this->hasOne(AccountTariff::className(), ['id' => 'account_tariff_id']);
    }

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
    public function getAccountEntry()
    {
        return $this->hasOne(AccountEntry::className(), ['id' => 'account_entry_id']);
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return Url::to(['uu/account-log/period', 'AccountLogPeriodFilter[id]' => $this->id]);
    }

    /**
     * Вернуть уникальный Id
     * Поле id хоть и уникальное, но не подходит для поиска нерассчитанных данных при тарификации
     * @return string
     */
    public function getUniqueId()
    {
        return $this->date_from . '_' . $this->tariff_period_id;
    }
}
