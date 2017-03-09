<?php

namespace app\classes\behaviors;

use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\Bill;
use app\models\billing\Locks;
use app\models\ClientAccount;
use yii\base\Behavior;
use yii\base\Event;
use yii\db\ActiveRecord;


class SetBillPaymentOverdue extends Behavior
{
    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => "setBillOverdue",
        ];
    }

    /**
     * Устанавливает дату просрочки платежа
     */
    public function setBillOverdue()
    {
        /** @var Bill $bill */
        $bill = $this->owner;

        $tz = new \DateTimeZone($bill->clientAccount->timezone_name);

        $billPayOverdue = new \DateTime($bill->bill_date, $tz);
        $billPayOverdue->modify("+" . $bill->clientAccount->pay_bill_until_days . " days");

        $bill->pay_bill_until = $billPayOverdue->format(DateTimeZoneHelper::DATE_FORMAT);
    }
}
