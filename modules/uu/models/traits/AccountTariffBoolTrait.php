<?php

namespace app\modules\uu\models\traits;

use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffLog;
use app\modules\uu\models\AccountTariffResourceLog;
use app\modules\uu\models\ResourceModel;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\Tariff;
use app\modules\uu\tarificator\SetCurrentTariffTarificator;
use DateTimeZone;
use Yii;
use yii\db\ActiveQuery;

trait AccountTariffBoolTrait
{
    /**
     * Можно ли редактировать
     *
     * @return bool
     */
    public function isEditable()
    {
        if (!$this->isActive()) {
            // уже закрытый
            return false;
        }

        if (
            array_key_exists($this->service_type_id, ServiceType::$packages)
            &&
            (
                (
                    $this->getNotNullTariffPeriod()->tariff->is_default
                    && !Yii::$app->user->can('services_voip.package') // если нельзя, но очень надо, то можно
                )
                || (
                    $this->prev_account_tariff_id
                    && $this->getNotNullTariffPeriod()->tariff->is_bundle
                ))
        ) {
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
        return $accountTariffLog->actual_from > $dateTimeNow->format(DateTimeZoneHelper::DATE_FORMAT)
            || ($accountTariffLog->tariff_period_id && $accountTariffLog->tariffPeriod->tariff->is_bundle && $this->prev_account_tariff_id);
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

        if (count($this->accountTariffLogs) > 1) {
            // исключили проблему при смене таймзоны клиента на тестовом тарифе
            // (менеджеры говорят, что это нормально)
            // @ Korobkov

            /** @var AccountTariffLog $accountTariffLog */
            foreach ($this->accountTariffLogs as $accountTariffLog) {
                if ($accountTariffLog->actual_from_utc > $currentDateTimeUtc) {
                    // Уже назначена смена тарифа в будущем.
                    // Если вы хотите установить новый тариф - сначала отмените эту смену.
                    return false;
                }
            }
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
            return false;
        }

        /** @var Tariff $tariff */
        $tariff = $this->tariffPeriod->tariff;

        if ($tariff->service_type_id != ServiceType::ID_VOIP) {
            return false;
        }

        // Если задано "Кол-во продлений" и "Кол-во продлений при переносе ресурса"
        if ($tariff->count_of_validity_period && $tariff->count_of_carry_period) {
            return false;
        }

        return true;
    }

    /**
     * Действует ли
     *
     * @return bool
     */
    public function isActive()
    {
        if ($this->tariff_period_id) {
            // действует
            return true;
        }

        if (count($this->accountTariffLogs) == 1) {
            // еще не включился
            return true;
        }

        // уже выключился
        return false;
    }

    /**
     * Услуга включена сейчас или уже отключена
     *
     * @return bool
     */
    public function isStarted()
    {
        return $this->tariff_period_id || count($this->accountTariffLogs) > 1;
    }


    /**
     * Можно ли отменить последнюю смену количества ресурса
     *
     * @param ResourceModel $resource
     * @return bool
     */
    public function isResourceCancelable(ResourceModel $resource)
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
        if (count($accountTariffResourceLogs) <= 1) {
            // нет смен ресурса или это инициализация, которую тоже нельзя отменить
            return false;
        }

        $accountTariffResourceLog = reset($accountTariffResourceLogs);

        /** @var ClientAccount $clientAccount */
        $clientAccount = $this->clientAccount;
        $dateTimeNow = $clientAccount->getDatetimeWithTimezone(); // по таймзоне клиента
        return $accountTariffResourceLog->actual_from > $dateTimeNow->format(DateTimeZoneHelper::DATE_FORMAT);
    }

    /**
     * Можно ли поменять количество ресурса
     *
     * @param ResourceModel $resource
     * @return bool
     */
    public function isResourceEditable(ResourceModel $resource)
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
        if ($resource->id == ResourceModel::ID_VOIP_FMC && ($number = $this->number) && !$number->isFmcEditable()) {
            // Костыль для FMC. Редактируемость этого ресурса зависит от типа телефонного номера
            return false;
        }

        if ($resource->id == ResourceModel::ID_VOIP_MOBILE_OUTBOUND && ($number = $this->number) && !$number->isMobileOutboundEditable()) {
            // Костыль для Исх.Моб.Связь. Редактируемость этого ресурса зависит от типа телефонного номера
            return false;
        }

        return !$this->isResourceCancelable($resource);
    }

    /**
     * Вернуть количество ресурса
     *
     * @param int $resourceId
     * @param bool $isCurrentOnly true - текущее, false - последнее (в том числе будущее)
     * @return float|null
     */
    public function getResourceValue($resourceId, $isCurrentOnly = true)
    {
        /** @var ClientAccount $clientAccount */
        $clientAccount = $this->clientAccount;
        $dateTimeNow = $clientAccount->getDatetimeWithTimezone(); // по таймзоне клиента

        /** @var AccountTariffResourceLog $accountTariffResourceLog */
        foreach ($this->getAccountTariffResourceLogsByResourceId($resourceId) as $accountTariffResourceLog) {
            if ($isCurrentOnly && $accountTariffResourceLog->actual_from > $dateTimeNow->format(DateTimeZoneHelper::DATE_FORMAT)) {
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
     * @return AccountTariffResourceLog[]
     * @throws \app\exceptions\ModelValidationException
     */
    public function setResourceSyncTime()
    {
        $accountTariffResourceLogs = [];

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

            $accountTariffResourceLogs[$accountTariffResourceLog->id] = $accountTariffResourceLog;
        }

        return $accountTariffResourceLogs;
    }

    /**
     * Услуга уже закрыта
     *
     * @return bool
     */
    public function isClosed()
    {
        return !$this->isActive()
            && ($accountTariffLogs = $this->accountTariffLogs)
            && ($lastTariffLog = reset($accountTariffLogs))
            && !$lastTariffLog->tariff_period_id;
    }

    /**
     * Переоткрытие услуги с сегодня
     *
     * @return AccountTariff
     * @throws \Exception
     */
    public function reopen()
    {
        if (!$this->isClosed()) {
            throw new \LogicException('Услуга не закрыта');
        }

        $transaction = \Yii::$app->db->beginTransaction();

        try {

            $newAccountTariff = new AccountTariff();
            $newAccountTariff->setAttributes($this->getAttributes(null, ['id']));
            if (!$newAccountTariff->save()) {
                throw new ModelValidationException($newAccountTariff);
            }

            $newAccountTariff->refresh();

            $logs = $this->accountTariffLogs;
            reset($logs);
            /** @var AccountTariffLog $lastActiveTariffLog */
            $lastActiveTariffLog = next($logs);

            if (!$lastActiveTariffLog || !$lastActiveTariffLog->tariff_period_id) {
                throw new \LogicException('Не найден тариф');
            }

            $newAccountTariffLog = new AccountTariffLog();
            $newAccountTariffLog->setAttributes($lastActiveTariffLog->getAttributes(null, ['id', 'account_tariff_id', 'actual_from_utc']));
            $newAccountTariffLog->account_tariff_id = $newAccountTariff->id;
            $newAccountTariffLog->actual_from_utc = (new \DateTime('00:00:00', $this->clientAccount->getTimezone()))
                ->setTimezone(new DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC))
                ->format(DateTimeZoneHelper::DATETIME_FORMAT);

            if (!$newAccountTariffLog->save()) {
                throw new ModelValidationException($newAccountTariffLog);
            }

            (new SetCurrentTariffTarificator())->tarificate($newAccountTariff->id);

            $newAccountTariff->refresh();

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return $newAccountTariff;
    }

    /**
     * Можно ли разархивировать ВАТС этой услуги
     *
     * @return bool
     */
    public function isVpbxUnzippable()
    {
        return $this->service_type_id === ServiceType::ID_VPBX && $this->_isUnzippable();
    }

    /**
     * Можно ли разархивировать ВАТС этой услуги
     *
     * @return bool
     */
    public function isVmUnzippable()
    {
        return $this->service_type_id === ServiceType::ID_VPS && $this->_isUnzippable();
    }

    /**
     * Можно ли разархивировать
     *
     * @return bool
     */
    private function _isUnzippable()
    {
        return !$this->is_unzipped && $this->isClosed();
    }

    /**
     * Есть ли включенные услуги на данном УЛС определенного типа
     *
     * @param integer $clientAccountId
     * @param integer $serviceTypeId
     * @return bool
     */
    public static function isServiceExists($clientAccountId, $serviceTypeId)
    {
        return AccountTariff::find()
            ->where([
                'client_account_id' => $clientAccountId,
                'service_type_id' => $serviceTypeId,
            ])
            ->andWhere($serviceTypeId == ServiceType::ID_TRUNK ? ['NOT', ['trunk_type_id' => AccountTariff::TRUNK_TYPE_MULTITRUNK]] : [])
            ->andWhere(['IS NOT', 'tariff_period_id', null])
            ->exists();
    }

    /**
     * Мегатранк?
     * Предполагается, что есть действующая услуга телефонии, а здесь проверяется только наличие услуги транка
     *
     * @param int $clientAccountId
     * @return bool
     */
    public static function hasTrunk($clientAccountId)
    {
        return AccountTariff::find()
            ->where([
                'client_account_id' => $clientAccountId,
                'service_type_id' => ServiceType::ID_TRUNK,
            ])
            ->andWhere(['IS NOT', 'tariff_period_id', null])
            ->exists();
    }

    public function getResourceMap()
    {
        $mapAccountTariffResources = [];
        foreach ($this->accountTariffResourceLogsAll as $accountTariffResourceLog) {
            // массив инвертирован. последние значение - первые
            if (!isset($mapAccountTariffResources[$accountTariffResourceLog->resource_id])) {
                $mapAccountTariffResources[$accountTariffResourceLog->resource_id] = $accountTariffResourceLog->amount;
            }
        }

        return $mapAccountTariffResources;
    }
}