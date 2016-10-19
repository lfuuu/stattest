<?php

namespace app\classes\behaviors\uu;

use app\classes\uu\model\AccountTariffLog;
use app\classes\uu\tarificator\AccountEntryTarificator;
use app\classes\uu\tarificator\AccountLogMinTarificator;
use app\classes\uu\tarificator\AccountLogPeriodTarificator;
use app\classes\uu\tarificator\AccountLogSetupTarificator;
use app\classes\uu\tarificator\BillConverterTarificator;
use app\classes\uu\tarificator\BillTarificator;
use app\classes\uu\tarificator\RealtimeBalanceTarificator;
use app\classes\uu\tarificator\SetCurrentTariffTarificator;
use Yii;
use yii\base\Behavior;
use yii\base\Event;
use yii\db\ActiveRecord;


class AccountTariffBiller extends Behavior
{
    const EVENT_RECALC = 'uu_account_tariff_biller_recalc';

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

        \app\classes\Event::go(self::EVENT_RECALC, [
                'accountTariffId' => $accountTariffId,
                'accountClientId' => $accountTariff->client_account_id,
            ]
        );
    }

    /**
     * Билинговать
     * @param array $params [accountTariffId, accountClientId]
     */
    public static function recalc(array $params)
    {
        ob_start();

        $accountTariffId = $params['accountTariffId'];
        $accountClientId = $params['accountClientId'];

        Yii::info('AccountTariffBiller. Before SetCurrentTariffTarificator', 'uu');
        (new SetCurrentTariffTarificator())->tarificate($accountTariffId);

        Yii::info('AccountTariffBiller. Before AccountLogSetupTarificator', 'uu');
        (new AccountLogSetupTarificator)->tarificate($accountTariffId);

        Yii::info('AccountTariffBiller. Before AccountLogPeriodTarificator', 'uu');
        (new AccountLogPeriodTarificator)->tarificate($accountTariffId);

        Yii::info('AccountTariffBiller. Before AccountLogMinTarificator', 'uu');
        (new AccountLogMinTarificator)->tarificate($accountTariffId);

        Yii::info('AccountTariffBiller. Before AccountEntryTarificator', 'uu');
        (new AccountEntryTarificator)->tarificate($accountTariffId);

        Yii::info('AccountTariffBiller. Before BillTarificator', 'uu');
        (new BillTarificator)->tarificate($accountTariffId);

        Yii::info('AccountTariffBiller. Before BillConverterTarificator', 'uu');
        (new BillConverterTarificator)->tarificate($accountClientId);

        Yii::info('AccountTariffBiller. Before RealtimeBalanceTarificator', 'uu');
        (new RealtimeBalanceTarificator)->tarificate($accountClientId);

        ob_end_clean();
    }
}
