<?php

namespace app\classes\behaviors;

use app\helpers\DateTimeZoneHelper;
use app\models\Country;
use app\models\Currency;
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
            ActiveRecord::EVENT_BEFORE_UPDATE => "setNextIdx",
        ];
    }

    public function setNextIdx(ModelEvent $event)
    {
        /** @var Invoice $invoice */
        $invoice = $event->sender;

        if (!$invoice->type_id || !$invoice->bill_no || !$invoice->date) {
            throw new \BadFunctionCallException('Не заданы обязательные параметры для генерации номера с/ф');
        }

        $startDate = (new \DateTimeImmutable($invoice->date))->modify('first day of this month');
        $endDate = (new \DateTimeImmutable($invoice->date))->modify('last day of this month');

        // с 1 января, для всех не Россиских компаний номерация с/ф сквозная в течении года
        if (
            $invoice->date >= Invoice::DATE_NO_RUSSIAN_ACCOUNTING
            && $invoice->bill->clientAccount->currency != Currency::RUB
        ) {
            $startDate = (new \DateTimeImmutable($invoice->date))
                ->setDate($startDate->format('Y'), 1, 1);

            $endDate = $startDate->setDate($startDate->format('Y'), 12, 31);
        }

        $isSetId = $event->name == ActiveRecord::EVENT_BEFORE_INSERT && $invoice->isSetDraft !== true
                || $event->name == ActiveRecord::EVENT_BEFORE_UPDATE && $invoice->isSetDraft === false;

        !$invoice->organization_id && $invoice->organization_id = $invoice->bill->organization_id;

        if ($isSetId && !$invoice->idx) {
            $maxIdx = Invoice::find()->where([
                'organization_id' => $invoice->organization_id,
            ])->andWhere([
                'between',
                'date',
                $startDate->format(DateTimeZoneHelper::DATE_FORMAT),
                $endDate->format(DateTimeZoneHelper::DATE_FORMAT)
            ])->max('idx');

            if (!$maxIdx) {
                $maxIdx = 0;
            }

            $invoice->idx = $maxIdx + 1;
            $invoice->number = $invoice->type_id .
                (new \DateTimeImmutable($invoice->date))->format('ym') .
                sprintf("%02d", $invoice->organization_id) . '-' .
                sprintf("%04d", $invoice->idx);
        }

        !$invoice->add_date && $invoice->add_date = (new \DateTime('now', new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_MOSCOW)))->format(DateTimeZoneHelper::DATETIME_FORMAT);
    }
}