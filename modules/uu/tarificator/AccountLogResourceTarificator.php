<?php

namespace app\modules\uu\tarificator;

use app\helpers\DateTimeZoneHelper;
use app\modules\uu\forms\AccountLogFromToTariff;
use app\modules\uu\models\AccountLogResource;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffLog;
use app\modules\uu\models\Resource;
use app\modules\uu\models\TariffResource;
use app\modules\uu\resourceReader\DummyResourceReader;
use app\modules\uu\resourceReader\ResourceReaderInterface;
use DateTimeImmutable;
use Yii;

/**
 * Предварительное списание (транзакции) платы за ресурсы. Тарификация
 */
class AccountLogResourceTarificator implements TarificatorI
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
        AccountLogResource::deleteAll(['<', 'date', $minLogDatetime->format(DateTimeZoneHelper::DATE_FORMAT)]);

        // рассчитать новое по каждой универсальной услуге
        $accountTariffs = AccountTariff::find();
        $accountTariffId && $accountTariffs->andWhere(['id' => $accountTariffId]);

        $i = 0;
        foreach ($accountTariffs->each() as $accountTariff) {
            if ($i++ % 1000 === 0) {
                echo '. ';
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

            $transaction = Yii::$app->db->beginTransaction();
            try {
                $this->tarificateAccountTariff($accountTariff);
                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollBack();
                echo PHP_EOL . $e->getMessage() . PHP_EOL;
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
     */
    public function tarificateAccountTariff(AccountTariff $accountTariff)
    {
        // ресурсы, по которым произведен расчет
        $accountLogsQuery = AccountLogResource::find()
            ->where(['account_tariff_id' => $accountTariff->id]);
        /** @var AccountLogResource $accountLog */
        $accountLogs = [];
        foreach ($accountLogsQuery->each() as $accountLog) {
            $accountLogs[$accountLog->date][$accountLog->tariffResource->resource_id] = $accountLog;
        }

        $untarificatedPeriodss = $accountTariff->getUntarificatedResourcePeriods($accountLogs);

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
                if (!$reader) {
                    continue;
                }

                $amountUse = $reader->read($accountTariff, $dateTime);
                if ($amountUse === null) {
                    if (!($reader instanceof DummyResourceReader)) {
                        echo PHP_EOL . '("' . $dateTime->format(DateTimeZoneHelper::DATE_FORMAT) . '", ' . $tariffPeriod->id . ', ' . $accountTariff->id . ', ' . $tariffResource->id . '), -- ' . $resourceId . PHP_EOL;
                    }

                    continue; // нет данных. Пропустить
                } else {
                    echo '+ ';
                }

                $accountLogResource = new AccountLogResource();
                $accountLogResource->date = $dateTime->format(DateTimeZoneHelper::DATE_FORMAT);
                $accountLogResource->tariff_period_id = $tariffPeriod->id;
                $accountLogResource->account_tariff_id = $accountTariff->id;
                $accountLogResource->tariff_resource_id = $tariffResource->id;
                $accountLogResource->amount_use = $amountUse;
                $accountLogResource->amount_free = $tariffResource->amount;
                $accountLogResource->price_per_unit = $reader->getIsMonthPricePerUnit() ?
                    $tariffResource->price_per_unit / $dateTime->format('t') : // это "цена за месяц", а надо перевести в "цену за день"
                    $tariffResource->price_per_unit; // это "цена за день", так и оставить
                $accountLogResource->amount_overhead = max(0, $accountLogResource->amount_use - $accountLogResource->amount_free);
                $accountLogResource->price = $accountLogResource->amount_overhead * $accountLogResource->price_per_unit;
                $accountLogResource->save();
            }
        }
    }
}
