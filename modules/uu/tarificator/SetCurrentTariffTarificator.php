<?php

namespace app\modules\uu\tarificator;

use app\classes\ActaulizerVoipNumbers;
use app\classes\HandlerLogger;
use app\exceptions\FinanceException;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\EventQueue;
use app\models\important_events\ImportantEvents;
use app\models\important_events\ImportantEventsNames;
use app\models\important_events\ImportantEventsSources;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffLog;
use app\modules\uu\models\ServiceType;
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
                        {$accountTariffLogTableName} account_tariff_log
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

        $query = $db->createCommand(
            $sql,
            [
                ':now' => DateTimeZoneHelper::getUtcDateTime()
                    ->format(DateTimeZoneHelper::DATETIME_FORMAT)
            ]
        )
            ->query();

        foreach ($query as $row) {

            $accountTariff = AccountTariff::findOne(['id' => $row['id']]);

            $isWithTransaction && $transaction = $db->beginTransaction();
            try {

                if ($accountTariff->tariff_period_id != $row['new_tariff_period_id']) {

                    if (!$accountTariff->tariff_period_id) {
                        // не было тарифа - включение услуги
                        $eventType = ImportantEventsNames::UU_SWITCHED_ON;
                    } elseif (!$row['new_tariff_period_id']) {
                        // не будет тарифа - закрытие услуги
                        $eventType = ImportantEventsNames::UU_SWITCHED_OFF;
                    } else {
                        // "Ленин - жил, Ленин - жив, Ленин - будет жить" - смена тарифа
                        $eventType = ImportantEventsNames::UU_UPDATED;
                    }

                    // создать важное событие
                    ImportantEvents::create($eventType,
                        ImportantEventsSources::SOURCE_STAT, [
                            'account_tariff_id' => $accountTariff->id,
                            'service_type_id' => $accountTariff->service_type_id,
                            'client_id' => $accountTariff->client_account_id,
                        ]);

                    // сменить тариф
                    $accountTariff->tariff_period_id = $row['new_tariff_period_id'];
                    $accountTariff->tariff_period_utc = DateTimeZoneHelper::getUtcDateTime()
                        ->format(DateTimeZoneHelper::DATETIME_FORMAT);
                    if (!$accountTariff->save()) {
                        throw new ModelValidationException($accountTariff);
                    }

                    if ($accountTariff->tariff_period_id) {
                        // Билинговать с новым тарифом при смене тарифа (но не при закрытии услуги)
                        $this->checkBalance($accountTariff);
                    }
                } else {
                    $eventType = null;
                }

                if ($eventType === ImportantEventsNames::UU_SWITCHED_ON) {
                    EventQueue::go(\app\modules\uu\Module::EVENT_UU_SWITCHED_ON, [
                        'client_account_id' => $accountTariff->client_account_id,
                        'account_tariff_id' => $accountTariff->id,
                    ]);
                }

                // доп. обработка в зависимости от типа услуги
                switch ($accountTariff->service_type_id) {

                    case ServiceType::ID_VOIP:
                        // Телефония
                        // \app\dao\ActualNumberDao::collectFromUsages ресурс "линии" всегда передает 1. Надо дополнительно отправить запрос про ресурсы
                        EventQueue::go(\app\modules\uu\Module::EVENT_VOIP_CALLS, [
                            'client_account_id' => $accountTariff->client_account_id,
                            'account_tariff_id' => $accountTariff->id,
                            'number' => $accountTariff->voip_number,
                        ]);

                        $isCoreServer = (isset(\Yii::$app->params['CORE_SERVER']) && \Yii::$app->params['CORE_SERVER']);
                        if ($isCoreServer) {
                            if (AccountTariff::hasTrunk($accountTariff->client_account_id)) {
                                HandlerLogger::me()->add('Мегатранк');
                            } else {
                                ActaulizerVoipNumbers::me()->actualizeByNumber($accountTariff->voip_number, $accountTariff->id); // @todo выпилить этот костыль и использовать напрямую ApiPhone::me()->addDid/editDid
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

                    case ServiceType::ID_VOIP_PACKAGE_CALLS:
                        // Пакеты телефонии
                        if ($eventType === ImportantEventsNames::UU_SWITCHED_OFF) {
                            // только при закрытиии - закрыть в Light
                            EventQueue::go(\app\modules\uu\Module::EVENT_CLOSE_LIGHT, [
                                'client_account_id' => $accountTariff->client_account_id,
                                'account_tariff_id' => $accountTariff->id,
                            ]);
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

                    case ServiceType::ID_CALLTRACKING:
                        if ($eventType == ImportantEventsNames::UU_UPDATED) {
                            return ;
                        }

                        // При выключении или выключении услуги добавить в очередь экспорт номера
                        EventQueue::go(\app\modules\callTracking\Module::EVENT_EXPORT_ACCOUNT_TARIFF, [
                            'account_tariff_id' => $accountTariff->id,
                            'is_active' => ($eventType == ImportantEventsNames::UU_SWITCHED_ON),
                            'calltracking_params' => $accountTariff->calltracking_params,
                        ]);
                        break;
                }

                $isWithTransaction && $transaction->commit();

            } catch (FinanceException $e) {
                $isWithTransaction && $transaction->rollBack();

                $errorMessage = $e->getMessage();
                $this->out(PHP_EOL . $errorMessage . PHP_EOL);
                Yii::error($errorMessage);

                HandlerLogger::me()->add($errorMessage);

                // смену тарифа отодвинуть в надежде, что за это время клиент пополнит баланс
                $isWithTransaction && $transaction = $db->beginTransaction();

                $accountTariff->comment .= ($accountTariff->comment ? PHP_EOL : '') . $errorMessage;
                if (!$accountTariff->save()) {
                    // "Не надо фаталиться, вся жизнь впереди. Вся жизнь впереди, надейся и жди." (С) Р. Рождественский
                    // throw new ModelValidationException($accountTariff);
                }

                $accountTariffLogs = $accountTariff->accountTariffLogs;
                $accountTariffLog = reset($accountTariffLogs);
                $accountTariffLog->actual_from_utc = $accountTariff->getDefaultActualFrom();
                if (!$accountTariffLog->save()) {
                    // "Не надо фаталиться, вся жизнь впереди. Вся жизнь впереди, надейся и жди." (С) Р. Рождественский
                    // throw new ModelValidationException($accountTariffLog);
                }

                $isWithTransaction && $transaction->commit();

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
     * Билинговать с новым тарифом
     *
     * @param AccountTariff $accountTariff
     *
     * @throws \yii\db\StaleObjectException
     * @throws \RangeException
     * @throws \app\exceptions\ModelValidationException
     * @throws \app\exceptions\FinanceException
     * @throws \yii\db\Exception
     * @throws \Exception
     * @throws \LogicException
     */
    protected function checkBalance(AccountTariff $accountTariff)
    {
        ob_start();
        try {
            (new AccountLogSetupTarificator)->tarificateAccountTariff($accountTariff);
            (new AccountLogPeriodTarificator)->tarificateAccountTariff($accountTariff);
            (new AccountLogResourceTarificator)->tarificateAccountTariffOption($accountTariff);
            (new AccountLogMinTarificator)->tarificate($accountTariff->id);
            (new AccountEntryTarificator)->tarificate($accountTariff->id);
            (new BillTarificator)->tarificate($accountTariff->id);
            (new RealtimeBalanceTarificator)->tarificate($accountTariff->client_account_id);
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
            throw new FinanceException(
                sprintf('У клиента %d нет денег на смену тарифа по услуге %d. После смены получится на счету %.2f %s и кредит %.2f %s',
                    $accountTariff->client_account_id,
                    $accountTariff->id,
                    $realtimeBalance, $clientAccount->currency,
                    $credit, $clientAccount->currency)
            );
        }

    }
}
