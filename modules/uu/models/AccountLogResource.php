<?php

namespace app\modules\uu\models;

use app\classes\model\ActiveRecord;
use app\helpers\DateTimeZoneHelper;
use app\modules\uu\classes\helper\AccountTariffRunner;
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
 * @property float $amount_use кэш из пребиллера (например, virtpbx_stat)       // Используемое количество ресурса
 * @property float $amount_free кэш tariffResource -> amount                    // Условно-бесплатное количество ресурса, включенное в тариф
 * @property float $amount_overhead кэш ceil(amount_use - amount_free)          // Количество ресурса сверх бесплатного (amount_use - amount_free)
 * @property float $price_per_unit кэш tariffResource -> price_per_unit         // Цена за единицу ресурса в день
 * @property int $coefficient кэш (date_to - date_from)                         // Количество дней
 * @property float $price кэш amount_overhead * price_per_unit * $coefficient   // Стоимость этого ресурса за период, указанный ниже. Если null - списание невозможно
 * @property float $cost_price                                                  // себестоимость
 * @property string $insert_time
 * @property int $account_entry_id
 * @property int $account_tariff_resource_log_id
 *
 * @property-read AccountTariff $accountTariff
 * @property-read TariffPeriod $tariffPeriod
 * @property-read TariffResource $tariffResource
 * @property-read AccountEntry $accountEntry
 * @property-read AccountTariffResourceLog $accountTariffResourceLog
 *
 * @method static AccountLogResource findOne($condition)
 * @method static AccountLogResource[] findAll($condition)
 */
class AccountLogResource extends ActiveRecord
{
    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits;

    protected $isAttributeTypecastBehavior = true;

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
            ['insert_time', 'default', 'value' => DateTimeZoneHelper::getUtcDateTime()->format(DateTimeZoneHelper::DATETIME_FORMAT)],
            [['price', 'cost_price'], 'double'],
            [['cost_price'], 'default', 'value' => 0.0],
            [['date_from', 'date_to'], 'string', 'max' => 10],
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
    public function getTariffResource()
    {
        return $this->hasOne(TariffResource::class, ['id' => 'tariff_resource_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getAccountEntry()
    {
        return $this->hasOne(AccountEntry::class, ['id' => 'account_entry_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getAccountTariffResourceLog()
    {
        return $this->hasOne(AccountTariffResourceLog::class, ['id' => 'account_tariff_resource_log_id']);
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

    /**
     * @param string $dateFromModify
     * @param string $dateToModify
     * @return int
     * @throws \yii\db\Exception
     * @throws \Exception
     */
    public static function clearCalls($dateFromModify, $dateToModify)
    {
        (new AccountTariffRunner())->run(function($fromId, $toId) use ($dateFromModify, $dateToModify) {
            self::_clearCalls($dateFromModify, $dateToModify, $fromId, $toId);
        });

        return null;
    }

    private static function _clearCalls($dateFromModify, $dateToModify, $fromAccountTariffId, $toAccountTariffId)
    {
        // сбросить кэш, чтобы биллер все пересчитал
        $query = AccountTariff::getDb()->createCommand()->update(AccountTariff::tableName(), ['account_log_resource_utc' => null], ['between', 'id', $fromAccountTariffId, $toAccountTariffId]);
        $query->execute();

        // удалить ресурсы
        $accountLogResourceTableName = AccountLogResource::tableName();
        $tariffResourceTableName = TariffResource::tableName();
        $resourceIdCalls = implode(', ', ResourceModel::$calls);
        $sql = <<<SQL
            DELETE
                account_log_resource.*
            FROM
                {$accountLogResourceTableName} account_log_resource,
                {$tariffResourceTableName} tariff_resource
            WHERE
                account_log_resource.tariff_resource_id = tariff_resource.id
                AND tariff_resource.resource_id IN ({$resourceIdCalls})
                AND account_log_resource.date_from BETWEEN :date_from AND :date_to
                AND account_log_resource.account_tariff_id BETWEEN :account_tariff_id_from AND :account_tariff_id_to
SQL;

        $query = AccountLogResource::getDb()
            ->createCommand($sql, [
                ':date_from' => (new \DateTime())->modify($dateFromModify)->format(DateTimeZoneHelper::DATE_FORMAT),
                ':date_to' => (new \DateTime())->modify($dateToModify)->format(DateTimeZoneHelper::DATE_FORMAT),
                ':account_tariff_id_from' => $fromAccountTariffId,
                ':account_tariff_id_to' => $toAccountTariffId,
            ]);

        return $query->execute();
    }

    /**
     * @return array
     */
    public static function getHelpConfluence()
    {
        return ['confluenceId' => 7176475, 'message' => 'Плата за ресурсы'];
    }
}
