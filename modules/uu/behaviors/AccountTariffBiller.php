<?php

namespace app\modules\uu\behaviors;

use app\classes\HandlerLogger;
use app\classes\model\ActiveRecord;
use app\helpers\DateTimeZoneHelper;
use app\helpers\Semaphore;
use app\models\EventQueue;
use app\modules\uu\models\AccountTariffLog;
use app\modules\uu\models\AccountTariffResourceLog;
use app\modules\uu\tarificator\AccountEntryTarificator;
use app\modules\uu\tarificator\AccountLogMinTarificator;
use app\modules\uu\tarificator\AccountLogPeriodPackageTarificator;
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
                ->modify('+10 second') // +1 minute
                ->format(DateTimeZoneHelper::DATETIME_FORMAT)

        );

        if ($event->name == ActiveRecord::EVENT_AFTER_UPDATE) {
            return true;
        }

        EventQueue::go(\app\modules\uu\Module::EVENT_UU_ANONCE, [
            'account_tariff_id' => $accountTariff->prev_account_tariff_id ?: $accountTariff->id,
            'client_account_id' => $accountTariff->client_account_id,
        ],
            $isForceAdd = false,
            $nextStart = DateTimeZoneHelper::getUtcDateTime()
                ->modify('+2 second')
                ->format(DateTimeZoneHelper::DATETIME_FORMAT)

        );
    }

    /**
     * Билинговать
     *
     * @param array $params [accountTariffId, clientAccountId]
     * @param array $isIntegrated
     * @throws \Throwable
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public static function recalc(array $params, $isIntegrated = false)
    {
        if (!$isIntegrated) {
            if (!Semaphore::me()->acquire(Semaphore::ID_UU_CALCULATOR, false)) {
                throw new \LogicException('Error. AccountTariff::recalc not started');
            }

            ob_start();
        }

        $accountTariffId = $params['account_tariff_id'];
        $clientAccountId = $params['client_account_id'];

        $isNeedRecalc = false;

        (new SetCurrentTariffTarificator())->tarificate($accountTariffId);
        (new SyncResourceTarificator())->tarificate($accountTariffId);

        $tarificator = (new AccountLogSetupTarificator);
        $tarificator->tarificate($accountTariffId);
        $tarificator->isNeedRecalc && $isNeedRecalc = true;

        $tarificator = (new AccountLogPeriodTarificator);
        $tarificator->tarificate($accountTariffId);
        $tarificator->isNeedRecalc && $isNeedRecalc = true;

        $tarificator = (new AccountLogResourceTarificator);
        $tarificator->tarificate($accountTariffId, $isIntegrated);
        $tarificator->isNeedRecalc && $isNeedRecalc = true;

        $tarificator = (new AccountLogMinTarificator);
        $tarificator->tarificate($accountTariffId);
        $tarificator->isNeedRecalc && $isNeedRecalc = true;

        if ($isNeedRecalc) {
            (new AccountEntryTarificator)->tarificate($accountTariffId);
            (new BillTarificator)->tarificate($accountTariffId);
//         (new BillConverterTarificator)->tarificate($clientAccountId); // это не обязательно делать в реалтайме. По крону вполне сойдет
        }

        (new RealtimeBalanceTarificator)->tarificate($clientAccountId, $accountTariffId);

        $tarificator = (new AccountLogPeriodPackageTarificator());
        $tarificator->tarificate($accountTariffId);
        $tarificator->isNeedRecalc && $isNeedRecalc = true;


        if (!$isIntegrated) {
            Semaphore::me()->release(Semaphore::ID_UU_CALCULATOR);

            HandlerLogger::me()->add(ob_get_clean());
        }

        return $isNeedRecalc ? '+' : '-';
    }
}
