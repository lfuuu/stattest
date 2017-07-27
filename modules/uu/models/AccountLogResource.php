<?php

namespace app\modules\uu\models;

use app\classes\model\ActiveRecord;
use yii\db\ActiveQuery;
use yii\helpers\Url;

/**
 * Предварительное списание (транзакции) платы за ресурсы
 *
 * @property int $id
 * @property string $date_from
 * @property string $date_to
 * @property int $tariff_period_id  кэш accountTariff -> accountTariffLog -> tariff_period_id
 * @property int $tariff_resource_id
 * @property int $account_tariff_id
 * @property float $amount_use кэш из пребиллера (например, virtpbx_stat)
 * @property float $amount_free кэш tariffResource -> amount
 * @property float $amount_overhead кэш ceil(amount_use - amount_free)
 * @property float $price_per_unit кэш tariffResource -> price_per_unit
 * @property int $coefficient кэш (date_to - date_from)
 * @property float $price кэш amount_overhead * price_per_unit * $coefficient
 * @property string $insert_time
 * @property int $account_entry_id
 * @property int $account_tariff_resource_log_id
 *
 * @property AccountTariff $accountTariff
 * @property TariffPeriod $tariffPeriod
 * @property TariffResource $tariffResource
 * @property AccountEntry $accountEntry
 * @property AccountTariffResourceLog $accountTariffResourceLog
 */
class AccountLogResource extends ActiveRecord
{
    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits;

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'uu_account_log_resource';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['id', 'tariff_period_id', 'account_tariff_id', 'tariff_resource_id', 'coefficient'], 'integer'],
            [['price'], 'double'],
            [['date_from', 'date_to'], 'string', 'max' => 10],
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
    public function getTariffResource()
    {
        return $this->hasOne(TariffResource::className(), ['id' => 'tariff_resource_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getAccountEntry()
    {
        return $this->hasOne(AccountEntry::className(), ['id' => 'account_entry_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getAccountTariffResourceLog()
    {
        return $this->hasOne(AccountTariffResourceLog::className(), ['id' => 'account_tariff_resource_log_id']);
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return Url::to(['/uu/account-log/resource', 'AccountLogResourceFilter[id]' => $this->id]);
    }

    /**
     * Вернуть уникальный Id в пределах account_tariff_id и tariff_resource_id
     * Поле id хоть и уникальное, но не подходит для поиска нерассчитанных данных при тарификации
     *
     * @return string
     */
    public function getUniqueId()
    {
        return
            $this->date_from .
            '_' .
            $this->date_to .
            '_' .
            $this->account_tariff_resource_log_id;
    }
}
