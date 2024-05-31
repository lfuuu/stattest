<?php

namespace app\classes\behaviors\payment;

use app\models\ClientAccount;
use app\models\Business;
use app\models\Country;
use app\models\Payment;
use yii\base\Behavior;
use yii\db\ActiveRecord;


// устанавливаем организацию платежа
class SetPaymentOrganization extends Behavior
{
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => "setOrganization",
        ];
    }

    public function setOrganization($event)
    {
        /** @var Payment $payment */
        $payment = $event->sender;
        if ($payment->organization_id) {
            return true;
        }

        $organizationId = $payment->bill->clientAccount->contract->organization_id;
        if ($organizationId) {
            $payment->organization_id = $organizationId;
        }
    }

}
