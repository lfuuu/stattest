<?php

namespace app\classes\behaviors;

use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\base\ModelEvent;
use app\helpers\DateTimeZoneHelper;
use app\models\Bill;

class SetBillPaymentDate extends Behavior
{
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => "insert",
            ActiveRecord::EVENT_BEFORE_UPDATE => "update",
        ];
    }

    /**
     * При создании счета и если счет оплачен, устанавливаем дату оплаты
     *
     * @param ModelEvent $event
     */
    public function insert(ModelEvent $event)
    {
        if ($event->sender instanceof Bill) {
            $bill = $event->sender;
            if ($bill->is_payed == Bill::STATUS_IS_PAID) {
                $bill->payment_date = date(DateTimeZoneHelper::DATE_FORMAT);
            }
        }
    }

    /**
     * При обновлении счета и если он оплачен, устанавливаем дату оплаты
     *
     * @param ModelEvent $event
     */
    public function update(ModelEvent $event)
    {
        if ($event->sender instanceof Bill) {
            $bill = $event->sender;

            if ($bill->payment_date) {
                return;
            }

            if (
                $bill->isAttributeChanged('is_payed') &&
                $bill->is_payed != $bill->getOldAttribute('is_payed') &&
                $bill->is_payed == Bill::STATUS_IS_PAID
            ) {
                $bill->payment_date = date(DateTimeZoneHelper::DATE_FORMAT);
            }
        }
    }
}