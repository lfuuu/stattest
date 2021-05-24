<?php

namespace app\classes;

use app\classes\documents\DocumentReport;
use app\helpers\DateTimeZoneHelper;
use app\models\BalanceByMonth;
use app\models\Bill;
use app\models\BillLine;
use app\models\ClientAccount;
use app\models\Country;
use app\models\Invoice;
use app\models\OperationType;
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
     * @param bool $sortByBillDate
     * @param bool $isWithBills
     * @return array
     * @throws Exception
     */
    public function getRevise(ClientAccount $account, $dateFrom, $dateTo, $startSaldo = 0, $sortByBillDate = false, $isWithBills = false)
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
                'bill_date' => 'payment_date',
                'add_datetime' => 'add_date',
            ])
            ->where([
                'client_id' => $account->id,
                'currency' => $account->currency
            ])
            ->andWhere(['between', 'payment_date', $dateFrom, $dateTo]);

        $billQuery = Bill::find()
            ->alias('b')
            ->select([
                'b.id',
                'sum',
                'type' => new Expression('"bill"'),
                'payment_type' => new Expression('""'),
                'date' => 'bill_date',
                'number' => 'bill_no',
                'correction_idx' => new Expression('""'),
                'bill_date' => 'bill_date',
                'add_datetime' => 'bill_date',
            ])
            ->where([
                'client_id' => $account->id,
                'currency' => $account->currency,
                'operation_type_id' => OperationType::ID_PRICE,
            ])
            ->andWhere(['>', 'sum', 0])
            ->andWhere(['between', 'bill_date', $dateFrom, $dateTo]);


        $query = Invoice::find()
            ->alias('i')
            ->select([
                'i.id',
                'i.sum',
                'type' => new Expression('"invoice"'),
                'payment_type' => 'type_id',
                'i.date',
                'number' => new Expression('if (i.type_id = 3, i.bill_no, i.number)'),
                'correction_idx',
                'bill_date',
                'add_datetime' => 'add_date',
            ])
            ->joinWith('bill')
            ->where([
                'client_id' => $account->id,
                'currency' => $account->currency
            ])
            ->andWhere(['!=', 'i.sum', 0])
            ->andWhere(['NOT', ['i.number' => null]])
            ->andWhere(['NOT', ['i.type_id' => Invoice::TYPE_PREPAID]])
            ->andWhere(['between', 'date', $dateFrom, $dateTo])
            ->union('SELECT
  b.id,
  -if(coalesce(ext_vat, 0) > 0,
     ext_vat + ext_sum_without_vat,
     round(((SELECT max(tax_rate)
             FROM newbill_lines l
             WHERE l.bill_no = b.bill_no) + 100) * ext_sum_without_vat / 100, 2)
  )                                         AS sum,
  \'invoice\'                                 AS type,
  \'\'                                        AS payment_type,
  STR_TO_DATE(ext_invoice_date, \'%d-%m-%Y\') AS date,
  ex.ext_invoice_no                         AS number,
  \'\'                                        AS correction_idx,
  b.bill_date                               AS bill_date,
  b.bill_date                               AS add_datetime
FROM newbills b
  JOIN `newbills_external` ex USING (bill_no)
WHERE b.client_id = ' . $account->id . '
      AND b.currency = \'' . $account->currency . '\'
      AND b.sum < 0
      AND length(trim(coalesce(ext_invoice_date))) > 0
      AND trim(coalesce(ext_invoice_no, \'\')) != \'\'
      AND coalesce(ext_sum_without_vat, 0) > 0
      AND STR_TO_DATE(ext_invoice_date, \'%d-%m-%Y\') BETWEEN \'' . $dateFrom . '\' AND \'' . $dateTo . '\'', true);

        $query->union($paymentsQuery, true);
        $isWithBills && $query->union($billQuery, true);


        // сортировка работает отдельно от union
        $arr = (new Query())
            ->from(['a' => $query])
            ->orderBy([($sortByBillDate ? 'bill_date' : 'date') => SORT_ASC, 'a.id' => SORT_ASC])
            ->all();

        foreach ($arr as $item) {
            $isInvoice = $item['type'] == 'invoice';
            $date = (new \DateTimeImmutable($item['date']))->format(DateTimeZoneHelper::DATE_FORMAT_EUROPE_DOTTED);
            $sum = $isInvoice ? $item['sum'] : -$item['sum'];

            $description = $isInvoice
                ? ($item['payment_type'] == Invoice::TYPE_GOOD ? 'Накладная' : 'Акт') . ' (' . $date . ', №' . $item['number'] . ')'
                : (
                ($item['payment_type'] == 'creditnote')
                    ? 'Кредит-нота от ' . $date
                    : 'Оплата' . ' (' . $date . ', №' . $item['number'] . ')'
                );

            if ($item['type'] == 'bill') {
                // select count(*) from newbill_lines where bill_no = '202012-018854' and id_service is not null
                $isServiceBill = BillLine::find()->where(['bill_no' => $item['number']])->andWhere(['NOT', ['id_service' => null]])->exists();
                $isInvoiceCreated = Invoice::find()->where(['bill_no' => $item['number']])->exists();
            }

            $result[] = [
                    'id' => $item['id'],
                    'type' => $item['payment_type'] == 'creditnote' ? 'creditnote' : $item['type'],
                    'date' => $item['date'],
                    'bill_date' => $item['bill_date'],
                    'number' => $item['number'],
                    'description' => $description,
                    'income_sum' => $sum > 0 ? $sum : '',
                    'outcome_sum' => $sum < 0 ? -$sum : '',
                ] + ($isInvoice ? ['correction_idx' => $item['correction_idx']] : ['add_datetime' => $item['add_datetime']])
            + ($item['type'] == 'bill' ? ['is_invoice_created' => $isServiceBill && $isInvoiceCreated] : []);

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
        $isNotRussia = $account->getUuCountryId() != Country::RUSSIA;
        if (!$dateFrom) {
            $dateFrom = $isNotRussia ? '2019-07-31' : '2019-01-01';
        }

        $dirtyData = $this->getRevise($account, $dateFrom, $dateTo, 0, $isNotRussia, !$isNotRussia);

        $data = array_reverse(
            array_filter($dirtyData['data'], function ($a) {
                return $a['type'] != 'saldo' && $a['type'] != 'period';
            })
        );

        // У клиентов вне России стоит неправильная дата. Её надо брать из счета.
        if ($isNotRussia) {
            $data =
                array_filter(
                    array_map(
                        function ($value) use ($account) {
                            if ($value['type'] == 'invoice') {
                                $value['date'] = $value['bill_date'];
                            }
                            return $value;
                        }, $data
                    ),
                    function ($value) use ($dateFrom) {
                        return $value['bill_date'] >= $dateFrom;
                    }
                );
        }

        $result = [];
        $balance = $account->billingCounters->realtimeBalance;
        $accountingBalance = $account->balance;

        $billBalanceDiff = $this->getBillBalanceDiff($account->id);

        if (abs($billBalanceDiff) < 0.05) {
            $accountingBalance -= $billBalanceDiff;
        }

        $diffBalance = $accountingBalance - $balance;

        $clientTimeZone = new \DateTimeZone($account->timezone_name);
        $utcTimeZone = new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC);

        $setDateTime = function ($dateTime, $isInTimeZoneClient = false) use ($clientTimeZone, $utcTimeZone) {
            return (new \DateTimeImmutable($dateTime, $isInTimeZoneClient ? $clientTimeZone : $utcTimeZone))
                ->setTimezone($clientTimeZone)
                ->format(DateTimeZoneHelper::DATETIME_FORMAT);
        };

        $d = array_filter($data, function ($d) {
            return $d['type'] == 'bill' && !$d['is_invoice_created'];
        });

        $sumNotInInvoice = 0;
        array_walk($d, function($v) use (&$sumNotInInvoice) {
            $sumNotInInvoice += (float)$v['outcome_sum'] - (float)$v['income_sum'];
        });

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

        $currentStatementSum += $diffBalance + $sumNotInInvoice;

        $result[] = [
            'type' => 'current_statement',
            'date' => date(DateTimeZoneHelper::DATE_FORMAT),
            'add_datetime' => $setDateTime('now'),
            'income_sum' => (float)$currentStatementSum,
            'description' => 'current_statement',
            'link' => Encrypt::encodeArray([
                'client' => $account->id,
                'doc_type' => DocumentReport::DOC_TYPE_CURRENT_STATEMENT,
                'tpl1' => 2,
                'is_pdf' => 1,
            ])
        ];
        $balance += $currentStatementSum;

        $firstMonthDate = date(DateTimeZoneHelper::DATE_FORMAT, strtotime('first day of this month'));
        $firstDataDate = $data ? reset($data)['date'] : false;

        if ($firstDataDate && $firstDataDate < $firstMonthDate) {
            $result[] = [
                'type' => 'month',
                'date' => $firstMonthDate,
                'add_datetime' => $setDateTime($firstMonthDate, true),
                'balance' => $balance,
                'description' => 'month_balance',
            ];
        }

        $findDate = null;
        foreach ($data as $idx => &$row) {
            $row['add_datetime'] = isset($row['add_datetime'])
                ? $setDateTime($row['add_datetime'])
                : $setDateTime($row['date'] . ' 00:00:00', true);

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
            } elseif ($row['type'] == 'bill' && !$isNotRussia) {
                if (!$row['is_invoice_created']) {
                    $row['outcome_sum'] = 0;
                }
                $row['link'] = Encrypt::encodeArray([
                    'bill' => $row['number'],
                    'object' => 'bill-2-RUB',
                    'client' => $account->id,
                    'is_pdf' => 1
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
            // месячная линия,  должна быть после всех документов в месяце
            if (
                $date < $findDate && (
                    !isset($data[$idx + 1])
                    || (
                        isset($data[$idx + 1])
                        && $data[$idx + 1]['date'] < $findDate
                    )
                )
            ) {
                while ($date < $findDate) {
                    $result[] = [
                        'type' => 'month',
                        'date' => $findDate,
                        'add_datetime' => $setDateTime($findDate, true),
                        'balance' => $balance,
                        'description' => 'month_balance',
                    ];

                    $findDate = date('Y-m-d', strtotime('first day of previous month', strtotime($findDate)));
                }
            }
            $result[] = $row;
            if ($row['type'] != 'bill') {
                $balance += $row['income_sum'] - $row['outcome_sum'];
            }

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
//            ->modify('+1 month')
            ->format(DateTimeZoneHelper::DATE_FORMAT);

        $result = array_reverse($result);

        $diffBalance = 0;

        $isStart = false;
        $r = [];
        $submitted = 0;
        $currentBalance = null;
        foreach ($result as $value) {
            if ($value['type'] == 'month' && $value['date'] == $date) {
                $diffBalance = round($value['balance'] - $mBalance->balance, 2);
                $isStart = true;
                continue;
//                break;
            }

            if ($isStart && in_array($value['type'], ['act', 'payment'])) {
                if (isset($value['outcome_sum']) && abs($value['outcome_sum']) > 0) {
                    $submitted += $value['outcome_sum'];
                }
                if (isset($value['income_sum']) && abs($value['income_sum']) > 0) {
                    $submitted -= $value['income_sum'];
                }

                $r[] = $value;
            }

            if ($value['type'] == 'current_balance') {
                $currentBalance = $value['balance'];
            }
        }

        if (abs($diffBalance) < 0.01) {
            return array_reverse($result);
        }

        foreach ($result as &$value) {
            if ($value['type'] == 'month') {
                $value['balance'] -= $diffBalance;
            } elseif ($value['type'] == 'current_statement') {
//                $value['income_sum'] -= $diffBalance + $d;
                unset($value['income_sum'], $value['outcome_sum']);

                $v = $mBalance->balance - $currentBalance + $submitted;

                if ($v >= 0) {
                    $value['income_sum'] = $v;
                } else {
                    $value['outcome_sum'] = $v;
                }
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
            $data = $this->getData($account, null, (date('Y') + 1) . '-01-01', false);

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

    public function getBillBalanceDiff($accountId)
    {
        $billSum = round(Bill::find()->where(['client_id' => $accountId])->sum('sum'), 2);
        $uBillSum = round(\app\modules\uu\models\Bill::find()->where(['client_account_id' => $accountId])->sum('price'), 2);

        return $billSum - $uBillSum;

    }
}
