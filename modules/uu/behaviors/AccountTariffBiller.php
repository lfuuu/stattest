<?php

namespace app\modules\uu\behaviors;

use app\classes\HandlerLogger;
use app\classes\model\ActiveRecord;
use app\models\EventQueue;
use app\modules\uu\models\AccountTariff;
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
    /** Максимальное кол-во услуг на УЛС, когда билинговать сразу (в очереди). Иначе - потом (по крону раз в час) */
    const MAX_ACCOUNT_TARIFFS = 30;

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
     * @throws \Exception
     */
    public function accountTariffLogChange(Event $event)
    {
        /** @var AccountTariffLog|AccountTariffResourceLog $accountTariffLog */
        $accountTariffLog = $event->sender;
        $accountTariff = $accountTariffLog->accountTariff;

        EventQueue::go(\app\modules\uu\Module::EVENT_RECALC_ACCOUNT, [
                'account_tariff_id' => $accountTariff->id,
                'client_account_id' => $accountTariff->client_account_id,
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

        $accountTariffId = $params['account_tariff_id'];
        $clientAccountId = $params['client_account_id'];

        // биллинг отложить можно, а вот установку текущего тарифа (+сопутствующие триггеры) откладывать нельзя
        (new SetCurrentTariffTarificator())->tarificate($accountTariffId);

//        $count = AccountTariff::find()
//            ->where(['client_account_id' => $clientAccountId])
//            ->count();
//        if ($count > self::MAX_ACCOUNT_TARIFFS) {
//            HandlerLogger::me()->add('Слишком много услуг на УЛС');
//            return;
//        }

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
