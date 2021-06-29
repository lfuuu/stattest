<?php

namespace app\modules\uu\tarificator;

use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\Number;
use app\modules\uu\classes\AccountLogFromToTariff;
use app\modules\uu\models\AccountLogSetup;
use app\modules\uu\models\AccountTariff;
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
        $minTarificateDatetime = AccountTariff::getMinSetupDatetime();

        // в целях оптимизации удалить слишком старые данные
        if (!$accountTariffId) {
            AccountLogSetup::deleteAll(['<', 'date', $minLogDatetime->format(DateTimeZoneHelper::DATE_FORMAT)], [], 'id ASC');
        }

        $accountTariffQuery = AccountTariff::find()
            ->where(['>=', 'tariff_period_utc', $minTarificateDatetime->format(DateTimeZoneHelper::DATETIME_FORMAT)]); // недавно (пару дней) произошла смена тарифа
        $accountTariffId && $accountTariffQuery->andWhere(['id' => $accountTariffId]);

        // рассчитать по каждой универсальной услуге
        $i = 0;
        foreach ($accountTariffQuery->each() as $accountTariff) {
            if ($i++ % 1000 === 0) {
                $this->out('. ');
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
     * @throws \Exception
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

            if (abs($accountLogSetup->price) >= 0.01) {
                $this->isNeedRecalc = true;
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

        // Стоимость подключения тарифа - только не при переносе
        $accountLogSetup->price_setup = $accountTariff->prev_usage_id ? 0 : $tariffPeriod->price_setup;

        if ($accountLogFromToTariff->isFirst
            && !$accountTariff->prev_usage_id
            && $tariffPeriod->tariff->service_type_id == ServiceType::ID_VOIP
            && !Number::isMcnLine($accountTariff->voip_number)
            && $accountTariff->number
        ) {
            // Телефонный номер (не телефонная линия).
            // Только первое подключение (при смене тарифа не считать).
            // При переносе не считать.
            $accountLogSetup->price_number = (float)$accountTariff->number
                ->getPrice($tariffPeriod->tariff->currency_id, $accountTariff->clientAccount);

            if (is_null($accountLogSetup->price_number)) {
                throw new \Exception('Не указана стоимость подключения номера ' . $accountTariff->voip_number);
            }
        } else {
            $accountLogSetup->price_number = 0;
        }

        if ($tariffPeriod->tariff->getIsTest()) {
            // Если тариф тестовый, то не взимаем ни стоимость подключения, ни абонентскую плату.
            $accountLogSetup->price = 0;
        } else {
            $accountLogSetup->price = $accountLogSetup->price_setup + $accountLogSetup->price_number;
        }

        return $accountLogSetup;
    }
}
