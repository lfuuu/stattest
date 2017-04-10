<?php

namespace app\modules\uu\behaviors;

use app\models\ClientAccount;
use app\models\Payment;
use app\modules\uu\tarificator\RealtimeBalanceTarificator;
use yii\base\Behavior;
use yii\base\Event;
use yii\db\ActiveRecord;


class RecalcRealtimeBalance extends Behavior
{
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'PaymentChange',
            ActiveRecord::EVENT_AFTER_UPDATE => 'PaymentChange',
            ActiveRecord::EVENT_AFTER_DELETE => 'PaymentChange',
        ];
    }

    /**
     * Пересчитать realtime баланс при поступлении платежа
     * @param Event $event
     */
    public function PaymentChange(Event $event)
    {
        /** @var Payment $payment */
        $payment = $event->sender;
        if ($payment->client->account_version != ClientAccount::VERSION_BILLER_UNIVERSAL) {
            return;
        }

        ob_start();
        (new RealtimeBalanceTarificator)->tarificate($payment->client_id);
        ob_end_clean();
    }
}
