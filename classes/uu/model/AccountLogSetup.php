<?php

namespace app\classes\uu\model;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Предварительное списание стоимости подключения
 *
 * @property int $id
 * @property string $date
 * @property int $tariff_period_id  кэш accountTariff -> accountTariffLog -> tariff_period_id
 * @property int $account_tariff_id
 * @property float $price кэш tariffPeriod -> price_setup
 * @property string $insert_time
 *
 * @property AccountTariff $accountTariff
 * @property TariffPeriod $tariffPeriod
 */
class AccountLogSetup extends ActiveRecord
{
    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits;

    public static function tableName()
    {
        return 'uu_account_log_setup';
    }

    public function rules()
    {
        return [
            [['id', 'tariff_period_id', 'account_tariff_id'], 'integer'],
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
     * Рассчитать плату всех услуг
     */
    public static function tarificateAll()
    {
        $accountTariffs = AccountTariff::find();

        // рассчитать по каждой универсальной услуге
        foreach ($accountTariffs->each() as $accountTariff) {
            echo '. ';
            self::tarificateAccountTariff($accountTariff);
        }
    }

    /**
     * Рассчитать плату по конкретной услуге
     * @param AccountTariff $accountTariff
     */
    public static function tarificateAccountTariff(AccountTariff $accountTariff)
    {
        /** @var self[] $accountLogs */
        $accountLogs = self::find()
            ->where('account_tariff_id = :account_tariff_id', [':account_tariff_id' => $accountTariff->id])
            ->indexBy('date')
            ->all(); // по которым произведен расчет

        $untarificatedPeriods = $accountTariff->getUntarificatedSetupPeriods($accountLogs);
        foreach ($untarificatedPeriods as $untarificatedPeriod) {
            $tariffPeriod = $untarificatedPeriod->getTariffPeriod();

            $accountLogSetup = new self();
            $accountLogSetup->date = $untarificatedPeriod->getDateFrom()->format('Y-m-d');
            $accountLogSetup->tariff_period_id = $tariffPeriod->id;
            $accountLogSetup->account_tariff_id = $accountTariff->id;
            $accountLogSetup->price = $tariffPeriod->price_setup;
            $accountLogSetup->save();
        }
    }
}
