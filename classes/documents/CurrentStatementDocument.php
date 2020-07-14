<?php

namespace app\classes\documents;

use app\classes\ActOfReconciliation;
use app\helpers\DateTimeZoneHelper;
use app\models\Currency;
use app\models\Language;
use app\modules\uu\models\AccountEntry;
use app\modules\uu\models\Bill;
use app\modules\uu\models\ResourceModel;

class CurrentStatementDocument extends DocumentReport
{
    const DOC_TYPE_BILL = 'current_statement';

    public $isMultiCurrencyDocument = true;

    public function getLanguage()
    {
        return Language::LANGUAGE_RUSSIAN;
    }

    public function getCurrency()
    {
        return $this->bill ? $this->bill->currency : Currency::USD;
    }

    public function getDocType()
    {
        return self::DOC_TYPE_CURRENT_STATEMENT;
    }

    public function getName()
    {
        return 'Current Statement';
    }

    protected function fetchLines()
    {
        $lines = [];

        $query = Bill::getUnconvertedAccountEntries($this->clientAccount->id);

        $setSum = 0;

        /** @var AccountEntry $uuLine */
        foreach ($query->each() as $uuLine) {
            $lines[] = [
                'item' => $uuLine->getFullName($this->getLanguage()),
                'date_from' => '',
                'amount' => 1,
                'price' => $uuLine->price_with_vat,
                'sum' => $uuLine->price_with_vat,
            ];

            $setSum += $uuLine->price_with_vat;
        }

        $balance = $this->clientAccount->billingCounters->realtimeBalance;
        $accountingBalance = $this->clientAccount->balance;

        $diffBalance = $accountingBalance - $balance;

        $setSum += $diffBalance;

        if ($diffBalance) {
            $lines[] = [
                'item' => \Yii::t('models/' . ResourceModel::tableName(), 'Resource #' . ResourceModel::ID_VOIP_PACKAGE_CALLS, [], $this->getLanguage()),
                'date_from' => '',
                'amount' => 1,
                'price' => $diffBalance,
                'sum' => $diffBalance,
            ];
        }

        $data = ActOfReconciliation::me()->getData(
            $this->clientAccount,
            null,
            (new \DateTimeImmutable('now'))
                ->modify('last day of this month')
                ->format(DateTimeZoneHelper::DATE_FORMAT)
        );

        $data = array_filter($data, function ($v) {
            return $v['type'] == 'current_statement';
        });

        if ($data) {
            $data = reset($data);

            $sum = -(-$data['income_sum'] ?? 0 + $data['outcome_sum'] ?? 0);

            if (abs(abs($sum) - abs($setSum)) > 0.01) {
                $lines[] = [
                    'item' => $lineItem = \Yii::t(
                        'biller',
                        'correct_sum',
                        [],
                        $this->getLanguage()
                    ),
                    'date_from' => '',
                    'amount' => 1,
                    'price' => $sum - $setSum,
                    'sum' => $sum - $setSum,
                ];
            }
        }


        $this->lines = $lines;

        return $this;
    }
}
