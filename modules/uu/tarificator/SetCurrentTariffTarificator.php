<?php

namespace app\modules\uu\tarificator;

use app\classes\HandlerLogger;
use app\exceptions\FinanceException;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\EventQueue;
use app\models\important_events\ImportantEvents;
use app\models\important_events\ImportantEventsNames;
use app\models\important_events\ImportantEventsSources;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffChange as Log;
use app\modules\uu\models\AccountTariffLog;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\TariffPeriod;
use app\modules\uu\Module;
use app\widgets\ConsoleProgress;
use Yii;

/**
 * Обновить AccountTariff.TariffPeriod на основе AccountTariffLog
 */
class SetCurrentTariffTarificator extends Tarificator
{
    /**
     * @param int|null $accountTariffId Если указан, то только для этой услуги. Если не указан - для всех
     * @param bool $isWithTransaction
     * @throws \Exception
     * @throws \Throwable
     */
    public function tarificate($accountTariffId = null, $isWithTransaction = true)
    {
        $db = Yii::$app->db;
        $accountTariffTableName = AccountTariff::tableName();
        $accountTariffLogTableName = AccountTariffLog::tableName();

        // найти все услуги, у которых надо обновить тариф
        $sql = <<<SQL
            SELECT
                account_tariff.id,
                account_tariff.tariff_period_id,
                (
                    SELECT
                        account_tariff_log.tariff_period_id
                    FROM
                        {$accountTariffLogTableName} account_tariff_log use index (`fk-uu_account_tariff_log-account_tariff_id`)
                    WHERE
                        account_tariff.id = account_tariff_log.account_tariff_id
                        AND account_tariff_log.actual_from_utc <= :now
                    ORDER BY
                        account_tariff_log.id DESC
                    LIMIT 1
                ) AS new_tariff_period_id
            FROM
                {$accountTariffTableName} account_tariff
SQL;
        if ($accountTariffId) {
            // только конкретную услугу, даже если не надо менять тариф
            $sql .= " WHERE account_tariff.id = {$accountTariffId} ";
        } else {
            // все услуги, где надо менять тариф
            $sql .= ' HAVING IFNULL(account_tariff.tariff_period_id, 0) != IFNULL(new_tariff_period_id, 0)';
        }

        $sql .= " ORDER BY account_tariff.id ASC"; // Основная услуга всегда включается раньше своих пакетов

        $query = $db->createCommand(
            $sql,
            [
                ':now' => DateTimeZoneHelper::getUtcDateTime()
                    ->format(DateTimeZoneHelper::DATETIME_FORMAT)
            ]
        )
            ->query();

        $progress = new ConsoleProgress($query->count(), function ($string) {
            $this->out($string);
        });
        foreach ($query as $row) {
            $progress->nextStep();

            $accountTariff = AccountTariff::findOne(['id' => $row['id']]);
            $newTariffPeriodId = $row['new_tariff_period_id'];

            if (
                (!$row['tariff_period_id'] && $row['new_tariff_period_id']) && // только на включение
                $accountTariff->prev_account_tariff_id
                && ($mainAccountTariff = AccountTariff::findOne(['id' => $accountTariff->prev_account_tariff_id]))
                && !$mainAccountTariff->tariff_period_id
            ) {
                // Пакет не включается, т.к. не включена основная услуга. У пакетов уже перенесена дата включения.
                continue;
            }

            $isWithTransaction && $transaction = $db->beginTransaction();
            try {
                // тип события
                $eventType = $this->getEventType($accountTariff, $newTariffPeriodId);

                $oldTariffPeriodId = $accountTariff->tariff_period_id;

                $this->saveStateChange($accountTariff, $newTariffPeriodId);

                // сменить тариф
                $this->changeAccountTariff($accountTariff, $newTariffPeriodId, $eventType);

                $this->generateUuEvent($accountTariff, $eventType);

                // создать события
                $this->generateEvents($accountTariff, $eventType, $oldTariffPeriodId, $newTariffPeriodId);

                $isWithTransaction && $transaction->commit();

            } catch (FinanceException $e) {
                // баланс отрицательный
                $isWithTransaction && $transaction->rollBack();

                $errorMessage = $e->getMessage();
                $this->out(PHP_EOL . $errorMessage . PHP_EOL);
                Yii::error($errorMessage);
                HandlerLogger::me()->add($errorMessage);

                $isWithTransaction && $transaction = $db->beginTransaction();

                // смену тарифа отодвинуть в надежде, что за это время клиент пополнит баланс
                $this->moveAccountTariffDate($accountTariff, $errorMessage);

                $isWithTransaction && $transaction->commit();

                if (defined('YII_ENV') && YII_ENV == 'test') {
                    throw $e;
                }

            } catch (\Exception $e) {
                $isWithTransaction && $transaction->rollBack();
                $this->out(PHP_EOL . 'Error. ' . $e->getMessage() . PHP_EOL);
                Yii::error($e->getMessage());
                HandlerLogger::me()->add($e->getMessage());
                if ($accountTariffId) {
                    throw $e;
                }
            }
        }
    }

    /**
     * Получаем тип события для универсального тарифа (включение, выключение, смена)
     *
     * @param AccountTariff $accountTariff
     * @param $newTariffPeriodId
     * @return string|null
     */
    protected function getEventType(AccountTariff $accountTariff, $newTariffPeriodId)
    {
        $eventType = null;
        if ($accountTariff->tariff_period_id != $newTariffPeriodId) {
            if (!$accountTariff->tariff_period_id) {
                // не было тарифа - включение услуги
                $eventType = ImportantEventsNames::UU_SWITCHED_ON;
            } elseif (!$newTariffPeriodId) {
                // не будет тарифа - закрытие услуги
                $eventType = ImportantEventsNames::UU_SWITCHED_OFF;
            } else {
                // "Ленин - жил, Ленин - жив, Ленин - будет жить" - смена тарифа
                // (C) Korobkov
                $eventType = ImportantEventsNames::UU_UPDATED;
            }

        }

        return $eventType;
    }

    protected function generateUuEvent(AccountTariff $accountTariff, $eventType)
    {
        if (!$eventType) {
            return;
        }

        switch ($eventType) {
            case ImportantEventsNames::UU_SWITCHED_ON:
                $event = \app\modules\uu\Module::EVENT_UU_SWITCHED_ON;
                break;
            case ImportantEventsNames::UU_SWITCHED_OFF:
                $event = \app\modules\uu\Module::EVENT_UU_SWITCHED_OFF;
                break;
            case ImportantEventsNames::UU_UPDATED:
                $event = \app\modules\uu\Module::EVENT_UU_UPDATE;
                break;
        }

        EventQueue::go($event, [
            'client_account_id' => $accountTariff->client_account_id,
            'account_tariff_id' => $accountTariff->prev_account_tariff_id ?: $accountTariff->id,
            'service_type_id' => $accountTariff->prev_account_tariff_id ? $accountTariff->prevAccountTariff->service_type_id : $accountTariff->service_type_id,
        ]);
    }

    /**
     * @param AccountTariff $accountTariff
     * @param string|null $eventType
     * @param int $oldTariffPeriodId
     * @param int $newTariffPeriodId
     * @throws ModelValidationException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    protected function generateEvents(AccountTariff $accountTariff, $eventType, $oldTariffPeriodId = null, $newTariffPeriodId = null)
    {
        if ($eventType) {
            // создать важное событие
            ImportantEvents::create($eventType,
                ImportantEventsSources::SOURCE_STAT, [
                    'account_tariff_id' => $accountTariff->id,
                    'service_type_id' => $accountTariff->service_type_id,
                    'client_id' => $accountTariff->client_account_id,
                ]
                + ($eventType === ImportantEventsNames::UU_SWITCHED_ON
                    ? ['tariff_status' => $accountTariff->tariffPeriod->tariff->status->name]
                    : []
                )
            );
        }

        // доп. обработка в зависимости от типа услуги
        switch ($accountTariff->service_type_id) {

            case ServiceType::ID_VOIP:
                // Телефония
                EventQueue::go(EventQueue::ACTUALIZE_NUMBER, ['number' => $accountTariff->voip_number]);

                // \app\dao\ActualNumberDao::collectFromUsages ресурс "линии" всегда передает 1. Надо дополнительно отправить запрос про ресурсы
                EventQueue::go(\app\modules\uu\Module::EVENT_VOIP_CALLS, [
                    'client_account_id' => $accountTariff->client_account_id,
                    'account_tariff_id' => $accountTariff->id,
                    'number' => $accountTariff->voip_number,
                ]);

                // is need sync tariff options
                // только для смены тарифа. При включении - и так отработает. При выключении - не надо.
                $oldTariffPeriod = null;
                if ($oldTariffPeriodId) {
                    $oldTariffPeriod = TariffPeriod::findOne(['id' => $oldTariffPeriodId]);
                }

                if ($oldTariffPeriodId && $newTariffPeriodId && $oldTariffPeriodId != $newTariffPeriodId) {

                    $isNewAutoDial = $accountTariff->tariffPeriod->tariff->isAutodial();
                    $isOldAutoDial = $oldTariffPeriod && $oldTariffPeriod->tariff->isAutodial();

                    if ($isNewAutoDial != $isOldAutoDial) {
                        SyncResourceTarificator::doSyncResources($accountTariff);
                    }
                }

                /** @var \app\modules\callTracking\Module $callTrackingModule */
                $callTrackingModule = Yii::$app->getModule('callTracking');
                $callTrackingParams = $callTrackingModule->params;
                if (isset($callTrackingParams['client_account_id']) && $callTrackingParams['client_account_id'] == $accountTariff->client_account_id) {
                    // При выключении или выключении услуги добавить в очередь экспорт номера
                    if (in_array($eventType, [ImportantEventsNames::UU_SWITCHED_ON, ImportantEventsNames::UU_SWITCHED_OFF], true)) {
                        EventQueue::go(\app\modules\callTracking\Module::EVENT_EXPORT_VOIP_NUMBER, [
                            'account_tariff_id' => $accountTariff->id,
                            'voip_number' => $accountTariff->voip_number,
                            'is_active' => $eventType === ImportantEventsNames::UU_SWITCHED_ON,
                        ]);
                    }
                }
                break;

            case ServiceType::ID_VPBX:
                // ВАТС
                EventQueue::go(\app\modules\uu\Module::EVENT_VPBX, [
                    'client_account_id' => $accountTariff->client_account_id,
                    'account_tariff_id' => $accountTariff->id,
                ]);
                // Из VirtPbx3Action::add/dataChanged передаются все текущие ресурсы. Больше ничего не надо
                break;

            case ServiceType::ID_VPS:
                EventQueue::go(\app\modules\uu\Module::EVENT_VPS_SYNC, [
                    'client_account_id' => $accountTariff->client_account_id,
                    'account_tariff_id' => $accountTariff->id,
                ]);
                break;

            case ServiceType::ID_VPS_LICENCE:
                EventQueue::go(\app\modules\uu\Module::EVENT_VPS_LICENSE, [
                    'client_account_id' => $accountTariff->client_account_id,
                    'account_tariff_id' => $accountTariff->id,
                ]);
                break;

            case ServiceType::ID_CALL_CHAT:
                // call chat
                switch ($eventType) {
                    case ImportantEventsNames::UU_SWITCHED_ON:
                        // создать
                        EventQueue::go(\app\modules\uu\Module::EVENT_CALL_CHAT_CREATE, [
                            'client_account_id' => $accountTariff->client_account_id,
                            'account_tariff_id' => $accountTariff->id,
                        ]);
                        break;

                    case ImportantEventsNames::UU_SWITCHED_OFF:
                        // удалить
                        EventQueue::go(\app\modules\uu\Module::EVENT_CALL_CHAT_REMOVE, [
                            'client_account_id' => $accountTariff->client_account_id,
                            'account_tariff_id' => $accountTariff->id,
                        ]);
                        break;

                    default:
                        // сменить тариф
                        break;
                }
                break;

            case ServiceType::ID_SIPTRUNK:
                $isCreate = false;
                $isDelete = false;

                switch ($eventType) {
                    case ImportantEventsNames::UU_SWITCHED_ON:
                        $isCreate = true;
                        break;
                    case ImportantEventsNames::UU_SWITCHED_OFF:
                        $isDelete = true;
                        break;
                    case ImportantEventsNames::UU_UPDATED:
                    default:
                        /*nothing*/
                        break;
                }

                EventQueue::go(Module::EVENT_SIPTRUNK_SYNC, [
                    'account_tariff_id' => $accountTariff->id,
                    'account_client_id' => $accountTariff->client_account_id,
                    'is_create' => $isCreate,
                    'is_delete' => $isDelete,
                ]);
                break;


//            case ServiceType::ID_CHAT_BOT:
//                // call chat
//                switch ($eventType) {
//                    case ImportantEventsNames::UU_SWITCHED_ON:
//                        // создать
//                        EventQueue::go(\app\modules\uu\Module::EVENT_CHAT_BOT_CREATE, [
//                            'client_account_id' => $accountTariff->client_account_id,
//                            'account_tariff_id' => $accountTariff->id,
//                            'tariff_id' => $accountTariff->tariffPeriod->tariff_id,
//                        ]);
//                        break;
//
//                    case ImportantEventsNames::UU_SWITCHED_OFF:
//                        // удалить
//                        EventQueue::go(\app\modules\uu\Module::EVENT_CHAT_BOT_REMOVE . [
//                                'client_account_id' => $accountTariff->client_account_id,
//                                'account_tariff_id' => $accountTariff->id,
//                            ]);
//                        break;
//
//                    default:
//                        // сменить тариф - не обрабатывается
//                        break;
//                }
//                break;

            case ServiceType::ID_VOICE_ROBOT:
                switch ($eventType) {
                    case ImportantEventsNames::UU_SWITCHED_ON:
                        EventQueue::go(\app\modules\uu\Module::EVENT_ROBOCALL_INTERNAL_CREATE, [
                            'client_account_id' => $accountTariff->client_account_id,
                            'account_tariff_id' => $accountTariff->id,
                        ]);
                        break;
                    case ImportantEventsNames::UU_SWITCHED_OFF:
                        EventQueue::go(\app\modules\uu\Module::EVENT_ROBOCALL_INTERNAL_REMOVE, [
                            'account_tariff_id' => $accountTariff->id,
                        ]);
                        break;

                    case ImportantEventsNames::UU_UPDATED:
                        EventQueue::go(\app\modules\uu\Module::EVENT_ROBOCALL_INTERNAL_UPDATE, [
                            'client_account_id' => $accountTariff->client_account_id,
                            'account_tariff_id' => $accountTariff->id,
                        ]);
                        break;
                }
                break;

            case ServiceType::ID_ESIM:
                if ($eventType == ImportantEventsNames::UU_SWITCHED_ON) {
                    EventQueue::go(\app\modules\sim\Module::EVENT_ESIM_CHECK, [
                        'client_account_id' => $accountTariff->client_account_id,
                        'account_tariff_id' => $accountTariff->id,
                    ]);
                }
                break;
        }

        if (
            $eventType === ImportantEventsNames::UU_SWITCHED_OFF
            && in_array($accountTariff->service_type_id, array_keys(ServiceType::$packages))
        ) {
            // только при закрытиии - закрыть в Light
            EventQueue::go(\app\modules\uu\Module::EVENT_CLOSE_LIGHT, [
                'client_account_id' => $accountTariff->client_account_id,
                'account_tariff_id' => $accountTariff->id,
            ]);
        }
    }

    private function saveStateChange(AccountTariff $accountTariff, $newTariffPeriodId)
    {
        if ($accountTariff->tariff_period_id == $newTariffPeriodId) {
            // nothing changed
            return;
        }
        $obj = $accountTariff->prev_account_tariff_id ? 'package' : 'service';

        $data = [
                'account_tariff_id' => $accountTariff->prev_account_tariff_id ?: $accountTariff->id,
                'tariff_period_id' => (int)$newTariffPeriodId,
            ] + ($accountTariff->prev_account_tariff_id ? ['package_id' => $accountTariff->id] : []);

        if (!$newTariffPeriodId) {
            // off
            Log::add($accountTariff->client_account_id, $data['account_tariff_id'], $data + ['action' => $obj . '_off_applied']);
        } elseif (!$accountTariff->tariff_period_id) {
            // on
            Log::add($accountTariff->client_account_id, $data['account_tariff_id'], $data + ['action' => $obj . '_on_applied']);
        } else {
            // change
            Log::add($accountTariff->client_account_id, $data['account_tariff_id'], $data + [
                    'action' => $obj . '_tariff_period_change_applied',
                    'tariff_period_id_from' => (int)$accountTariff->tariff_period_id,
                ]);
        }
    }

    /**
     * Сменить тариф
     *
     * @param AccountTariff $accountTariff
     * @param $newTariffId
     * @param string|null $eventType
     * @throws FinanceException
     * @throws ModelValidationException
     * @throws \Throwable
     * @throws \yii\base\Exception
     */
    protected function changeAccountTariff(AccountTariff $accountTariff, $newTariffId, $eventType)
    {
        if (is_null($eventType)) {
            return;
        }

        $accountTariff->tariff_period_id = $newTariffId;
        $accountTariff->tariff_period_utc = DateTimeZoneHelper::getUtcDateTime()
            ->format(DateTimeZoneHelper::DATETIME_FORMAT);

        if (!$accountTariff->save()) {
            throw new ModelValidationException($accountTariff);
        }

        if ($accountTariff->tariff_period_id) {
            // Билинговать с новым тарифом при смене тарифа (но не при закрытии услуги)

            // менять тариф, даже если нет денег
            // менять тариф, если стоит флаг "списывать после блокировки" не смотря ни на что
            // включать пакет, если он "по-умолчанию" или "бандл"
            $this->checkBalance(
                $accountTariff,
                $eventType == ImportantEventsNames::UU_UPDATED
//                || $accountTariff->tariffPeriod->tariff->is_charge_after_blocking
                || $accountTariff->tariffPeriod->tariff->is_default
                || $accountTariff->tariffPeriod->tariff->is_bundle
            );
        }
    }

    /**
     * Билинговать с новым тарифом
     *
     * @param AccountTariff $accountTariff
     * @param bool $isForceUpdate
     * @throws FinanceException
     * @throws ModelValidationException
     * @throws \Throwable
     * @throws \yii\base\Exception
     */
    protected function checkBalance(AccountTariff $accountTariff, $isForceUpdate)
    {
        ob_start();
        $isNeedRecalc = false;
        try {
            $tarificator = (new AccountLogSetupTarificator);
            $tarificator->tarificateAccountTariff($accountTariff);
            $tarificator->isNeedRecalc && $isNeedRecalc = true;
            $tarificator = (new AccountLogPeriodTarificator);
            $tarificator->tarificateAccountTariff($accountTariff);
            $tarificator->isNeedRecalc && $isNeedRecalc = true;
            $tarificator = (new AccountLogResourceTarificator);
            $tarificator->tarificateAccountTariffOption($accountTariff);
            $tarificator->isNeedRecalc && $isNeedRecalc = true;
            $tarificator = (new AccountLogMinTarificator);
            $tarificator->tarificate($accountTariff->id);
            $tarificator->isNeedRecalc && $isNeedRecalc = true;

            if ($isNeedRecalc) {
                (new AccountEntryTarificator)->tarificate($accountTariff->id);
                (new BillTarificator)->tarificate($accountTariff->id);
                HandlerLogger::me()->add('Balance full recalced');
            } else {
                HandlerLogger::me()->add('Balance recalced partially');
            }
            (new RealtimeBalanceTarificator)->tarificate($accountTariff->client_account_id, $accountTariff->id);
            HandlerLogger::me()->add(ob_get_clean());
        } catch (\Exception $e) {
            HandlerLogger::me()->add(ob_get_clean());
            throw $e;
        }

        // баланс изменился, надо перезагрузить clientAccount
        $accountTariff->refresh();
        $clientAccount = $accountTariff->clientAccount;

        $credit = $clientAccount->credit; // кредитный лимит
        $realtimeBalance = $clientAccount->balance;
        $realtimeBalanceWithCredit = $realtimeBalance + $credit;

        if ($realtimeBalanceWithCredit < 0) {

            $errorMessage = sprintf('У клиента %d нет денег на смену тарифа по услуге %d. После смены получится на счету %.2f %s и кредит %.2f %s',
                $accountTariff->client_account_id,
                $accountTariff->id,
                $realtimeBalance, $clientAccount->currency,
                $credit, $clientAccount->currency);

            // всегда менять тариф, даже если денег не хватает
            if ($isForceUpdate) {
                $accountTariff->comment .= ($accountTariff->comment ? PHP_EOL : '') . $errorMessage . ', но тариф изменен';
                if (!$accountTariff->save()) {
                    throw new ModelValidationException($accountTariff);
                }
                return;
            }

            throw new FinanceException($errorMessage);
        }

    }

    /**
     * Отодвинуть смену тарифа
     *
     * @param AccountTariff $accountTariff
     * @param string $errorMessage
     * @throws ModelValidationException
     */
    protected function moveAccountTariffDate(AccountTariff $accountTariff, $errorMessage)
    {
        $accountTariff->comment .= ($accountTariff->comment ? PHP_EOL : '') . $errorMessage;
        if (!$accountTariff->save()) {
            // "Не надо фаталиться, вся жизнь впереди. Вся жизнь впереди, надейся и жди." (С) Р. Рождественский
            // (C) Korobkov
            // throw new ModelValidationException($accountTariff);
        }

        $accountTariffLogs = $accountTariff->accountTariffLogs;
        $accountTariffLog = reset($accountTariffLogs);
        $accountTariffLog->actual_from_utc = $accountTariff->getDefaultActualFrom();
        if (!$accountTariffLog->save()) {
            // "Не надо фаталиться, вся жизнь впереди. Вся жизнь впереди, надейся и жди." (С) Р. Рождественский
            // (C) Korobkov
            // throw new ModelValidationException($accountTariffLog);
        }

        // Сдвигаем дату включения невключенных пакетов вместе с основной услугой
        $packages = $accountTariff->nextAccountTariffs;
        foreach ($packages as $package) {
            if ($package->isStarted()) {
                continue;
            }

            // только для ещё невключенных пакетов
            $packageLogs = $package->accountTariffLogs;
            $packageLog = reset($packageLogs);

            // пакет должен включится позже
            if ($packageLog->actual_from_utc > $accountTariffLog->actual_from_utc) {
                continue;
            }

            $packageLog->actual_from_utc = $accountTariffLog->actual_from_utc;

            if (!$packageLog->save()) {
                throw new ModelValidationException($packageLog);
            }
        }
    }
}
