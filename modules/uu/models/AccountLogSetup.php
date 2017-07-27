<?php

namespace app\modules\uu\models;

use app\classes\model\ActiveRecord;
use yii\db\ActiveQuery;
use yii\helpers\Url;

/**
 * Предварительное списание (транзакции) стоимости подключения
 *
 * @property int $id
 * @property string $date
 * @property int $tariff_period_id  кэш accountTariff -> accountTariffLog -> tariff_period_id
 * @property int $account_tariff_id
 * @property float $price_setup кэш tariffPeriod -> price_setup
 * @property float $price_number
 * @property float $price кэш price_setup + price_number
 * @property string $insert_time
 * @property int $account_entry_id
 *
 * @property AccountTariff $accountTariff
 * @property TariffPeriod $tariffPeriod
 * @property AccountEntry $accountEntry
 */
class AccountLogSetup extends ActiveRecord
{
    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits;

    protected $isAttributeTypecastBehavior = true;

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'uu_account_log_setup';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['id', 'tariff_period_id', 'account_tariff_id'], 'integer'],
            [['price_setup', 'price_number', 'price'], 'double'],
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
    public function getAccountEntry()
    {
        return $this->hasOne(AccountEntry::className(), ['id' => 'account_entry_id']);
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return Url::to(['/uu/account-log/setup', 'AccountLogSetupFilter[id]' => $this->id]);
    }

    /**
     * Вернуть уникальный Id
     * Поле id хоть и уникальное, но не подходит для поиска нерассчитанных данных при тарификации
     *
     * @return string
     */
    public function getUniqueId()
    {
        return $this->date . '_' . $this->tariff_period_id;
    }
}
