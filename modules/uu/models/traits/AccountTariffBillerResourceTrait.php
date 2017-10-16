<?php

namespace app\modules\uu\models\traits;

use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\modules\uu\classes\AccountLogFromToResource;
use app\modules\uu\classes\AccountLogFromToTariff;
use app\modules\uu\models\AccountLogResource;
use app\modules\uu\models\AccountTariffResourceLog;
use app\modules\uu\models\Period;
use app\modules\uu\models\Resource;
use app\modules\uu\models\TariffResource;
use DateTimeImmutable;
use yii\db\ActiveQuery;

trait AccountTariffBillerResourceTrait
{
    /** @var AccountLogFromToTariff[] */
    private $_accountLogFromToTariffsOption = null;

    /**
     * Вернуть периоды, по которым не произведен расчет по ресурсам-трафику
     *
     * Здесь все просто: посуточно. Данные берутся из внешних источников (звонки - из низкоуровневого биллера, дисковое пространство - с платформы)
     *
     * @return AccountLogFromToTariff[][]
     * @throws \LogicException
     */
    public function getUntarificatedResourceTrafficPeriods()
    {
        // по которым произведен расчет
        $accountLogsQuery = AccountLogResource::find()
            ->joinWith('tariffResource')
            ->where([
                AccountLogResource::tableName() . '.account_tariff_id' => $this->id,
                TariffResource::tableName() . '.resource_id' => array_keys(Resource::getReaderNames()), // только трафик
            ]);

        /** @var AccountLogResource $accountLog */
        $accountLogss = [];
        foreach ($accountLogsQuery->each() as $accountLog) {
            // у трафика date_from и date_to совпадают, поэтому date_to можно игнорировать
            $accountLogss[$accountLog->date_from][$accountLog->tariffResource->resource_id] = $accountLog;
        }


        // по которым должен быть произведен расчет
        /** @var AccountLogFromToTariff[] $accountLogFromToTariffs */
        $chargePeriod = Period::findOne(['id' => Period::ID_DAY]); // трафик - посуточно
        $accountLogFromToTariffs = $this->getAccountLogFromToTariffs($chargePeriod, $isWithCurrent = false);


        // по которым не произведен расчет, хотя был должен
        $untarificatedPeriodss = [];
        $readerNames = Resource::getReaderNames();
        foreach ($accountLogFromToTariffs as $accountLogFromToTariff) {

            $dateYmd = $accountLogFromToTariff->dateFrom->format(DateTimeZoneHelper::DATE_FORMAT);
            $tariffResources = $accountLogFromToTariff->tariffPeriod->tariff->tariffResources;

            // по всем ресурсам тарифа
            foreach ($tariffResources as $tariffResource) {

                $resourceId = $tariffResource->resource_id;
                if (!array_key_exists($resourceId, $readerNames)) {
                    // этот ресурс - не трафик. Он считается в соседнем методе
                    continue;
                }

                if (array_key_exists($dateYmd, $accountLogss) && array_key_exists($resourceId, $accountLogss[$dateYmd])) {

                    // такой ресурс-период рассчитан. unset нельзя, иначе потом ресурс добавится заново от другого пересекающегося периода
                    $accountLogss[$dateYmd][$resourceId] = null;

                } else {

                    // этот ресурс-период не рассчитан
                    // если в середине месяца сменили тариф, то за этот день будет две абонентки, но ресурс надо рассчитать только один раз (по последнему тарифу), поэтому используем хэш $dateYmd
                    $untarificatedPeriodss[$dateYmd][$resourceId] = $accountLogFromToTariff;

                }
            }
        }

        if (count($accountLogss)) {
            foreach ($accountLogss as $dateYmd => $accountLogs) {
                foreach ($accountLogs as $resourceId => $accountLog) {
                    if (!$accountLog) {
                        continue;
                    }

                    // остался неизвестный период, который уже рассчитан
                    throw new \LogicException(sprintf(PHP_EOL . 'There are unknown calculated accountLogResource for accountTariffId = %d, date = %s, resource = %d' . PHP_EOL, $this->id, $dateYmd, $resourceId));
                }
            }
        }

        return $untarificatedPeriodss;
    }

    /**
     * Вернуть периоды, по которым не произведен расчет по ресурсам-опциям
     *
     * @return AccountLogFromToResource[][]
     * @throws \app\exceptions\ModelValidationException
     * @throws \yii\db\StaleObjectException
     * @throws \Exception
     * @throws \LogicException
     */
    public function getUntarificatedResourceOptionPeriods()
    {
        $minLogDatetime = self::getMinLogDatetime();
        $accountLogFromToResourcess = [];

        // по которым произведен расчет
        $accountLogsQuery = AccountLogResource::find()
            ->joinWith('tariffResource')
            ->where([
                'AND',
                [AccountLogResource::tableName() . '.account_tariff_id' => $this->id],
                ['NOT', [TariffResource::tableName() . '.resource_id' => array_keys(Resource::getReaderNames())]], // только опции
            ]);
        /** @var AccountLogResource $accountLog */
        $accountLogss = [];
        foreach ($accountLogsQuery->each() as $accountLog) {
            $accountLogss[$accountLog->tariffResource->resource_id][$accountLog->getUniqueId()] = $accountLog;
        }


        // по всем ресурсам
        $resources = Resource::findAll(['service_type_id' => $this->service_type_id]);
        foreach ($resources as $resource) {

            if (!$resource->isOption()) {
                // этот ресурс - не опция. Он считается в соседнем методе
                continue;
            }

            // Вернуть периоды не более месяца, по которым надо расчитывать списания за смену ресурсов
            $accountLogFromToResources = $this->getAccountLogFromToResources($resource->id);
            /** @var AccountLogFromToResource $accountLogFromToResource */
            foreach ($accountLogFromToResources as $accountLogFromToResource) {

                $dateFrom = $accountLogFromToResource->dateFrom;
                if ($dateFrom && $dateFrom < $minLogDatetime) {
                    // слишком старый. Для оптимизации считать не будем
                    continue;
                }

                $uniqueId = $accountLogFromToResource->getUniqueId();
                if (isset($accountLogss[$resource->id][$uniqueId])) {
                    // уже посчитан
                    unset($accountLogss[$resource->id][$uniqueId]);
                } else {
                    // не посчитан
                    $accountLogFromToResourcess[$resource->id][$uniqueId] = $accountLogFromToResource;
                }
            }
        }

        if (count($accountLogss)) {
            foreach ($accountLogss as $resourceId => $accountLogs) {
                foreach ($accountLogs as $dateYmd => $accountLog) {
                    if (!$accountLog) {
                        continue;
                    }

                    // остался неизвестный период, который уже рассчитан
                    throw new \LogicException(sprintf(PHP_EOL . 'There are unknown calculated accountLogResource for accountTariffId = %d, date = %s, resource = %d' . PHP_EOL, $this->id, $dateYmd, $resourceId));
                }
            }
        }

        return $accountLogFromToResourcess;
    }

    /**
     * Вернуть периоды не более месяца, по которым надо расчитывать списания за смену ресурсов
     *
     * @param int $resourceId
     * @return AccountLogFromToResource[]
     * @throws \LogicException
     */
    public function getAccountLogFromToResources($resourceId)
    {
        $accountLogFromToResources = [];

        // взять большие периоды и разбить помесячно
        $hugeAccountLogFromToResources = $this->getHugeAccountLogFromToResources($resourceId);
        foreach ($hugeAccountLogFromToResources as $hugeAccountLogFromToResource) {

            $dateFrom = $hugeAccountLogFromToResource->dateFrom;
            $dateTo = $hugeAccountLogFromToResource->dateFrom;

            do {

                // сделать дату "до" в том же месяце, но не больше конца периода
                $dateTo = $dateTo->modify('last day of this month');
                if (
                    $dateTo->format(DateTimeZoneHelper::DATE_FORMAT) >
                    $hugeAccountLogFromToResource->dateTo->format(DateTimeZoneHelper::DATE_FORMAT)
                ) {
                    $dateTo = $hugeAccountLogFromToResource->dateTo;
                }

                $accountLogFromToResource = new AccountLogFromToResource;
                $accountLogFromToResource->dateFrom = $dateFrom;
                $accountLogFromToResource->dateTo = $dateTo;
                $accountLogFromToResource->amountOverhead = $hugeAccountLogFromToResource->amountOverhead;
                $accountLogFromToResource->tariffPeriod = $hugeAccountLogFromToResource->tariffPeriod;
                $accountLogFromToResource->account_tariff_resource_log_id = $hugeAccountLogFromToResource->account_tariff_resource_log_id;

                $accountLogFromToResources[] = $accountLogFromToResource;

            } while (
                (
                    // еще не достигли конца большого периода
                    $dateTo->format(DateTimeZoneHelper::DATE_FORMAT) <
                    $hugeAccountLogFromToResource->dateTo->format(DateTimeZoneHelper::DATE_FORMAT)
                )
                && ($dateFrom = $dateTo = $dateTo->modify('+1 day')) // это не проверка, а просто переход на следующий месяц
            );
        }

        return $accountLogFromToResources;
    }

    /**
     * Вернуть большие периоды, по которым надо расчитывать списания за смену ресурсов
     *
     * Здесь сложная логика: списывается заранее до конца периода.
     * Если ресурс увеличивается - списание тоже увеличивается.
     * Если ресурс уменьшается - то до конца периода ничего не меняется, а списание уменьшается только со следующего периода.
     *
     * @param int $resourceId
     * @return AccountLogFromToResource[]
     * @throws \LogicException
     */
    public function getHugeAccountLogFromToResources($resourceId)
    {
        $hugeAccountLogFromToResources = [];

        // лог смены ресурсов
        /** @var ActiveQuery $accountTariffResourceLogsQuery */
        $accountTariffResourceLogsQuery = $this->getAccountTariffResourceLogs($resourceId);
        /** @var AccountTariffResourceLog[] $accountTariffResourceLogs */
        $accountTariffResourceLogs = $accountTariffResourceLogsQuery
            ->orderBy(
                [
                    'actual_from_utc' => SORT_ASC, // обязательно по порядку!
                    'id' => SORT_ASC,
                ])
            ->all();

        /** @var ClientAccount $clientAccount */
        $clientAccount = $this->clientAccount;
        $dateCurrentYmd = $clientAccount->getDatetimeWithTimezone()->format(DateTimeZoneHelper::DATE_FORMAT); // по таймзоне клиента

        // смена тарифов по периодам оплаты (не всегда по месяцам!)
        if (is_null($this->_accountLogFromToTariffsOption)) {
            // если ресурсов несколько, то выгоднее закэшировать, чем по каждому спрашивать одно и то же
            $this->_accountLogFromToTariffsOption = $this->getAccountLogFromToTariffs($chargePeriodMain = null, $isWithCurrent = true, $isSplitByMonth = false);
        }


        // берем все периоды оплаты. Внутри них находим смены ресурсов и считаем по наибольшему
        foreach ($this->_accountLogFromToTariffsOption as $accountLogFromToTariff) {

            // нужно знать, сколько ресурса включено в тариф. И билинговать только превышение
            $tariffResources = $accountLogFromToTariff->tariffPeriod->tariff->tariffResourcesIndexedByResourceId;
            $amountPaid = $tariffResources[$resourceId]->amount;

            $dateFromYmd = $accountLogFromToTariff->dateFrom->format(DateTimeZoneHelper::DATE_FORMAT);
            $dateToYmd = $accountLogFromToTariff->dateTo->format(DateTimeZoneHelper::DATE_FORMAT);

            $prevDateYmd = $dateFromYmd;
            $prevAmount = null;

            // просмотреть предыдущие периоды оплаты ресурсов и исключить из них те, которые произошли уже при этом тарифе
            // они должны быть пробилингованы по этому тарифу
            $hugeAccountLogFromToResources = array_filter(
                $hugeAccountLogFromToResources,
                function (AccountLogFromToResource $accountLogFromToResource) use ($accountLogFromToTariff) {
                    return $accountLogFromToResource->dateFrom < $accountLogFromToTariff->dateFrom;
                }
            );

            // по всем сменам ресурсов
            $accountTariffResourceLogPrev = null;
            foreach ($accountTariffResourceLogs as $accountTariffResourceLog) {

                $actualFromYmd = $accountTariffResourceLog->actual_from;

                if (
                    (
                        // строго до начала периода
                        $actualFromYmd < $dateFromYmd
                    )
                    ||
                    (
                        // начинает действовать в начало периода...
                        $actualFromYmd == $dateFromYmd
                        &&
                        (
                            // ... смена произведена заранее
                            $accountTariffResourceLog->actual_from_utc > $accountTariffResourceLog->insert_time
                            // или это первоначальное включение ресурса
                            || null === $prevAmount
                        )
                    )
                ) {

                    $prevAmount = $accountTariffResourceLog->amount;
                    $accountTariffResourceLogPrev = $accountTariffResourceLog;
                    continue;
                }

                if ($actualFromYmd > $dateToYmd) {
                    // после
                    break;
                }

                if ($actualFromYmd > $dateCurrentYmd) {
                    // в будущем
                    break;
                }

                // внутри периода
                //
                if ($accountTariffResourceLog->amount <= $prevAmount) {
                    // уменьшили ресурс - в этом периоде не учитываем
                    continue;
                }

                // значение ресурса увеличили
                //
                if (null === $prevAmount) {
                    throw new \LogicException('Начальное значение ресурса ' . $resourceId . ' не инициализировано. AccountTariffId = ' . $this->id);
                }

                // от предыдущего увеличения (или начала периода) до конца периода
                if ($prevAmount > $amountPaid) {
                    $hugeAccountLogFromToResource = new AccountLogFromToResource;
                    $hugeAccountLogFromToResource->dateFrom = new DateTimeImmutable($prevDateYmd);
                    $hugeAccountLogFromToResource->dateTo = $accountLogFromToTariff->dateTo;
                    $hugeAccountLogFromToResource->amountOverhead = $prevAmount - $amountPaid;
                    $hugeAccountLogFromToResource->tariffPeriod = $accountLogFromToTariff->tariffPeriod;
                    $hugeAccountLogFromToResource->account_tariff_resource_log_id = $accountTariffResourceLogPrev->id;
                    $hugeAccountLogFromToResources[] = $hugeAccountLogFromToResource;

                    $amountPaid = $prevAmount;
                }

                // следущее списание будет с этой даты
                $prevDateYmd = $actualFromYmd;
                $prevAmount = $accountTariffResourceLog->amount;
                $accountTariffResourceLogPrev = $accountTariffResourceLog;
            }

            if (null === $prevAmount) {
                throw new \LogicException('Начальное значение ресурса ' . $resourceId . ' не инициализировано. AccountTariffId = ' . $this->id);
            }

            // от предыдущего увеличения (или начала периода) до конца периода
            if ($prevAmount > $amountPaid) {
                $hugeAccountLogFromToResource = new AccountLogFromToResource;
                $hugeAccountLogFromToResource->dateFrom = new DateTimeImmutable($prevDateYmd);
                $hugeAccountLogFromToResource->dateTo = $accountLogFromToTariff->dateTo;
                $hugeAccountLogFromToResource->amountOverhead = $prevAmount - $amountPaid;
                $hugeAccountLogFromToResource->tariffPeriod = $accountLogFromToTariff->tariffPeriod;
                $hugeAccountLogFromToResource->account_tariff_resource_log_id = $accountTariffResourceLogPrev->id;
                $hugeAccountLogFromToResources[] = $hugeAccountLogFromToResource;
            }
        }

        return $hugeAccountLogFromToResources;
    }
}