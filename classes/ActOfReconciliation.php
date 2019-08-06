<?php

namespace app\classes;

use app\helpers\DateTimeZoneHelper;
use app\models\BalanceByMonth;
use app\models\ClientAccount;
use app\models\Country;
use app\models\Invoice;
use app\models\Payment;
use app\modules\uu\models\Bill as uuBill;
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
    public function getRevise(ClientAccount $account, $dateFrom, $dateTo, $startSaldo = 0)
    {
        $dateFrom = DateTimeZoneHelper::getDateTime($dateFrom, DateTimeZoneHelper::DATE_FORMAT, false);
        $dateTo = DateTimeZoneHelper::getDateTime($dateTo, DateTimeZoneHelper::DATE_FORMAT, false);
        if (!$dateFrom || !$dateTo) {
            throw new Exception('Заполните дату');
        }

        $result = [];
        $period = [
            'income_sum' => 0,
            'outcome_sum' => 0
        ];

        $dateFromFormated = (new \DateTimeImmutable($dateFrom))->format(DateTimeZoneHelper::DATE_FORMAT_EUROPE_DOTTED);
        $dateToFormated = (new \DateTimeImmutable($dateTo))->format(DateTimeZoneHelper::DATE_FORMAT_EUROPE_DOTTED);

        $result[] = [
            'type' => 'saldo',
            'date' => $dateFrom,
            'description' => 'Сальдо на ' . $dateFromFormated,
            'income_sum' => -$startSaldo > 0 ? -$startSaldo : 0,
            'outcome_sum' => $startSaldo > 0 ? $startSaldo : 0
        ];

        $paymentsQuery = Payment::find()
            ->alias('p')
            ->select([
                'p.id',
                'sum',
                'type' => new Expression('"payment"'),
                'payment_type' => 'type',
                'date' => 'payment_date',
                'number' => 'payment_no',
                'correction_idx' => new Expression('""'),
                'add_datetime' => 'add_date',
            ])
            ->where([
                'client_id' => $account->id,
                'currency' => $account->currency
            ])
            ->andWhere(['between', 'payment_date', $dateFrom, $dateTo]);


        $query = Invoice::find()
            ->alias('i')
            ->select([
                'i.id',
                'i.sum',
                'type' => new Expression('"invoice"'),
                'payment_type' => new Expression('""'),
                'i.date',
                'number',
                'correction_idx',
                'add_datetime' => 'add_date',
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
        $arr = (new Query())->from(['a' => $query])->orderBy(['date' => SORT_ASC, 'a.id' => SORT_ASC])->all();

        foreach ($arr as $item) {
            $isInvoice = $item['type'] == 'invoice';
            $date = (new \DateTimeImmutable($item['date']))->format(DateTimeZoneHelper::DATE_FORMAT_EUROPE_DOTTED);
            $sum = $isInvoice ? $item['sum'] : -$item['sum'];

            $description = $isInvoice
                ? 'Акт' . ' (' . $date . ', №' . $item['number'] . ')'
                : (
                ($item['payment_type'] == 'creditnote')
                    ? 'Кредит-нота от ' . $date
                    : 'Оплата' . ' (' . $date . ', №' . $item['number'] . ')'
                );

            $result[] = [
                    'id' => $item['id'],
                    'type' => $item['payment_type'] == 'creditnote' ? 'creditnote' : $item['type'],
                    'date' => $item['date'],
                    'number' => $item['number'],
                    'description' => $description,
                    'income_sum' => $sum > 0 ? $sum : '',
                    'outcome_sum' => $sum < 0 ? -$sum : '',
                ] + ($isInvoice ? ['correction_idx' => $item['correction_idx']] : ['add_datetime' => $item['add_datetime']]);

            $period[$isInvoice ? 'income_sum' : 'outcome_sum'] += $item['sum'];
        }

        $result[] = ['type' => 'period', 'description' => 'Обороты за период'] + $period;
        $ressaldo = $period['income_sum'] - $period['outcome_sum'] - $startSaldo;
        $result[] = [
            'type' => 'saldo',
            'date' => $dateTo,
            'description' => 'Сальдо на ' . $dateToFormated,
            'income_sum' => $ressaldo > 0 ? $ressaldo : 0,
            'outcome_sum' => -$ressaldo > 0 ? -$ressaldo : 0
        ];

        $deposits = \Yii::$app->db->createCommand("
        SELECT n.client_id,concat(n.bill_no,'-3') AS inv_no,n.bill_date,nb.bill_no,nb.item,nb.sum FROM newbills n
        JOIN newbill_lines nb ON n.bill_no = nb.bill_no
        WHERE client_id = $account->id AND nb.type = 'zalog'")->queryAll();

        $depositSum = 0;
        foreach ($deposits as $item) {
            $depositSum += $item['sum'];
        }

        $depositBalance = $depositSum + $ressaldo;

        return [
            'data' => $result,
            'deposit' => $deposits,
            'deposit_balance' => $depositBalance
        ];
    }

    public function getData(ClientAccount $account, $dateFrom, $dateTo, $isWithCorrection = true)
    {
        $dirtyData = $this->getRevise($account, $dateFrom, $dateTo);

        $data = array_reverse(
            array_filter($dirtyData['data'], function ($a) {
                return $a['type'] != 'saldo' && $a['type'] != 'period';
            })
        );

        // У клиентов вне россии стоит неправильная дата. Её надо брать из счета.
        if ($account->getUuCountryId() != Country::RUSSIA) {
            $data = array_map(function($value) use ($account) {
                if ($value['type'] == 'invoice') {
                    $invoice = Invoice::findOne(['id' => $value['id']]);
                    if ($invoice && $invoice->bill) {
                        $value['date'] = $invoice->bill->bill_date;
                    }
                }
                return $value;
            }, $data);
        }

        $result = [];
        $balance = $account->billingCounters->realtimeBalance;
        $accountingBalance = $account->balance;

        $diffBalance = $accountingBalance - $balance;

        $clientTimeZone = new \DateTimeZone($account->timezone_name);
        $utcTimeZone = new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC);

        $setDateTime = function ($dateTime, $isInTimeZoneClient = false) use ($clientTimeZone, $utcTimeZone) {
            return (new \DateTimeImmutable($dateTime, $isInTimeZoneClient ? $clientTimeZone : $utcTimeZone))
                ->setTimezone($clientTimeZone)
                ->format(DateTimeZoneHelper::DATETIME_FORMAT);
        };

        $result[] = [
            'type' => 'current_balance',
            'date' => date(DateTimeZoneHelper::DATE_FORMAT),
            'add_datetime' => $setDateTime('now'),
            'balance' => $balance,
            'description' => 'current_balance',
        ];

        $currentStatementSum = 0;
        if ($account->account_version == ClientAccount::VERSION_BILLER_UNIVERSAL) {
            $currentStatementSum = uuBill::getUnconvertedAccountEntries($account->id)->sum('price_with_vat') ?: 0;
        }

        $currentStatementSum += $diffBalance;

        $result[] = [
            'type' => 'current_statement',
            'date' => date(DateTimeZoneHelper::DATE_FORMAT),
            'add_datetime' => $setDateTime('now'),
            'income_sum' => (float)$currentStatementSum,
            'description' => 'current_statement',
        ];
        $balance += $currentStatementSum;

        $firstMonthDate = date(DateTimeZoneHelper::DATE_FORMAT, strtotime('first day of this month'));
        $result[] = [
            'type' => 'month',
            'date' => $firstMonthDate,
            'add_datetime' => $setDateTime($firstMonthDate, true),
            'balance' => $balance,
            'description' => 'month_balance',
        ];


        $findDate = null;
        foreach ($data as &$row) {
            $row['add_datetime'] = isset($row['add_datetime']) ? $setDateTime($row['add_datetime']) : $setDateTime($row['date'] . ' 00:00:00', true);

            if ($row['type'] == 'invoice') {
                $row['type'] = 'act';
            }

            if ($row['type'] == 'act') {
                $row['link'] = Encrypt::encodeArray([
                    'is_pdf' => 1,
                    'tpl1' => 1,
                    'client' => $account->id,
                    'invoice_id' => $row['id']
                ]);
            }
            unset($row['id']);

            $row['description'] = $row['type'];

            if (!$findDate) {
                $findDate = date('Y-m-d', strtotime('first day of this month', strtotime($row['date'])));
                $result[] = $row;
                $balance += $row['income_sum'] - $row['outcome_sum'];
                continue;
            }

            $date = $row['date'];
            while ($date <= $findDate) {
                $result[] = [
                    'type' => 'month',
                    'date' => $findDate,
                    'add_datetime' => $setDateTime($findDate, true),
                    'balance' => $balance,
                    'description' => 'month_balance',
                ];

                $findDate = date('Y-m-d', strtotime('first day of previous month', strtotime($findDate)));
            }
            $result[] = $row;
            $balance += $row['income_sum'] - $row['outcome_sum'];

        }
        unset($row);

        if ($findDate) {
            $result[] = [
                'type' => 'month',
                'date' => $findDate,
                'add_datetime' => $setDateTime($findDate, true),
                'balance' => $balance,
                'description' => 'month_balance',
            ];
        }

        if (!$isWithCorrection) {
            return $result;
        }

        return $this->makingAdjustments($account, $result);
    }

    protected function makingAdjustments(ClientAccount $account, $result)
    {
        /** @var BalanceByMonth $mBalance */
        $mBalance = BalanceByMonth::find()
            ->where(['account_id' => $account->id])
            ->orderBy(['year' => SORT_DESC, 'month' => SORT_DESC])
            ->one();

        if (!$mBalance) {
            return $result;
        }

        $date = (new \DateTimeImmutable())
            ->setTime(0, 0, 0)
            ->setDate($mBalance->year, $mBalance->month, 1)
            ->modify('+1 month')
            ->format(DateTimeZoneHelper::DATE_FORMAT);

        $result = array_reverse($result);

        $diffBalance = 0;
        foreach ($result as $value) {
            if ($value['type'] == 'month' && $value['date'] == $date) {
                $diffBalance = round($value['balance'] - $mBalance->balance, 2);
                break;
            }
        }

        if (abs($diffBalance) < 0.01) {
            return array_reverse($result);
        }

        foreach ($result as &$value) {
            if ($value['type'] == 'month') {
                $value['balance'] -= $diffBalance;
            } elseif ($value['type'] == 'current_statement') {
                $value['income_sum'] -= $diffBalance;
            }
        }

        return array_reverse($result);
    }

    /**
     * Сохраняем балансы в ЛС по месяцам
     */
    public function saveBalances()
    {
        $clientQuery = ClientAccount::find()->where(['is_active' => 1]);

        foreach ($clientQuery->each() as $account) {
            $data = $this->getData($account, '2019-01-01', (date('Y') + 1) . '-01-01', false);

            $data = array_filter($data, function ($row) {
                return $row['type'] == 'month';
            });

            $data = array_map(function ($value) use ($account) {
                $dateAr = explode('-', $value['date']);
                return [$account->id, $dateAr[0], $dateAr[1], $value['balance']];
            }, $data);

            BalanceByMonth::getDb()->transaction(function ($db) use ($account, $data) {
                /** @var Connection $db */
                $db->createCommand()->delete(BalanceByMonth::tableName(), ['account_id' => $account->id])->execute();
                $db->createCommand()->batchInsert(BalanceByMonth::tableName(), ['account_id', 'year', 'month', 'balance'], $data)->execute();
            });
        }
    }
}
