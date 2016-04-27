<?php

namespace app\classes\uu\model;

use RangeException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Предварительное списание абонентской платы
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
 *
 * @property AccountTariff $accountTariff
 * @property TariffPeriod $tariffPeriod
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
        $minLogDatetime = AccountTariff::getMinLogDatetime();
        // в целях оптимизации удалить старые данные
        self::deleteAll(['<', 'date_to', $minLogDatetime->format('Y-m-d')]);

        $accountTariffs = AccountTariff::find();

        // рассчитать по каждой универсальной услуге
        foreach ($accountTariffs->each() as $accountTariff) {
            echo '. ';

            /** @var AccountTariffLog $accountTariffLog */
            $accountTariffLogs = $accountTariff->accountTariffLogs;
            $accountTariffLog = reset($accountTariffLogs);
            if (!$accountTariffLog ||
                (!$accountTariffLog->tariff_period_id && $accountTariffLog->actual_from < $minLogDatetime->format('Y-m-d'))
            ) {
                // услуга отключена давно - в целях оптимизации считать нет смысла
                continue;
            }

            self::tarificateAccountTariff($accountTariff);
        }
    }

    /**
     * Рассчитать плату по конкретной услуге
     * @param AccountTariff $accountTariff
     */
    public static function tarificateAccountTariff(AccountTariff $accountTariff)
    {
        // по которым произведен расчет
        /** @var AccountLogPeriod[] $accountLogs */
        $accountLogs = self::find()
            ->where(['account_tariff_id' => $accountTariff->id])
            ->indexBy('date_from')
            ->all();

        $untarificatedPeriods = $accountTariff->getUntarificatedPeriodPeriods($accountLogs);
        foreach ($untarificatedPeriods as $untarificatedPeriod) {
            $tariffPeriod = $untarificatedPeriod->getTariffPeriod();
            $period = $tariffPeriod->period;

            $accountLogPeriod = new self();
            $accountLogPeriod->date_from = $untarificatedPeriod->getDateFrom()->format('Y-m-d');
            $accountLogPeriod->date_to = $untarificatedPeriod->getDateTo()->format('Y-m-d');
            if ($untarificatedPeriod->getDateTo() < $untarificatedPeriod->getDateFrom()) {
                throw new RangeException(sprintf('Date_to %s can not be less than date_from %s. AccountTariffId = %d', $accountLogPeriod->date_to, $accountLogPeriod->date_from, $accountTariff->id));
            }

            $accountLogPeriod->tariff_period_id = $tariffPeriod->id;
            $accountLogPeriod->account_tariff_id = $accountTariff->id;
            $accountLogPeriod->period_price = $tariffPeriod->price_per_period;
            $accountLogPeriod->coefficient = 1 + $untarificatedPeriod->getDateTo()
                    ->diff($untarificatedPeriod->getDateFrom())
                    ->days; // кол-во потраченных дней
            if ($period->monthscount) {
                // разделить на кол-во дней в периоде
                $days = 1 + $untarificatedPeriod->getDateFrom()
                        ->modify($period->getModify())
                        ->modify('-1 day')
                        ->diff($untarificatedPeriod->getDateFrom())
                        ->days;
                $accountLogPeriod->coefficient /= $days;
            }
            $accountLogPeriod->price = $accountLogPeriod->period_price * $accountLogPeriod->coefficient;
            $accountLogPeriod->save();
        }
    }
}
