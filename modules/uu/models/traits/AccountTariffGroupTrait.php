<?php

namespace app\modules\uu\models\traits;

use app\helpers\DateTimeZoneHelper;
use app\modules\uu\models\AccountLogPeriod;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffLog;
use app\modules\uu\models\AccountTariffResourceLog;
use app\modules\uu\models\ResourceModel;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffResource;
use DateTime;
use yii\db\ActiveQuery;

trait AccountTariffGroupTrait
{
    /**
     * Сгруппировать одинаковые город-тариф-пакеты по строчкам
     *
     * @param ActiveQuery $query
     * @return AccountTariff[][]
     */
    public static function getGroupedObjects(ActiveQuery $query)
    {
        $rows = [];

        /** @var AccountTariff $accountTariff */
        foreach ($query->each() as $accountTariff) {

            $hash = $accountTariff->getHash();
            !isset($rows[$hash]) && $rows[$hash] = [];
            $rows[$hash][$accountTariff->id] = $accountTariff;
        }

        return $rows;
    }

    /**
     * Вернуть хеш услуги. Нужно для группировки похожих услуг телефонии по разным городам-тарифам-пакетам.
     *
     * @return string
     * @throws \yii\db\Exception
     */
    public function getHash()
    {
        // не так красиво, но быстро
        $sql = <<<SQL
SELECT MD5(CONCAT(
               GROUP_CONCAT(DISTINCT a.client_account_id ORDER BY a.id), '-',
               GROUP_CONCAT(DISTINCT service_type_id ORDER BY a.id), '-',
               GROUP_CONCAT(DISTINCT COALESCE(a.city_id, 'c') ORDER BY a.id),
               GROUP_CONCAT(COALESCE(a1, 'm') ORDER BY a.id),
               GROUP_CONCAT(COALESCE(a2, 'b') ORDER BY a.id),
               GROUP_CONCAT(COALESCE(a3, 'v') ORDER BY a.id)
           )) AS hash
FROM (
       SELECT
         COALESCE((SELECT GROUP_CONCAT(CONCAT(COALESCE(tariff_period_id, ''), '-', actual_from_utc) ORDER BY id DESC)
                   FROM uu_account_tariff_log
                   WHERE account_tariff_id = a.id
                  ), 'n')                                                                          a1,
         (SELECT GROUP_CONCAT(CONCAT(resource_id, '-', amount, '-', actual_from_utc) ORDER BY l.id)
          FROM uu_account_tariff_resource_log l
          WHERE l.account_tariff_id = a.id AND resource_id NOT IN (1, 14, 18, 41, 40, 42, 44, 45)) a2,
         IF(a.tariff_period_id IS NULL, -1000, a.tariff_period_id) AS                              a3,
         IF(prev_account_tariff_id IS NULL, id,
            prev_account_tariff_id)                                                                account_tariff_group_id,
         if (a.service_type_id = 22, a.region_id, a.city_id) as city_id,
         a.client_account_id,
         service_type_id,
         a.id
       FROM `uu_account_tariff` a
       WHERE a.id = :account_tariff_id OR a.prev_account_tariff_id = :account_tariff_id
       ORDER BY a.id
     ) a
GROUP BY account_tariff_group_id

SQL;


        return self::getDb()->createCommand($sql, [':account_tariff_id' => $this->id])->queryScalar();

        $dateTimeUtc = DateTimeZoneHelper::getUtcDateTime()
            ->format(DateTimeZoneHelper::DATETIME_FORMAT);
        $hashes = [];

        // город
        $hashes[] = $this->city_id;

        // лог тарифа и даты
        /** @var AccountTariffLog[] $accountTariffLogs */
        $accountTariffLogs = $this->accountTariffLogs;
        foreach ($accountTariffLogs as $accountTariffLog) {
            $hashes[] = $accountTariffLog->tariff_period_id ?: '';
            $hashes[] = $accountTariffLog->actual_from;

            if ($accountTariffLog->actual_from_utc < $dateTimeUtc) {
                // показываем только текущий. Старье не нужно
                break;
            }
        }

        unset($accountTariffLogs, $accountTariffLog);


        // Пакет. Лог тарифа  и даты
        /** @var AccountTariff[] $nextAccountTariffs */
        $nextAccountTariffs = $this->nextAccountTariffs;
        foreach ($nextAccountTariffs as $accountTariffPackage) {
            foreach ($accountTariffPackage->accountTariffLogs as $accountTariffPackageLog) {
                // лог тарифа
                $hashes[] = $accountTariffPackageLog->tariff_period_id ?: '';
                $hashes[] = $accountTariffPackageLog->actual_from;

                if ($accountTariffPackageLog->actual_from_utc < $dateTimeUtc) {
                    // показываем только текущий. Старье не нужно
                    break;
                }
            }
        }

        unset($nextAccountTariffs, $accountTariffPackage);

        // ресурсы
        /** @var ResourceModel[] $resources */
        $resources = $this->resources;
        foreach ($resources as $resource) {
            if (!$resource->isEditable()) {
                // динамический ресурс
                continue;
            }

            // лог ресурсов
            /** @var ActiveQuery $accountTariffResourceLogsQuery */
            $accountTariffResourceLogsQuery = $this->getAccountTariffResourceLogs($resource->id);

            /** @var AccountTariffResourceLog $accountTariffResourceLogTmp */
            foreach ($accountTariffResourceLogsQuery->each() as $accountTariffResourceLogTmp) {
                $hashes[] = $accountTariffResourceLogTmp->amount;
                $hashes[] = $accountTariffResourceLogTmp->actual_from;
            }
        }

        return md5(implode('_', $hashes));
    }

    /**
     * Сгруппировать одинаковые город-тариф по строчкам
     *
     * @param ActiveQuery $query
     * @return AccountTariff[][]
     */
    public static function getGroupedObjectsLight(ActiveQuery $query)
    {
        $rows = [];

        /** @var AccountTariff $accountTariff */
        foreach ($query->each() as $accountTariff) {

            $hash = $accountTariff->getHashLight();
            !isset($rows[$hash]) && $rows[$hash] = [];
            $rows[$hash][$accountTariff->id] = $accountTariff;
        }

        return $rows;
    }

    /**
     * Вернуть хеш услуги. Нужно для группировки похожих услуг телефонии по разным городам.
     *
     * @return string
     */
    public function getHashLight()
    {
        $hashes = [];
        $hashes[] = $this->service_type_id;
        $hashes[] = $this->service_type_id == ServiceType::ID_SIPTRUNK ? $this->region_id : $this->city_id;
        $hashes[] = $this->tariff_period_id;

        return md5(implode('_', $hashes));
    }

    /**
     * Даты последнего периода абонентской платы
     *
     * @return string[]
     */
    public function getLastLogPeriod()
    {
        $date = date(DateTimeZoneHelper::DATE_FORMAT);

        /** @var AccountLogPeriod[] $accountLogPeriods */
        $accountLogPeriods = $this->accountLogPeriods;
        if (!count($accountLogPeriods)) {
            return [$date, $date];
        }

        // после завершения оплаченного
        $accountLogPeriod = end($accountLogPeriods);

        $modify = $accountLogPeriod->tariffPeriod
            ->chargePeriod
            ->getModify($isPositive = false);

        $actualTo = $accountLogPeriod->date_to;
        $actualFrom = (new DateTime($actualTo))
            ->modify($modify)
            ->modify('+1 day')
            ->format(DateTimeZoneHelper::DATE_FORMAT);

        return [$actualFrom, $actualTo];
    }

    /**
     * Сколько ресурсов уже оплачено (или входит в тариф) до минимальной даты
     *
     * @param Tariff $tariff
     * @param int $resourceId
     * @return float|int
     */
    public function getMaxPaidAmount($tariff, $resourceId)
    {
        list($dateFrom,) = $this->getLastLogPeriod();
        $currentDate = date(DateTimeZoneHelper::DATE_FORMAT);

        // входит в тариф
        /** @var TariffResource $tariffResource */
        $tariffResource = $tariff->getTariffResource($resourceId)->one();
        $maxPaidAmount = $tariffResource ? $tariffResource->amount : 0;

        // оплачено
        /** @var AccountTariffResourceLog $accountTariffResourceLogTmp */
        $accountTariffResourceLogsQuery = $this->getAccountTariffResourceLogs($resourceId);
        foreach ($accountTariffResourceLogsQuery->each() as $accountTariffResourceLogTmp) {
            if ($accountTariffResourceLogTmp->actual_from > $currentDate) {
                // еще не действует
                continue;
            }

            $maxPaidAmount = max($maxPaidAmount, $accountTariffResourceLogTmp->amount);

            if ($accountTariffResourceLogTmp->actual_from < $dateFrom) {
                // все старые уже не действуют
                break;
            }
        }

        return $maxPaidAmount;
    }
}