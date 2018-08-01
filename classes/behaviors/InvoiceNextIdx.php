<?php

namespace app\classes\behaviors;

use app\helpers\DateTimeZoneHelper;
use app\models\Invoice;
use yii\base\Behavior;
use yii\base\ModelEvent;
use yii\db\ActiveRecord;


class InvoiceNextIdx extends Behavior
{
    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => "setNextIdx",
        ];
    }

    public function setNextIdx(ModelEvent $event)
    {
        /** @var Invoice $invoice */
        $invoice = $event->sender;

        if (!$invoice->type_id || !$invoice->bill_no || !$invoice->date) {
            throw new \BadFunctionCallException('Не заданы обязательные параметры для генерации номера с/ф');
        }

        $maxIdx = Invoice::find()->where([
            'organization_id' => $invoice->bill->organization_id,
        ])->andWhere([
            'between',
            'date',
            (new \DateTimeImmutable($invoice->date))->modify('first day of this month')->format(DateTimeZoneHelper::DATE_FORMAT),
            (new \DateTimeImmutable($invoice->date))->modify('last day of this month')->format(DateTimeZoneHelper::DATE_FORMAT)
        ])->max('idx');

        if (!$maxIdx) {
            $maxIdx = 0;
        }

        $invoice->idx = $maxIdx + 1;
        $invoice->organization_id = $invoice->bill->organization_id;
        $invoice->number = $invoice->type_id . (new \DateTimeImmutable($invoice->date))->format('ym') . sprintf("%02d", $invoice->organization_id) . '-' . sprintf("%04d", $invoice->idx);
    }
}