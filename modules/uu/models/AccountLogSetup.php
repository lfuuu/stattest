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
 * @property-read AccountTariff $accountTariff
 * @property-read TariffPeriod $tariffPeriod
 * @property-read AccountEntry $accountEntry
 *
 * @method static AccountLogSetup findOne($condition)
 * @method static AccountLogSetup[] findAll($condition)
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

    /**
     * @return array
     */
    public static function getHelpConfluence()
    {
        return ['confluenceId' => 7176467, 'message' => 'Плата за подключение'];
    }
}
