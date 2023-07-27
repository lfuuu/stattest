<?php

namespace app\modules\uu\tarificator;

use app\classes\model\ActiveRecord;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\Param;
use app\modules\uu\classes\AccountLogFromToResource;
use app\modules\uu\classes\AccountLogFromToTariff;
use app\modules\uu\classes\DateTimeOffsetParams;
use app\modules\uu\classes\ParallelExecutionSettings;
use app\modules\uu\models\AccountLogResource;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ResourceModel;
use app\modules\uu\resourceReader\ResourceReaderInterface;
use DateTimeImmutable;
use Yii;

/**
 * Списание (транзакции) платы за ресурсы. Тарификация
 */
class AccountLogResourceTarificator extends Tarificator
{
    const BATCH_READ_SIZE = 500;

    /** @var ResourceReaderInterface[] */
    protected $readersByResourceId = [];

    /**
     * Получить ридер по Id ресурса
     *
     * @param $resourceId
     * @return ResourceReaderInterface
     */
    protected function getReaderByResourceId($resourceId)
    {
        if (!isset($this->readersByResourceId[$resourceId])) {
            $this->readersByResourceId[$resourceId] = ResourceModel::getReader($resourceId);
        }

        return $this->readersByResourceId[$resourceId];
    }

    /**
     * Рассчитать плату всех услуг
     *
     * @param int|null $accountTariffId Если указан, то только для этой услуги. Если не указан - для всех
     * @param bool $isForceTarifficationTraffic Принудительный пересчет ресурсов
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function tarificate($accountTariffId = null, $isForceTarifficationTraffic = false)
    {
        $minSetupDatetime = AccountTariff::getMinSetupDatetime();

        $dateTimeOffsetParams = new DateTimeOffsetParams($this);
        $utcDateTime = $dateTimeOffsetParams->getCurrentDateTime();

        $fromId = $toId = null;

        // check console start
        $isConsoleScriptAndNotParamers = isset($_SERVER['argv']) && count($_SERVER['argv']) == 2 && $_SERVER['argv'][1] == 'ubiller';

        // распараллеливание обработки
        $pes = new ParallelExecutionSettings($_SERVER['argv']);
        if ($pes->isParallel()) {
            $fromId = $pes->getFromId();
            $toId = $pes->getToId();

            echo PHP_EOL . 'parallel execution: from ' . $fromId. ' to ' . $toId;
            echo PHP_EOL;
        }

        // рассчитать новое по каждой универсальной услуге
        $accountTariffQuery = AccountTariff::find()
            ->alias('a')
            ->andWhere([
                'OR',
                ['account_log_resource_utc' => null], // ресурсы не списаны
                ['<', 'account_log_resource_utc', $utcDateTime->format(DateTimeZoneHelper::DATE_FORMAT)] // или списаны давно
            ]);

        if (!$isForceTarifficationTraffic) {
            $accountTariffQuery->andWhere([
                'OR',
                // незакрытые
                ['IS NOT', 'tariff_period_id', null],
                // или недавно произошла смена тарифа (если вчера закрыли, то деньги все равно надо списать)
                ['>=', 'tariff_period_utc', $minSetupDatetime->format(DateTimeZoneHelper::DATETIME_FORMAT)],
            ]);
        }


        $fromId && $toId && $accountTariffQuery->andWhere(['between', 'a.id', $fromId, $toId]);

        $accountTariffQuery
            ->with('clientAccount')
            ->with('resources')
            ->with('accountLogResourceOptions')
            ->with('accountLogResourceTraffics')
            ->with('accountTariffResourceLogsAll')
            ->with('accountTariffLogs.tariffPeriod.tariff.tariffResourcesIndexedByResourceId')
            ->with('accountTariffLogs.tariffPeriod.chargePeriod')
            ->with('tariffPeriod.tariff')
            ->with('number');

        if ($accountTariffId) {
            $accountTariffQuery->andWhere(['a.id' => $accountTariffId]);
        } else {
            $accountTariffQuery->orderBy([
                'client_account_id' => SORT_ASC,
                'prev_account_tariff_id' => SORT_ASC,
                'a.id' => SORT_ASC,
            ]);
        }

//        if (\Yii::$app->isEu()) {
//            $accountTariffQuery
//                ->joinWith('clientAccount as c')
//                ->andWhere(['not', ['c.currency' => Currency::RUB]]);
//        }

        $i = 0;
        $all = $accountTariffQuery->count();
        foreach ($accountTariffQuery->each(self::BATCH_READ_SIZE) as $accountTariff) {
            /** @var AccountTariff $accountTariff */
            if ($i++ % 500 === 0) {
                $this->out(PHP_EOL . ($i - 1) . ' of ' . $all . ' account tariffs processed' . PHP_EOL);
            }

            if ($isConsoleScriptAndNotParamers && $i % 50 === 0) {
                if (Param::getParam(Param::STOP_UBILLER_FLAG, Param::IS_OFF)) {
                    Param::setParam(Param::STOP_UBILLER_FLAG, Param::IS_OFF);

                    echo PHP_EOL . date('r') . ': Ubiller stoped';
                    echo PHP_EOL;
                    exit();
                }
            }

            $transaction = Yii::$app->db->beginTransaction();
            try {

                // ресурсы-опции
                $this->tarificateAccountTariffOption($accountTariff);

                // ресурсы-трафик
                if ($accountTariffId && !$isForceTarifficationTraffic) {
                    $isOk = false;
                } else {
                    // только по крону
                    $isOk = $this->tarificateAccountTariffTraffic($accountTariff);
                }

                if ($isOk) {
                    // пометить, что все рассчитано
                    $accountTariff->account_log_resource_utc = $accountTariff
                        ->clientAccount
                        ->getDatetimeWithTimezone()// по таймзоне клиента
                        ->setTime(23, 59, 59)// до конца "сегодня"
                        ->setTimezone(new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC))// перевести в UTC
                        ->format(DateTimeZoneHelper::DATETIME_FORMAT);
                    if (!$accountTariff->save()) {
                        // "Не надо фаталиться, вся жизнь впереди. Вся жизнь впереди, надейся и жди." (С) Р. Рождественский
                        // throw new ModelValidationException($accountTariffLog);
                    }
                }

                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollBack();
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
     * Рассчитать плату по конкретной услуге за ресурсы-опции (количество линий, запись звонков и пр.)
     *
     * @param AccountTariff $accountTariff
     * @throws ModelValidationException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function tarificateAccountTariffOption(AccountTariff $accountTariff)
    {
        /** @var AccountLogFromToResource[][] $untarificatedPeriodss */
        $untarificatedPeriodss = $accountTariff->getUntarificatedResourceOptionPeriods();

        $modelsToSave = [];
        $isNeedRecalc = false;
        /** @var AccountLogFromToResource[] $untarificatedPeriods */
        foreach ($untarificatedPeriodss as $resourceId => $untarificatedPeriods) {

            /** @var AccountLogFromToResource $untarificatedPeriod */
            foreach ($untarificatedPeriods as $untarificatedPeriod) {
                $model = $this->getAccountLogResource($accountTariff, $untarificatedPeriod, $resourceId);
                if (!$isNeedRecalc && abs($model->price) >= 0.01) {
                    $isNeedRecalc = true;
                }

                $modelsToSave[] = $model;
            }
        }
        $this->out('+ ');
        ActiveRecord::batchInsertModels($modelsToSave);

        $isNeedRecalc && $this->isNeedRecalc = true;
    }

    /**
     * Рассчитать плату по конкретной услуге за ресурсы-трафик (звонки и дисковое пространство)
     *
     * @param AccountTariff $accountTariff
     * @return bool Успешно ли (нет ли пропущенных)
     * @throws ModelValidationException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function tarificateAccountTariffTraffic(AccountTariff $accountTariff)
    {
        $isOk = true;

        $untarificatedPeriodss = $accountTariff->getUntarificatedResourceTrafficPeriods();

        $isNeedRecalc = false;
        $modelsToSave = [];
        /** @var AccountLogFromToTariff[] $untarificatedPeriods */
        foreach ($untarificatedPeriodss as $dateYmd => $untarificatedPeriods) {

            /** @var AccountLogFromToTariff $untarificatedPeriod */
            foreach ($untarificatedPeriods as $resourceId => $untarificatedPeriod) {

                /** @var DateTimeImmutable $dateTime */
                $dateTime = $untarificatedPeriod->dateFrom;
                $tariffPeriod = $untarificatedPeriod->tariffPeriod;

                $tariffResource = $tariffPeriod->tariff->tariffResourcesIndexedByResourceId[$resourceId];

                $reader = $this->getReaderByResourceId($resourceId);
                $amounts = $reader->read($accountTariff, $dateTime, $tariffPeriod);
                if ($amounts->amount === null) {
                    $this->out(PHP_EOL . '("' . $dateTime->format(DateTimeZoneHelper::DATE_FORMAT) . '", ' . $tariffPeriod->id . ', ' . $accountTariff->id . ', ' . $tariffResource->id . '), -- Resource ' . $resourceId . ' is null' . PHP_EOL);
                    $isOk = false;
                    continue; // нет данных. Пропустить
                }

                $accountLogResource = new AccountLogResource();
                $accountLogResource->date_from
                    = $accountLogResource->date_to
                    = $dateTime->format(DateTimeZoneHelper::DATE_FORMAT);
                $accountLogResource->tariff_period_id = $tariffPeriod->id;
                $accountLogResource->account_tariff_id = $accountTariff->id;
                $accountLogResource->tariff_resource_id = $tariffResource->id;
                $accountLogResource->amount_use = $amounts->amount;
                $accountLogResource->amount_free = $tariffResource->amount;
                $accountLogResource->price_per_unit = $reader->getIsMonthPricePerUnit() ?
                    $tariffResource->price_per_unit / $dateTime->format('t') : // это "цена за месяц", а надо перевести в "цену за день"
                    $tariffResource->price_per_unit; // это "цена за день", так и оставить

                if ($amounts->amount < 0) {
                    $accountLogResource->amount_overhead = $accountLogResource->amount_use;
                } else {
                    $accountLogResource->amount_overhead = max(
                        0,
                        $accountLogResource->amount_use - $accountLogResource->amount_free
                    );
                }

                $accountLogResource->coefficient = 1;
                $accountLogResource->price = $accountLogResource->amount_overhead * $accountLogResource->price_per_unit;
                $accountLogResource->cost_price = $amounts->costAmount * $accountLogResource->price_per_unit;

                if (!$isNeedRecalc && abs($accountLogResource->price) >= 0.01) {
                    $isNeedRecalc = true;
                }


                $modelsToSave[] = $accountLogResource;
            }
        }
        $this->out('+ ');
        ActiveRecord::batchInsertModels($modelsToSave);

        $isNeedRecalc && $this->isNeedRecalc = true;

        return $isOk;
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
    public function getAccountLogResource(AccountTariff $accountTariff, AccountLogFromToResource $accountLogFromToResource, $resourceId)
    {
        $tariffPeriod = $accountLogFromToResource->tariffPeriod;

        $tariffResource = $tariffPeriod->tariff->tariffResourcesIndexedByResourceId[$resourceId];

        $accountLogResource = new AccountLogResource();
        $accountLogResource->date_from = $accountLogFromToResource->dateFrom->format(DateTimeZoneHelper::DATE_FORMAT);
        $accountLogResource->date_to = $accountLogFromToResource->dateTo->format(DateTimeZoneHelper::DATE_FORMAT);
        $accountLogResource->tariff_period_id = $tariffPeriod->id;
        $accountLogResource->account_tariff_id = $accountTariff->id;
        $accountLogResource->tariff_resource_id = $tariffResource->id;
        $accountLogResource->amount_overhead
            = $accountLogResource->amount_use
            = (float)$accountLogFromToResource->amountOverhead;
        $accountLogResource->amount_free = 0; // В amount_use не всего, а уже превышение, то есть за вычетом бесплатного
        $accountLogResource->price_per_unit = $tariffResource->price_per_unit / $accountLogFromToResource->dateFrom->format('t'); // это "цена за месяц", а надо перевести в "цену за день"
        $accountLogResource->coefficient = 1 + (int)$accountLogFromToResource->dateTo->diff($accountLogFromToResource->dateFrom)->days; // кол-во дней между dateTo и dateFrom
        $accountLogResource->price = $accountLogResource->amount_overhead * $accountLogResource->price_per_unit * $accountLogResource->coefficient;
        $accountLogResource->account_tariff_resource_log_id = $accountLogFromToResource->account_tariff_resource_log_id;

        return $accountLogResource;
    }
}
