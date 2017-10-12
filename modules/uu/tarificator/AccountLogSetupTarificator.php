<?php

namespace app\modules\uu\tarificator;

use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\Number;
use app\modules\uu\classes\AccountLogFromToTariff;
use app\modules\uu\models\AccountLogSetup;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffLog;
use app\modules\uu\models\ServiceType;
use Yii;

/**
 * Предварительное списание (транзакции) стоимости подключения. Тарификация
 */
class AccountLogSetupTarificator extends Tarificator
{
    /**
     * Рассчитать плату всех услуг
     *
     * @param int|null $accountTariffId Если указан, то только для этой услуги. Если не указан - для всех
     * @param bool $isWithTransaction
     * @throws \Exception
     */
    public function tarificate($accountTariffId = null, $isWithTransaction = true)
    {
        $minLogDatetime = AccountTariff::getMinLogDatetime();
        // в целях оптимизации удалить слишком старые данные
        AccountLogSetup::deleteAll(['<', 'date', $minLogDatetime->format(DateTimeZoneHelper::DATE_FORMAT)], [], 'id ASC');

        $accountTariffs = AccountTariff::find();
        $accountTariffId && $accountTariffs->andWhere(['id' => $accountTariffId]);

        // рассчитать по каждой универсальной услуге
        $i = 0;
        foreach ($accountTariffs->each() as $accountTariff) {
            if ($i++ % 1000 === 0) {
                $this->out('. ');
            }

            /** @var AccountTariffLog $accountTariffLog */
            $accountTariffLogs = $accountTariff->accountTariffLogs;
            $accountTariffLog = reset($accountTariffLogs);
            if (!$accountTariffLog ||
                (!$accountTariffLog->tariff_period_id && $accountTariffLog->actual_from_utc < $minLogDatetime->format(DateTimeZoneHelper::DATETIME_FORMAT))
            ) {
                // услуга отключена давно - в целях оптимизации считать нет смысла
                continue;
            }

            $isWithTransaction && $transaction = Yii::$app->db->beginTransaction();
            try {
                $this->tarificateAccountTariff($accountTariff);
                $isWithTransaction && $transaction->commit();
            } catch (\Exception $e) {
                $isWithTransaction && $transaction->rollBack();
                $this->out(PHP_EOL . 'Error. ' . $e->getMessage() . PHP_EOL);
                Yii::error($e->getMessage());
                // не получилось с одной услугой - пойдем считать другую
                if ($accountTariffId) {
                    throw $e;
                }
            }
        }
    }

    /**
     * Рассчитать плату по конкретной услуге
     *
     * @param AccountTariff $accountTariff
     * @throws \app\exceptions\ModelValidationException
     */
    public function tarificateAccountTariff(AccountTariff $accountTariff)
    {
        $untarificatedPeriods = $accountTariff->getUntarificatedSetupPeriods();
        /** @var AccountLogFromToTariff $untarificatedPeriod */
        foreach ($untarificatedPeriods as $untarificatedPeriod) {
            $accountLogSetup = $this->getAccountLogSetup($accountTariff, $untarificatedPeriod);
            if (!$accountLogSetup->save()) {
                throw new ModelValidationException($accountLogSetup);
            }
        }
    }

    /**
     * Создать и вернуть AccountLogPeriod, но не сохранять его!
     * "Не сохранение" нужно для проверки возможности списания без фактического списывания
     *
     * @param AccountTariff $accountTariff
     * @param AccountLogFromToTariff $accountLogFromToTariff
     * @return AccountLogSetup
     * @throws \Exception
     */
    public function getAccountLogSetup(AccountTariff $accountTariff, AccountLogFromToTariff $accountLogFromToTariff)
    {
        $tariffPeriod = $accountLogFromToTariff->tariffPeriod;

        $accountLogSetup = new AccountLogSetup();
        $accountLogSetup->date = $accountLogFromToTariff->dateFrom->format(DateTimeZoneHelper::DATE_FORMAT);
        $accountLogSetup->tariff_period_id = $tariffPeriod->id;
        $accountLogSetup->account_tariff_id = $accountTariff->id;

        $accountLogSetup->price_setup = $tariffPeriod->price_setup;

        if ($accountLogFromToTariff->isFirst && $tariffPeriod->tariff->service_type_id == ServiceType::ID_VOIP && $accountTariff->voip_number > Number::NUMBER_MAX_LINE && $accountTariff->number) {
            // телефонный номер кроме телефонной линии (4-5 знаков)
            // только первое подключение. При смене тарифа на том же ЛС не считать
            $accountLogSetup->price_number = $accountTariff->number
                ->getPrice($tariffPeriod->tariff->currency_id, $accountTariff->clientAccount);

            if (is_null($accountLogSetup->price_number)) {
                throw new \Exception('Не указана стоимость подключения номера ' . $accountTariff->voip_number);
            }
        } else {
            $accountLogSetup->price_number = 0;
        }

        if ($tariffPeriod->tariff->getIsTest()) {
            // Если тариф тестовый, то не взимаем ни стоимость подключения, ни абонентскую плату.
            // @link http://rd.welltime.ru/confluence/pages/viewpage.action?pageId=4391334
            $accountLogSetup->price = 0;
        } else {
            $accountLogSetup->price = $accountLogSetup->price_setup + $accountLogSetup->price_number;
        }

        return $accountLogSetup;
    }
}
