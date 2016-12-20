<?php

namespace app\classes\uu\model;

use app\classes\uu\resourceReader\ResourceReaderInterface;
use DateTimeImmutable;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\Url;

/**
 * Предварительное списание (транзакции) платы за ресурсы
 *
 * @property int $id
 * @property string $date
 * @property int $tariff_period_id  кэш accountTariff -> accountTariffLog -> tariff_period_id
 * @property int $tariff_resource_id
 * @property int $account_tariff_id
 * @property float $amount_use кэш из пребиллера (например, virtpbx_stat)
 * @property float $amount_free кэш tariffResource -> amount
 * @property float $amount_overhead кэш ceil(amount_use - amount_free)
 * @property float $price_per_unit кэш tariffResource -> price_per_unit
 * @property float $price кэш amount_overhead * price_per_unit
 * @property string $insert_time
 * @property int $account_entry_id
 *
 * @property AccountTariff $accountTariff
 * @property TariffPeriod $tariffPeriod
 * @property TariffResource $tariffResource
 * @property AccountEntry $accountEntry
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
            [['id', 'tariff_period_id', 'account_tariff_id', 'tariff_resource_id'], 'integer'],
            [['price'], 'double'],
            [['date'], 'string', 'max' => 255],
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
     * @return string
     */
    public function getUrl()
    {
        return Url::to(['uu/account-log/resource', 'AccountLogResourceFilter[id]' => $this->id]);
    }
}
