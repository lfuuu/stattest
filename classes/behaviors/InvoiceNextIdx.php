<?php

namespace app\classes\behaviors;

use app\helpers\DateTimeZoneHelper;
use app\models\Country;
use app\models\Currency;
use app\models\Invoice;
use app\models\Organization;
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

        $organization = null;

        // с 1 января, для всех не Россиских компаний номерация с/ф сквозная в течении года
        if (
            $invoice->date >= Invoice::DATE_NO_RUSSIAN_ACCOUNTING
        ) {
            $organization = $invoice->calculateOrganization();
            if ($organization->invoice_counter_range_id == Organization::INVOICE_COUNTER_RANGE_ID_YEAR) {
                $startDate = (new \DateTimeImmutable($invoice->date))
                    ->setDate($startDate->format('Y'), 1, 1);

                $endDate = $startDate->setDate($startDate->format('Y'), 12, 31);
            }
        }

        $isSetId = $event->name == ActiveRecord::EVENT_BEFORE_INSERT && $invoice->isSetDraft !== true
            || $event->name == ActiveRecord::EVENT_BEFORE_UPDATE && $invoice->isSetDraft === false;

        if (!$invoice->organization_id) {
            if (!$organization) {
                $organization = $invoice->calculateOrganization();
            }
            $invoice->organization_id = $organization->organization_id;
        }

        if ($isSetId && !$invoice->idx) {
            $isNeedNewNumber = true;
            if ($invoice->bill->clientAccount->contragent->country_id == Country::RUSSIA) {
                /** @var Invoice $prevInvoice */
                $prevInvoice = Invoice::find()
                    ->where([
                        'bill_no' => $invoice->bill_no,
                        'type_id' => $invoice->type_id,
                    ])
                    ->andWhere(['NOT', ['number' => null]])
                    ->orderBy(['id' => SORT_DESC])
                    ->one();

                if ($prevInvoice) {
                    $invoice->idx = $prevInvoice->idx;
                    $invoice->number = $prevInvoice->number;
                    $isNeedNewNumber = false;
                }

            }

            if ($isNeedNewNumber) {
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
        }

        if (
            $isSetId
            && !$invoice->is_reversal
            && !$invoice->correction_idx
            && $invoice->bill->clientAccount->contragent->country_id == Country::RUSSIA
        ) {
            $count = Invoice::find()->where([
                'bill_no' => $invoice->bill_no,
                'is_reversal' => 0,
                'type_id' => $invoice->type_id
            ])->count();

            if ($count > 1) {
                $invoice->correction_idx = $count - 1;
            }
        }

        !$invoice->add_date && $invoice->add_date = (new \DateTime('now', new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_MOSCOW)))->format(DateTimeZoneHelper::DATETIME_FORMAT);
    }
}