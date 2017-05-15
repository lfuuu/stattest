<?php

namespace app\modules\uu\models\traits;

use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
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
                    printf(PHP_EOL . 'Error. There are unknown calculated accountLogResource for accountTariffId = %d, date = %s, resource = %d' . PHP_EOL, $this->id, $dateYmd, $resourceId);
                }
            }
        }

        return $untarificatedPeriodss;
    }

    /**
     * Вернуть периоды, по которым не произведен расчет по ресурсам-опциям
     *
     * @return AccountLogFromToResource[][]
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
            $accountLogss[$accountLog->tariffResource->resource_id][$accountLog->date_from . '_' . $accountLog->date_to] = $accountLog;
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

                    // Остался неизвестный период, который уже рассчитан
                    // Это не ошибка. Такое может быть, когда списали ресурс до конца периода, а в середине кол-во увеличилось. Приходится перерасчитывать
                    // printf(PHP_EOL . 'Error. There are unknown calculated accountLogResource for accountTariffId = %d, date = %s, resource = %d' . PHP_EOL, $this->id, $dateYmd, $resourceId);
                    if (!$accountLog->delete()) {
                        throw new ModelValidationException($accountLog);
                    }
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
                $accountLogFromToResource->amount = $hugeAccountLogFromToResource->amount;
                $accountLogFromToResource->tariffPeriod = $hugeAccountLogFromToResource->tariffPeriod;

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
        /** @var AccountTariffResourceLog[] $accountTariffResourceLogs */
        $accountTariffResourceLogsQuery = $this->getAccountTariffResourceLogs($resourceId);
        $accountTariffResourceLogs = $accountTariffResourceLogsQuery
            ->orderBy(
                [
                    'actual_from_utc' => SORT_ASC, // обязательно по порядку!
                    'id' => SORT_ASC,
                ])
            ->all();


        // смена тарифов по периодам оплаты (не всегда по месяцам!), чтобы правильно рассчитать срок оплаты ресурса
        if (is_null($this->_accountLogFromToTariffsOption)) {
            // если ресурсов несколько, то выгоднее закэшировать, чем по каждому спрашивать одно и то же
            $this->_accountLogFromToTariffsOption = $this->getAccountLogFromToTariffs($chargePeriodMain = null, $isWithCurrent = true, $isSplitByMonth = false);
            // если тариф менялся во время действия предыдущего, то диапазоны будут пересекаться
            // надо сделать непересекающиеся (предыдущий закончить до начала действия последующего)
            foreach ($this->_accountLogFromToTariffsOption as $i => $accountLogFromToTariff) {
                if (!$i) {
                    // перед первым диапазоном нет ничего
                    continue;
                }

                if ($this->_accountLogFromToTariffsOption[$i - 1]->dateTo >= $accountLogFromToTariff->dateFrom) {
                    // тут может получиться from больше to. Это мы проигнорируем позже
                    $this->_accountLogFromToTariffsOption[$i - 1]->dateTo = $accountLogFromToTariff->dateFrom->modify('-1 day');
                }
            }
        }


        // берем все периоды оплаты. Внутри них находим смены ресурсов и считаем по наибольшему
        foreach ($this->_accountLogFromToTariffsOption as $accountLogFromToTariff) {
            $dateFromYmd = $accountLogFromToTariff->dateFrom->format(DateTimeZoneHelper::DATE_FORMAT);
            $dateToYmd = $accountLogFromToTariff->dateTo->format(DateTimeZoneHelper::DATE_FORMAT);

            if ($dateFromYmd > $dateToYmd) {
                // в течение дня несколько раз меняли. В результате получилась фигня, которую надо проигнонрировать в ресурсах (но в абонентке это надо учитывать)
                continue;
            }

            $prevDateYmd = $dateFromYmd;
            $prevAmount = null;

            // по всем сменам ресурсов
            foreach ($accountTariffResourceLogs as $accountTariffResourceLog) {

                $actualFromYmd = $accountTariffResourceLog->actual_from;

                if ($actualFromYmd <= $dateFromYmd) {
                    // до
                    $prevAmount = $accountTariffResourceLog->amount;
                    continue;
                }

                if ($actualFromYmd > $dateToYmd) {
                    // после
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

                // от предыдущего увеличения (или начала периода) до текущего увеличения
                $hugeAccountLogFromToResource = new AccountLogFromToResource;
                $hugeAccountLogFromToResource->dateFrom = new DateTimeImmutable($prevDateYmd);
                $hugeAccountLogFromToResource->dateTo = (new DateTimeImmutable($actualFromYmd))->modify('-1 day');
                $hugeAccountLogFromToResource->amount = $prevAmount;
                $hugeAccountLogFromToResource->tariffPeriod = $accountLogFromToTariff->tariffPeriod;

                if ($hugeAccountLogFromToResource->dateFrom <= $hugeAccountLogFromToResource->dateTo) {
                    // если в течение дня меняли несколько раз, то учитывать только максимальное
                    $hugeAccountLogFromToResources[] = $hugeAccountLogFromToResource;
                }

                // следущее списание будет с этой даты
                $prevDateYmd = $actualFromYmd;
                $prevAmount = $accountTariffResourceLog->amount;
            }

            if (null === $prevAmount) {
                throw new \LogicException('Начальное значение ресурса ' . $resourceId . ' не инициализировано. AccountTariffId = ' . $this->id);
            }

            // от предыдущего увеличения (или начала периода) до конца периода
            $hugeAccountLogFromToResource = new AccountLogFromToResource;
            $hugeAccountLogFromToResource->dateFrom = new DateTimeImmutable($prevDateYmd);
            $hugeAccountLogFromToResource->dateTo = (new DateTimeImmutable($dateToYmd));
            $hugeAccountLogFromToResource->amount = $prevAmount;
            $hugeAccountLogFromToResource->tariffPeriod = $accountLogFromToTariff->tariffPeriod;

            $hugeAccountLogFromToResources[] = $hugeAccountLogFromToResource;
        }

        return $hugeAccountLogFromToResources;
    }
}