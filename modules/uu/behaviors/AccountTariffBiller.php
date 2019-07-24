<?php

namespace app\modules\uu\behaviors;

use app\classes\HandlerLogger;
use app\classes\model\ActiveRecord;
use app\helpers\DateTimeZoneHelper;
use app\models\EventQueue;
use app\modules\uu\models\AccountTariffLog;
use app\modules\uu\models\AccountTariffResourceLog;
use app\modules\uu\tarificator\AccountEntryTarificator;
use app\modules\uu\tarificator\AccountLogMinTarificator;
use app\modules\uu\tarificator\AccountLogPeriodTarificator;
use app\modules\uu\tarificator\AccountLogResourceTarificator;
use app\modules\uu\tarificator\AccountLogSetupTarificator;
use app\modules\uu\tarificator\BillTarificator;
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
        ],
            $isForceAdd = false,
            // 1. Чтобы пересчет был не по каждому ресурсу услуги, а один на всю услугу
            // 2. Костыль, чтобы обработка очереди не обгоняла сохранение
            $nextStart = DateTimeZoneHelper::getUtcDateTime()
                ->modify('+1 minute')
                ->format(DateTimeZoneHelper::DATETIME_FORMAT)

        );
    }

    /**
     * Билинговать
     *
     * @param array $params [accountTariffId, clientAccountId]
     * @throws \Throwable
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public static function recalc(array $params)
    {
        ob_start();

        $accountTariffId = $params['account_tariff_id'];
        $clientAccountId = $params['client_account_id'];

        (new SetCurrentTariffTarificator())->tarificate($accountTariffId);
        (new SyncResourceTarificator())->tarificate($accountTariffId);
        (new AccountLogSetupTarificator)->tarificate($accountTariffId);
        (new AccountLogPeriodTarificator)->tarificate($accountTariffId);
        (new AccountLogResourceTarificator)->tarificate($accountTariffId);
        (new AccountLogMinTarificator)->tarificate($accountTariffId);
        (new AccountEntryTarificator)->tarificate($accountTariffId);
        (new BillTarificator)->tarificate($accountTariffId);
        // (new BillConverterTarificator)->tarificate($clientAccountId); // это не обязательно делать в реалтайме. По крону вполне сойдет
        (new RealtimeBalanceTarificator)->tarificate($clientAccountId);

        HandlerLogger::me()->add(ob_get_clean());
    }
}
