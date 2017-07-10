<?php

namespace app\classes\notification\processors;

use app\models\ClientAccount;
use app\models\important_events\ImportantEventsNames;
use app\models\Payment;
use yii\base\InvalidConfigException;

class AddPaymentNotificationProcessor extends NotificationProcessor
{
    /**
     * @var \app\models\Payment|null
     */
    protected $payment = null;

    public function __construct($clientId, $paymentId)
    {
        parent::__construct();

        $client = ClientAccount::findOne(['id' => $clientId]);

        if (!$client) {
            throw new InvalidConfigException('Клиент не найден');
        }

        $this->setClient($client);

        $this->payment = Payment::findOne(['id' => $paymentId, 'client_id' => $client->id]);

        if (!$this->payment) {
            throw new InvalidConfigException('Платеж не найден');
        }

        $this->eventFields['payment_id'] = $this->payment->id;
        $this->eventFields['currency'] = $this->payment->currency;
    }

    /**
     * @inheritdoc
     */
    public function getEnterEvent()
    {
        return ImportantEventsNames::ADD_PAY_NOTIF;
    }

    /**
     * @inheritdoc
     */
    public function getLeaveEvent()
    {
        return ImportantEventsNames::ADD_PAY_NOTIF;
    }


    /**
     * @inheritdoc
     */
    public function getValue()
    {
        return $this->payment->sum;
    }

    /**
     * @inheritdoc
     */
    public function getLimit()
    {
        return 0;
    }

}