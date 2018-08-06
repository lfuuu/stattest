<?php

namespace app\classes\behaviors;

use app\models\Bill;
use app\models\Invoice;
use yii\base\Behavior;
use yii\base\ModelEvent;
use yii\db\ActiveRecord;


class BillInvoiceReversal extends Behavior
{
    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_DELETE => "toReversal",
        ];
    }

    public function toReversal(ModelEvent $event)
    {
        /** @var Bill $bill */
        $bill = $event->sender;

        Bill::dao()->invoiceReversal($bill);
    }
}