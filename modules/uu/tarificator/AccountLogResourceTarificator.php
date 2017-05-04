<?php

namespace app\modules\uu\tarificator;

use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\modules\uu\classes\AccountLogFromToResource;
use app\modules\uu\classes\AccountLogFromToTariff;
use app\modules\uu\models\AccountLogResource;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffLog;
use app\modules\uu\models\Resource;
use app\modules\uu\models\TariffResource;
use app\modules\uu\resourceReader\ResourceReaderInterface;
use DateTimeImmutable;
use Yii;

/**
 * Предварительное списание (транзакции) платы за ресурсы. Тарификация
 */
class AccountLogResourceTarificator extends Tarificator
{
    /** @var ResourceReaderInterface[] кэш */
    protected $resourceIdToReader = [];

    /**
     * Рассчитать плату всех услуг
     *
     * @param int|null $accountTariffId Если указан, то только для этой услуги. Если не указан - для всех
     * @throws \Exception
     */
    public function tarificate($accountTariffId = null)
    {
        $minLogDatetime = AccountTariff::getMinLogDatetime();
        // в целях оптимизации удалить старые данные
        AccountLogResource::deleteAll(['<', 'date_from', $minLogDatetime->format(DateTimeZoneHelper::DATE_FORMAT)]);

        // рассчитать новое по каждой универсальной услуге
        $accountTariffQuery = AccountTariff::find();
        $accountTariffId && $accountTariffQuery->andWhere(['id' => $accountTariffId]);

        $i = 0;
        /** @var AccountTariff $accountTariff */
        foreach ($accountTariffQuery->each() as $accountTariff) {
            if ($i++ % 1000 === 0) {
                $this->out('. ');
            }

            /** @var AccountTariffLog[] $accountTariffLogs */
            $accountTariffLogs = $accountTariff->accountTariffLogs;
            $accountTariffLog = reset($accountTariffLogs);
            if (!$accountTariffLog ||
                (!$accountTariffLog->tariff_period_id && $accountTariffLog->actual_from_utc < $minLogDatetime->format(DateTimeZoneHelper::DATETIME_FORMAT))
            ) {
                // услуга отключена давно - в целях оптимизации считать нет смысла
                continue;
            }

            $transaction = Yii::$app->db->beginTransaction();
            try {
                $this->tarificateAccountTariff($accountTariff);
                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollBack();
                $this->out(PHP_EOL . $e->getMessage() . PHP_EOL);
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
     * @throws \LogicException
     * @throws \Exception
     * @throws \app\exceptions\ModelValidationException
     */
    public function tarificateAccountTariff(AccountTariff $accountTariff)
    {
        $this->tarificateAccountTariffOption($accountTariff);
        $this->tarificateAccountTariffTraffic($accountTariff);
    }

    /**
     * Рассчитать плату по конкретной услуге за ресурсы-опции (количество линий, запись звонков и пр.)
     *
     * @param AccountTariff $accountTariff
     * @throws \LogicException
     * @throws \app\exceptions\ModelValidationException
     * @throws \Exception
     * @throws \yii\db\StaleObjectException
     */
    public function tarificateAccountTariffOption(AccountTariff $accountTariff)
    {
        /** @var AccountLogFromToResource[][] $untarificatedPeriodss */
        $untarificatedPeriodss = $accountTariff->getUntarificatedResourceOptionPeriods();

        /** @var AccountLogFromToResource[] $untarificatedPeriods */
        foreach ($untarificatedPeriodss as $resourceId => $untarificatedPeriods) {

            /** @var AccountLogFromToResource $untarificatedPeriod */
            foreach ($untarificatedPeriods as $untarificatedPeriod) {
                $accountLogResource = $this->getAccountLogPeriod($accountTariff, $untarificatedPeriod, $resourceId);
                if (!$accountLogResource->save()) {
                    throw new ModelValidationException($accountLogResource);
                }
            }
        }
    }

    /**
     * Рассчитать плату по конкретной услуге за ресурсы-трафик (звонки и дисковое пространство)
     *
     * @param AccountTariff $accountTariff
     * @throws \LogicException
     * @throws \app\exceptions\ModelValidationException
     */
    public function tarificateAccountTariffTraffic(AccountTariff $accountTariff)
    {
        $untarificatedPeriodss = $accountTariff->getUntarificatedResourceTrafficPeriods();

        /** @var AccountLogFromToTariff[] $untarificatedPeriods */
        foreach ($untarificatedPeriodss as $dateYmd => $untarificatedPeriods) {

            /** @var AccountLogFromToTariff $untarificatedPeriod */
            foreach ($untarificatedPeriods as $resourceId => $untarificatedPeriod) {

                /** @var DateTimeImmutable $dateTime */
                $dateTime = $untarificatedPeriod->dateFrom;
                $tariffPeriod = $untarificatedPeriod->tariffPeriod;

                $tariffResource = TariffResource::findOne([
                    'resource_id' => $resourceId,
                    'tariff_id' => $tariffPeriod->tariff_id,
                ]);

                if (!isset($this->resourceIdToReader[$resourceId])) {
                    // записать в кэш
                    $this->resourceIdToReader[$resourceId] = Resource::getReader($resourceId);
                }

                /** @var ResourceReaderInterface $reader */
                $reader = $this->resourceIdToReader[$resourceId];
                $amountUse = $reader->read($accountTariff, $dateTime);
                if ($amountUse === null) {
                    $this->out(PHP_EOL . '("' . $dateTime->format(DateTimeZoneHelper::DATE_FORMAT) . '", ' . $tariffPeriod->id . ', ' . $accountTariff->id . ', ' . $tariffResource->id . '), -- ' . $resourceId . PHP_EOL);
                    continue; // нет данных. Пропустить
                }

                $this->out('+ ');

                $accountLogResource = new AccountLogResource();
                $accountLogResource->date_from
                    = $accountLogResource->date_to
                    = $dateTime->format(DateTimeZoneHelper::DATE_FORMAT);
                $accountLogResource->tariff_period_id = $tariffPeriod->id;
                $accountLogResource->account_tariff_id = $accountTariff->id;
                $accountLogResource->tariff_resource_id = $tariffResource->id;
                $accountLogResource->amount_use = $amountUse;
                $accountLogResource->amount_free = $tariffResource->amount;
                $accountLogResource->price_per_unit = $reader->getIsMonthPricePerUnit() ?
                    $tariffResource->price_per_unit / $dateTime->format('t') : // это "цена за месяц", а надо перевести в "цену за день"
                    $tariffResource->price_per_unit; // это "цена за день", так и оставить
                $accountLogResource->amount_overhead = max(0, $accountLogResource->amount_use - $accountLogResource->amount_free);
                $accountLogResource->coefficient = 1;
                $accountLogResource->price = $accountLogResource->amount_overhead * $accountLogResource->price_per_unit;
                if (!$accountLogResource->save()) {
                    throw new ModelValidationException($accountLogResource);
                }
            }
        }
    }

    /**
     * Создать и вернуть AccountLogResource, но не сохранять его!
     * "Не сохранение" нужно для проверки возможности списания без фактического списывания
     *
     * @param AccountTariff $accountTariff
     * @param AccountLogFromToResource $accountLogFromToResource
     * @param int $resourceId
     * @return AccountLogResource
     * @throws \RangeException
     */
    public function getAccountLogPeriod(AccountTariff $accountTariff, AccountLogFromToResource $accountLogFromToResource, $resourceId)
    {
        $tariffPeriod = $accountLogFromToResource->tariffPeriod;

        /** @var TariffResource $tariffResource */
        $tariffResource = TariffResource::findOne([
            'resource_id' => $resourceId,
            'tariff_id' => $tariffPeriod->tariff_id,
        ]);

        $this->out('+ ');

        $accountLogResource = new AccountLogResource();
        $accountLogResource->date_from = $accountLogFromToResource->dateFrom->format(DateTimeZoneHelper::DATE_FORMAT);
        $accountLogResource->date_to = $accountLogFromToResource->dateTo->format(DateTimeZoneHelper::DATE_FORMAT);
        $accountLogResource->tariff_period_id = $tariffPeriod->id;
        $accountLogResource->account_tariff_id = $accountTariff->id;
        $accountLogResource->tariff_resource_id = $tariffResource->id;
        $accountLogResource->amount_use = $accountLogFromToResource->amount;
        $accountLogResource->amount_free = $tariffResource->amount;
        $accountLogResource->price_per_unit = $tariffResource->price_per_unit / $accountLogFromToResource->dateFrom->format('t'); // это "цена за месяц", а надо перевести в "цену за день"
        $accountLogResource->amount_overhead = max(0, $accountLogResource->amount_use - $accountLogResource->amount_free);
        $accountLogResource->coefficient = 1 + (int)$accountLogFromToResource->dateTo->diff($accountLogFromToResource->dateFrom)->days; // кол-во дней между dateTo и dateFrom
        $accountLogResource->price = $accountLogResource->amount_overhead * $accountLogResource->price_per_unit * $accountLogResource->coefficient;

        return $accountLogResource;
    }
}
