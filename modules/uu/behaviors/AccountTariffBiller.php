<?php

namespace app\modules\uu\behaviors;

use app\classes\HandlerLogger;
use app\classes\model\ActiveRecord;
use app\modules\uu\models\AccountTariffLog;
use app\modules\uu\models\AccountTariffResourceLog;
use app\modules\uu\tarificator\AccountEntryTarificator;
use app\modules\uu\tarificator\AccountLogMinTarificator;
use app\modules\uu\tarificator\AccountLogPeriodTarificator;
use app\modules\uu\tarificator\AccountLogResourceTarificator;
use app\modules\uu\tarificator\AccountLogSetupTarificator;
use app\modules\uu\tarificator\BillConverterTarificator;
use app\modules\uu\tarificator\BillTarificator;
use app\modules\uu\tarificator\CreditMgpTarificator;
use app\modules\uu\tarificator\RealtimeBalanceTarificator;
use app\modules\uu\tarificator\SetCurrentTariffTarificator;
use app\modules\uu\tarificator\SyncResourceTarificator;
use Yii;
use yii\base\Behavior;
use yii\base\Event;


class AccountTariffBiller extends Behavior
{
    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'accountTariffLogChange',
            ActiveRecord::EVENT_AFTER_UPDATE => 'accountTariffLogChange',
            ActiveRecord::EVENT_AFTER_DELETE => 'accountTariffLogChange',
        ];
    }

    /**
     * Триггер при изменении лога тарифов
     * Пересчитать транзакции, проводки и счета
     *
     * @param Event $event
     * @throws \app\exceptions\ModelValidationException
     */
    public function accountTariffLogChange(Event $event)
    {
        /** @var AccountTariffLog|AccountTariffResourceLog $accountTariffLog */
        $accountTariffLog = $event->sender;
        $accountTariff = $accountTariffLog->accountTariff;
        $accountTariffId = $accountTariff->id;

        \app\classes\Event::go(\app\modules\uu\Module::EVENT_RECALC_ACCOUNT, [
                'accountTariffId' => $accountTariffId,
                'clientAccountId' => $accountTariff->client_account_id,
            ]
        );
    }

    /**
     * Билинговать
     *
     * @param array $params [accountTariffId, clientAccountId]
     * @throws \Exception
     */
    public static function recalc(array $params)
    {
        ob_start();

        $accountTariffId = $params['accountTariffId'];
        $clientAccountId = $params['clientAccountId'];

        Yii::info('AccountTariffBiller. Before SetCurrentTariffTarificator', 'uu');
        (new SetCurrentTariffTarificator())->tarificate($accountTariffId);

        Yii::info('AccountTariffBiller. Before AccountLogSetupTarificator', 'uu');
        (new AccountLogSetupTarificator)->tarificate($accountTariffId);

        Yii::info('AccountTariffBiller. Before AccountLogPeriodTarificator', 'uu');
        (new AccountLogPeriodTarificator)->tarificate($accountTariffId);

        Yii::info('AccountTariffBiller. Before AccountLogResourceTarificator', 'uu');
        (new AccountLogResourceTarificator)->tarificate($accountTariffId);

        Yii::info('AccountTariffBiller. Before AccountLogMinTarificator', 'uu');
        (new AccountLogMinTarificator)->tarificate($accountTariffId);

        Yii::info('AccountTariffBiller. Before AccountEntryTarificator', 'uu');
        (new AccountEntryTarificator)->tarificate($accountTariffId);

        Yii::info('AccountTariffBiller. Before SyncResourceTarificator', 'uu');
        (new SyncResourceTarificator())->tarificate($accountTariffId);

        Yii::info('AccountTariffBiller. Before BillTarificator', 'uu');
        (new BillTarificator)->tarificate($accountTariffId);

        Yii::info('AccountTariffBiller. Before BillConverterTarificator', 'uu');
        (new BillConverterTarificator)->tarificate($clientAccountId);

        Yii::info('AccountTariffBiller. Before RealtimeBalanceTarificator', 'uu');
        (new RealtimeBalanceTarificator)->tarificate($clientAccountId);

        Yii::info('AccountTariffBiller. Before CreditMgpTarificator', 'uu');
        (new CreditMgpTarificator)->tarificate($clientAccountId);

        HandlerLogger::me()->add(ob_get_clean());
    }
}
