<?php

namespace app\classes\behaviors;

use app\classes\Assert;
use app\models\EventQueue;
use app\models\Invoice;
use yii\base\Behavior;
use yii\base\ModelEvent;
use yii\db\ActiveRecord;
use yii\db\AfterSaveEvent;


class InvoiceGeneratePdf extends Behavior
{
    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => "doCheck",
            ActiveRecord::EVENT_BEFORE_UPDATE => "doCheck",
        ];
    }

    public function doCheck($event)
    {
        /** @var Invoice $invoice */
        $invoice = $event->sender;

        $isNewRecord = $event instanceof AfterSaveEvent;

        // новая с номером
        // или номер установлен при обновлении
        if (
            ($isNewRecord && $invoice->number)
            || (!$isNewRecord && $invoice->number && !$invoice->getOldAttribute('number'))
        ) {
            EventQueue::go(EventQueue::INVOICE_GENERATE_PDF, ['id' => $invoice->id]);
        }
    }

    public static function generate($invoiceId)
    {
        $invoice = Invoice::findOne(['id' => $invoiceId]);

        Assert::isObject($invoice);

        $invoice->generatePdfFile();
    }
}