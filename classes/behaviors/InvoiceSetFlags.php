<?php

namespace app\classes\behaviors;

use app\models\BillDocument;
use app\models\Invoice;
use yii\base\Behavior;
use yii\base\ModelEvent;
use yii\db\ActiveRecord;


class InvoiceSetFlags extends Behavior
{
    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => "setFlags",
        ];
    }

    public function setFlags(ModelEvent $event)
    {
        /** @var Invoice $invoice */
        $invoice = $event->sender;

        $invoiceDate = new \DateTimeImmutable($invoice->date);

        $invoice->is_invoice = (bool)BillDocument::dao()->me()->_isSF($invoice->bill->client_id, BillDocument::TYPE_INVOICE, $invoiceDate->getTimestamp(), $invoice->type_id);
        $invoice->is_act = (bool)BillDocument::dao()->me()->_isSF($invoice->bill->client_id, BillDocument::TYPE_AKT, $invoiceDate->getTimestamp());
    }
}