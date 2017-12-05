<?php

namespace app\modules\uu\behaviors;

use app\classes\HandlerLogger;
use app\classes\model\ActiveRecord;
use app\models\ClientAccount;
use app\models\EventQueue;
use app\models\Payment;
use app\modules\uu\tarificator\RealtimeBalanceTarificator;
use yii\base\Behavior;
use yii\base\Event;


class RecalcRealtimeBalance extends Behavior
{
    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'postponeRecalc',
            ActiveRecord::EVENT_AFTER_UPDATE => 'postponeRecalc',
            ActiveRecord::EVENT_AFTER_DELETE => 'postponeRecalc',
        ];
    }

    /**
     * Пересчитать realtime баланс при поступлении платежа
     * Сейчас для надежности, чтобы не задержать основное действие или тем более не сфаталить его, надо как можно меньше действий - максимум поставить в очередь
     *
     * @param Event $event
     * @throws \app\exceptions\ModelValidationException
     */
    public function postponeRecalc(Event $event)
    {
        /** @var Payment $payment */
        $payment = $event->sender;

        EventQueue::go(\app\modules\uu\Module::EVENT_RECALC_BALANCE, [
                'client_account_id' => $payment->client_id,
            ]
        );
    }

    /**
     * Пересчитать realtime баланс при поступлении платежа
     *
     * @param int $clientAcccountId
     * @throws \InvalidArgumentException
     * @throws \yii\db\Exception
     */
    public static function recalc($clientAcccountId)
    {
        $clientAcccount = ClientAccount::findOne(['id' => $clientAcccountId]);
        if (!$clientAcccount) {
            throw new \InvalidArgumentException('Неправильный аккаунт ' . $clientAcccountId);
        }

        if ($clientAcccount->account_version != ClientAccount::VERSION_BILLER_UNIVERSAL) {
            return;
        }

        ob_start();
        (new RealtimeBalanceTarificator)->tarificate($clientAcccount->id);
        HandlerLogger::me()->add(ob_get_clean());
    }
}
