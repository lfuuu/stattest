<?php

namespace app\classes\documents;

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

        /** @var AccountEntry $uuLine */
        foreach ($query->each() as $uuLine) {
            $lines[] = [
                'item' => $uuLine->getFullName($this->getLanguage()),
                'date_from' => '',
                'amount' => 1,
                'price' => $uuLine->price_with_vat,
                'sum' => $uuLine->price_with_vat,
            ];
        }

        $balance = $this->clientAccount->billingCounters->realtimeBalance;
        $accountingBalance = $this->clientAccount->balance;

        $diffBalance = $accountingBalance - $balance;

        if ($diffBalance) {
            $lines[] = [
                'item' => \Yii::t('models/' . ResourceModel::tableName(), 'Resource #' . ResourceModel::ID_VOIP_PACKAGE_CALLS, [], $this->getLanguage()),
                'date_from' => '',
                'amount' => 1,
                'price' => $diffBalance,
                'sum' => $diffBalance,
            ];
        }


        $this->lines = $lines;

        return $this;
    }
}
