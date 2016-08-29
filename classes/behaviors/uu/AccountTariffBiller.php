<?php

namespace app\classes\behaviors\uu;

use app\classes\uu\model\AccountTariffLog;
use app\classes\uu\tarificator\AccountEntryTarificator;
use app\classes\uu\tarificator\AccountLogMinTarificator;
use app\classes\uu\tarificator\AccountLogPeriodTarificator;
use app\classes\uu\tarificator\AccountLogSetupTarificator;
use app\classes\uu\tarificator\BillTarificator;
use app\classes\uu\tarificator\RealtimeBalanceTarificator;
use app\classes\uu\tarificator\SetCurrentTariffTarificator;
use yii\base\Behavior;
use yii\base\Event;
use yii\db\ActiveRecord;


class AccountTariffBiller extends Behavior
{
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'AccountTariffLogChange',
            ActiveRecord::EVENT_AFTER_UPDATE => 'AccountTariffLogChange',
            ActiveRecord::EVENT_AFTER_DELETE => 'AccountTariffLogChange',
        ];
    }

    /**
     * Триггер при изменении лога тарифов
     * Пересчитать транзакции, проводки и счета
     * @param Event $event
     */
    public function AccountTariffLogChange(Event $event)
    {
        /** @var AccountTariffLog $accountTariffLog */
        $accountTariffLog = $event->sender;
        $accountTariff = $accountTariffLog->accountTariff;
        $accountTariffId = $accountTariff->id;

        ob_start();
        (new SetCurrentTariffTarificator())->tarificate($accountTariffId, false);
        (new AccountLogSetupTarificator)->tarificate($accountTariffId, false);
        (new AccountLogPeriodTarificator)->tarificate($accountTariffId, false);
        (new AccountLogMinTarificator)->tarificate($accountTariffId);
        (new AccountEntryTarificator)->tarificate($accountTariffId);
        (new BillTarificator)->tarificate($accountTariffId);
        (new RealtimeBalanceTarificator)->tarificate($accountTariff->client_account_id);
        ob_end_clean();
    }
}
