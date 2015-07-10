<?php
namespace app\dao;

use app\models\ClientStatuses;
use Yii;
use app\classes\Assert;
use app\classes\Singleton;
use app\models\Bill;
use app\models\ClientAccount;
use app\models\GoodsIncomeOrder;
use app\models\PaymentOrder;
use app\models\Saldo;
use app\models\Datacenter;
use app\models\ServerPBX;
use app\models\Region;
use DateTime;
use DateTimeZone;

/**
 * @method static ClientAccountDao me($args = null)
 * @property
 */
class ClientAccountDao extends Singleton
{

    public function getLastBillDate(ClientAccount $clientAccount)
    {
        $billDate =
            ClientAccount::getDb()->createCommand("
                    select max(b.bill_date)
                    from newbills b, newbill_lines bl
                    where b.client_id=:clientAccountId and day(b.bill_date) = 1 and b.bill_no=bl.bill_no and bl.id_service > 0
                ",
                [':clientAccountId' => $clientAccount->id]
            )->queryScalar();

        if (!$billDate) {
            $billDate = '2000-01-01';
        }

        $billDate = new DateTime($billDate, $clientAccount->timezone);
        $billDate->setTimezone(new DateTimeZone('UTC'));

        return $billDate->format('Y-m-d H:i:s');
    }

    public function getLastPayedBillMonth(ClientAccount $clientAccount)
    {
        return ClientAccount::getDb()->createCommand("
                select b.bill_date - interval day(b.bill_date)-1 day
                from newbills b left join newbill_lines bl on b.bill_no=bl.bill_no
                where b.client_id=:clientAccountId and b.is_payed=1 and bl.id_service > 0
                group by b.bill_date
                order by b.bill_date desc
                limit 1
            ",
            [':clientAccountId' => $clientAccount->id]
        )->queryScalar();
    }

    public function updateBalance($clientAccountId)
    {
        $clientAccount = ClientAccount::findOne($clientAccountId);
        Assert::isObject($clientAccount);

        $saldo = $this->getSaldo($clientAccount);

        $R1 = $this->enumBillsFullSum($clientAccount,$saldo['ts']);
        $R2 = $this->enumPayments($clientAccount,$saldo['ts']);

        $sum = -$saldo['saldo'];

        $balance = 0;

        if ($sum > 0){

            array_unshift($R2, Array
            (
                'id' => '0',
                'client_id' => $clientAccount->id,
                'payment_no' => 0,
                'bill_no' => 'saldo',
                'bill_vis_no' => 'saldo',
                'payment_date' => $saldo['ts'],
                'oper_date' => $saldo['ts'],
                'type' => 'priv',
                'comment' => '',
                'add_date' => $saldo['ts'],
                'add_user' => 0,
                'sum' => $sum,
                "is_billpay" => 0,
            ) );
        }elseif($sum < 0){

            array_unshift($R1, Array
                (
                    'bill_no' => 'saldo',
                    'is_payed' => 1,
                    'sum' => -$sum,
                    'new_is_payed' => 0,
                )
            );
        }

        $paymentsOrders = array();

        foreach ($R1 as $r) {
            $balance = $balance - $r['sum'];
        }

        foreach ($R2 as $r) {
            if (!$r["is_billpay"])
            {
                $balance = $balance + $r['sum'];
            }
        }

        // Цикл оплачивает минусовые счета
        foreach ($R2 as $kp => $r) {
            if ($r['sum'] >= 0) continue;

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
            if ($r['sum'] < 0.01) {continue;}


            if ($r['bill_no'] == '') continue;
            $bill_no = $r['bill_no'];

            if (isset($R1[$bill_no]) && ($R1[$bill_no]['new_is_payed']==0 || $R1[$bill_no]['new_is_payed']==2) && $R1[$bill_no]['sum'] >= 0) {
                if ($this->sum_more($r['sum'],$R1[$bill_no]['sum'])) {
                    $sum = round($R1[$bill_no]['sum'], 2);

                    $paymentsOrders[] = [
                        'payment_id' => $r['id'],
                        'bill_no'    => $bill_no,
                        'sum'        => $sum,
                    ];


                    $R2[$kp]['sum'] -= $sum;

                    if  ($R2[$kp]['sum'] < 0.01) {
                        $R2[$kp]['sum'] = 0;
                    }

                    $R1[$bill_no]['new_is_payed'] = 1;
                    $R1[$bill_no]['sum'] = 0;

                } elseif ($r['sum'] >= 0.01){

                    $sum = $r['sum'];

                    $paymentsOrders[] = [
                        'payment_id' => $r['id'],
                        'bill_no'=>$bill_no,
                        'sum'=>$sum,
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
            if ($r['sum'] < 0.01) continue;

            $bill_no = $r['bill_vis_no'];

            if (isset($R1[$bill_no]) && ($R1[$bill_no]['new_is_payed']==0 || $R1[$bill_no]['new_is_payed']==2) && $R1[$bill_no]['sum'] >= 0) {
                if ($this->sum_more($r['sum'],$R1[$bill_no]['sum'])) {
                    $sum = round($R1[$bill_no]['sum'], 2);

                    if (abs($sum) >= 0.01){
                        $paymentsOrders[] = [
                            'payment_id' => $r['id'],
                            'bill_no'=>$bill_no,
                            'sum'=>$sum,
                        ];
                    }


                    $R2[$kp]['sum'] -= $sum;

                    if  ($R2[$kp]['sum'] < 0.01) {
                        $R2[$kp]['sum'] = 0;
                    }


                    $R1[$bill_no]['new_is_payed'] = 1;
                    $R1[$bill_no]['sum'] = 0;

                } elseif ($r['sum'] >= 0.01){
                    $sum = $r['sum'];

                    if (abs($sum) >= 0.01){
                        $paymentsOrders[] = [
                            'payment_id' => $r['id'],
                            'bill_no'=>$bill_no,
                            'sum'=>$sum,
                        ];
                    }

                    $R2[$kp]['sum'] = 0;

                    $R1[$bill_no]['new_is_payed'] = 2;
                    $R1[$bill_no]['sum'] -= $sum;

                    if  ($R1[$bill_no]['sum'] < 0.01) {
                        $R1[$bill_no]['sum'] = 0;
                        $R1[$bill_no]['new_is_payed'] = 1;
                    }
                }
            }
        }

        if ($clientAccount->type != "multi"){ // не магазин

            // Раскидываем остатки оплаты по неоплаченным счетам
            foreach ($R2 as $kp => $r) {
                if ($r['sum'] < 0.01) continue;

                foreach ($R1 as $kb => $rb) {

                    if ($rb['new_is_payed']==1 || $rb['new_is_payed']==3 || $rb['sum'] < 0 || $r['sum'] < 0.01) continue;

                    if ($this->sum_more($r['sum'],$rb['sum'])) {

                        $sum = $rb['sum'];

                        if (abs($sum) >= 0.01){
                            $paymentsOrders[] = [
                                'payment_id' => $r['id'],
                                'bill_no'=>$rb['bill_no'],
                                'sum'=>$sum,
                            ];
                        }


                        $r['sum'] -= $sum;
                        $R2[$kp]['sum'] -= $sum;

                        if  ($R2[$kp]['sum'] < 0.01) {
                            $R2[$kp]['sum'] = 0;
                            $r['sum'] = 0;
                        }


                        $R1[$kb]['new_is_payed'] = 1;
                        $R1[$kb]['sum'] = 0;

                    } elseif ($r['sum'] >= 0.01){

                        $sum = $r['sum'];

                        if (abs($sum) >= 0.01){
                            $paymentsOrders[] = [
                                'payment_id' => $r['id'],
                                'bill_no'=>$rb['bill_no'],
                                'sum'=>$sum,
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

                if ( ($r['new_is_payed']==0 || $r['new_is_payed']==2) && $this->sum_more(0,$r['sum'], 1)) {
                    $R1[$k]['new_is_payed'] = 1;
                }

                if ($r['sum'] < 0){
                    $R1[$k]['new_is_payed'] = 1;
                }

                $last_payment = $r;
            }

            foreach ($R2 as $k => $v) {
                if ($v['sum'] == 0) continue;

                $sum = $v['sum'];

                if (abs($sum) >= 0.01){
                    $paymentsOrders[] = [
                        'payment_id' => $v['id'],
                        'bill_no'=>$last_payment['bill_no'],
                        'sum'=>$sum,
                    ];
                }
            }

        } // не магазин

        $transaction = Bill::getDb()->beginTransaction();

        foreach ($R1 as $billNo => $v)
        {
            if($v["bill_no"] == "saldo") continue;

            if ($v['is_payed'] != $v['new_is_payed'])
            {
                $documentType = Bill::dao()->getDocumentType($billNo);
                if ($documentType['type'] == 'bill') {
                    $bill = Bill::findOne(['bill_no' => $billNo]);
                    $bill->is_payed = $v['new_is_payed'];
                    $bill->save();
                } elseif ($documentType['type'] == 'incomegood') {
                    $order = GoodsIncomeOrder::findOne(['number' => $billNo]);
                    $order->is_payed = $v['new_is_payed'];
                    $order->save();
                }
            }
        }

        PaymentOrder::deleteAll(['client_id' => $clientAccount->id]);

        foreach ($paymentsOrders as $r) {

            if (!$r["bill_no"])
                continue;

            PaymentOrder::getDb()
                ->createCommand('
                    INSERT INTO newpayments_orders (payment_id, bill_no, client_id, `sum`)
                    VALUES (:paymentId, :billNo, :clientAccountId, :sum)
                    ON DUPLICATE KEY UPDATE `sum` = `sum` + :sum
                    ', [
                        ':paymentId' => $r['payment_id'],
                        ':billNo' => $r['bill_no'],
                        ':clientAccountId' => $clientAccount->id,
                        ':sum' => $r['sum']
                    ]
                )
                ->execute();
        }

        $lastBillDate = ClientAccount::dao()->getLastBillDate($clientAccount);
        $lastPayedBillMonth = ClientAccount::dao()->getLastPayedBillMonth($clientAccount);

        ClientAccount::getDb()
            ->createCommand('
                UPDATE clients
                SET balance = :balance,
                    last_account_date = :lastBillDate,
                    last_payed_voip_month = :lastPayedBillMonth
                WHERE id = :clientAccountId', [
                    ':clientAccountId' => $clientAccount->id,
                    ':balance' => $balance,
                    ':lastBillDate' => $lastBillDate,
                    ':lastPayedBillMonth' => $lastPayedBillMonth
                ]
            )
            ->execute();

        $transaction->commit();
    }

    private function getSaldo(ClientAccount $clientAccount)
    {
        $saldo =
            Saldo::find()
                ->andWhere(['client_id' => $clientAccount->id, 'is_history' => 0, 'currency' => $clientAccount->currency])
                ->orderBy('id desc')
                ->limit(1)
                ->one();
        if ($saldo) {
            return ['ts' => $saldo->ts, 'saldo' => $saldo->saldo];
        } else {
            return ['ts' => 0, 'saldo' => 0];
        }
    }


    private function enumBillsFullSum(ClientAccount $clientAccount, $saldoDate)
    {
        $sql = '
            SELECT * FROM (
                SELECT
                    B.bill_no,
                    B.bill_date,
                    B.currency as currency,
                    B.is_payed,
                    B.sum,
                    '.($saldoDate?' CASE B.bill_date>="'.$saldoDate.'" WHEN true THEN 0 ELSE 3 END ':'0').' as new_is_payed
                FROM
                    newbills B
                WHERE
                    B.client_id = :clientAccountId
                    and B.currency = :currency
                    and B.bill_date >= :saldoDate

                UNION

                SELECT
                    G.number as bill_no,
                    cast(G.date as date) bill_date,
                    currency as currency,
                    G.is_payed,
                    G.sum,
                    '.($saldoDate?' CASE G.date>="'.$saldoDate.'" WHEN true THEN 0 ELSE 3 END ':'0').' as new_is_payed

                  FROM g_income_order G
                  WHERE
                        G.client_card_id = :clientAccountId
                    and G.currency = :currency
                    and G.date >= :saldoDate
            ) as B

            GROUP BY B.bill_no, B.is_payed, B.sum, B.bill_date, B.currency
            ORDER BY bill_date asc, bill_no asc';

        $bills =
            Bill::getDb()
                ->createCommand(
                    $sql,
                    [':clientAccountId' => $clientAccount->id, ':currency' => $clientAccount->currency, ':saldoDate' => $saldoDate]
                )
                ->queryAll();

        $result = [];
        foreach($bills as $bill) {
            if ($bill['currency'] == 'USD' && $bill['currency']) {

            }
            $result[$bill['bill_no']] = $bill;
        }

        return $result;
    }

    private function enumPayments(ClientAccount $clientAccount, $saldoDate)
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
        $payments =
            Bill::getDb()->createCommand(
                $sql,
                [':clientAccountId' => $clientAccount->id, ':currency' => $clientAccount->currency, ':saldoDate' => $saldoDate]
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
        ";
        $billPayments =
            Bill::getDb()->createCommand(
                $sql,
                [':clientAccountId' => $clientAccount->id, ':saldoDate' => $saldoDate]
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
                    if ($v['bill_no'] == $v2['bill_no'] && $v['sum'] < 0 && $v2['sum'] < 0)
                    {
                        $v['sum'] -= $v2['sum'];
                    }
                }

                if ($v['sum'] < 0) {
                    $pay = array (
                        'id' => $v['bill_no'],
                        'client_id' => $v['client_id'],
                        'payment_date' => $v['bill_date'],
                        'payment_id' => $v['bill_no'],
                        'currency' => $v['currency'],
                        'sum' => -$v['sum'],
                        'bill_no' => '',
                        'bill_vis_no' => '',
                        "is_billpay" => 1
                    );
                    $paymentsById[$v['bill_no']] = $pay;
                }
            }
        }
        return $paymentsById;
    }

    private function sum_more($pay,$bill,$diff=0.01) {
        return ($pay-$bill>-$diff);
    }

    public function updateIsActive(ClientAccount $clientAccount)
    {
        $now = new \DateTime();

        $hasUsage =
            Yii::$app->db->createCommand("
                select id
                from emails u
                where u.client = :client and u.actual_to >= :date
                limit 1

                union all

                select id
                from usage_extra u
                where u.client = :client and u.actual_to >= :date
                limit 1

                union all

                select id
                from usage_welltime u
                where u.client = :client and u.actual_to >= :date
                limit 1

                union all

                select id
                from usage_ip_ports u
                where u.client = :client and u.actual_to >= :date
                limit 1

                union all

                select id
                from usage_sms u
                where u.client = :client and u.actual_to >= :date
                limit 1

                union all

                select id
                from usage_virtpbx u
                where u.client = :client and u.actual_to >= :date
                limit 1

                union all

                select id
                from usage_voip u
                where u.client = :client and u.actual_to >= :date
                limit 1
            ", [
                ':client' => $clientAccount->client,
                ':date' => $now->format('Y-m-d'),
            ])
                ->queryOne();

        $newIsActive = $hasUsage ? 1 : 0;
        if ($clientAccount->is_active != $newIsActive) {
            $clientAccount->is_active = $newIsActive;
            $clientAccount->save();

            $cs = new ClientStatuses();

            $cs->ts = date("Y-m-d H:i:s");
            $cs->id_client = $clientAccount->id;
            $cs->user = \Yii::$app->user->getIdentity()->user;
            $cs->status = "";
            $cs->comment = "Лицевой счет " . ($clientAccount->is_active ? "открыт" : "закрыт");
        }
    }

    public function getAccountPropertyOnDate($clientId, $date)
    {
        $dNow = date("Y-m-d",strtotime("+1 day"));
        $c = ClientAccount::findOne($clientId)->toArray();

        $trasitFields = array("mail_print", "bill_rename1", "address_post_real", "mail_who", "credit");
        $transit = array();

        foreach($trasitFields as $f)
            $transit[$f] = $c[$f];

        if($dNow >= $date)
        {
            $rows = ClientAccount::getDB()->createCommand("
                        select *
                        from log_client lc, log_client_fields lf
                        where client_id = :client_id and
                            if(apply_ts = '0000-00-00', ts >= :date_full, apply_ts > :date)
                            and if(apply_ts = '0000-00-00', ts < :now_full, apply_ts <= :now)
                            and type='fields'
                            and lc.id = lf.ver_id
                            and is_overwrited = 'no'
                            and is_apply_set = 'yes'
                        order by lf.id desc ", [":client_id" => $c["id"], ":date" => $date, ":date_full" => $date." 23:59:59", ":now" => $dNow, ":now_full" => $dNow." 00:00:00"])->queryAll();
            if ($rows) {
                foreach ($rows as $l) {
                    $c[$l["field"]] = $l["value_from"];
                }
            }
        }
        if ($dNow <= $date)
        {
            $rows = ClientAccount::getDB()->createCommand("
                        select *
                        from log_client lc, log_client_fields lf
                        where client_id = :client_id
                            and apply_ts BETWEEN :now AND :date
                            and type='fields'
                            and lc.id = lf.ver_id
                            and is_apply_set = 'no'
                        order by lf.id", [":client_id" => $c["id"], ":now" => $dNow, ":date" => $date])->queryAll();
            if ($rows) {
                foreach ($rows as $l) {
                    $c[$l["field"]] = $l["value_to"];
                }
            }
        }

        foreach($trasitFields as $f) {
            if (isset($transit[$f])) {
                $c[$f] = $transit[$f];
            }
        }

        return $c;
    }

    public function getServerPBXId(ClientAccount $account, $region = 0)
    {
        if (!$region)
        {
            $region = $account->region;
        }

        $isFind = false;
        foreach(Region::findAll(["country_id" => $account->country_id]) as $r)
        {
            if ($r->id == $region)
            {
                $isFind = true;
                break;
            }
        }

        if (!$isFind)
            $region = $account->region;

        if ($region == 99)
        {
            return ServerPBX::MSK_SERVER_ID;
        } else {
            $datacenter = Datacenter::findOne(["region" => $region]);
            if ($datacenter)
            {
                $server = ServerPBX::findOne(["datacenter_id" => $datacenter->id]);

                if ($server)
                {
                    return $server->id;
                }
            }
        }
        return ServerPBX::MSK_SERVER_ID;
    }
}
