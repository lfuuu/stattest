<?php

namespace app\classes;

use app\helpers\DateTimeZoneHelper;
use app\models\Bill;
use app\models\ClientAccount;
use app\models\Invoice;
use app\models\Payment;
use app\models\Saldo;
use Exception;
use yii\db\Expression;
use yii\db\Query;

class ActOfReconciliation extends Singleton
{
    /**
     * @param ClientAccount $account
     * @param string $dateFrom
     * @param string $dateTo
     * @param int $startSaldo
     * @return array
     * @throws Exception
     */
    public function getRevise($account, $dateFrom, $dateTo, $startSaldo = 0)
    {
        $dateFrom = DateTimeZoneHelper::getDateTime($dateFrom, DateTimeZoneHelper::DATE_FORMAT, false);
        $dateTo = DateTimeZoneHelper::getDateTime($dateTo, DateTimeZoneHelper::DATE_FORMAT, false);
        if (!$dateFrom || !$dateTo) {
            throw new Exception('Заполните дату');
        }

        $result = [];
        $period = [];

        $dateFromFormated = (new \DateTimeImmutable($dateFrom))->format(DateTimeZoneHelper::DATE_FORMAT_EUROPE_DOTTED);
        $dateToFormated = (new \DateTimeImmutable($dateTo))->format(DateTimeZoneHelper::DATE_FORMAT_EUROPE_DOTTED);

        $result[] = [
            'type' => 'saldo',
            'description' => 'Сальдо на ' . $dateFromFormated,
            'income_sum' => -$startSaldo > 0 ? -$startSaldo : 0,
            'outcome_sum' => $startSaldo > 0 ? $startSaldo : 0
        ];

        $paymentsQuery = Payment::find()
            ->select([
                'sum',
                'type' => new Expression('"payment"'),
                'date' => 'payment_date',
                'number' => 'payment_no',
            ])
            ->where([
                'client_id' => $account->id,
                'currency' => $account->currency
            ])
            ->andWhere(['between', 'payment_date', $dateFrom, $dateTo]);


        $query = Invoice::find()
            ->alias('i')
            ->select([
                'i.sum',
                'type' => new Expression('"invoice"'),
                'i.date',
                'number'
            ])
            ->joinWith('bill')
            ->where([
                'client_id' => $account->id,
                'currency' => $account->currency
            ])
            ->andWhere(['!=', 'i.sum', 0])
            ->andWhere(['NOT', ['i.number' => null]])
            ->andWhere(['between', 'date', $dateFrom, $dateTo])
            ->union($paymentsQuery, true);

        // сортировка работает отдельно от union
        $arr = (new Query())->from(['a' => $query])->orderBy(['date' => SORT_ASC])->all();

        foreach ($arr as $item) {
            $isInvoice = $item['type'] == 'invoice';
            $date = (new \DateTimeImmutable($item['date']))->format(DateTimeZoneHelper::DATE_FORMAT_EUROPE_DOTTED);
            $sum = number_format($item['sum'], 2, ',', ' ');
            $result[] = [
                'income_sum' => $isInvoice ? $sum : '',
                'outcome_sum' => !$isInvoice ? $sum : '',
                'type' => $item['type'],
                'description' => ($isInvoice ? 'Акт' : 'Оплата') . ' (' . $date . ', №' . $item['number'] . ')'
            ];
            $period[$isInvoice ? 'income_sum' : 'outcome_sum'] += $item['sum'];
        }

        $result[] = ['type' => 'period', 'description' => 'Обороты за период'] + $period;
        $ressaldo = $period['income_sum'] - $period['outcome_sum'] - $startSaldo;
        $result[] = [
            'type' => 'saldo',
            'description' => 'Сальдо на ' . $dateToFormated,
            'income_sum' => $ressaldo > 0 ? $ressaldo : 0,
            'outcome_sum' => -$ressaldo > 0 ? -$ressaldo : 0
        ];

        return $result;
    }

}
