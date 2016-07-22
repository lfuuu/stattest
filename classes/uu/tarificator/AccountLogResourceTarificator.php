<?php

namespace app\classes\uu\tarificator;

use app\classes\uu\model\AccountLogResource;
use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\AccountTariffLog;
use app\classes\uu\model\Resource;
use app\classes\uu\model\TariffResource;
use app\classes\uu\resourceReader\ResourceReaderInterface;
use DateTimeImmutable;
use Yii;

/**
 * Предварительное списание (транзакции) платы за ресурсы. Тарификация
 */
class AccountLogResourceTarificator
{

    /** @var TariffResource[] кэш */
    protected $tariffIdToTariffResources = [];

    /** @var ResourceReaderInterface[] кэш */
    protected $resourceIdToReader = [];

    /**
     * Рассчитать плату всех услуг
     */
    public function tarificateAll()
    {
        $minLogDatetime = AccountTariff::getMinLogDatetime();
        // в целях оптимизации удалить старые данные
        AccountLogResource::deleteAll(['<', 'date', $minLogDatetime->format('Y-m-d')]);

        // рассчитать новое по каждой универсальной услуге
        $accountTariffs = AccountTariff::find();
        $i = 0;
        foreach ($accountTariffs->each() as $accountTariff) {
            if ($i++ % 1000 === 0) {
                echo '. ';
            }

            /** @var AccountTariffLog $accountTariffLog */
            $accountTariffLogs = $accountTariff->accountTariffLogs;
            $accountTariffLog = reset($accountTariffLogs);
            if (!$accountTariffLog ||
                (!$accountTariffLog->tariff_period_id && $accountTariffLog->actual_from < $minLogDatetime->format('Y-m-d'))
            ) {
                // услуга отключена давно - в целях оптимизации считать нет смысла
                continue;
            }

            $transaction = Yii::$app->db->beginTransaction();
            try {
                $this->_tarificateAccountTariff($accountTariff);
                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollBack();
                echo PHP_EOL . $e->getMessage() . PHP_EOL;
                Yii::error($e->getMessage());
                // не получилось с одной услугой - пойдем считать другую
            }
        }
    }

    /**
     * Рассчитать плату по конкретной услуге
     * @param AccountTariff $accountTariff
     */
    protected function _tarificateAccountTariff(AccountTariff $accountTariff)
    {
        // ресурсы, по которым произведен расчет
        /** @var AccountLogResource[] $accountLogs */
        $accountLogs = AccountLogResource::find()
            ->where(['account_tariff_id' => $accountTariff->id])
            ->indexBy('date')
            ->all();

        $untarificatedPeriods = $accountTariff->getUntarificatedResourcePeriods($accountLogs);
        foreach ($untarificatedPeriods as $untarificatedPeriod) {
            /** @var DateTimeImmutable $date */
            $date = $untarificatedPeriod->dateFrom;
            $tariffPeriod = $untarificatedPeriod->tariffPeriod;

            $tariffId = $tariffPeriod->tariff_id;
            if (!isset($this->tariffIdToTariffResources[$tariffId])) {
                // записать в кэш
                $this->tariffIdToTariffResources[$tariffId] = $tariffPeriod->tariff->tariffResources;
            }

            /** @var TariffResource[] $tariffResources */
            $tariffResources = $this->tariffIdToTariffResources[$tariffId];

            foreach ($tariffResources as $tariffResource) {

                $resourceId = $tariffResource->resource_id;
                if (!isset($this->resourceIdToReader[$resourceId])) {
                    // записать в кэш
                    $this->resourceIdToReader[$resourceId] = Resource::getReader($resourceId);
                }
                /** @var ResourceReaderInterface $reader */
                $reader = $this->resourceIdToReader[$resourceId];

                $accountLogResource = new AccountLogResource();
                $accountLogResource->date = $date->format('Y-m-d');
                $accountLogResource->tariff_period_id = $tariffPeriod->id;
                $accountLogResource->account_tariff_id = $accountTariff->id;
                $accountLogResource->tariff_resource_id = $tariffResource->id;
                $accountLogResource->amount_use = $reader->read($accountTariff, $date);
                if ($accountLogResource->amount_use === null) {
                    continue; // нет данных. Пропустить
                }
                $accountLogResource->amount_free = $tariffResource->amount;
                $accountLogResource->price_per_unit = $reader->getIsMonthPricePerUnit() ?
                    $tariffResource->price_per_unit / $date->format('t') : // это "цена за месяц", а надо перевести в "цену за день"
                    $tariffResource->price_per_unit; // это "цена за день", так и оставить
                $accountLogResource->amount_overhead = max(0,
                    $accountLogResource->amount_use - $accountLogResource->amount_free);
                $accountLogResource->price = $accountLogResource->amount_overhead * $accountLogResource->price_per_unit;
                $accountLogResource->save();
            }
        }
    }
}
