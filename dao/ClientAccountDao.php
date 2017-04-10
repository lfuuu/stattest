<?php
namespace app\dao;

use app\modules\uu\tarificator\RealtimeBalanceTarificator;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\Business;
use app\models\ClientContract;
use app\models\ClientContractComment;
use app\models\ClientContragent;
use app\models\Param;
use Yii;
use app\classes\Assert;
use app\classes\Singleton;
use app\classes\api\ApiPhone;
use app\models\Bill;
use app\models\ClientAccount;
use app\models\GoodsIncomeOrder;
use app\models\PaymentOrder;
use app\models\Saldo;
use DateTime;
use DateTimeZone;
use yii\db\Query;

/**
 * @method static ClientAccountDao me($args = null)
 */
class ClientAccountDao extends Singleton
{

    private $_voipNumbers;

    /**
     * @param ClientAccount $clientAccount
     * @return string
     */
    public function getLastBillDate(ClientAccount $clientAccount)
    {
        $billDate = ClientAccount::getDb()->createCommand('
                select max(b.bill_date)
                from newbills b, newbill_lines bl
                where
                        b.client_id=:clientAccountId
                    and day(b.bill_date) = 1
                    and b.bill_no=bl.bill_no
                    and bl.id_service > 0
                    and biller_version = :billerVersion
            ',
            [
                ':clientAccountId' => $clientAccount->id,
                ':billerVersion' => ClientAccount::VERSION_BILLER_USAGE
            ]
        )->queryScalar();

        if (!$billDate) {
            $billDate = '2000-01-01';
        }

        $billDate = new DateTime($billDate, $clientAccount->timezone);
        $billDate->setTimezone(new DateTimeZone('UTC'));

        return $billDate->format(DateTimeZoneHelper::DATETIME_FORMAT);
    }

    /**
     * @param ClientAccount $clientAccount
     * @return false|null|string
     */
    public function getLastPayedBillMonth(ClientAccount $clientAccount)
    {
        return ClientAccount::getDb()->createCommand('
                select b.bill_date - interval day(b.bill_date)-1 day
                from newbills b left join newbill_lines bl on b.bill_no=bl.bill_no
                where b.client_id=:clientAccountId and b.is_payed=1 and bl.id_service > 0 and biller_version = :billerVersion
                group by b.bill_date
                order by b.bill_date desc
                limit 1
            ',
            [
                ':clientAccountId' => $clientAccount->id,
                ':billerVersion' => ClientAccount::VERSION_BILLER_USAGE
            ]
        )->queryScalar();
    }

    /**
     * @param int $clientAccountId
     * @param string $search
     * @return Query
     */
    public function clientAccountSearch($clientAccountId, $search)
    {
        return (new Query)
            ->select(['client.id', 'contragent.name'])
            ->from(['client' => ClientAccount::tableName()])
            ->innerJoin(['contract' => ClientContract::tableName()], 'contract.id = client.contract_id')
            ->innerJoin(['contragent' => ClientContragent::tableName()], 'contragent.id = contract.contragent_id')
            ->where(['!=', 'client.id', (int)$clientAccountId])
            ->andWhere([
                'OR',
                ['LIKE', 'client.client', $search],
                ['client.id' => $search],
                ['LIKE', 'contragent.name', $search],
            ])
            ->orderBy([
                'contragent.name' => SORT_DESC,
                'client.id' => SORT_DESC,
            ])
            ->limit(10);
    }

    /**
     * Обновление баланса ЛС
     *
     * @param int $clientAccountId
     * @param bool $isForce
     */
    public function updateBalance($clientAccountId, $isForce = true)
    {
        if (!$isForce && Param::findOne(Param::DISABLING_RECALCULATION_BALANCE_WHEN_EDIT_BILL)) {
            return;
        }

        if ($clientAccountId instanceof ClientAccount) {
            $clientAccount = $clientAccountId;
        } else {
            $clientAccount = ClientAccount::findOne(['id' => $clientAccountId]);
        }

        Assert::isObject($clientAccount);

        if ($clientAccount->account_version == ClientAccount::VERSION_BILLER_UNIVERSAL) {
            (new RealtimeBalanceTarificator)->tarificate($clientAccount->id);
            return;
        }

        $saldo = $this->_getSaldo($clientAccount);

        $R1 = $this->_enumBillsFullSum($clientAccount, $saldo['ts']);
        $R2 = $this->_enumPayments($clientAccount, $saldo['ts']);

        $sum = -$saldo['saldo'];

        $balance = 0;

        if ($sum > 0) {
            array_unshift($R2, [
                'id' => '0',
                'client_id' => $clientAccount->id,
                'payment_no' => 0,
                'bill_no' => 'saldo',
                'bill_vis_no' => 'saldo',
                'payment_date' => $saldo['ts'],
                'oper_date' => $saldo['ts'],
                'comment' => '',
                'add_date' => $saldo['ts'],
                'add_user' => 0,
                'sum' => $sum,
                'is_billpay' => 0,
            ]);
        } elseif ($sum < 0) {
            array_unshift($R1, [
                'bill_no' => 'saldo',
                'is_payed' => 1,
                'sum' => -$sum,
                'new_is_payed' => 0,
            ]);
        }

        $paymentsOrders = [];

        foreach ($R1 as $r) {
            $balance = $balance - $r['sum'];
        }

        foreach ($R2 as $r) {
            if (!$r['is_billpay']) {
                $balance = $balance + $r['sum'];
            }
        }

        // Цикл оплачивает минусовые счета
        foreach ($R2 as $kp => $r) {
            if ($r['sum'] >= 0) {
                continue;
            }

            $bill_no = $r['bill_no'];

            if (isset($R1[$bill_no])) {
                $sum = $r['sum'];

                $paymentsOrders[] = [
                    'payment_id' => $r['id'],
                    'bill_no' => $bill_no,
                    'sum' => $sum,
                ];

                $R1[$bill_no]['sum'] -= $sum;

                $R2[$kp]['sum'] = 0;
            }
        }

        // Цикл оплачивает счета для которых существует оплата с жестко указанным номером счета
        foreach ($R2 as $kp => $r) {
            if ($r['sum'] < 0.01) {
                continue;
            }

            if ($r['bill_no'] == '') {
                continue;
            }

            $bill_no = $r['bill_no'];

            if (isset($R1[$bill_no]) && ($R1[$bill_no]['new_is_payed'] == 0 || $R1[$bill_no]['new_is_payed'] == 2) && $R1[$bill_no]['sum'] >= 0) {
                if ($this->_sumMore($r['sum'], $R1[$bill_no]['sum'])) {
                    $sum = round($R1[$bill_no]['sum'], 2);

                    $paymentsOrders[] = [
                        'payment_id' => $r['id'],
                        'bill_no' => $bill_no,
                        'sum' => $sum,
                    ];


                    $R2[$kp]['sum'] -= $sum;

                    if ($R2[$kp]['sum'] < 0.01) {
                        $R2[$kp]['sum'] = 0;
                    }

                    $R1[$bill_no]['new_is_payed'] = 1;
                    $R1[$bill_no]['sum'] = 0;

                } elseif ($r['sum'] >= 0.01) {

                    $sum = $r['sum'];

                    $paymentsOrders[] = [
                        'payment_id' => $r['id'],
                        'bill_no' => $bill_no,
                        'sum' => $sum,
                    ];

                    $R2[$kp]['sum'] = 0;

                    $R1[$bill_no]['new_is_payed'] = 2;
                    $R1[$bill_no]['sum'] -= $sum;

                    if ($R1[$bill_no]['sum'] < 0.01) {
                        $R1[$bill_no]['sum'] = 0;
                        $R1[$bill_no]['new_is_payed'] = 1;
                    }
                }
            }
        }

        // Цикл оплачивает счета для которых существует оплата с жестко указанным номером счета ПРИВЯЗКИ.
        // Новых счетов с привязкой не будет. Нужно для совместимости
        foreach ($R2 as $kp => $r) {
            if ($r['sum'] < 0.01) {
                continue;
            }

            $bill_no = $r['bill_vis_no'];

            if (isset($R1[$bill_no]) && ($R1[$bill_no]['new_is_payed'] == 0 || $R1[$bill_no]['new_is_payed'] == 2) && $R1[$bill_no]['sum'] >= 0) {
                if ($this->_sumMore($r['sum'], $R1[$bill_no]['sum'])) {
                    $sum = round($R1[$bill_no]['sum'], 2);

                    if (abs($sum) >= 0.01) {
                        $paymentsOrders[] = [
                            'payment_id' => $r['id'],
                            'bill_no' => $bill_no,
                            'sum' => $sum,
                        ];
                    }


                    $R2[$kp]['sum'] -= $sum;

                    if ($R2[$kp]['sum'] < 0.01) {
                        $R2[$kp]['sum'] = 0;
                    }


                    $R1[$bill_no]['new_is_payed'] = 1;
                    $R1[$bill_no]['sum'] = 0;

                } elseif ($r['sum'] >= 0.01) {
                    $sum = $r['sum'];

                    if (abs($sum) >= 0.01) {
                        $paymentsOrders[] = [
                            'payment_id' => $r['id'],
                            'bill_no' => $bill_no,
                            'sum' => $sum,
                        ];
                    }

                    $R2[$kp]['sum'] = 0;

                    $R1[$bill_no]['new_is_payed'] = 2;
                    $R1[$bill_no]['sum'] -= $sum;

                    if ($R1[$bill_no]['sum'] < 0.01) {
                        $R1[$bill_no]['sum'] = 0;
                        $R1[$bill_no]['new_is_payed'] = 1;
                    }
                }
            }
        }

        if ($clientAccount->contract->business_id != Business::INTERNET_SHOP) { // не магазин

            // Раскидываем остатки оплаты по неоплаченным счетам
            foreach ($R2 as $kp => $r) {
                if ($r['sum'] < 0.01) {
                    continue;
                }

                foreach ($R1 as $kb => $rb) {

                    if ($rb['new_is_payed'] == 1 || $rb['new_is_payed'] == 3 || $rb['sum'] < 0 || $r['sum'] < 0.01) {
                        continue;
                    }

                    if ($this->_sumMore($r['sum'], $rb['sum'])) {

                        $sum = $rb['sum'];

                        if (abs($sum) >= 0.01) {
                            $paymentsOrders[] = [
                                'payment_id' => $r['id'],
                                'bill_no' => $rb['bill_no'],
                                'sum' => $sum,
                            ];
                        }


                        $r['sum'] -= $sum;
                        $R2[$kp]['sum'] -= $sum;

                        if ($R2[$kp]['sum'] < 0.01) {
                            $R2[$kp]['sum'] = 0;
                            $r['sum'] = 0;
                        }


                        $R1[$kb]['new_is_payed'] = 1;
                        $R1[$kb]['sum'] = 0;

                    } elseif ($r['sum'] >= 0.01) {

                        $sum = $r['sum'];

                        if (abs($sum) >= 0.01) {
                            $paymentsOrders[] = [
                                'payment_id' => $r['id'],
                                'bill_no' => $rb['bill_no'],
                                'sum' => $sum,
                            ];
                        }

                        $r['sum'] = 0;
                        $R2[$kp]['sum'] = 0;

                        $R1[$kb]['new_is_payed'] = 2;
                        $R1[$kb]['sum'] -= $sum;

                    }
                }
            }

            // Если все счета оплачены и осталась лишняя оплата то в любом случае закидываем ее на последний счет, даже если будет переплата.
            $last_payment = null;
            foreach ($R1 as $k => $r) {

                if (($r['new_is_payed'] == 0 || $r['new_is_payed'] == 2) && $this->_sumMore(0, $r['sum'], 1)) {
                    $R1[$k]['new_is_payed'] = 1;
                }

                if ($r['sum'] < 0) {
                    $R1[$k]['new_is_payed'] = 1;
                }

                $last_payment = $r;
            }

            foreach ($R2 as $k => $v) {
                if ($v['sum'] == 0) {
                    continue;
                }

                $sum = $v['sum'];

                if (abs($sum) >= 0.01) {
                    $paymentsOrders[] = [
                        'payment_id' => $v['id'],
                        'bill_no' => $last_payment['bill_no'],
                        'sum' => $sum,
                    ];
                }
            }
        }

        $transaction = Bill::getDb()->beginTransaction();

        $savedBills = [];

        foreach ($R1 as $billNo => $v) {
            if ($v['bill_no'] == 'saldo') {
                continue;
            }

            if ($v['is_payed'] != $v['new_is_payed']) {
                $documentType = Bill::dao()->getDocumentType($billNo);
                if ($documentType['type'] == 'bill') {
                    /** @var Bill $bill */
                    $bill = Bill::findOne(['bill_no' => $billNo]);
                    if ($bill->is_payed != $v['new_is_payed']) {
                        $bill->is_payed = $v['new_is_payed'];
                        $savedBills[$bill->bill_no] = 1;
                        if (!$bill->save()) {
                            throw new ModelValidationException($bill);
                        }
                    }
                } elseif ($documentType['type'] == 'incomegood') {
                    $order = GoodsIncomeOrder::findOne(['number' => $billNo]);
                    $order->is_payed = $v['new_is_payed'];
                    $order->save();
                }
            }
        }

        // проверяем изменение оплаты счета
        $savedPaymentOrders = PaymentOrder::find()
            ->select(['sum', 'bill_no'])
            ->where(['client_id' => $clientAccount->id])
            ->indexBy('bill_no')
            ->column();

        $resortPaymentOrders = [];
        foreach ($paymentsOrders as $order) {
            if (!isset($order['bill_no']) || !$order['bill_no']) {
                continue;
            }

            if (!isset($resortPaymentOrders[$order['bill_no']])) {
                $resortPaymentOrders[$order['bill_no']] = $order;
            } else {
                $resortPaymentOrders[$order['bill_no']]['sum'] += $order['sum'];
            }
        }

        $batchInsertPaymentOrders = array_map(function ($order) use ($clientAccount) {
            return [$order['payment_id'], $order['bill_no'], $clientAccount->id, $order['sum']];
        }, $resortPaymentOrders);


        // пересохранение PaymentOrder
        PaymentOrder::deleteAll(['client_id' => $clientAccount->id]);

        if ($batchInsertPaymentOrders) {
            Yii::$app->db->createCommand()
                ->batchInsert(
                    PaymentOrder::tableName(),
                    ['payment_id', 'bill_no', 'client_id', 'sum'],
                    $batchInsertPaymentOrders
                )->execute();
        }


        // проверка изменения частичной оплаты счета
        foreach (array_intersect(array_keys($savedPaymentOrders), array_keys($resortPaymentOrders)) as $billNo) {
            if (isset($savedBills[$billNo])) {
                continue;
            }

            if ($savedPaymentOrders[$billNo] == $resortPaymentOrders[$billNo]['sum']) {
                continue;
            }

            $bill = Bill::findOne(['bill_no' => $billNo]);

            if ($bill) {
                $bill->trigger(Bill::TRIGGER_CHECK_OVERDUE);
                if ($bill->isSetPayOverdue !== null) {
                    if (!$bill->save()) {
                        throw new ModelValidationException($bill);
                    }
                }
            }
        }

        $lastBillDate = ClientAccount::dao()->getLastBillDate($clientAccount);
        $lastPayedBillMonth = ClientAccount::dao()->getLastPayedBillMonth($clientAccount);

        $p = [
            ':clientAccountId' => $clientAccount->id,
            ':balance' => $balance,
            ':lastBillDate' => $lastBillDate,
            ':lastPayedBillMonth' => $lastPayedBillMonth
        ];

        ClientAccount::getDb()
            ->createCommand('
                UPDATE clients
                SET balance = :balance,
                    last_account_date = :lastBillDate,
                    last_payed_voip_month = :lastPayedBillMonth
                WHERE id = :clientAccountId', $p
            )
            ->execute();

        $transaction->commit();
    }

    /**
     * @param ClientAccount $clientAccount
     * @return array
     */
    private function _getSaldo(ClientAccount $clientAccount)
    {
        $saldo = Saldo::find()
            ->andWhere([
                'client_id' => $clientAccount->id,
                'is_history' => 0,
                'currency' => $clientAccount->currency
            ])
            ->orderBy('id desc')
            ->limit(1)
            ->one();

        if ($saldo) {
            return ['ts' => $saldo->ts, 'saldo' => $saldo->saldo];
        }

        return ['ts' => 0, 'saldo' => 0];
    }

    /**
     * @param ClientAccount $clientAccount
     * @param string $saldoDate
     * @return array
     */
    private function _enumBillsFullSum(ClientAccount $clientAccount, $saldoDate)
    {
        $sql = '
            SELECT * FROM (
                SELECT
                    B.bill_no,
                    B.bill_date,
                    B.currency as currency,
                    B.is_payed,
                    B.sum,
                    ' . ($saldoDate ? ' CASE B.bill_date>="' . $saldoDate . '" WHEN true THEN 0 ELSE 3 END ' : '0') . ' as new_is_payed
                FROM
                    newbills B
                WHERE
                    B.client_id = :clientAccountId
                    and B.currency = :currency
                    and B.bill_date >= :saldoDate
                    and B.biller_version = :billerVersion

                UNION

                SELECT
                    G.number as bill_no,
                    cast(G.date as date) bill_date,
                    currency as currency,
                    G.is_payed,
                    G.sum,
                    ' . ($saldoDate ? ' CASE G.date>="' . $saldoDate . '" WHEN true THEN 0 ELSE 3 END ' : '0') . ' as new_is_payed

                  FROM g_income_order G
                  WHERE
                        G.client_card_id = :clientAccountId
                    and G.currency = :currency
                    and G.date >= :saldoDate
                    and G.deleted = 0
                    and G.active = 1
                    and if (40 != ifnull((
                        select 
                            state_id 
                        from 
                            tt_troubles t, 
                            tt_stages s 
                        WHERE 
                                G.id = t.bill_id 
                            AND s.stage_id = t.cur_stage_id)
                       , 40), true, false)

                
            


            ) as B

            GROUP BY B.bill_no, B.is_payed, B.sum, B.bill_date, B.currency
            ORDER BY bill_date asc, bill_no asc';

        $bills = Bill::getDb()->createCommand(
            $sql,
            [
                ':clientAccountId' => $clientAccount->id,
                ':currency' => $clientAccount->currency,
                ':saldoDate' => $saldoDate,
                ':billerVersion' => ClientAccount::VERSION_BILLER_USAGE
            ]
        )
        ->queryAll();

        $result = [];
        foreach ($bills as $bill) {
            if ($bill['currency'] == 'USD' && $bill['currency']) {

            }

            $result[$bill['bill_no']] = $bill;
        }

        return $result;
    }

    /**
     * @param ClientAccount $clientAccount
     * @param string $saldoDate
     * @return array
     */
    private function _enumPayments(ClientAccount $clientAccount, $saldoDate)
    {
        $sql = '
            select P.*, 0 as is_billpay  from newpayments as P
            left join newbills as B ON P.client_id = B.client_id
            where
                P.client_id = :clientAccountId
                and P.payment_date >= :saldoDate
                and B.bill_no IS NULL

            UNION

            select P.*, 0 as is_billpay
            from newpayments as P
            left join newbills as B ON P.client_id = B.client_id
            where
                P.client_id = :clientAccountId
                and B.bill_no=P.bill_no
                and B.currency = :currency
                and B.bill_date >= :saldoDate
            order by payment_date asc
        ';

        $payments = Bill::getDb()->createCommand(
            $sql,
            [
                ':clientAccountId' => $clientAccount->id,
                ':currency' => $clientAccount->currency,
                ':saldoDate' => $saldoDate
            ]
        )->queryAll();

        $paymentsById = [];
        foreach ($payments as $payment) {
            $paymentsById[$payment['id']] = $payment;
        }

        $sql = "
                        SELECT B.*
                        FROM newbills as B
                        LEFT JOIN clients as C ON C.id = B.client_id
                        WHERE
                            B.client_id = :clientAccountId
                            and B.bill_date >= :saldoDate
                            and B.sum < 0
                            and B.currency = 'RUB'
                            and C.status NOT IN ('operator', 'distr')
                            and B.biller_version = :billerVersion
        ";

        $billPayments = Bill::getDb()->createCommand(
            $sql,
            [
                ':clientAccountId' => $clientAccount->id,
                ':saldoDate' => $saldoDate,
                ':billerVersion' => ClientAccount::VERSION_BILLER_USAGE,
            ]
        )->queryAll();

        $paymentsAndChargebacks = [];
        foreach ($paymentsById as $v) {
            foreach ($paymentsById as $v2) {
                if ($v['bill_no'] == $v2['bill_no'] && $v['sum'] == -$v2['sum']) {
                    $paymentsAndChargebacks[$v['bill_no']] = 1;
                }
            }
        }

        foreach ($billPayments as $v) {

            if (!isset($paymentsAndChargebacks[$v['bill_no']])) {

                foreach ($paymentsById as $v2) {
                    if ($v['bill_no'] == $v2['bill_no'] && $v['sum'] < 0 && $v2['sum'] < 0) {
                        $v['sum'] -= $v2['sum'];
                    }
                }

                if ($v['sum'] < 0) {
                    $pay = array(
                        'id' => $v['bill_no'],
                        'client_id' => $v['client_id'],
                        'payment_date' => $v['bill_date'],
                        'payment_id' => $v['bill_no'],
                        'currency' => $v['currency'],
                        'sum' => -$v['sum'],
                        'bill_no' => '',
                        'bill_vis_no' => '',
                        'is_billpay' => 1
                    );
                    $paymentsById[$v['bill_no']] = $pay;
                }
            }
        }

        return $paymentsById;
    }

    /**
     * @param float $pay
     * @param float $bill
     * @param float $diff
     * @return bool
     */
    private function _sumMore($pay, $bill, $diff = 0.01)
    {
        return ($pay - $bill > -$diff);
    }

    /**
     * @param ClientAccount $clientAccount
     */
    public function updateIsActive(ClientAccount $clientAccount)
    {
        $now = new \DateTime();

        $hasUsage = Yii::$app->db->createCommand('
            select id
            from usage_extra u
            where u.client = :client and u.actual_to >= :date

            union all

            select id
            from usage_welltime u
            where u.client = :client and u.actual_to >= :date

            union all

            select id
            from usage_ip_ports u
            where u.client = :client and u.actual_to >= :date

            union all

            select id
            from usage_sms u
            where u.client = :client and u.actual_to >= :date

            union all

            select id
            from usage_virtpbx u
            where u.client = :client and u.actual_to >= :date

            union all

            select id
            from usage_voip u
            where u.client = :client and u.actual_to >= :date

            union all

            select id
            from usage_trunk u
            where u.client_account_id = :client_account_id and u.actual_to >= :date

            union all

            select id
            from uu_account_tariff u
            where u.client_account_id = :client_account_id and tariff_period_id is not null
        ', [
            ':client' => $clientAccount->client,
            ':client_account_id' => $clientAccount->id,
            ':date' => $now->format(DateTimeZoneHelper::DATE_FORMAT),
        ])
            ->queryOne();

        $newIsActive = $hasUsage ? 1 : 0;
        if ($clientAccount->is_active != $newIsActive) {
            $clientAccount->is_active = $newIsActive;
            $clientAccount->save();
        }
    }

    /**
     * @param ClientAccount $clientAccount
     * @return array
     */
    public function getClientVoipNumbers(ClientAccount $clientAccount)
    {
        if (is_null($this->_voipNumbers)) {
            $this->_voipNumbers = ApiPhone::getNumbersInfo($clientAccount);
        }

        return $this->_voipNumbers;
    }

}
