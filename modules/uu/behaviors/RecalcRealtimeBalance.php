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
    const EVENT_RECALC = 'uu_recalc_realtime_balance';

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

        \app\classes\Event::go(self::EVENT_RECALC, [
                'accountClientId' => $payment->client_id,
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
        ob_end_clean();
    }
}
