<?php

namespace app\modules\uu\models\traits;

use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\modules\uu\models\AccountTariffLog;
use app\modules\uu\models\AccountTariffResourceLog;
use app\modules\uu\models\Resource;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\TariffPeriod;
use DateTimeZone;
use yii\db\ActiveQuery;

trait AccountTariffBoolTrait
{
    /**
     * Можно ли сменить редактировать
     *
     * @return bool
     */
    public function isEditable()
    {
        if (!$this->isActive()) {
            // уже закрытый
            return false;
        }

        if (in_array($this->service_type_id, ServiceType::$packages) && $this->tariffPeriod->tariff->is_default) {
            // дефолтный пакет нельзя редактировать. Он должен закрыться автоматически при закрытии базового тарифа
            return false;
        }

        if (!$this->client_account_id) {
            // не указан ЛС
            return false;
        }

        // таки можно
        return true;
    }

    /**
     * Можно ли отменить последнюю смену тарифа
     *
     * @return bool
     */
    public function isLogCancelable()
    {
        if (!$this->isEditable()) {
            // нередактируемый в принципе
            return false;
        }

        /** @var AccountTariffLog[] $accountTariffLogs */
        $accountTariffLogs = $this->accountTariffLogs;
        $accountTariffLog = reset($accountTariffLogs);
        if (!$accountTariffLog) {
            return false;
        }

        /** @var ClientAccount $clientAccount */
        $clientAccount = $this->clientAccount;
        $dateTimeNow = $clientAccount->getDatetimeWithTimezone(); // по таймзоне клиента
        return $accountTariffLog->actual_from > $dateTimeNow->format(DateTimeZoneHelper::DATE_FORMAT);
    }

    /**
     * Можно ли сменить тариф или закрыть услугу
     *
     * @return bool
     */
    public function isLogEditable()
    {
        if (!$this->isEditable()) {
            // нередактируемый в принципе
            return false;
        }

        /** @var ClientAccount $clientAccount */
        $clientAccount = $this->clientAccount;
        $currentDateTimeUtc = $clientAccount
            ->getDatetimeWithTimezone()
            ->setTime(0, 0, 0)
            ->setTimezone(new DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC))
            ->format(DateTimeZoneHelper::DATETIME_FORMAT);

        if (AccountTariffLog::find()
            ->where(['account_tariff_id' => $this->id])
            ->andWhere(['>', 'actual_from_utc', $currentDateTimeUtc])
            ->count()
        ) {
            // Уже назначена смена тарифа в будущем. Если вы хотите установить новый тариф - сначала отмените эту смену.
            return false;
        }

        // таки можно
        return true;
    }

    /**
     * Можно ли подключить пакет
     *
     * @return bool
     */
    public function isPackageAddable()
    {
        if (!$this->isEditable()) {
            // нередактируемый в принципе
            return false;
        }

        /** @var TariffPeriod $tariffPeriod */
        $tariffPeriod = $this->tariffPeriod;
        if ($tariffPeriod->tariff->service_type_id != ServiceType::ID_VOIP) {
            // не телефония
            return false;
        }

        // таки можно
        return true;
    }

    /**
     * Действует ли
     *
     * @return bool
     */
    public function isActive()
    {
        return (bool)$this->tariff_period_id;
    }

    /**
     * Можно ли отменить последнюю смену количества ресурса
     *
     * @param \app\modules\uu\models\Resource $resource
     * @return bool
     */
    public function isResourceCancelable(\app\modules\uu\models\Resource $resource)
    {
        if (!$this->isEditable()) {
            // услуга нередактируемая
            return false;
        }

        if (!$resource->isEditable()) {
            // ресурс нередактируемый
            return false;
        }

        /** @var ActiveQuery $accountTariffResourceLogsQuery */
        $accountTariffResourceLogsQuery = $this->getAccountTariffResourceLogs($resource->id);
        $accountTariffResourceLogs = $accountTariffResourceLogsQuery->all();
        $accountTariffResourceLog = reset($accountTariffResourceLogs);
        if (!$accountTariffResourceLog) {
            return false;
        }

        /** @var ClientAccount $clientAccount */
        $clientAccount = $this->clientAccount;
        $dateTimeNow = $clientAccount->getDatetimeWithTimezone(); // по таймзоне клиента
        return $accountTariffResourceLog->actual_from > $dateTimeNow->format(DateTimeZoneHelper::DATE_FORMAT);
    }

    /**
     * Можно ли поменять количество ресурса
     *
     * @param \app\modules\uu\models\Resource $resource
     * @return bool
     */
    public function isResourceEditable(\app\modules\uu\models\Resource $resource)
    {
        if (!$this->isEditable()) {
            // услуга нередактируемая
            return false;
        }

        if (!$resource->isEditable()) {
            // ресурс нередактируемый
            return false;
        }

        /** @var \app\models\Number $number */
        if ($resource->id == Resource::ID_VOIP_FMC && ($number = $this->number) && !$number->isFmcEditable()) {
            // Костыль для FMC. Редактируемость этого ресурса зависит от типа телефонного номера
            return false;
        }

        return !$this->isResourceCancelable($resource);
    }

    /**
     * Вернуть текущее количество ресурса
     *
     * @param int $resourceId
     * @return float|null
     */
    public function getResourceValue($resourceId)
    {
        /** @var ClientAccount $clientAccount */
        $clientAccount = $this->clientAccount;
        $dateTimeNow = $clientAccount->getDatetimeWithTimezone(); // по таймзоне клиента

        /** @var ActiveQuery $accountTariffResourceLogsQuery */
        $accountTariffResourceLogsQuery = $this->getAccountTariffResourceLogs($resourceId);

        /** @var AccountTariffResourceLog $accountTariffResourceLog */
        foreach ($accountTariffResourceLogsQuery->each() as $accountTariffResourceLog) {
            if ($accountTariffResourceLog->actual_from > $dateTimeNow->format(DateTimeZoneHelper::DATE_FORMAT)) {
                // еще не действует
                continue;
            }

            return $accountTariffResourceLog->amount;
        }

        return null;
    }

    /**
     * Всем примененным ресурсам установить текущую дату синхронизации
     *
     * @throws \app\exceptions\ModelValidationException
     */
    public function setResourceSyncTime()
    {
        /** @var ClientAccount $clientAccount */
        $clientAccount = $this->clientAccount;
        $dateTimeNow = $clientAccount->getDatetimeWithTimezone(); // по таймзоне клиента

        /** @var ActiveQuery $accountTariffResourceLogsQuery */
        $accountTariffResourceLogsQuery = $this->getAccountTariffResourceLogs();

        /** @var AccountTariffResourceLog $accountTariffResourceLog */
        foreach ($accountTariffResourceLogsQuery->each() as $accountTariffResourceLog) {
            if ($accountTariffResourceLog->actual_from > $dateTimeNow->format(DateTimeZoneHelper::DATE_FORMAT)) {
                // еще не действует
                continue;
            }

            if ($accountTariffResourceLog->sync_time) {
                // дата синхронизации уже установлена
                continue;
            }

            $accountTariffResourceLog->sync_time = date(DateTimeZoneHelper::DATETIME_FORMAT);
            if (!$accountTariffResourceLog->save()) {
                throw new ModelValidationException($accountTariffResourceLog);
            }
        }
    }
}