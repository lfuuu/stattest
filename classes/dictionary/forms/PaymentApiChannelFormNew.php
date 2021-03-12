<?php

namespace app\classes\dictionary\forms;

use app\classes\Utils;
use app\models\PaymentApiChannel;

class PaymentApiChannelFormNew extends PaymentApiChannelForm
{
    /**
     * @return PaymentApiChannel
     */
    public function getFormModel()
    {
        $payment = new PaymentApiChannel();
        $payment->access_token = Utils::gen_password(32);
        return $payment;
    }
}