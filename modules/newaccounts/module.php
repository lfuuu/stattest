<?php
class m_newaccounts extends IModule
{
    private static $object;
    private static $bb_c = array();

    function do_include() {
        static $inc = false;
        if ($inc) return;
        $inc = true;
        include_once INCLUDE_PATH.'bill.php';
        include_once INCLUDE_PATH.'payments.php';
    }
    function GetMain($action,$fixclient){
        $this->do_include();
        if (!$action || $action=='default') $action='bill_list';
        if (!isset($this->actions[$action])) return;
        $act=$this->actions[$action];
        if ($act!=='' && !access($act[0],$act[1])) return;
        call_user_func(array($this,'newaccounts_'.$action),$fixclient);
    }

    function newaccounts_default($fixclient){
    }
    function newaccounts_saldo ($fixclient) {
        global $design,$db,$user,$fixclient_data;
        $saldo = get_param_protected('saldo');
        $date = get_param_protected('date');
        $db->Query('update newsaldo set is_history=1 where client_id='.$fixclient_data['id']);
        $db->Query('insert into newsaldo (client_id,saldo,currency,ts,is_history,edit_user,edit_time) values ('.$fixclient_data['id'].','.$saldo.',"'.$fixclient_data['currency'].'","'.$date.'",0,"'.$user->Get('id').'",NOW())');
        $this->update_balance($fixclient_data['id'],$fixclient_data['currency']);
        if ($design->ProcessEx('errors.tpl')) header("Location: ".$design->LINK_START."module=newaccounts&action=bill_list");
    }
    function newaccounts_bill_balance($fixclient){
        global $design,$db,$user,$fixclient_data;
        $client_id=$fixclient_data['id'];
        $this->update_balance($client_id,$fixclient_data['currency']);
        if ($design->ProcessEx('errors.tpl')) header("Location: ".$design->LINK_START."module=newaccounts&action=bill_list");
    }
    function newaccounts_bill_balance_mass($fixclient){
        global $design,$db,$user,$fixclient;
        $design->ProcessEx('errors.tpl');
        //$R=$db->AllRecords('select client,id,currency from clients order by client');
/*        $R=$db->AllRecords("select c.id, c.currency from clients c where id in (SELECT distinct b.client_id FROM `newbills` b, `newpayments` p  where is_payed != 1 and p.bill_no = b.bill_no and sum = sum_rub and
            (b.bill_no like '201%') )
                ");*/
        $R=$db->AllRecords("select c.id, c.client, c.currency from clients c where status not in ( 'closed', 'trash') ");
        set_time_limit(0);
        session_write_close();
        foreach ($R as $r) {
            echo $r['client']."<br>\n";flush();
            $this->update_balance($r['id'],$r['currency']);
        }
    }

    function sum_more($pay,$bill,$currency, $diff=0.01) {
        if ($pay-$bill>-$diff) return 1;
        //echo "$pay|$bill|<br>\n";
        return 0;

        $diff = $bill - $pay;

        $perc3 = $bill*0.03;
        $line = 5; //USD
        if ($currency=='RUR') $line = 100; //RUR
        if($diff <= $perc3 && $diff <= $line) return 1;
        return 0;
    }

    function getClientSaldo($client_id,$currency,$ret_zero = false) {
        global $db;
        $r = null;
        if (!$ret_zero) $r=$db->GetRow('select * from newsaldo where client_id='.$client_id.' and is_history=0 and currency="'.$currency.'" order by id desc limit 1');
        if (!$r) return array('ts'=>0,'saldo'=>0);
        return array('ts'=>$r['ts'],'saldo'=>$r['saldo']);
    }

    private function enumBills($client_id,$currency,$saldo_ts,$select,$sort = 'bill_date asc, bill_no asc',$addSql = '',$arrKeySrc = 'bill_no', $W_add = null) {
        global $db;
        $W = array('AND','B.client_id='.$client_id,'B.currency="'.$currency.'"','B.bill_date>="'.$saldo_ts.'"');
        if ($W_add) $W[] = $W_add;
        return $db->AllRecords('select '.$select.' from newbills as B where '.MySQLDatabase::Generate($W).($sort?' order by '.$sort:'').$addSql,$arrKeySrc);
    }

    private function enumBillsFullSum(
            $client_id,
            $currency,
            $saldo_ts,
            $sort = 'bill_date asc, bill_no asc',
            $addSql = '',
            $arrKeySrc = 'bill_no', 
            $W_add = null) 
    {
        global $db;

        $W = array('AND','B.client_id='.$client_id,'B.currency="'.$currency.'"','B.bill_date>="'.$saldo_ts.'"');
        if ($W_add) $W[] = $W_add;

        $r = $db->AllRecords($q='
                SELECT * FROM (
                SELECT 
                    B.bill_no, 
                    B.bill_date,
                    B.currency,
                    B.is_payed, 
                    B.inv_rur, 
                    B.sum, 
                    '.($saldo_ts?' CASE B.bill_date>="'.$saldo_ts.'" WHEN true THEN 0 ELSE 3 END ':'0').' as new_is_payed, 
                    CASE B.cleared_sum > 0 WHEN true THEN B.cleared_sum ELSE B.sum END as sum_full
                FROM 
                    newbills B 
                WHERE 
                    '.MySQLDatabase::Generate($W).' 

                UNION 

                SELECT 
                    G.number as bill_no, 
                    cast(G.date as date) bill_date,
                    if(currency = "RUB", "RUR", currency) as currency,
                    G.is_payed,
                    0.0 as inv_rur,  
                    G.sum, 
                    '.($saldo_ts?' CASE G.date>="'.$saldo_ts.'" WHEN true THEN 0 ELSE 3 END ':'0').' as new_is_payed, 
                    G.sum as sum_full

                  FROM `g_income_order` G
                  WHERE 
                        client_card_id = "'.$client_id.'"
                    and G.currency = "'.($currency == "RUR" ? "RUB" : $currency).'"
                    and G.date>="'.$saldo_ts.'"
                ) as B #bills_and_incomegoods

                GROUP BY 
                    B.bill_no, B.is_payed, B.inv_rur, B.sum, B.bill_date, B.currency '.
                ($sort?' order by '.$sort:'').$addSql,$arrKeySrc);

        return $r;
    }

    private function enumPayments($client_id,$currency,$saldo_ts,$select,$sort = 'payment_date asc',$addSql = '',$arrKeySrc = 'id', $W_add = null) {
        global $db;
        $W = array('AND','P.client_id='.$client_id);
        $W2 = $W;

        $W[] = array('AND','B.bill_no IS NULL', 'P.payment_date>="'.$saldo_ts.'"');

        $W2[] = array('AND','B.currency="'.$currency.'"','B.bill_date>="'.$saldo_ts.'"', 'B.bill_no=P.bill_no');
        if ($W_add) {$W[] = $W_add; $W2[] = $W_add;}


        $r = $db->AllRecords($q =
                'select '.$select.' from newpayments as P
                left join newbills as B ON (P.client_id = B.client_id)
                where '.MySQLDatabase::Generate($W).
                " UNION ".
                'select '.$select.' from newpayments as P
                left join newbills as B ON (P.client_id = B.client_id)
                where '.MySQLDatabase::Generate($W2).

                ($sort?' order by '.$sort:'').$addSql,$arrKeySrc);

        return $r;
    }
    function update_balance($client_id,$currency) {
        global $db, $fixclient_data;
        set_time_limit(120);
        $saldo=$this->getClientSaldo($client_id,$currency,get_param_raw('nosaldo'));

        $fixclient_data=$client = $db->GetRow("select * from clients where id=".intval($client_id));

        $R1 = $this->enumBillsFullSum        ($client_id,$currency,$saldo['ts']);
        $R2 = $this->enumPayments    ($client_id,$currency,$saldo['ts'],'P.*,round(P.sum_rub/P.payment_rate,2) as sum');


        $sum = -$saldo['saldo'];

        $balance = 0;

        if(isset($R1["201007/2373"]))
        {
//            $R1["201007/2373"]["is_zadatok"] = 1;
            $R1["201007/2373"]["sum_full"] = 0;
        }

        //$R2 = array();
        //echo $saldo['ts'].'|'.$sum;
        if ($sum > 0){

            array_unshift($R2, Array
                    (
                     'id' => '0',
                     'client_id' => $client_id,
                     'payment_no' => 0,
                     'bill_no' => 'saldo',
                     'bill_vis_no' => 'saldo',
                     'payment_date' => $saldo['ts'],
                     'oper_date' => $saldo['ts'],
                     'payment_rate' => 1.0000,
                     'type' => 'priv',
                     'sum_rub' => $sum,
                     'currency' => 'RUR',
                     'comment' => '',
                     'add_date' => $saldo['ts'],
                     'add_user' => 0,
                     'push_1c' => 'yes',
                     'sync_1c' => 'yes',
                     'sum' => $sum
                    ) );
        }elseif($sum < 0){

            array_unshift($R1, Array
                    (
                     'bill_no' => 'saldo',
                     'is_payed' => 1,
                     'inv_rur' => -$sum,
                     'sum' => -$sum,
                     'new_is_payed' => 0,
                     'sum_full' => -$sum
                    )
                    );
        }

        //        print_r($R2);
        //        die();

        $PaymentsOrders = array();

        foreach ($R1 as $r) {
            $balance = $balance - $r['sum'];
        }
        foreach ($R2 as $r) {
            $balance = $balance + $r['sum'];
        }
        // Цикл оплачивает минусовые счета
        foreach ($R2 as $kp => $r) {
            if ($r['sum'] >= 0) continue;

            $bill_no = $r['bill_no'];

            $sum = $r['sum'];
            if ($currency == 'RUR')
                $sum_rub = $sum;
            else
                $sum_rub = $R1[$bill_no]['sum'] * $R1[$bill_no]['inv_rur'] / $sum;

            $PaymentsOrders[] = array(    'payment_id' => $r['id'],
                    'bill_no'=>$bill_no,
                    'sum'=>$sum,
                    'currency'=>$currency,
                    'sum_rub'=>$sum_rub,
                    "tt" => 1);

            $R1[$bill_no]['sum_full'] -= $sum;
            $R1[$bill_no]['sum'] -= $sum;
            $R1[$bill_no]['inv_rur'] -= $sum_rub;

            $R2[$kp]['sum'] = 0;
        }

        // Цикл оплачивает счета для которых существует оплата с жестко указанным номером счета
        foreach ($R2 as $kp => $r) {
            if ($r['sum'] < 0.01) {continue;}


            if ($r['bill_no'] == '') continue;
            $bill_no = $r['bill_no'];

            if (isset($R1[$bill_no]) && ($R1[$bill_no]['new_is_payed']==0 || $R1[$bill_no]['new_is_payed']==2) && $R1[$bill_no]['sum_full'] >= 0) {
                if ($this->sum_more($r['sum'],$R1[$bill_no]['sum_full'],$currency)) {
                    //echo "[".$r['sum']."|".$R1[$bill_no]['sum_full']."]";
                    $sum = round($R1[$bill_no]['sum_full'], 2);
                    if ($currency == 'RUR')
                        $sum_rub = $sum;
                    else{
                        $sum_rub = $sum != 0 ? $R1[$bill_no]['sum'] * $R1[$bill_no]['inv_rur'] / $sum : 0;
                    }

                    $PaymentsOrders[] = array(
                            'payment_id' => $r['id'],
                            'bill_no'    => $bill_no,
                            'sum'        => $sum,
                            'currency'   => $currency,
                            'sum_rub'    => $sum_rub,
                            'payment_no' => $r['payment_no'],
                            'tt'         => 2);


                    $R2[$kp]['sum'] -= $sum;

                    if  ($R2[$kp]['sum'] < 0.01) {
                        $R2[$kp]['sum'] = 0;
                    }

                    $R1[$bill_no]['new_is_payed'] = 1;
                    $R1[$bill_no]['sum'] = 0;
                    $R1[$bill_no]['sum_full'] = 0;
                    $R1[$bill_no]['inv_rur'] = 0;

                } elseif ($r['sum'] >= 0.01){

                    $sum = $r['sum'];
                    if ($currency == 'RUR')
                        $sum_rub = $sum;
                    else
                        $sum_rub = $R1[$bill_no]['sum'] * $R1[$bill_no]['inv_rur'] / $sum;

                    $PaymentsOrders[] = array(    'payment_id' => $r['id'],
                            'bill_no'=>$bill_no,
                            'sum'=>$sum,
                            'currency'=>$currency,
                            'sum_rub'=>$sum_rub,
                            'payment_no'=>$r['payment_no'],
                            'tt' => 3);

                    $R2[$kp]['sum'] = 0;

                    $R1[$bill_no]['new_is_payed'] = 2;
                    $R1[$bill_no]['sum'] -= $sum;
                    $R1[$bill_no]['sum_full'] -= $sum;
                    $R1[$bill_no]['inv_rur'] -= $sum_rub;

                    if ($R1[$bill_no]['sum_full'] < 0.01) {
                        $R1[$bill_no]['sum'] = 0;
                        $R1[$bill_no]['sum_full'] = 0;
                        $R1[$bill_no]['inv_rur'] = 0;
                    }

                }
            }
        }

        // если счет оплатили и столько же списали - считать не оплаченным
        foreach($R1 as $k => $r)
        {
            if($r["new_is_payed"] == 2 && $r["inv_rur"] == 0)
            {
                $R1[$k]["new_is_payed"] = 0;
            }
        }

        // Цикл оплачивает счета для которых существует оплата с жестко указанным номером счета ПРИВЯЗКИ.
        // Новых счетов с привязкой не будет. Нужно для совместимости
        foreach ($R2 as $kp => $r) {
            if ($r['sum'] < 0.01) continue;

            $bill_no = $r['bill_vis_no'];

            if (isset($R1[$bill_no]) && ($R1[$bill_no]['new_is_payed']==0 || $R1[$bill_no]['new_is_payed']==2) && $R1[$bill_no]['sum_full'] > 0) {
                if ($this->sum_more($r['sum'],$R1[$bill_no]['sum_full'],$currency)) {
                    ///echo "[".$r['sum']."|".$R1[$bill_no]['sum_full']."]";
                    $sum = round($R1[$bill_no]['sum_full'], 2);
                    if ($currency == 'RUR')
                        $sum_rub = $sum;
                    else
                        $sum_rub = $R1[$bill_no]['sum'] * $R1[$bill_no]['inv_rur'] / $sum;

                    if (abs($sum) >= 0.01){
                        $PaymentsOrders[] = array(    'payment_id' => $r['id'],
                                'bill_no'=>$bill_no,
                                'sum'=>$sum,
                                'currency'=>$currency,
                                'sum_rub'=>$sum_rub,
                                'tt' => 4);
                    }


                    $R2[$kp]['sum'] -= $sum;

                    if  ($R2[$kp]['sum'] < 0.01) {
                        $R2[$kp]['sum'] = 0;
                    }


                    $R1[$bill_no]['new_is_payed'] = 1;
                    $R1[$bill_no]['sum'] = 0;
                    $R1[$bill_no]['sum_full'] = 0;
                    $R1[$bill_no]['inv_rur'] = 0;

                } elseif ($r['sum'] >= 0.01){
                    $sum = $r['sum'];
                    if ($currency == 'RUR')
                        $sum_rub = $sum;
                    else
                        $sum_rub = $R1[$bill_no]['sum'] * $R1[$bill_no]['inv_rur'] / $sum;

                    if (abs($sum) >= 0.01){
                        $PaymentsOrders[] = array(    'payment_id' => $r['id'],
                                'bill_no'=>$bill_no,
                                'sum'=>$sum,
                                'currency'=>$currency,
                                'sum_rub'=>$sum_rub,
                                'tt' => 5);
                    }

                    $R2[$kp]['sum'] = 0;

                    $R1[$bill_no]['new_is_payed'] = 2;
                    $R1[$bill_no]['sum'] -= $sum;
                    $R1[$bill_no]['sum_full'] -= $sum;
                    $R1[$bill_no]['inv_rur'] -= $sum_rub;

                    if  ($R1[$bill_no]['sum_full'] < 0.01) {
                        $R1[$bill_no]['sum'] = 0;
                        $R1[$bill_no]['sum_full'] = 0;
                        $R1[$bill_no]['inv_rur'] = 0;
                    }
                }
            }
        }

        //print_r($R2);

        if ($fixclient_data["type"] != "multi"){ // не магазин

            // Раскидываем остатки оплаты по неоплаченным счетам
            foreach ($R2 as $kp => $r) {
                if ($r['sum'] < 0.01) continue;

                foreach ($R1 as $kb => $rb) {
                    //                if ($rb['new_is_payed']==0 ) { echo $rb['sum']."|"; $rb['new_is_payed']=1; continue;}

                    if ($rb['new_is_payed']==1 || $rb['new_is_payed']==3 || $rb['sum'] < 0 || $r['sum'] < 0.01) continue;

                    if ($this->sum_more($r['sum'],$rb['sum'],$currency)) {

                        $sum = $rb['sum'];
                        if ($currency == 'RUR' || $sum == 0)
                            $sum_rub = $sum;
                        else
                            $sum_rub = $rb['sum'] * $rb['inv_rur'] / $sum;

                        if (abs($sum) >= 0.01){
                            $PaymentsOrders[] = array(    'payment_id' => $r['id'],
                                    'bill_no'=>$rb['bill_no'],
                                    'sum'=>$sum,
                                    'currency'=>$currency,
                                    'sum_rub'=>$sum_rub,
                                    'tt' => 6);
                        }


                        $r['sum'] -= $sum;
                        $R2[$kp]['sum'] -= $sum;

                        if  ($R2[$kp]['sum'] < 0.01) {
                            $R2[$kp]['sum'] = 0;
                            $r['sum'] = 0;
                        }


                        $R1[$kb]['new_is_payed'] = 1;
                        $R1[$kb]['sum'] = 0;
                        $R1[$kb]['sum_full'] = 0;
                        $R1[$kb]['inv_rur'] = 0;

                    } elseif ($r['sum'] >= 0.01){

                        $sum = $r['sum'];
                        if ($currency == 'RUR')
                            $sum_rub = $sum;
                        else
                            //$sum_rub = $rb['sum'] * $rb['inv_rur'] / $sum;
                            $sum_rub =  $sum * $rb['inv_rur'] / $rb['sum'] ;

                        if (abs($sum) >= 0.01){
                            $PaymentsOrders[] = array(
                                    'payment_id' => $r['id'],
                                    'bill_no'=>$rb['bill_no'],
                                    'sum'=>$sum,
                                    'currency'=>$currency,
                                    'sum_rub'=>$sum_rub,
                                    'rb' => $rb,
                                    'ttt' => $rb["sum"]." * ".$rb["inv_rur"]." / ".$sum,
                                    'tt' => 7);
                        }

                        $r['sum'] = 0;
                        $R2[$kp]['sum'] = 0;

                        $R1[$kb]['new_is_payed'] = 2;
                        $R1[$kb]['sum'] -= $sum;
                        $R1[$kb]['sum_full'] -= $sum;
                        $R1[$kb]['inv_rur'] -= $sum_rub > $R1[$kb]['inv_rur'] ? $R1[$kb]['inv_rur'] : $sum_rub ;

                    }
                }
            }


            // Если все счета оплачены и осталась лишняя оплата то в любом случае закидываем ее на последний счет, даже если будет переплата.

            $last_payment = null;
            foreach ($R1 as $k => $r) {

                if ( ($r['new_is_payed']==0 || $r['new_is_payed']==2) && $this->sum_more(0,$r['sum_full'],$currency, 1)) {
                    $R1[$k]['new_is_payed'] = 1;
                }

                if ($r['sum_full'] < 0){
                    $R1[$k]['new_is_payed'] = 1;
                }

                $last_payment = $r;
            }

            foreach ($R2 as $k => $v) {
                if ($v['sum'] == 0) continue;

                $sum = $v['sum'];
                if ($currency == 'RUR')
                    $sum_rub = $sum;
                else
                    $sum_rub = $last_payment['sum'] * $last_payment['inv_rur'] / $sum;

                if (abs($sum) >= 0.01){
                    $PaymentsOrders[] = array(    'payment_id' => $v['id'],
                            'bill_no'=>$last_payment['bill_no'],
                            'sum'=>$sum,
                            'currency'=>$currency,
                            'sum_rub'=>$sum_rub,
                            'tt' => 8
                            );
                }
            }

        } // не магазин


        $db->Query('START TRANSACTION');

        foreach ($R1 as $k => $v) 
        {
            if($v["bill_no"] == "saldo") continue; 

            if ($v['is_payed'] != $v['new_is_payed']) 
            {
                $doc = Bill::getDocument($k, $fixclient_data["id"]);
                $doc->is_payed = $v['new_is_payed'];
                $doc->save();
            }
        }
        $db->Query('delete from newpayments_orders where client_id='.$client_id);

        foreach ($PaymentsOrders as $r) {
            $db->Query($q='INSERT INTO newpayments_orders (`payment_id`, `bill_no`, `client_id`, `sum`, `currency`, `sum_rub`, `sync_1c`)
                    VALUES ("'.$r['payment_id'].'","'.$r['bill_no'].'","'.$client_id.'","'.$r['sum'].'","'.$r['currency'].'","'.$r['sum_rub'].'","no")
                    ON DUPLICATE KEY UPDATE `sum`=`sum`+"'.$r['sum'].'", sum_rub=sum_rub+"'.$r['sum_rub'].'"');
            //echo "<br>".$q;
        }
        //$db->Query("UPDATE clients SET balance='{$balance}' WHERE id={$client_id}");
        $db->Query("
              UPDATE clients
                    SET balance='{$balance}',
                              last_account_date=(
                      select b.bill_date
                      from newbills b left join newbill_lines bl on b.bill_no=bl.bill_no
                      where b.client_id={$client_id} and bl.service = 'usage_voip'
                      group by b.bill_date
                      order by b.bill_date desc
                      limit 1),
                                last_payed_voip_month=(
                      select b.bill_date - interval day(b.bill_date)-1 day
                      from newbills b left join newbill_lines bl on b.bill_no=bl.bill_no
                      where b.client_id={$client_id} and bl.service = 'usage_voip' and b.is_payed=1
                      group by b.bill_date
                      order by b.bill_date desc
                                    limit 1)
                    WHERE id={$client_id}");
        $db->Query('COMMIT');
    }

    function newaccounts_bill_list($fixclient,$get_sum=false){
        global $design, $db, $pg_db, $user, $fixclient_data;
        if(!$fixclient)
            return;

        set_time_limit(60);

        $_SESSION['clients_client'] = $fixclient;

        $t = get_param_raw('simple',null);
        if($t!==null)
            $user->SetFlag('balance_simple',$t);

        $sum_l = array(
            "service" => array("USD" => 0, "RUR" => 0),
            "zalog"   => array("USD" => 0, "RUR" => 0),
            "zadatok" => array("USD" => 0, "RUR" => 0),
            "good"    => array("USD" => 0, "RUR" => 0)

        );

        foreach($db->AllRecords(
            "select sum(l.sum) as sum, l.type, b.currency
            from newbills b, newbill_lines l
            where
                    client_id = '".$fixclient_data["id"]."'
                and b.bill_no = l.bill_no
                and state_1c != 'Отказ'
            group by l.type, b.currency") as $s)
                $sum_l[$s["type"]][$s["currency"]] = $s["sum"];

        $sum_l["service_and_goods"]["USD"] = $sum_l["service"]["USD"] +$sum_l["good"]["USD"];
        $sum_l["service_and_goods"]["RUR"] = $sum_l["service"]["RUR"] +$sum_l["good"]["RUR"];

        $sum_l["payments"] = $db->GetValue("select sum(sum_rub) from newpayments where client_id ='".$fixclient_data["id"]."'");

        $design->assign("sum_l", $sum_l);



        $counters = array('amount_sum'=>0, 'amount_day_sum'=>0,'amount_month_sum'=>0);

        try{

        $counters_reg = $pg_db->GetRow("SELECT  CAST(amount_sum as NUMERIC(8,2)) as amount_sum,
                                                CAST(amount_day_sum as NUMERIC(8,2)) as amount_day_sum,
                                                CAST(amount_month_sum as NUMERIC(8,2)) as amount_month_sum
                                        FROM billing.counters
                                        WHERE client_id='".$fixclient_data["id"]."'");
        }catch(Exception $e)
        {
            trigger_error($e->getMessage());
        }
        $counters['amount_sum'] = $counters_reg['amount_sum'];
        $counters['amount_day_sum'] = $counters_reg['amount_day_sum'];
        $counters['amount_month_sum'] = $counters_reg['amount_month_sum'];

        $design->assign("counters", $counters);




        if($user->Flag('balance_simple')){
            return $this->newaccounts_bill_list_simple($get_sum);
        }else{
            return $this->newaccounts_bill_list_full($get_sum);
        }
    }

    function _getSwitchTelekomDate($clientId)
    {
        global $db;

        return $db->GetValue("select min(if(apply_ts = '0000-00-00', cast(ts as date), apply_ts)) as switch_date from log_client l, log_client_fields f where client_id = '".$clientId."' and f.ver_id = l.id and l.type='fields' and field='firma' and value_to = 'mcn_telekom'");
    }

    function newaccounts_bill_list_simple($get_sum=false){
        global $design, $db, $user, $fixclient_data;

        $isMulty = $fixclient_data["type"] == "multi";
        $isViewCanceled = get_param_raw("view_canceled", null);

        if($isViewCanceled === null){
            if(isset($_SESSION["view_canceled"])){
                $isViewCanceled = $_SESSION["view_canceled"];
            }else{
                $isViewCanceled = 0;
                $_SESSION["view_canceled"] = $isViewCanceled;
            }
        }else{
            $_SESSION["view_canceled"] = $isViewCanceled;
        }

        $design->assign("view_canceled", $isViewCanceled);

        $sum = array(
            'USD'=>array(
                'delta'=>0,
                'bill'=>0,
                'ts'=>''
            ),
            'RUR'=>array(
                'delta'=>0,
                'bill'=>0,
                'ts'=>''
            )
        );

        $r=$db->GetRow('
            select
                *
            from
                newsaldo
            where
                client_id='.$fixclient_data['id'].'
            and
                currency="'.$fixclient_data['currency'].'"
            and
                is_history=0
            order by
                id desc
            limit 1
        ');
        if($r){
            $sum[$fixclient_data['currency']]
                =
            array(
                'delta'=>0,
                'bill'=>$r['saldo'],
                'ts'=>$r['ts'],
                'saldo'=>$r['saldo']
            );
        }else{
            $sum[$fixclient_data['currency']]
                =
            array(
                'delta'=>0,
                'bill'=>0,
                'ts'=>''
            );
        }

        $sqlLimit = $fixclient_data["type"] == "multi" ? " limit 200" : "";

        $R1 = $db->AllRecords($q='
            select
                *,
                '.(
                    $sum[$fixclient_data['currency']]['ts']
                        ?    'IF(bill_date >= "'.$sum[$fixclient_data['currency']]['ts'].'",1,0)'
                        :    '1'
                ).' as in_sum
            from
                newbills
            '.($isMulty && !$isViewCanceled ? "
                left join tt_troubles t using (bill_no)
                left join tt_stages ts on  (ts.stage_id = t. cur_stage_id)
                " : "").'
            where
                client_id='.$fixclient_data['id'].'
                '.($isMulty && !$isViewCanceled? " and (state_id is null or (state_id is not null and state_id !=21)) " : "").'
            order by
                bill_date desc,
                bill_no desc
            '.$sqlLimit.'
        ','',MYSQL_ASSOC);


        $R2 = $db->AllRecords('
            select
                P.*,
                (P.sum_rub/P.payment_rate) as sum,
                U.user as user_name,
                '.(
                    $sum[$fixclient_data['currency']]['ts']
                        ?    'IF(P.payment_date>="'.$sum[$fixclient_data['currency']]['ts'].'",1,0)'
                        :    '1'
                ).' as in_sum
            from
                newpayments as P
            LEFT JOIN
                user_users as U
            on
                U.id=P.add_user
            where
                P.client_id='.$fixclient_data['id'].'
            order by
                P.payment_date
            desc
                '.$sqlLimit.'
            ',
        '',MYSQL_ASSOC);

        $R=array();
        foreach($R1 as &$r){
            $v=array(
                'bill'=>$r,
                'date'=>$r['bill_date'],
                'pays'=>array(),
                'delta'=>-$r['sum']
            );
            foreach($R2 as $k2=>$r2){
                $r2['bill_vis_no'] = $r2['bill_no'];
                $R2[$k2]['bill_vis_no'] = $r2['bill_no'];
                if(
                    $r['bill_no'] == $r2['bill_no']
                &&
                    (
                        $r2['bill_no'] == $r2['bill_vis_no']
                    )
                ){
                    $r2['divide']=0;
                    $v['pays'][]=$r2;
                    $v['delta']+=$r2['sum'];
                    unset($R2[$k2]);
                }
            }

            foreach($R2 as $k2=>$r2)
                if(
                    $r['bill_no'] == $r2['bill_no']
                &&
                    $r2['bill_no'] != $r2['bill_vis_no']
                ){
                    $d = round(-$v['delta'],2);
                    $R2[$k2]['sum'] = $r2['sum']-$d;
                    $R2[$k2]['sum_rub'] = round($R2[$k2]['sum']*$R2[$k2]['payment_rate'],2);
                    $r2['sum'] = $d;
                    $r2['sum_rub'] = round($r2['sum']*$r2['payment_rate'],2);
                    $r2['divide'] = 1;
                    $v['pays'][] = $r2;
                    $v['delta'] -= $d;
                }
            $r['v'] = $v;
        }
        unset($r);
        foreach($R1 as $r){
            $v=$r['v'];
            foreach($R2 as $k2=>$r2)
                if(
                    $r['bill_no'] == $r2['bill_vis_no']
                &&
                    $r2['bill_no'] != $r['bill_no']
                ){
                    $r2['divide']=2;
                    $v['pays'][]=$r2;
                    $v['delta']+=round($r2['sum'],2);
                    unset($R2[$k2]);
                }
            if($r['in_sum']){
                $sum[$r['currency']]['bill'] += $r['sum'];
                $sum[$r['currency']]['delta'] -= $v['delta'];
            }
            $R[$r['bill_no']] = $v;
        }
        foreach($R2 as $r2){
            $v = array(
                'date'=>$r2['payment_date'],
                'pays'=>array($r2),
                'delta'=>$r2['sum']
            );
            if($r2['in_sum'])
                $sum[$fixclient_data['currency']]['delta']-=$v['delta'];
            $R[]=$v;
        }
        if($get_sum){
            return $sum;
        }
        ## sorting
        $sk = array();
        foreach($R as $bn=>$b){
            if(!isset($sk[$b['date']]))
                $sk[$b['date']] = array();
            $sk[$b['date']][$bn] = 1;
        }
        $buf = array();

        $sw = array();

        krsort($sk);

        foreach($sk as $bn){
            krsort($bn);
            foreach($bn as $billno=>$v)
            {
                $buf[$billno] = $R[$billno];

                $bDate = isset($R[$billno]) && isset($R[$billno]["bill"]) ? $R[$billno]["bill"]["bill_date"] : false;

                if($bDate)
                {
                    $sw[$bDate] = $billno;
                }

            }
        }

        $R = $buf;


        ksort($buf);
        ksort($sw);

        if($stDate = $this->_getSwitchTelekomDate($fixclient_data["id"]))
        {
            $ks = false;
            foreach($sw as $bDate => $billNo)
            {
                if($bDate >= $stDate)
                {
                    $ks = $billNo;
                    break;
                }
            }

            if($ks && isset($R[$ks]))
                $R[$ks]["switch_to_mcn"] = 1;
            
        }

        #krsort($R);
        $design->assign('billops',$R);
        $design->assign('sum',$sum);
        $design->assign('sum_cur',$sum[$fixclient_data['currency']]);
        $design->assign(
            'saldo_history',
            $db->AllRecords('
                select
                    newsaldo.*,
                    user_users.name as user_name
                from
                    newsaldo
                LEFT JOIN
                    user_users
                ON
                    user_users.id = newsaldo.edit_user
                where
                    client_id='.$fixclient_data['id'].'
                order by
                    id DESC
            ')
        );

        $design->AddMain('newaccounts/bill_list_simple.tpl');
    }

    function newaccounts_bill_list_full($get_sum=false)
    {
        global $design, $db, $user, $fixclient_data;

        $isMulty = $fixclient_data["type"] == "multi";
        $isViewCanceled = get_param_raw("view_canceled", null);

        if($isViewCanceled === null){
            if(isset($_SESSION["view_canceled"])){
                $isViewCanceled = $_SESSION["view_canceled"];
            }else{
                $isViewCanceled = 0;
                $_SESSION["view_canceled"] = $isViewCanceled;
            }
        }else{
            $_SESSION["view_canceled"] = $isViewCanceled;
        }

        $design->assign("view_canceled", $isViewCanceled);

        $sum = array(
            'USD'=>array(
                'delta'=>0,
                'bill'=>0,
                'ts'=>''
            ),
            'RUR'=>array(
                'delta'=>0,
                'bill'=>0,
                'ts'=>''
            )
        );

        $r=$db->GetRow('
            select
                *
            from
                newsaldo
            where
                client_id='.$fixclient_data['id'].'
            and
                currency="'.$fixclient_data['currency'].'"
            and
                is_history=0
            order by
                id desc
            limit 1
        ');
        if($r){
            $sum[$fixclient_data['currency']]
                =
            array(
                'delta'=>0,
                'bill'=>$r['saldo'],
                'ts'=>$r['ts'],
                'saldo'=>$r['saldo']
            );
        }else{
            $sum[$fixclient_data['currency']]
                =
            array(
                'delta'=>0,
                'bill'=>0,
                'ts'=>''
            );
        }

        $R1 = $db->AllRecords($q='
                select * from (
            select
                bill_no, bill_date, client_id, currency, sum, inv_rur, is_payed, P.comment, postreg, nal,
                '.(
                    $sum[$fixclient_data['currency']]['ts']
                        ?    'IF(bill_date >= "'.$sum[$fixclient_data['currency']]['ts'].'",1,0)'
                        :    '1'
                ).' as in_sum
            from
                newbills P
            '.($isMulty && !$isViewCanceled ? "
                left join tt_troubles t using (bill_no)
                left join tt_stages ts on  (ts.stage_id = t. cur_stage_id)
                " : "").'
            where
                client_id='.$fixclient_data['id'].'
                '.($isMulty && !$isViewCanceled? " and (state_id is null or (state_id is not null and state_id !=21)) " : "").'
                ) bills
                union
                (
                    ### incomegoods
                 SELECT 
                    number as bill_no, 
                    cast(date as date) as bill_date, 
                    client_card_id as client_id, 
                    if(currency = "RUB", "RUR", currency) as currency, 
                    sum, 
                    0.0 as inv_rur,  
                    is_payed,
                    "" `comment`, 
                    "0000-00-00" postreg , 
                    "" nal, 
                    1 in_sum 

                  FROM `g_income_order` where client_card_id = "'.$fixclient_data['id'].'"
                )
            order by
                bill_date desc,
                bill_no desc
            limit 500
        ','',MYSQL_ASSOC);

        if (isset($sum[$fixclient_data['currency']]['saldo']) && $sum[$fixclient_data['currency']]['saldo'] > 0){
            array_unshift($R1, Array
                                (
                                    'bill_no' => 'saldo',
                                    'bill_date' => $sum[$fixclient_data['currency']]['ts'],
                                    'client_id' => $fixclient_data['id'],
                                    'currency' => $fixclient_data['currency'],
                                    'sum' => $sum[$fixclient_data['currency']]['saldo'],
                                    'inv_rur' => $sum[$fixclient_data['currency']]['saldo'],
                                    'is_payed' => 1,
                                    'comment' => '',
                                    'postreg' => $sum[$fixclient_data['currency']]['ts'],
                                    'nal' => 'prov',
                                    'in_sum' => 1
                                ) );
            $sum[$fixclient_data['currency']]['saldo'] = 0;
        }


        $R2 = $db->AllRecords('

            select
                P.id, P.client_id, P.payment_no, P.payment_date, P.oper_date, P.payment_rate, P.type, P.sum_rub, P.currency, P.comment, P.add_date, P.add_user, P.p_bill_no, P.p_bill_vis_no,
                P.sum_rub as sum_rub_full,
                U.user as user_name,
                '.(
                    $sum[$fixclient_data['currency']]['ts']
                        ?    'IF(P.payment_date>="'.$sum[$fixclient_data['currency']]['ts'].'",1,0)'
                        :    '1'
                ).' as in_sum,
                P.payment_id,
                P.bill_no,
                P.sum_pay,
                P.sum_pay_rub,
                P.bank
            from (    SELECT P.id, P.client_id, P.payment_no, P.payment_date, P.oper_date, P.payment_rate, P.type, P.sum_rub, P.currency, P.comment, P.add_date, P.add_user, P.bill_no as p_bill_no, P.bill_vis_no as p_bill_vis_no,
                        L.payment_id, L.bill_no, L.sum as sum_pay, L.sum_rub as sum_pay_rub, P.bank
                    FROM newpayments P LEFT JOIN newpayments_orders L ON L.client_id='.$fixclient_data['id'].' and P.id=L.payment_id
                    WHERE P.client_id='.$fixclient_data['id'].'
                    UNION
                    SELECT P.id, P.client_id, P.payment_no, P.payment_date, P.oper_date, P.payment_rate, P.type, P.sum_rub, P.currency, P.comment, P.add_date, P.add_user, P.bill_no as p_bill_no, P.bill_vis_no as p_bill_vis_no,
                        L.payment_id, L.bill_no, L.sum as sum_pay, L.sum_rub as sum_pay_rub, P.bank
                    FROM newpayments P RIGHT JOIN newpayments_orders L ON P.client_id='.$fixclient_data['id'].' and P.id=L.payment_id
                    WHERE L.client_id='.$fixclient_data['id'].'
                ) as P

            LEFT JOIN user_users as U on U.id=P.add_user

            order by
                P.payment_date desc
            limit 500
                ',
        '',MYSQL_ASSOC);
        $R=array();


        foreach($R1 as $k=>$r){
            $v=array(
                'bill'=>$r,
                'date'=>$r['bill_date'],
                'pays'=>array(),
                'delta'=>-$r['sum'],
                'delta2'=>-$r['sum']
            );

            foreach($R2 as $k2=>$r2){

                if ($r2['payment_rate'] != ''){
                    $r2['sum_rub_full'] = round($r2['sum_rub_full'],2);
                    $r2['sum_full'] = round($r2['sum_rub_full']/$r2['payment_rate'],2);
                    $R2[$k2]['sum_rub_full'] = $r2['sum_rub_full'];
                    $R2[$k2]['sum_full'] = $r2['sum_full'];
                }
//                $r2['sum_pay_rub'] = round($r2['sum_pay']*$r2['payment_rate'],2);

                if($r['bill_no'] == $r2['p_bill_no']){
                    if (!isset($v['pays'][$r2['id']])){
                        if ($r2['payment_rate'] != '')
                            $v['delta']+=$r2['sum_full'];
                        $v['pays'][$r2['id']]=$r2;
                    }
                }
            }
/*
            foreach($R2 as $k2=>$r2){

                if ($r2['payment_rate'] != ''){
                    $r2['sum_rub_full'] = round($r2['sum_rub_full'],2);
                    $r2['sum_full'] = round($r2['sum_rub_full']/$r2['payment_rate'],2);
                    $R2[$k2]['sum_rub_full'] = $r2['sum_rub_full'];
                    $R2[$k2]['sum_full'] = $r2['sum_full'];
                }
//                $r2['sum_pay_rub'] = round($r2['sum_pay']*$r2['payment_rate'],2);

                if($r['bill_no'] == $r2['p_bill_no']){
                    if (!isset($v['pays'][$r2['id']])){
                        if ($r2['payment_rate'] != '')
                            $v['delta']+=$r2['sum_full'];
                        $v['pays'][$r2['id']]=$r2;
                    }
                }
            }
*/
            $R1[$k]['v'] = $v;
        }

        foreach($R1 as $k=>$r){
            $v=$r['v'];

            foreach($R2 as $k2=>$r2){

                if($r['bill_no'] == $r2['bill_no']){
                    $v['delta2']+=$r2['sum_pay'];

                    if ($r['bill_no'] != $r2['p_bill_no'])
                        $r2['comment'] = '';

                    $v['pays'][$r2['id']]=$r2;

                    unset($R2[$k2]);
                }

            }
            $R1[$k]['v'] = $v;
        }



        foreach($R1 as $r){
            $v=$r['v'];
            if($r['in_sum']){
                $sum[$r['currency']]['bill'] += $r['sum'];
                $sum[$r['currency']]['delta'] -= $v['delta'];
            }
            $R[$r['bill_no']] = $v;
        }
        foreach($R2 as $r2){
            if ($r2['sum_pay'] == '' || $r2['sum_pay'] == 0) continue;
            $v = array(
                'date'=>$r2['payment_date'],
                'pays'=>array($r2),
                'delta'=>$r2['sum_pay'],
                'delta2'=>$r2['sum_pay']
            );
            if($r2['in_sum'])
                $sum[$fixclient_data['currency']]['delta']-=$v['delta2'];
            $R[]=$v;
        }
        if($get_sum){
            return $sum;
        }


        ## sorting
        $sk = array();
        foreach($R as $bn=>$b){
            if(!isset($sk[$b['date']]))
                $sk[$b['date']] = array();
            $sk[$b['date']][$bn] = 1;
        }

        $sw = array();
        $buf = array();
        krsort($sk);

        foreach($sk as $bn){
            krsort($bn);
            foreach($bn as $billno=>$v)
            {
                $buf[$billno] = $R[$billno];

                $bDate = isset($R[$billno]) && isset($R[$billno]["bill"]) ? $R[$billno]["bill"]["bill_date"] : false;

                if($bDate)
                {
                    $sw[$bDate] = $billno;
                }
            }
        }
        $R = $buf;

        ksort($buf);
        ksort($sw);

        if($stDate = $this->_getSwitchTelekomDate($fixclient_data["id"]))
        {
            $ks = false;
            foreach($sw as $bDate => $billNo)
            {
                if($bDate >= $stDate)
                {
                    $ks = $billNo;
                    break;
                }
            }

            if($ks && isset($R[$ks]))
                $R[$ks]["switch_to_mcn"] = 1;
            
        }

        $qrs = array();
        foreach($db->QuerySelectAll("qr_code", array("client_id" => $fixclient_data["id"])) as $q)
        {
            $qrs[$q["bill_no"]][$q["doc_type"]] = $q["id"];
        }

        $design->assign("qrs", $qrs);


        #krsort($R);
        $design->assign('billops',$R);
        $design->assign('sum',$sum);
        $design->assign('sum_cur',$sum[$fixclient_data['currency']]);
        $design->assign(
            'saldo_history',
            $db->AllRecords('
                select
                    newsaldo.*,
                    user_users.name as user_name
                from
                    newsaldo
                LEFT JOIN
                    user_users
                ON
                    user_users.id = newsaldo.edit_user
                where
                    client_id='.$fixclient_data['id'].'
                order by
                    id DESC
            ')
        );

        $design->AddMain('newaccounts/bill_list_full.tpl');
    }

    function newaccounts_bill_create($fixclient) {
        global $design,$db,$user,$fixclient_data;
        if (!$fixclient) return;
        $currency=get_param_raw('currency');
        $bill = new Bill(null,$fixclient_data,time(),0,$currency);
        $no = $bill->GetNo();
        unset($bill);
        $db->QueryInsert("log_newbills",array('bill_no'=>$no,'ts'=>array('NOW()'),'user_id'=>$user->Get('id'),'comment'=>'Счет создан'));
        if ($design->ProcessEx('errors.tpl')) header("Location: ".$design->LINK_START."module=newaccounts&action=bill_view&bill=".$no);
    }

    function newaccounts_bill_view($fixclient){
        global $design, $db, $user, $fixclient_data;
        
        
        //old all4net bills
        if(isset($_POST['bill_no']) && preg_match('/^\d{6}-\d{4}-\d+$/',$_POST['bill_no'])){

            //set doers
            if(isset($_POST['select_doer'])){
                $d = (int)$_POST['doer'];
                $db->Query("select name from courier where id=".$d);
                $row = $db->NextRecord(MYSQL_ASSOC);
                $db->Query("update newbills set courier_id=".$d." where bill_no='".$_POST['bill_no']."'");
                $db->Query("insert into log_newbills set `bill_no` = '".$_POST['bill_no']."', ts=now(), user_id=".$user->Get('id').", comment='Назначен курьер ".$row['name']."'");
                unset($row);
            }elseif(isset($_POST['select_nal'])){
                $n = addcslashes($_POST['nal'],"\\'");
                $db->Query("update newbills set nal='".$n."' where bill_no='".$_POST['bill_no']."'");
            }
            // 1c || all4net bills
		}elseif(isset($_GET['bill']) && preg_match('/^(\d{6}\/\d{4}|\d{6,7})$/',$_GET['bill'])){
			$design->assign('1c_bill_flag',true);
			if(isset($_POST['select_doer'])){
				$d = (int)$_POST['doer'];
				$db->Query("select name from courier where id=".$d);
				$row = $db->NextRecord(MYSQL_ASSOC);
				$db->Query("update newbills set courier_id=".$d." where bill_no='".$_POST['bill_no']."'");
				$db->Query("insert into log_newbills set `bill_no` = '".$_POST['bill_no']."', ts=now(), user_id=".$user->Get('id').", comment='Назначен курьер ".$row['name']."'");
				unset($row);
			}elseif(isset($_POST['select_nal'])){
				$n = addcslashes($_POST['nal'],"\\\\'");
				$db->Query("update newbills set nal='".$n."' where bill_no='".$_POST['bill_no']."'");
			}

            //income orders
		}elseif(isset($_GET["bill"]) && preg_match("/\d{2}-\d{8}/", $_GET["bill"])){ // incoming orders
            header("Location: ./?module=incomegoods&action=order_view&number=".urlencode($_GET["bill"]));
            exit();

            // stat bills
        }elseif(preg_match("/\d{6}-\d{4}/", $_GET["bill"])){
            //nothing
        }else{
            die("Неизвестный тип документа");
        }

        $bill_no=get_param_protected("bill");
        if(!$bill_no)
            return;
        $bill = new Bill($bill_no);
        if(get_param_raw('err')==1)
            trigger_error('Невозможно добавить строки из-за несовпадния валют');
        $design->assign('bgen_psum',$this->do_generate($bill,'invoice','psum',array(),false));
        $design->assign('bgen_rate',array(
            $this->do_generate($bill,'invoice','cbrf',array('inv_num'=>1),false),
            $this->do_generate($bill,'invoice','cbrf',array('inv_num'=>2),false),
            $this->do_generate($bill,'invoice','cbrf',array('inv_num'=>3),false),
            $this->do_generate($bill,'invoice','cbrf',array('inv_num'=>4),false),
            $this->do_generate($bill,'bill','cbrf',array(),false),
        ));
        if(preg_match('/^\d{6}-\d{4}-(\d+)$/',trim($bill->getNo()),$match)){
            $design->assign('all4net_order_number',$match[1]);
        }else{
            $design->assign('all4net_order_number',false);
        }

        $adminNum = false;
        if(preg_match("/^(\d{6,7})$/", trim($bill->getNo()),$match))
        {
            $adminNum = $match[1];
            $design->assign('order_editor',$bill->Get("editor"));
        }
        $design->assign('admin_order',$adminNum);

        $design->assign('assignment',$db->GetValue("select id from test_operator.mcn_client where id = '".$bill->Get("client_id")."'"));

        $design->assign('bill',$bill->GetBill());
        $design->assign('bill_manager',getUserName($bill->GetManager()));
        $design->assign('bill_comment',$bill->GetStaticComment());
        $design->assign('bill_courier',$bill->GetCourier());
        $design->assign('bill_lines',$L = $bill->GetLines());
        $design->assign('bill_bonus',$bill->GetBonus());

        /*
           счет-фактура(1)-абонен.плата
           счет-фактура(2)-превышение
           счет-фктура (3)-если есть товар, тоесть тов.накладная
           Счет-фактура(4)-авансовая

           Акт (1) - абонен.плата
           Акт (2) - превышение
           Акт (3) - залог
           
           счет-фактура-акт(1)-абонен.плата
           счет-фактура-акт(2)-превышение
           
         */

        list($bill_akts, $bill_invoices, $bill_utd) = $this->get_bill_docs($bill, $L);

        $design->assign('bill_akts', $bill_akts);

        $design->assign('bill_invoices', $bill_invoices);

        $design->assign('bill_utd', $bill_utd);

        $design->assign('template_bills',
            $db->AllRecords('
                SELECT *
                FROM newbills
                WHERE client_id=2818
                    AND currency="'.$bill->Get('currency').'"
                ORDER BY bill_no
            ')
        );

        //// start:comstar
        $pay_to_comstar = 0;
        foreach ($L as $l_item){
            if ($l_item['num_id'] == 3234 || $l_item['num_id'] == 4216 || $l_item['num_id'] == 3606 || $l_item['num_id'] == 3785 || $l_item['num_id'] == 3865){
                $pay_to_comstar = $pay_to_comstar + round($l_item['outprice']*1.18,2);
            }
        }
        if ($pay_to_comstar > 0){
            $ai = $db->GetRow("select acc_no from newbills_add_info where bill_no = '".$bill_no."'");
            $pay_to_comstar_acc_no = trim(substr(trim($ai['acc_no']), 0, 12));
            $pay_to_comstar_back = WEB_ADDRESS.WEB_PATH.$design->LINK_START.'module=newaccounts&action=bill_view&bill='.$bill_no;

            $design->assign('pay_to_comstar_acc_no', $pay_to_comstar_acc_no);
            $design->assign('pay_to_comstar_back', $pay_to_comstar_back);
        }
        $design->assign('pay_to_comstar', $pay_to_comstar);
        if (isset($_GET['ym_pay']))
        $design->assign('ym_pay', $_GET['ym_pay']);
        //// end:comstar

        $r = $bill->Client();
        ClientCS::Fetch($r);


        $r["client_orig"] = $r["client"];

        if(access("clients", "read_multy"))
            if($r["type"] != "multi"){
            trigger_error('Доступ к клиенту ограничен');
            return;
        }

        if($r["type"] == "multi" && isset($_GET["bill"])){
            $ai = $db->GetRow("select fio from newbills_add_info where bill_no = '".$_GET["bill"]."'");
            if($ai){
                $r["client"] = $ai["fio"]." (".$r["client"].")";
            }
        }
        $design->assign('bill_client',$r);
        $design->assign('bill_history',
            $db->AllRecords('
                SELECT
                    log_newbills.*,
                    user_users.user
                FROM log_newbills
                LEFT JOIN user_users ON user_users.id = user_id
                WHERE bill_no="'.$bill_no.'"
                ORDER BY ts DESC
            ')
        );
        $design->assign('doers',
            $db->AllRecords('
                SELECT *
                FROM courier
                WHERE enabled="yes"
                ORDER BY `depart` DESC, `name`
            ')
        );

        $design->assign("is_set_date", $bill->is1CBill() || $bill->isOneTimeService()); //дату документа можно установить в 1Сных счетах и счетах, с разовыми услугами

        $design->assign("store", $db->GetValue("SELECT s.name FROM newbills_add_info n, `g_store` s where s.id = n.store_id and n.bill_no = '".$bill_no."'"));

        $design->AddMain('newaccounts/bill_view.tpl');

        $tt = $db->GetRow("SELECT * FROM tt_troubles WHERE bill_no='".$bill_no."'");
        if($tt){
            $GLOBALS['module_tt']->dont_filters = true;
            #$GLOBALS['module_tt']->showTroubleList(0,'top',$fixclient,null,null,$tt['id']);
            $GLOBALS['module_tt']->cur_trouble_id = $tt['id'];
            $GLOBALS['module_tt']->tt_view($fixclient);
            $GLOBALS['module_tt']->dont_again = true;
        }
    }

    function get_bill_docs(Bill &$bill, $L = null)
    {
        if(!$L)
            $L = $bill->GetLines();

        $period_date = get_inv_date_period($bill->GetTs());
        $p1 = self::do_print_prepare_filter('invoice',1,$L,$period_date);
        $a1 = self::do_print_prepare_filter('akt',1,$L,$period_date);

        $p2 = self::do_print_prepare_filter('invoice',2,$L,$period_date);
        $a2 = self::do_print_prepare_filter('akt',2,$L,$period_date);

        $p3 = self::do_print_prepare_filter('invoice',3,$L,$period_date,true,true);
        $a3 = self::do_print_prepare_filter('akt',3,$L,$period_date);

        $p4 = self::do_print_prepare_filter('lading',1,$L,$period_date);
        $p5 = self::do_print_prepare_filter('invoice',4,$L,$period_date);

        $p6 = self::do_print_prepare_filter('invoice',5,$L,$period_date);

        $gds = self::do_print_prepare_filter('gds',3,$L,$period_date);

        $bill_akts = array(
                null,
            1=>count($a1),
            2=>count($a2),
            3=>count($a3)
        );

        $bill_invoices = array(
            null,
            count($p1),
            count($p2),
            count($p3),
            count($p4),
            ($p5==-1 || $p5 == 0)?$p5:count($p5),
            count($p6),
            count($gds)
        );
        
        $bill_utd = array(
            null,
            1=>count($p1),
            2=>count($p2)
        );

        //printdbg(array("akts" => $bill_akts, "bills" => $bill_invoices, "p3" => $p3));
        return array($bill_akts, $bill_invoices, $bill_utd);
    }

    function newaccounts_bill_courier_comment()
    {
        $doerId= get_param_raw("doer_id", "0");
        $billNo= get_param_raw("bill", "");
        $comment = trim(get_param_protected("comment", ""));
        var_export($comment);
        var_export($billNo);
        if($comment && $billNo)
        {
            global $db;
            $db->Query("update tt_troubles set doer_comment = '".$comment."' where bill_no = '".$billNo."'");
            all4geo::getId($billNo, $doerId, $comment);
        }

        header("Location: ./?module=newaccounts&action=bill_view&bill=".urlencode($billNo)."#doer_comment");
        exit();
    }

    function newaccounts_bill_cleared(){
        global $db;
        $bill_no=$_POST['bill_no'];
        $db->Query('call switch_bill_cleared("'.addcslashes($bill_no, "\\\"").'")');
        header('Location: index.php?module=newaccounts&action=bill_view&bill='.$bill_no);
        exit();
    }

    function newaccounts_bill_edit($fixclient){
        global $design,$db,$user,$fixclient_data;
        $bill_no = get_param_protected("bill");
        if(!$bill_no)
            return;

        if(!eregi("20[0-9]{4}-[0-9]{4}", $bill_no)) {
            header("Location: ./?module=newaccounts&action=make_1c_bill&bill_no=".$bill_no);
            exit();
        }


        $bill = new Bill($bill_no);
        if($bill->IsClosed()){
            header("Location: ./?module=newaccounts&action=bill_view&bill=".$bill_no);
            exit();
        }
        $_SESSION['clients_client'] = $bill->Get("client_id");
        $fixclient_data = ClientCS::FetchClient($bill->Get("client_id"));
        if(!$bill->CheckForAdmin())
            return;
        $design->assign('bills_list',$db->AllRecords("select `bill_no`,`bill_date` from `newbills` where `client_id`=".$fixclient_data['id']." order by `bill_date` desc",null,MYSQL_ASSOC));
        $design->assign('bill',$bill->GetBill());
        $design->assign('l_couriers',$bill->GetCouriers());
        $V = $bill->GetLines();
        $V[$bill->GetMaxSort()+1] = array();
        $V[$bill->GetMaxSort()+2] = array();
        $V[$bill->GetMaxSort()+3] = array();
        $design->assign('bill_lines',$V);
        $design->AddMain('newaccounts/bill_edit.tpl');
    }

    function newaccounts_bill_comment($fixclient) {
        global $design,$db,$user,$fixclient_data;
        $bill_no=get_param_protected("bill"); if (!$bill_no) return;
        $bill = new Bill($bill_no);
        $bill->Set('comment',get_param_raw("comment"));
        unset($bill);
        if ($design->ProcessEx('errors.tpl')) header("Location: ".$design->LINK_START."module=newaccounts&action=bill_view&bill=".$bill_no);
    }
    function newaccounts_bill_postreg($fixclient) {
        global $design,$db,$user,$fixclient_data;

        $bills=get_param_raw("bill", array());
        if (!$bills) return;

        if(!is_array($bills)) $bills = array($bills);
        $option = get_param_protected('option');
        $isImport = get_param_raw("from", "") == "import";

        foreach($bills as $bill_no)
        {
            $bill = new Bill($bill_no);

            $bill->Set('postreg',$option?'':date('Y-m-d'));
            $db->QueryInsert("log_newbills",array('bill_no'=>$bill_no,'ts'=>array('NOW()'),'user_id'=>$user->Get('id'),'comment'=>$option?'Удаление из почтового реестра':('Почтовый реестр '.date('Y-m-d').($isImport ? " (из импорта платежей)" : ""))));
            unset($bill);
        }
        if ($design->ProcessEx('errors.tpl')) header("Location: ".$design->LINK_START."module=newaccounts&action=bill_view&bill=".$bill_no);
    }
    function newaccounts_bill_apply($fixclient) {
        global $design,$db,$user,$fixclient_data;

        $_SESSION['clients_client'] = get_param_integer("client_id",0);

        $bill_no = get_param_protected("bill");
        if(!$bill_no)
            return;
        $item = get_param_raw("item");
        if(!$item)
            return;
        $amount = get_param_raw("amount");
        if(!$amount)
            return;
        $price = get_param_raw("price");
        if(!$price)
            return;
        $type = get_param_raw("type");
        if(!$type)
            return;
        $del = get_param_raw("del",array());
        $bill_date = get_param_raw("bill_date");
        $bill_nal = get_param_raw("nal");
        $billCourier = get_param_raw("courier");
        $bill = new Bill($bill_no);
        if(!$bill->CheckForAdmin())
            return;
        $bill->Set('bill_date',$bill_date);
        $bill->SetCourier($billCourier);
        $bill->SetNal($bill_nal);
        $V = $bill->GetLines();
        $V[$bill->GetMaxSort()+1] = array();
        $V[$bill->GetMaxSort()+2] = array();
        $V[$bill->GetMaxSort()+3] = array();
        foreach($V as $k=>$arr_v){
            if(((!isset($item[$k]) || (isset($item[$k]) && !$item[$k])) && isset($arr_v['item'])) || isset($del[$k])){
                $bill->RemoveLine($k);
            }elseif(isset($item[$k]) && $item[$k] && isset($arr_v['item'])){
                $bill->EditLine($k,$item[$k],$amount[$k],$price[$k],$type[$k]);
            }elseif(isset($item[$k]) && $item[$k]){
                $bill->AddLine($bill->Get('currency'),$item[$k],$amount[$k],$price[$k],$type[$k],'','','','');
            }
        }
        $bill->Save();
        /*
        $move = get_param_raw("move",false);
        $move_bill = get_param_raw('move_bill',false);
        if($move && $move_bill && preg_match('/^\d+-\d+(?:-\d+)?$/',$move_bill)){
            $mv = array();
            foreach($move as $sort=>$v){
                $mv[] = $sort;
            }
            $db->Query("
                update
                    `newbill_lines`
                set
                    `bill_no` = '".$move_bill."'
                where
                    `bill_no` = '".$bill_no."'
                and
                    `sort` in (".implode(',',$mv).")
            ");
        }
        $bill->Save();
        */
        $this->update_balance($bill->Client('id'),$bill->Get('currency'));
        unset($bill);
        if ($design->ProcessEx('errors.tpl')) header("Location: ?module=newaccounts&action=bill_view&bill=".$bill_no);
    }

    function newaccounts_bill_add($fixclient){
        global $design, $db,$user, $fixclient_data;
        $bill_no = get_param_protected("bill");
        if(!$bill_no)
            return;
        $obj = get_param_protected("obj");
        if(!$obj)
            return;
        $bill = new Bill($bill_no);
        if(!$bill->CheckForAdmin())
            return;
        $L=array('USD'=>array(
                    'avans' =>            array("Аванс за подключение интернет-канала",1,500/27,'zadatok'),
                    'deposit' =>        array("Задаток за подключение интернет-канала",1,SUM_ADVANCE,'zadatok'),
                    'deposit_back' =>    array("Возврат задатка за подключение интернет-канала",1,-SUM_ADVANCE,'zadatok'),
                    'deposit_sub' =>    array("За вычетом ранее оплаченного задатка",1,-SUM_ADVANCE,'zadatok'),
                ),'RUR'=>array(
                    'avans' =>            array("Аванс за подключение интернет-канала",1,500,'zadatok'),
                    'deposit' =>        array("Задаток за подключение интернет-канала",1,SUM_ADVANCE*27,'zadatok'),
                    'deposit_back' =>    array("Возврат задатка за подключение интернет-канала",1,-SUM_ADVANCE*27,'zadatok'),
                    'deposit_sub' =>    array("За вычетом ранее оплаченного задатка",1,-SUM_ADVANCE*27,'zadatok'),
                ));
        $err=0;
        if ($obj=='connecting' || $obj=='connecting_ab') {
            $services = get_all_services($fixclient,$fixclient_data['id'],1);
            foreach ($services as $service) {
                $s=ServiceFactory::Get($service,$bill);
                $s->SetMonth(strtotime($s->service['actual_from']));
                if ($obj=='connecting') {
                    $R=$s->GetLinesConnect();
                    if (!$bill->AddLines($R)) $err=1;
                }
                $R=$s->GetLinesMonth();
                if (!$bill->AddLines($R)) $err=1;
                $db->Query("update ".$service['service']." set status='working' where id='".$service['id']."'");
            }
        } elseif ($obj=='regular') {
            ClientCS::getClientClient($fixclient);
            $services = get_all_services($fixclient,$fixclient_data['id']);

            $time = $bill->GetTs(); //берем дату счета, а не дату нажатия кнопки
            foreach ($services as $service){
                // если у нас телефония, или интернет, и канал уже закрыт прошлым числом - все равно надо предъявлять превышение лимита
                if(!in_array($service['service'],array('usage_voip','usage_ip_ports')) && (unix_timestamp($service['actual_from']) > $time || unix_timestamp($service['actual_to']) < $time))
                    continue;
                $s=ServiceFactory::Get($service,$bill);
                $s->SetMonth($bill->GetTs());
                $R=$s->GetLinesMonth();
                if(empty($R[0][0]))
                    continue;
                if(!$bill->AddLines($R))
                    $err=1;
            }
        } elseif ($obj=='template') {
            $tbill=get_param_protected("tbill");
            foreach ($db->AllRecords('select * from newbill_lines where bill_no="'.$tbill.'" order by sort') as $r) {
                $bill->AddLine($bill->Get('currency'),$r['item'],$r['amount'],$r['price'],$r['type']);
            }
        } elseif (isset($L[$bill->Get('currency')][$obj])) {
            $D=$L[$bill->Get('currency')][$obj];
            if (!is_array($D[0])) $D=array($D);
            foreach ($D as $d) $bill->AddLine($bill->Get('currency'),$d[0],$d[1],$d[2],$d[3]);
        }
        $bill->Save();
        $client=$bill->Client('client');
        $this->update_balance($bill->Client('id'),$bill->Get('currency'));
        unset($bill);

        if (!$err && $design->ProcessEx('errors.tpl')) {
            header("Location: ".$design->LINK_START."module=newaccounts&action=bill_view&err=".$err."&bill=".$bill_no);
        } else return $this->newaccounts_bill_list($client);
    }
    function newaccounts_bill_mass($fixclient) {
        global $design,$db;
        set_time_limit(0);
        session_write_close();
        $obj=get_param_raw('obj');
        if ($obj=='create') {
            $p='<span style="display:none">';
            for ($i=0;$i<200;$i++) $p.='                                                     ';
            $p.='</span>';
            //$R=$db->AllRecords('select * from clients where client!="" and status NOT IN ("closed","deny","tech_deny") and client<>"technotrade" order by client');
            $i=0;

            $res = mysql_query('select * from clients where client!="" and status NOT IN ("closed","deny","tech_deny") /*and id=2363*/ order by client');
            while($c=mysql_fetch_assoc($res)){

                $bill = new Bill(null,$c,time(), 1, null, false);
                $bill2 = null;
                $services = get_all_services($c['client'],$c['id']);
                foreach($services as $service){
                    $s=ServiceFactory::Get($service,$bill);
                    $s->SetMonth(time());
                    $R=$s->GetLinesMonth();
                    if(!$bill->AddLines($R)){    //1 - не все строки добавлены из-за расхождения валют
                        if(!$bill2)
                            $bill2 = new Bill(null,$c,time(),1, ($c['currency']=='RUR'?'USD':'RUR'), false);
                        $bill2->AddLines($R);
                    }
                }
                if($c['form_type']=='bill' && $bill->Get('currency')=='USD'){
                    $this->do_generate($bill,'invoice','cbrf',array('inv_num'=>1),true);
                    $this->do_generate($bill,'invoice','cbrf',array('inv_num'=>2),true);
                    $this->do_generate($bill,'invoice','cbrf',array('inv_num'=>3),true);
                }
                if($bill2){
                    $no=$bill2->GetNo();
                    $v=$bill2->Save(1);
                    if($v)
                        echo("&nbsp; Счёт <a href='?module=newaccounts&action=bill_view&bill={$no}'>{$no}</a> для клиента <a href='?module=clients&id={$c['client']}'>{$c['client']}</a> выставлен<br>");
                    unset($bill2);
                }
                $no=$bill->GetNo();
                $v=$bill->Save(1);
                unset($bill);
                if($v==1){
                    echo("&nbsp; Счёт <a href='?module=newaccounts&action=bill_view&bill={$no}'>{$no}</a> для клиента <a href='?module=clients&id={$c['client']}'>{$c['client']}</a> выставлен".$p."<br>");
                    flush();
                }
            }
            $design->ProcessEx();
            return;
        }
        elseif ($obj=='print') {
            $do_bill=get_param_raw('do_bill');
            $do_inv=get_param_raw('do_inv');
            $do_akt=get_param_raw('do_akt');

            $page=get_param_integer('page',-1);
            $design->assign('date',$date = get_param_protected('date','month'));
            $design->assign('do_bill',$do_bill);
            $design->assign('do_inv',$do_inv);
            $design->assign('do_akt',$do_akt);
            if ($date=='month') {
                $date = 'WHERE bill_date >= "'.date('Y-m-01').'"';
            } elseif ($date=='today') {
                $date = 'WHERE bill_date = "'.date('Y-m-d').'"';
            } else {
                $date = 'INNER JOIN newpayments as P ON P.bill_no = newbills.bill_no AND P.payment_date = "'.date('Y-m-d').'" GROUP BY newbills.id';
            }
            if ($page==-1) {
                $r=$db->GetRow('select count(*) as C from newbills '.
                                'INNER JOIN clients ON clients.id=newbills.client_id '.
                                $date);
                $pages=array();
                for ($i=0;$i<ceil($r['C']/50);$i++) {
                    $pages[]=$i;
                }
                $design->assign('pages',$pages);
                $design->AddMain('newaccounts/bill_mass_print.tpl');
            } else {
                $R = array(); $rows = '';
                foreach ($db->AllRecords('select bill_no from newbills '.
                                'INNER JOIN clients ON clients.id=newbills.client_id '.
                                $date.
                                'ORDER by client LIMIT '.($page*50).',50') as $r) {
                    $bill = new Bill($r['bill_no']);
                    $L = $bill->GetLines();
                    $period_date = get_inv_date_period($bill->GetTs());
                    $p1 = count(self::do_print_prepare_filter('invoice',1,$L,$period_date));
                    $p2 = count(self::do_print_prepare_filter('invoice',2,$L,$period_date));
                    $p3 = count(self::do_print_prepare_filter('invoice',3,$L,$period_date));
                    if (($bill->Get('currency')=='USD') && !floatval($bill->Get('inv_rur'))) {
                         if (!floatval($bill->Get('inv1_rate'))) $p1 = 0;
                         if (!floatval($bill->Get('inv2_rate'))) $p2 = 0;
                         if (!floatval($bill->Get('inv3_rate'))) $p3 = 0;
                    }
                    if (($do_bill && count($L)) || $p1 || $p2 || $p3) {
                        $R[] = array($r['bill_no'],$p1,$p2,$p3);
                        $rows.=($rows?',':'');
                        if ($do_bill) $rows.='10%';
                        if ($do_inv) {
                            if ($p1) $rows.=',10%';
                            if ($p2) $rows.=',10%';
                            if ($p3) $rows.=',10%';
                        }
                        if ($do_akt) {
                            if ($p1) $rows.=',10%';
                            if ($p2) $rows.=',10%';
                            if ($p3) $rows.=',10%';
                        }
                    }
                }
                $design->assign('bills',$R);
                $design->assign('rows',$rows);
                $design->ProcessEx('newaccounts/bill_mass_print_frames.tpl');
                return;
            }
        } else {
            $design->AddMain('newaccounts/bill_mass.tpl');
        }
    }


    function newaccounts_bill_publish($fixclient)
    {
        global $db;

        $r = $db->Query("update newbills set is_lk_show =1 where bill_no like '".date("Ym")."-%' and !is_lk_show");

        trigger_error("<font style=\"color: green;\">Опубликованно счетов: ".mysql_affected_rows()."</font>");

        return;
    }

    function newaccounts_bill_email($fixclient) {
        global $design,$db,$_GET;
        $this->do_include();
        $bill_no=get_param_protected("bill"); if (!$bill_no) return;
        $bill = new Bill($bill_no);

        $template = 'Уважаемые господа!<br>Отправляем Вам следующие документы:<br>';
        $template = array($template,$template);
        $D = array(
                    'Конверт: '=>array('envelope'),
                    'Счет: '=>array('bill-1-USD','bill-2-USD','bill-1-RUR','bill-2-RUR'),
                    'Счет-фактура: '=>array('invoice-1','invoice-2','invoice-3','invoice-4'),
                    'Акт: '=>array('akt-1','akt-2','akt-3'),
                    'Накладная: '=>array('lading'),
                    'Соглашение о передаче прав и обязанностей: ' => array("assignment","assignmentcomstar","assignment_stamp","assignment_wo_stamp"),
                    'Приказ о назначении: ' => array("order"),
                    'Уведомление о назначении: ' => array("notice")
        );

        foreach ($D as $k=>$rs) {
            foreach ($rs as $r) if (get_param_protected($r)) {
                $R = array('bill'=>$bill_no,'object'=>$r,'client'=>$bill->Get('client_id'));
                if(isset($_REQUEST['without_date'])){
                    $R['without_date'] = 1;
                    $R['without_date_date'] = $_REQUEST['without_date_date'];
                }
                $link = array();
                if(in_array($r, array("notice", "order")))
                {
                    $link[] = "https://stat.mcn.ru/client/pdf/".$r.".pdf";
                    $link[] = "https://stat.mcn.ru/client/pdf/".$r.".pdf";
                }

                if(in_array($r, array("assignment_stamp", "assignment_wo_stamp")))
                {
                    $R["emailed"] = ($r == "assignment_stamp" ? 1 :0);
                    $R["object"] = "assignment-".get_param_protected("assignment_select", "4");
                    $link[] = 'https://lk.mcn.ru/print?bill='.udata_encode_arr($R);
                    $link[] = 'https://lk.mcn.ru/print?bill='.udata_encode_arr($R);
                }


                $link[] = 'https://lk.mcn.ru/print?bill='.udata_encode_arr($R);
                $R['emailed'] = '0';
                $link[] = 'https://lk.mcn.ru/print?bill='.udata_encode_arr($R);
                foreach ($template as $tk=>$tv) $template[$tk].=$k.'<a href="'.$link[$tk].'">'.$link[$tk].'</a><br>';
            }
        }
        $design->ProcessEx();

        $cs=new ClientCS($bill->Client('id'));
        $contact = $cs->GetContact();
        $this->_bill_email_ShowMessageForm('с печатью',$contact['email'],"Счет за телекоммуникационные услуги",$template[0]);
        $this->_bill_email_ShowMessageForm('без печати',$contact['email'],"Счет за телекоммуникационные услуги",$template[1]);
        echo $template[0];
        $design->ProcessEx('errors.tpl');
    }

    function _bill_email_ShowMessageForm($submit,$to,$subject,$msg) {
        global $design,$user;

        // Исключения для пользователей, у которые отправляет почту из стата не с ящика по умолчанию
        $_SPECIAL_USERS = array(
                "istomina" => 191 /* help@mcn.ru */
                );
        $_DEFAULT_MAIL_TRUNK_ID = 5; /* info@mcn.ru */
               


        $design->assign('subject',iconv("KOI8-R","UTF-8",$subject));
        $design->assign('new_msg',iconv("KOI8-R","UTF-8",$msg));
        if (is_array($to)) {
            $s = "";
            foreach ($to as $r) {
                if (is_array($r)) $r = $r['data'];
                $s.= ($s?',':'').$r;
            }
        } else $s = $to;

        $userLogin = $user->Get('user');

        $design->assign('mail_trunk_id', isset($_SPECIAL_USERS[$userLogin]) ? $_SPECIAL_USERS[$userLogin] : $_DEFAULT_MAIL_TRUNK_ID);
        $design->assign('user',$userLogin);
        $design->assign('to',iconv("KOI8-R","UTF-8",$s));
        $design->assign('submit',$submit);
        $design->ProcessEx('comcenter_msg.tpl');
    }


    function newaccounts_bill_mprint($fixclient) {
        global $design,$db,$user;
        $this->do_include();
        $bills=get_param_raw("bill",array()); if (!$bills) return;
        if(!is_array($bills)) $bills = array($bills);

        $R = array();
        $P = '';


        $clientsToSend = array();
        foreach($db->AllRecords("
                select id from test_operator.mcn_client
                 where id not in
                 (select distinct c.id
                 from log_newbills l, test_operator.mcn_client c, newbills b
                 where l.bill_no like '20120%'
                  and l.comment like 'Печать Соглашение о передачи прав%'
                  and l.bill_no = b.bill_no
                  and c.id = b.client_id
                  order by l.id desc)
        ") as $l){
            $clientsToSend[$l["id"]] = 1;
        }

        ///$clientsToSend["15776"] = 1;

        //printdbg($clientsToSend,111);


        $isFromImport = get_param_raw("from", "") == "import";
        $stamp = get_param_raw("stamp", "");

        $L = array('envelope','bill-1-USD','bill-2-USD','bill-1-RUR','bill-2-RUR','lading','lading','gds','gds-2','gds-serial');
        $L = array_merge($L, array('invoice-1','invoice-2','invoice-3','invoice-4','invoice-5','akt-1','akt-2','akt-3','utd-1', 'utd-2'));
        $L = array_merge($L, array('akt-1','akt-2','akt-3', 'assignment','assignment_stamp','assignment_wo_stamp','order','notice','assignmentcomstar'));
        $L = array_merge($L, array('nbn_deliv','nbn_modem','nbn_gds'));
        $L = array_merge($L, array("assignment-4"));

        //$bills = array("201204-0465");

            $idxs = array();


        foreach($bills as $bill_no)
        {
            $bill = new Bill($bill_no);

            $bb = $bill->GetBill();


            // установка/удаление даты документа
            if (isset($_REQUEST['without_date']) && ($bill->is1CBill() || $bill->isOneTimeService()))
            {
                $wDate = get_param_raw("without_date_date", "");

                $toDelDate = false;

                if($wDate)
                {
                    list($d, $m, $y) = explode(".", $wDate."...");

                    $utDate = @mktime(0,0,0, $m, $d, $y);

                    // дата корректная
                    if($utDate)
                    {
                        if($bb["doc_ts"] != $utDate)
                        {
                            $bill->SetDocDate($utDate);
                            $bb = $bill->GetBill(); // обновляем счет
                        }
                    }else{
                        $toDelDate = true;
                    }
                }else{
                    $toDelDate = true;
                }

                // удалить дату
                if($toDelDate)
                {
                    $bill->SetDocDate(0);
                    $bb = $bill->GetBill(); 
                }
            }

            if($isFromImport)
            {
                $isSF = get_param_raw("invoice-1", "") == "1";
                $c = $bill->Client();
                if($c["mail_print"] == "no") continue;

                $d = $this->get_bill_docs($bill);

                $isAkt1 = $d[1][1];
                $isAkt2 = $d[1][2];

            }
            //$design->assign('bill',$bb);

            $h = array();
            foreach($L as $r) {

                $reCode = false;

                if($r == "invoice-2" && $isFromImport && $isAkt2 && $isSF)
                    $reCode = $r;

                if($r == "akt-2" && $isFromImport && $isAkt2 && !$isSF)
                    $reCode = $r;

                $toPass = false;
                if($isFromImport && !$isSF && $r == "assignment-4" && isset($clientsToSend[$c["id"]]))
                    $toPass = true;

                $isDeny = false;
                if($r == "akt-1" && $isFromImport && !$isAkt1 && !$isSF)
                    $isDeny = true;

                if($r == "invoice-1" && $isFromImport && !$isAkt1 && $isSF)
                    $isDeny = true;

                if($r == "assignment_stamp" && $isFromImport)
                    $isDeny = true;

                if ((get_param_protected($r) || $reCode || $toPass) && !$isDeny) {

                    if($reCode)
                        $r = $reCode;

                    // при импорте клиентов с долларами, печатать долларовые счета
                    if($isFromImport && $c["currency"] == "USD" && $r == "bill-2-RUR") $r = "bill-2-USD";
                    if($isFromImport && $r == "assignment-4") $r = "assignment-4&emailed=1";

                    if($r == "assignment_stamp")
                    {
                        $r = "assignment-".get_param_raw("assignment_select", "4")."&emailed=1";
                    }elseif($r == "assignment_wo_stamp")
                    {
                        $r = "assignment-".get_param_raw("assignment_select", "4");
                    }
                    if (isset($h[$r]))
                    {
                        $idxs[$bill_no."==".$r."-2"] = count($R);
                        $r .= "&to_client=true";
                    }else{
                        $idxs[$bill_no."==".$r] = count($R);
                        $h[$r] = count($R);
                    }
                    /*
                    if($withoutDate){
                        $r.= '&without_date=1&without_date_date='.$withoutDate;
                    }
                    */

                    if($stamp)
                        $r.="&stamp=".$stamp;

                    $ll = array(
                            "bill_no" => $bill_no, 
                            "obj" => $r, 
                            "bill_client" => $bill->Get("client_id"), 
                            "g" => get_param_protected($r), 
                            "r"  => $reCode, 
                            "p" => $toPass
                            );

                    $R[] = $ll;
                    $P.=($P?',':'').'1';
                }
            }
            unset($bill);
        }


        $_R = $R;
        $R = array();

        $set = array();
        foreach($idxs as $key => $idx)
        {
            if(isset($set[$key])) continue;
            $R[] = $_R[$idx];

            if(isset($idxs[$key."-2"]))
            {
                $R[] = $_R[$idxs[$key."-2"]];
                $set[$key."-2"] = 1;
            }
        }
        if(count($R) == 1 && $R[0]["obj"] == "envelope")
        {
            $R[0]["param"] = "alone=true";
        }
               
        $design->assign('rows',$P);
        $design->assign('objects',$R);
        $design->ProcessEx('newaccounts/print_bill_frames.tpl');
        #$design->ProcessEx('errors.tpl');
    }

    function newaccounts_bill_clear($fixclient) {
        global $design,$db;
        $bill_no=get_param_protected("bill"); if (!$bill_no) return;
        $bill = new Bill($bill_no);
        if($bill->IsClosed()){
            header("Location: ./?module=newaccounts&action=bill_view&bill=".$bill_no);
            exit();
        }
        if (!$bill->CheckForAdmin()) return;
        $db->Query('delete from newbill_lines where bill_no="'.$bill_no.'"');
        ServicePrototype::CleanOverprice($bill_no);
        $bill->Save(0,0);
        if ($design->ProcessEx('errors.tpl')) header("Location: ".$design->LINK_START."module=newaccounts&action=bill_view&bill=".$bill_no);
    }
    function newaccounts_line_delete($fixclient) {
        global $design,$db;
        $bill_no=get_param_protected("bill"); if (!$bill_no) return;
        $bill = new Bill($bill_no);
        if (!$bill->CheckForAdmin()) return;
        $sort=get_param_integer("sort"); if (!$sort) return;
        $bill->RemoveLine($sort);
        if ($design->ProcessEx('errors.tpl')) header("Location: ".$design->LINK_START."module=newaccounts&action=bill_view&bill=".$bill_no);
    }
    function newaccounts_bill_delete($fixclient) {
        global $design,$db;
        $bill_no=get_param_protected("bill"); if (!$bill_no) return;

        $bill = new Bill($bill_no);
        if($bill->IsClosed()){
            header("Location: ./?module=newaccounts&action=bill_view&bill=".$bill_no);
            exit();
        }

        Bill::RemoveBill($bill_no);
        if ($design->ProcessEx('errors.tpl')) header("Location: ".$design->LINK_START."module=newaccounts&action=bill_list");
    }
    //эта функция готовит счёт к печати. ФОРМИРОВАНИЕ СЧЁТА
    function newaccounts_bill_print($fixclient){
        global $design,$db,$user;
        $this->do_include();
        $bill_no=get_param_protected("bill");
        if(!$bill_no)
            return;


        $bill = new Bill($bill_no);
        $bb = $bill->GetBill();

        $design->assign('without_date_date', $bill->getShipmentDate());

        $design->assign("to_client", get_param_raw("to_client", "false"));
        $design->assign("stamp", $this->get_import1_name($bill, get_param_raw("stamp", "false")));

        if(get_param_raw("emailed", "0") != "0")
            $design->assign("emailed", get_param_raw("emailed", "0"));

        $object = get_param_protected('object');

        $mode = get_param_protected('mode', 'html');
        self::$object = $object;
        if ($object) {
            list($obj,$source,$curr) = explode('-',$object.'---');
        } else {
            $obj=get_param_protected("obj");
            $source = get_param_integer('source',1);
            $curr = get_param_raw('curr','RUR');
        }

        if(in_array($obj,array('nbn_deliv', 'nbn_modem','nbn_gds'))){
            $this->do_print_prepare($bill,'bill',1,'RUR');
            $design->assign('cli',$cli=$db->GetRow("select * from newbills_add_info where bill_no='".$bill_no."'"));
            if(ereg("([0-9]{2})\.([0-9]{2})\.([0-9]{4})",$cli["passp_birthday"], $out))
                    $cli["passp_birthday"] = $out[1]."-".$out[2]."-".$out[3];

            $lastDoer = $db->GetValue("select name from tt_doers d , courier c where stage_id in (select stage_id from tt_stages where trouble_id = (SELECT id FROM `tt_troubles` where bill_no ='".$cli["bill_no"]."')) and d.doer_id = c.id order by d.id desc");

            list($f, $i, $o) = explode(" ",$lastDoer."   ");
            if(strlen($i) > 2) $i = $i[0].".";
            if($o && strlen($o) > 2) $o = $i[0].".";

            $design->assign("cli_doer", $lastDoer ? $f." ".$i." ".$o : "");

            $design->assign('cli_fio',explode(' ',$cli['fio']));
            $design->assign('cli_bd',explode('-',$cli['passp_birthday']));
            $design->assign("serial", $this->do_print_serials($bill_no));
            $cli_passp_when_given = explode('-',$cli['passp_when_given']);
            if(count($cli_passp_when_given)==1)
                $cli_passp_when_given = array_reverse(explode('.',$cli_passp_when_given[0]));
            $design->assign('cli_passp_when_given',$cli_passp_when_given);
            $design->assign('cli_acc_no',explode(' ',$cli['acc_no']));


            $design->ProcessEx('newaccounts/'.$obj.'.html');
            return true;
        }

        if($obj == "assignment")
        {
            $source = $source >= 1 && $source <= 12 ? $source : 4;
            $assignmentDate = strtotime("2000-".$source."-01");
            $design->assign("assignment_month", mdate('месяца', $assignmentDate));
        }

        if (!in_array($obj, array('invoice', 'akt', 'utd', 'lading', 'gds', 'assignment', 'order', 'notice','assignmentcomstar', 'new_director_info')))
            $obj='bill';

        if ($obj!='bill')
            $curr = 'RUR';

        $assignment = false;

        $cc = $bill->Client();

        if(
                (
                 (
                  $obj == "bill" && preg_match("/^201204/", $bill_no) && $cc["firma"] == "mcn_telekom"
                  || $obj == "assignment"
                  || $obj == "assignmentcomstar"
                 )
                 && $db->GetValue("select id from test_operator.mcn_client where id = '".$bill->Get("client_id")."'")
                )
          )
        {

            $assignment = $db->GetRow("select contract_no as no, contract_date as no_date from client_contracts where client_id = '".$bill->Get("client_id")."' and is_active and contract_date <= '2012-04-01' order by id desc limit 1");
            $assignment["no_date"] = mdate('d месяца Y', strtotime($assignment["no_date"]));
        }


        if(
                (
                 (
                  $obj == "bill" && preg_match("/^201205/", $bill_no) && $cc["firma"] == "mcn_telekom"
                  || $obj == "assignment"
                  || $obj == "assignmentcomstar"
                 )
                 && $db->GetValue("select id from test_operator.mcn_client_may where id = '".$bill->Get("client_id")."'")
                )
          )
        {

            $assignment = $db->GetRow("select contract_no as no, contract_date as no_date from client_contracts where client_id = '".$bill->Get("client_id")."' and is_active and contract_date <= '2012-04-01' order by id desc limit 1");

            if($assignment)
                $assignment["no_date"] = mdate('d месяца Y', strtotime($assignment["no_date"]));
        }
        $design->assign("assignment", $assignment);



        if(in_array($obj, array("order","notice", "assignment", "assignmentcomstar")))
        {
            $t = ($obj == "assignmentcomstar" ? "Соглашение 3хсторонее" :($obj == "order" ?
                    "Приказ (Телеком)":
                    ($obj == "notice" ?
                        "Уведомление (Телеком)":
                        "Соглашение о передачи прав (".mdate("месяц", $assignmentDate).") (Телеком)")));
            if($user->Get('id'))
            $db->QueryInsert(
                "log_newbills",
                array(
                    'bill_no'=>$bill_no,
                    'ts'=>array('NOW()'),
                    'user_id'=>$user->Get('id'),
                    'comment'=>'Печать '.$t
                )
            );

            /*
            if($obj != "assignment")
            {
                header("Location: /client/pdf/".$obj.".jpg");
                exit();
            }*/
        }

        if($obj == "new_director_info")
        {
            $this->docs_echoFile(STORE_PATH."new_director_info.pdf", "Смена директора.pdf");
            exit();
        }
            



        if ($this->do_print_prepare($bill,$obj,$source,$curr) || in_array($obj, array("order","notice", "assignment"))){

      $design->assign("bill_no_qr", ($bill->GetTs() >= strtotime("2013-05-01") ? QRCode::getNo($bill->GetNo()) : false));
      $design->assign("source", $source);

            if($source==3 && $obj=='akt')
            {
                if($mode=='html')
                    $design->ProcessEx('newaccounts/print_akt_num3.tpl');
            }else{
                if(in_array($obj, array('invoice','utd'))){
                    $id = $db->QueryInsert(
                        "log_newbills",
                        array(
                            'bill_no'=>$bill_no,
                            'ts'=>array('NOW()'),
                            'user_id'=>$user->Get('id'),
                            'comment'=>'Печать с/ф &#8470;'.$source
                        )
                    );
                }elseif($obj == 'gds'){

                    $serials = array();
                    $onlimeOrder = false;
                    foreach(Serial::find('all', array(
                                    'conditions' => array(
                                        'bill_no' => $bill->GetNo()
                                        ),
                                    'order' => 'code_1c'
                                    )
                                ) as $s)
                    {
                        $serials[$s->code_1c][] = $s->serial;
                    }

                    // для onlime'а показываются номера купонов, если таковые есть
                    if($bill->Get("client_id") == "18042")
                    {
                        $oo = OnlimeOrder::find_by_bill_no($bill->GetNo());
                        if($oo)
                        {
                            if($oo->coupon)
                            {
                                $onlimeOrder = $oo;
                            }
                        }
                    }

                    $design->assign("onlime_order", $onlimeOrder);


                    include_once INCLUDE_PATH.'1c_integration.php';
                    $bm = new \_1c\billMaker($db);
                    $f = null;
                    $b = $bm->getOrder($bill_no, $fault);

                    $_1c_lines = array();
                    if($b)
                    {
                        foreach($b['list'] as $item){
                            $_1c_lines[$item['strCode']] = $item;
                        }
                    }
                    $design->assign("serials", $serials);
                    $design->assign('1c_lines',$_1c_lines);
                }
                if($mode=='html')
                {
                    $design->ProcessEx('newaccounts/print_'.$obj.'.tpl');
                }elseif($mode=='xml'){
                    $design->ProcessEx('newaccounts/print_'.$obj.'.xml.tpl');
                }elseif($mode=='pdf'){
                    include(INCLUDE_PATH.'fpdf/model/'.$obj.'.php');
                }
            }
        }else{
            trigger_error('Документ не готов');
        }
        $design->ProcessEx('errors.tpl');
    }


    function get_import1_name($bill, $flag)
    {
        if($flag == "import1")
        {
            $ts = $bill->GetTs();

            //return "solop";

            if($ts >= strtotime("2010-10-01"))
            {
                return "uskova";
            }elseif($ts >= strtotime("2010-04-01")) 
            {
                return "zam_solop_tp";
            }elseif($ts >= strtotime("2009-07-01"))
            {
                return "solop_tp";

            }elseif($ts >= strtotime("2008-11-01"))
            {
                return "solop_nm";
            }else{
                return "false";
            }
        }

        return false;
    }

    function do_print_serials($billNo)
    {
        global $db;
        $s = array("decoder" => array(), "w300" => array(), "other" => array(), "cii" => array(), "fonera" => array());
        foreach($db->AllRecords("SELECT num_id, item, serial FROM newbill_lines l, g_serials s, g_goods g  where l.bill_no = '".$billNo."' and l.bill_no = s.bill_no and l.code_1c = s.code_1c and g.id = l.item_id") as $l){
            $idx = "other";

            if(eregi("w300", $l["item"])) $idx = "w300";
            if(eregi("декодер", $l["item"])) $idx = "decoder";
            if($l["num_id"] == 11243) $idx = "cii";
            if($l["num_id"] == 11241) $idx = "fonera";

            $s[$idx][] = $l["serial"];
        }
        unset($l);

        foreach($s as &$l)
            $l = implode(", ", $l);

        return $s;
    }

    function report_stream_blank($bill_no)
    {
        global $db;
        $a=$db->GetRow("select * from newbills_add_info where bill_no='".$bill_no."'");
        $t= $db->GetRow("SELECT item FROM newbill_lines where bill_no = '".$bill_no."' and price != 0 and type = 'service' and item like 'МТС%'");
        $tarif = $t ? $t["item"] : "";
        include "report.stream.blank.php";
    }

    function newaccounts_bill_generate($fixclient) {
        global $design,$db;
        $bill_no=get_param_protected("bill"); if (!$bill_no) return;
        $bill = new Bill($bill_no);
        $obj=get_param_protected("obj",'bill');
        if ($obj=='inv2to1') {
            $bill->Set('inv2to1',get_param_integer('inv2to1',0));
            $bill->Save();
        } else {
            if ($obj!='invoice' && $obj!='akt') $obj='bill'; else $obj='inv';
            $type = get_param_raw('type','cbrf');            //cbrf,rate,vsum,psum
            $P = array();
            $P['inv_num']= get_param_integer('inv_num',1); if ($P['inv_num']!=1 && $P['inv_num']!=3) $P['inv_num'] = 2;
            $P['sum'] = floatval(get_param_raw('sum'));
            $P['rate'] = floatval(get_param_raw('rate'));
            $this->do_generate($bill,$obj,$type,$P,true);
        }
        if ($design->ProcessEx('errors.tpl')) header("Location: ".$design->LINK_START."module=newaccounts&action=bill_view&bill=".$bill_no);
    }

    //obj:        invoice, bill
    //type:        cvrf, vsum, rate, psum
    function do_generate(Bill &$bill,$obj,$type,$P = array(),$save = true) {
        global $db,$user;
        $upd_inv = null;
        $sum_rur = null;
        $usd_rate = null;
        if($type=='cbrf'){
            if($obj=='bill'){
                $date=$bill->GetTs();
            }else{
                list($date) = get_inv_date($bill->GetTs(),$P['inv_num']);
                $upd_inv = $P['inv_num'];
            }
            if(isset($P['inv_num']) && $P['inv_num']==4){
                $r = $db->QuerySelectRow('newpayments',array('bill_no'=>$bill->GetNo()));
                $usd_rate = $r['payment_rate'];
            }else{
                $r=$db->QuerySelectRow('bill_currency_rate',array('currency'=>'USD','date'=>date('Y-m-d',$date)));
                $usd_rate=$r['rate'];
            }
        }elseif($type=='rate'){
            $usd_rate=$P['rate'];
            if($obj!='bill' && isset($P['inv_num']) && $P['inv_num']!=0)
                $upd_inv = $P['inv_num'];
        }elseif($type=='psum'){
            $r = $db->GetRow('
                select
                    sum(sum_rub) as s
                from
                    newpayments as P
                where
                    bill_no="'.$bill->GetNo().'"
            ');
            $sum_rur = $r['s'];
        }elseif($type=='vsum'){
            $sum_rur = $P['sum'];
        }
        if($sum_rur !== null)
            $sum_rur=round($sum_rur,2);
        if($save){
            if($sum_rur !== null){
                if($obj == 'bill'){
                    $db->Query('
                        update
                            newbills
                        set
                            gen_bill_rur = "'.$sum_rur.'",
                            gen_bill_rate = NULL,
                            gen_bill_date = NOW()
                        where
                            bill_no = "'.$bill->GetNo().'"
                    ');
                }else{
                    $db->Query('
                        update
                            newbills
                        set
                            inv_rur = "'.$sum_rur.'",
                            inv1_date = NOW(),
                            inv2_date = NOW(),
                            inv3_date = NOW(),
                            inv1_rate = NULL,
                            inv2_rate = NULL,
                            inv3_rate = NULL
                        where
                            bill_no = "'.$bill->GetNo().'"
                    ');
                }
            }elseif($upd_inv === null){
                if($obj == 'bill'){
                    $db->Query('
                        update
                            newbills
                        set
                            gen_bill_rur = NULL,
                            gen_bill_rate = "'.$usd_rate.'",
                            gen_bill_date = NOW()
                        where
                            bill_no = "'.$bill->GetNo().'"
                    ');
                }else{
                    $db->Query('
                        update
                            newbills
                        set
                            inv_rur = NULL,
                            inv1_date = NOW(),
                            inv2_date = NOW(),
                            inv3_date = NOW(),
                            inv1_rate = "'.$usd_rate.'",
                            inv2_rate = "'.$usd_rate.'",
                            inv3_rate = "'.$usd_rate.'"
                        where
                            bill_no = "'.$bill->GetNo().'"
                    ');
                }
            }else{
                if($obj == 'bill'){
                    $t1 = $t2 = 'gen_bill';
                }else{
                    $t1 = 'inv';
                    $t2 = 'inv'.$upd_inv;
                }
                $db->Query('
                    update
                        newbills
                    set
                        '.$t1.'_rur = NULL,
                        '.$t2.'_rate = "'.$usd_rate.'",
                        '.$t2.'_date = NOW()
                    where
                        bill_no = "'.$bill->GetNo().'"
                ');
            }
        }
        return array($sum_rur,$usd_rate);
    }

    public static function do_print_prepare_RecalculateItems($sum_rur,$d,&$L_prev,&$bdata,$mode = 1){

        $d_last_I = abs($d)+10;
        $I=0;
        $I_max = ($mode==1?50000:1);
        while(abs($d)<abs($d_last_I) && $I<$I_max){
            $d_last_I = $d;
            foreach($L_prev as &$li)
                if($li['price']!=0){
                    $sgn = ($d>0?1:-1);
                    $i_max = ($mode==1?10:1);
                    $i=0;
                    do {
                        $d_last = $d;
                        if($mode==1){
                            $li['outprice']+=round(0.01*$sgn/$li['amount'],4);
                            $d_item = round($li['outprice']*$li['amount'],2);
                        } else {
                            $d_item = round($li['outprice']*$li['amount'],4);
                        }
                        $d = round($sum_rur-$bdata['tsum']+$li['tsum']-round($d_item*1.18,2),4);
                        $i++;
                    }while(abs($d)<abs($d_last) && ($i<$i_max));

                if(abs($d) >= abs($d_last)){
                        if($mode==1){
                            $li['outprice'] -= round(0.01*$sgn/$li['amount'],4);
                            $li['sum'] = null;
                        }elseif($mode==3){
                            $li['sum'] = null;
                        }
                        $d = $d_last;
                    }else
                        $li['sum']=null;
                    $bdata['tax']-=$li['tax'];
                    $bdata['tsum']-=$li['tsum'];

                    if($li['sum']===null)
                        $li['sum'] = round($li['outprice']*$li['amount'],($mode==1?2:4));
                    $li['tax'] = round($li['sum']*0.18,2);
                    $li['tsum']= round($li['sum']*1.18,2);
                    $bdata['tax']+=$li['tax'];
                    $bdata['tsum']+=$li['tsum'];
                }
            $I++;
        }
        unset($li);
        return $d;
    }

    public static function do_print_prepare_filter($obj,$source,&$L,$period_date, $inv3Full=true, $isViewOnly=false, $origObj = false) {
        $M = array();

        if($origObj === false)
        {
            $origObj = $obj;
        }

        if($obj == "gds")
        {
                $M = array(
                    'all4net'=>0,
                    'service'=>0,
                    'zalog'     =>0,
                    'zadatok'=>0,
                    'good'     =>1,
                    '_'         =>0
                );
        }else
        if($obj=='bill'){
            $M['all4net']=1;
            $M['service']=1;
            $M['zalog']=1;
            $M['zadatok']=($source==2?1:0);
            $M['good']=1;
            $M['_']=0;
        }elseif ($obj=='lading'){
            $M['all4net']=1;
            $M['service']=0;
            $M['zalog']=0;
            $M['zadatok']=0;
            $M['good']=1;
            $M['_']=0;
        }elseif($obj == 'akt'){
            if($source == 3){
                $M = array(
                    'all4net'=>0,
                    'service'=>0,
                    'zalog'     =>1,
                    'zadatok'=>0,
                    'good'     =>0,
                    '_'         =>0
                );
            }elseif(in_array($source,array(1,2))){
                $M = array(
                    'all4net'=>1,
                    'service'=>1,
                    'zalog'     =>0,
                    'zadatok'=>0,
                    'good'     =>0,
                    '_'         =>$source
                );
            }
        }else{ //invoice
            if(in_array($source, array(1,2))){
                $M['all4net']=1;
                $M['service']=1;
                $M['zalog']=0;
                $M['zadatok']=0;
                $M['good']=0;//($obj=='invoice'?1:0);
                $M['_']=$source;
            }elseif($source == 4){
                if(!count($L))
                    return array();
                foreach($L as $val){
                    $bill = $val;
                    break;
                }
                global $db;

                $db->Query("
                    SELECT
                        bill_date,
                        nal
                    FROM
                        newbills
                    WHERE
                        bill_no = '".$bill['bill_no']."'
                ");

                $ret = $db->NextRecord(MYSQL_ASSOC);

                if(substr($ret['bill_date'], 0, 4)<'2009'){
                    return array();
                }
                if(in_array($ret['nal'],array('nal','prov'))){
                    $db->Query($q="
                        SELECT
                            *
                        FROM
                            newpayments
                        WHERE
                            bill_no = '".$bill['bill_no']."'
                    ");
                    $ret = $db->NextRecord(MYSQL_ASSOC);
                    if($ret == 0)
                        return -1;
                }

                $query = "
                    SELECT
                        *
                    FROM
                        newbills nb
                    INNER JOIN
                        newpayments np
                    ON
                        (
                            np.bill_vis_no = nb.bill_no
                        OR
                            np.bill_no = nb.bill_no
                        )
                    AND
                        (
                            (
                                YEAR(np.payment_date)=YEAR(nb.bill_date)
                            AND
                                (
                                    MONTH(np.payment_date)=MONTH(nb.bill_date)
                                OR
                                    MONTH(nb.bill_date)-1=MONTH(np.payment_date)
                                )
                            )
                        OR
                            (
                                YEAR(nb.bill_date)-1=YEAR(np.payment_date)
                            AND
                                MONTH(np.payment_date)=1
                            AND
                                MONTH(nb.bill_date)=12
                            )
                        )

                    WHERE
                        nb.bill_no = '".$bill['bill_no']."'
                ";

                //echo $query;
                $db->Query($query);
                $ret = $db->NextRecord(MYSQL_ASSOC);

                if($ret == 0)
                    return 0;

                $R = array();
                foreach($L as $item){
                    if(preg_match("/^\s*Абонентская\s+плата|^\s*Поддержка\s+почтового\s+ящика|^\s*Виртуальная\s+АТС|^\s*Перенос|^\s*Выезд|^\s*Сервисное\s+обслуживание|^\s*Хостинг|^\s*Подключение|^\s*Внутренняя\s+линия|^\s*Абонентское\s+обслуживание|^\s*Услуга\s+доставки|^\s*Виртуальный\s+почтовый|^\s*Размещение\s+сервера|^\s*Настройка[0-9a-zA-Zа-яА-Я]+АТС|^Дополнительный\sIP[\s\-]адрес|^Поддержка\sпервичного\sDNS|^Поддержка\sвторичного\sDNS|^Аванс\sза\sподключение\sинтернет-канала|^Администрирование\sсервер|^Обслуживание\sрабочей\sстанции|^Оптимизация\sсайта/",$item['item']))
                        $R[] = $item;
                }
                return $R;
            } elseif($source == 5){
                if(!count($L))
                    return array();
                foreach($L as $val){
                    $bill = $val;
                    break;
                }

                global $db;

                $payments = $db->AllRecords("
                    select
                        *
                    from
                        `newpayments`
                    where
                        `bill_no`='".$bill['bill_no']."'
                    and
                        `sum_rub`<0
                ",null,MYSQL_ASSOC);

                return $payments;
            }else{ //source 3
                $M['all4net']=1;
                $M['service']=0;
                $M['zalog']= ($isViewOnly)?0:1;
                $M['zadatok']=0;
                $M['good']=$inv3Full ? 1 :0;
                $M['_']=0;
            }
        }

        $R = array();
        foreach($L as &$li){
            if($li["sum"] == 0){
                $li["outprice"] = 0;
                $li["price"] = 0;
            }
            if($M[$li['type']]==1){
                if(
                         $M['_']==0
                    || ( $M['_']==1 && $li['ts_from']>=$period_date)
                    || ( $M['_']==2 && $li['ts_from']<$period_date)
                ){
                    if(
                            $li['sum']!=0 || 
                            $li["item"] == "S" || 
                            ($origObj == "gds" && $source == 2) || 
                            eregi("^Аренд", $li["item"]) ||
                            ($li["sum"] == 0 && eregi("^МГТС/МТС", $li["item"]))
                            ) {
                        $R[]=&$li;
                    }
                }
            }
        }

        return $R;
    }

    function do_print_prepare(Bill &$bill,$obj,$source = 1,$curr,$do_assign=1, $isSellBook = false){
        global $design,$db,$user;

        $design->assign('invoice_source',$source);
        $origObj = $obj;
        if($obj == 'gds') {
            $obj = 'bill';
        }
        if($source == 4){
            $source = 1;
            $is_four_order = true;
        }elseif($source == 5){
            $is_four_order = true;
        }else
            $is_four_order = false;
        $design->assign('is_four_order',$is_four_order);
        if($source == 5)
            $is_four_order = false;
        $sum_rur=0;
        if(is_null($source))
            $source=3;

        if ($bill->Get('currency')=='USD' && $curr=='RUR') {
            if ($obj=='bill'){
                $sum_rur = $bill->Get('gen_bill_rur');
                $usd_rate = $bill->Get('gen_bill_rate');
            } else {
                $sum_rur = $bill->Get('inv_rur');
                $usd_rate = $bill->Get('inv'.(($source==5)?1:$source).'_rate');

                if($usd_rate > 1)
                    $sum_rur = $bill->Get("sum")/$usd_rate;
            }
        } else {
            $usd_rate=1;
            $curr=$bill->Get('currency');
        }


        $usd_rate = floatval($usd_rate);
        $sum_rur = floatval($sum_rur);

        if(!$usd_rate && !$sum_rur && $source<>5)
            return false;

        $bdata=$bill->GetBill();


        // Если счет 1С, на товар, 
        if($bill->is1CBill())
        {
            //то доступны только счета (в RUR || USD)
            if($obj == "bill" && in_array($source, array('1','2')))
            {
                $inv_date = $bill->GetTs();
            }else{
                // остальные документы после отггрузки

                if($bdata["doc_ts"])
                {
                    $inv_date = $bdata["doc_ts"];
                }else{
                    if($shipDate = $bill->getShipmentDate())
                    {
                        $inv_date = $shipDate;
                    }else{
                        return ; //Документ не готов
                    }
                }
            }
            $period_date = get_inv_period($inv_date);;
        }elseif($bill->isOneTimeService())// или разовая услуга
        {
            if($bdata["doc_ts"])
            {
                $inv_date = $bill->GetTs();
                $period_date = get_inv_period($inv_date);
            }else{
                list($inv_date, $period_date)=get_inv_date($bill->GetTs(),($bill->Get('inv2to1')&&($source==2))?1:$source);
            }
        }else{ // статовские переодичекские счета
            list($inv_date, $period_date)=get_inv_date($bill->GetTs(),($bill->Get('inv2to1')&&($source==2))?1:$source);
        }


        if(in_array($obj, array('invoice','akt','utd')))
        {
            if(date("Ymd", $inv_date) != date("Ymd", $bill->GetTs()))
            {
               $bill->SetClientDate(date("Y-m-d", $inv_date));
            } 
        }

        if($is_four_order){
            $row = $db->QuerySelectRow('newpayments',array('bill_no'=>$bill->GetNo()));
            if($row['payment_date']){
                $da = explode('-',$row['payment_date']);
                $inv_date = mktime(0,0,0,$da[1],$da[2],$da[0]);
            }else{
                $inv_date= time();
            }
            unset($da,$row);
        }




        if(in_array($obj, array('invoice','utd')) && (in_array($source, array(1,3,5)) || ($source==2 && $bill->Get('inv2to1'))) && $do_assign) {//привязанный к фактуре счет
            /*$W = array('AND');
            $W[] = 'payment_no!=""';
            $W[] = '(bill_no="'.$bdata['bill_no'].'") OR (bill_vis_no="'.$bdata['bill_no'].'")';
            $W[] = "1 IN (
                SELECT
                    newpayments.payment_date between adddate(date_format(newbills.bill_date,'%Y-%m-01'), interval -1 month)
                and
                    adddate(adddate(date_format(newbills.bill_date,'%Y-%m-01') ,interval 1 month), interval -1 day)
                FROM
                    newbills
                WHERE
                    newbills.bill_no = newpayments.bill_no)";*/
            //не отображать если оплата позже счета-фактуры
            $query = "
                SELECT
                    *,
                    UNIX_TIMESTAMP(payment_date) as payment_date_ts
                FROM
                    newpayments
                WHERE
                    payment_no<>''
                AND
                    `sum_rub`>=0
                AND
                    (
                        bill_no='".$bdata['bill_no']."'
                    OR
                        bill_vis_no='".$bdata['bill_no']."'
                    )
                AND
                    1  IN (
                        SELECT
                            newpayments.payment_date
                                BETWEEN
                                    adddate(
                                        date_format(newbills.bill_date,'%Y-%m-01'),
                                        interval -1 month
                                    )
                                AND
                                    adddate(
                                        adddate(
                                            date_format(newbills.bill_date,'%Y-%m-01'),
                                            interval 1 month
                                        ),
                                        interval -1 day
                                    )
                        FROM
                            newbills
                        WHERE
                            newbills.bill_no = IFNULL(
                                (
                                    SELECT np1.bill_no
                                    FROM newpayments np1
                                    WHERE np1.bill_no = '".$bdata['bill_no']."'
                                    GROUP BY np1.bill_no
                                ),
                                (
                                    SELECT np2.bill_vis_no
                                    FROM newpayments np2
                                    WHERE np2.bill_vis_no = '".$bdata['bill_no']."'
                                    GROUP BY np2.bill_vis_no
                                )
                            )
                    ) /*or bill_no = '201109/0574'*/
            ";

            $inv_pays = $db->AllRecords($query,null,MYSQL_ASSOC);
            if($inv_pays)
                $design->assign('inv_pays',$inv_pays);
        }

        $bdata['ts']=$bill->GetTs();

        if(!$usd_rate){
            $s = $bill->CalculateSum(1,$obj=='bill'?'A':'B');
            if($s>0)
            {
                $usd_rate=$sum_rur/$bill->CalculateSum(1,$obj=='bill'?'A':'B');
            }else{
                $usd_rate=0;
            }
            unset($s);
        }


        $L_prev=$bill->GetLines($usd_rate,((preg_match('/bill-\d/',self::$object))?'order':false));//2 для фактур значит за прошлый период


        if(in_array($obj, array("invoice","utd")))
        {
            $this->checkSF_discount($L_prev);
        }


        $isNdsZero = $bill->Client("nds_zero");
        $nds = $bill->Client("nds_zero") ? 1 : 1.18;

        $design->assign_by_ref('negative_balance', $bill->negative_balance); // если баланс отрицательный - говорим, что недостаточно средств для проведения авансовых платежей
        $bdata['tax']=0;
        $bdata['tsum']=0;


        foreach($L_prev as $k => $li){
            //$s=round($li['outprice']*$li['amount'],4);
            //$L_prev[$k]['sum']=$s;

            $L_prev[$k]['tax']=round($isNdsZero ? 0 : $L_prev[$k]['sum']*0.18,2);

            if($L_prev[$k]['all4net_price']<>0){
                $L_prev[$k]['tsum'] = round($L_prev[$k]['all4net_price']*$L_prev[$k]['amount'],2);
            }else{
                $L_prev[$k]['tsum']=round($L_prev[$k]['sum']*$nds,2);
            }

            $L_prev[$k]['sum']=$L_prev[$k]['sum'];

            // add calc line nds
            if($li["line_nds"] != 18)
            {
                $L_prev[$k]["outprice"] = $L_prev[$k]["tsum"]/$li["amount"];
                $L_prev[$k]["sum"] = $L_prev[$k]["tsum"];
                $L_prev[$k]["tax"] = 0;
            }


            if ($obj=='bill' || ($li['type']!='zadatok' || $is_four_order)) {
                $bdata['tax']+=$L_prev[$k]['tax'];
                $bdata['tsum']+=$L_prev[$k]['tsum'];
            } else {unset($L_prev[$k]);}
        }
        unset($li);

        $L = self::do_print_prepare_filter($obj,$source,$L_prev,$period_date,$obj == "invoice" && $source == 3, $isSellBook ? true : false, $origObj);


        if ($sum_rur!=0) { //расчет долларовых счетов

            if($bill->GetTs() < 1351713600)  // 2012-11-01 00:00:00
            {
                   if($d=round($sum_rur-$bdata['tsum'],4))
                       self::do_print_prepare_RecalculateItems($sum_rur,$d,$L_prev,$bdata,1);
                   if($d=round($sum_rur-$bdata['tsum'],4))
                       self::do_print_prepare_RecalculateItems($sum_rur,$d,$L_prev,$bdata,2);
                   if($d=round($sum_rur-$bdata['tsum'],4))
                       self::do_print_prepare_RecalculateItems($sum_rur,$d,$L_prev,$bdata,3);
            }else{

                $rate = $sum_rur/$bdata["sum"];

                foreach($L_prev as &$l)
                {
                    if($l["tsum"])
                    {
                        $l["tsum"] *= $rate;
                        $l["sum"] *= $rate;
                        $l["tax"] *= $rate;

                        if($l["amount"])
                        {
                            $l["sum"] = $l["tsum"]/1.18;
                            $l["outprice"] = $l["sum"]/$l["amount"];
                        }

                        $l["tax"] = $l["tsum"]/1.18*0.18;
                    }
                }
            }
		}

		if($is_four_order){
			$L =& $L_prev;
			$bill->refactLinesWithFourOrderFacure($L);
		}elseif($source == 5){
			$pays = $db->AllRecords("
				select
					*
				from
					`newpayments`
				where
					`bill_no` = '".$bdata['bill_no']."'
				and
					`sum_rub`<0
			",null,MYSQL_ASSOC);

			$L_prev = array(array(
				'bill_no'=>$bdata['bill_no'],
				'sort'=>1,
				'item'=>'Авансовый платеж за доступ в интернет',
				'amount'=>1,
				'price'=>0,
				'all4net_price'=>0,
				'service'=>'usage_ip_ports',
				'id_service'=>0,
				'date_from'=>0,
				'date_to'=>0,
				'type'=>'zadatok',
				'outprice'=>0,
				'sum'=>0,
				'id'=>1,
				'ts_from'=>0,
				'ts_to'=>0,
				'tsum'=>0,
				'tax'=>0
			));
			foreach($pays as $pay){
				$L_prev[0]['sum']+=($pay['sum_rub']*-1);
				$L_prev[0]['tax']+=($pay['sum_rub']*-1)/1.18*0.18;
				$L_prev[0]['outprice']+=($pay['sum_rub']*-1);
				$L_prev[0]['tsum']+=($pay['sum_rub']*-1);
			}
			$L =& $L_prev;
		}

		//подсчёт итоговых сумм, получить данные по оборудованию для акта-3

		$cpe = array();
		$bdata['tax']=0; $bdata['tsum']=0; $bdata["sum"] = 0;


        $r = $bill->Client();

        foreach ($L as &$li) {

            if($r["nds_calc_method"] == 3 && !$isNdsZero)
            {
                $li["tax"] = round($li["tsum"]/1.18*0.18,2);
                $li["sum"] = $li["tsum"] - $li["tax"];
            }

            $bdata['tax']+=round($li['tax'],2);
            $bdata['tsum']+=$li['tsum'];
            $bdata['sum']+=round($li['sum'],2);

            if ($obj=='akt' && $source==3 && $do_assign) {			//связь строчка>устройство или строчка>подключение>устройство
                $id = null;
                if ($li['service']=='tech_cpe') {
                    $id = $li['id_service'];
                } elseif ($li['service']=='usage_ip_ports') {
                    $r = $db->GetRow('select id_service from tech_cpe where id_service='.$li['id_service'].' AND actual_from<"'.$inv_date.'" AND actual_to>"'.$inv_date.'" order by id desc limit 1');
                    if ($r) $id = $r['id_service'];
                }
                if ($id) {
                    $r=$db->GetRow('select tech_cpe.*,model,vendor,type from tech_cpe INNER JOIN tech_cpe_models ON tech_cpe_models.id=tech_cpe.id_model WHERE tech_cpe.id='.$id);
                    $r['amount'] = floatval($li['amount']);
                    $cpe[]=$r;
                } else {
                    $cpe[]=array('type'=>'','vendor'=>'','model'=>$li['item'],'serial'=>'','amount'=>floatval($li['amount']), "actual_from" => $li["date_from"]);
                }
            }
        }
		unset($li);

        if($bdata["currency"] == "USD")
        {
            if($bdata["tsum"] - $bdata["sum"] != $bdata["tax"])
            {
                $bdata["tax"] = $bdata["tsum"] - $bdata["sum"];
            }
        }

        //каст! 200909-1868
        $bdata['tax'] = $isNdsZero ? 0 : $bdata['tax'];//round($bdata['tsum']/($nds)*($nds-1),2);

        //$bdata['sum'] = $bdata['tsum']-$bdata['tax'];

        $b = $bill->GetBill();

        if($r["nds_calc_method"] == 2 && !$isNdsZero)
        {
            $bdata["tax"] = $bdata["tsum"] - $bdata["sum"];
        }

        if($r["nds_calc_method"] == 3 && !$isNdsZero)
        {
            $bdata["tax"] = round($bdata["tsum"]/1.18*0.18,2);
            $bdata["sum"] = $bdata["tsum"] - $bdata["tax"];
        }

        if ($do_assign){
            $design->assign('cpe',$cpe);
            $design->assign('curr',$curr);
            if (in_array($obj, array('invoice','akt','utd'))) {
                $design->assign('inv_no','-'.$source);
                $design->assign('inv_date',$inv_date);
                $design->assign('inv_is_new',($inv_date>=mktime(0,0,0,5,1,2006)));
                $design->assign('inv_is_new2',($inv_date>=mktime(0,0,0,6,1,2009)));
                $design->assign('inv_is_new3', ($inv_date>=mktime(0,0,0,1,24,2012)));
                $design->assign('inv_is_new4', ($inv_date>=mktime(0,0,0,2,13,2012)));
                $design->assign('inv_is_new5', ($inv_date>=mktime(0,0,0,10,1,2012))); // доработки в акте и сф, собственные (акциз, шт => -) + увеличен шрифт в шапке
                $design->assign('inv_is_new6', ($inv_date>=mktime(0,0,0,1,1,2013))); // 3 (объем), 5 всего, 6 сумма, 8 предъявлен покупателю, 8 всего
            }
            $design->assign('opener','interface');
            $design->assign('bill',$bdata);
            $design->assign('bill_lines',$L);
            $total_amount = 0;
            foreach($L as $line){
                $total_amount += round($line['amount'],2);
            }
            $design->assign('total_amount',$total_amount);

            Company::setResidents($r["firma"], $b);
            $design->assign("firm", Company::getProperty($r["firma"], $b));

            ClientCS::Fetch($r);
            $r["manager_name"] = ClientCS::getManagerName($r["manager"]);
            $design->assign('bill_client',$r);
            return true;
        } else {
            if (in_array($obj, array('invoice','akt','utd'))) {
                return array('bill'=>$bdata,'bill_lines'=>$L,'inv_no'=>$bdata['bill_no'].'-'.$source,'inv_date'=>$inv_date);
            } else return array('bill'=>$bdata,'bill_lines'=>$L);
        }
    }

    function checkSF_discount(&$L)
    {
        foreach($L as &$l)
        {
            if($l["discount_set"] || $l["discount_auto"])
            {
                $discount = ($l["discount_set"] + $l["discount_auto"])/1.18;

                $l["sum"] -= $discount;

                if($discount && $l["amount"])
                    $l["outprice"] = $l["price"] -= $discount/$l["amount"];

            }
        }
    }

    function newaccounts_pi_list($fixclient) {
        global $design,$db;
        $filter=get_param_raw('filter',array('d'=>'','m'=>date('m'),'y'=>date('Y')));
        $R=array();
        $d=dir(PAYMENTS_FILES_PATH);
        if (!$filter['d'] && !$filter['m'] && !$filter['y']) $pattern=''; else {
            $pattern='/';
            if ($filter['d']) $pattern.=$filter['d']; else $pattern.='..';
            $pattern.='.';
            if ($filter['m']) $pattern.=$filter['m']; else $pattern.='..';
            $pattern.='.';
            if ($filter['y']) $pattern.=$filter['y']; else $pattern.='....';
            $pattern.='\.txt$/';
        }

        $data = array();
        while ($e=$d->read()) if ($e!='.' && $e!='..') {
            if (!$pattern || preg_match($pattern,$e)) {
                $R[]=$e;
                if(preg_match_all("/^([^_]+)_([^_]+)(_([^_]+))?__(\d+-\d+-\d+).+/",$e,$o, PREG_SET_ORDER))
                {
                    $o = $o[0];
                    $data[strtotime($o[5])][$o[2]][$o[1].$o[3]] = $o[0];
                }elseif(preg_match_all("/^(.+?)(\d+_\d+_\d+).+/", $e, $o, PREG_SET_ORDER))
                {
                    $o = $o[0];
                    $co = $o[1] == "citi" ? "mcn" : ($o[1]=="ural" ? "cmc" : "mcn");
                    $acc = $o[1] == "mcn" ? "mos" : $o[1];
                    $data[strtotime(str_replace("_","-", $o[2]))][$co][$acc] = $o[0];
                }else{
                }

            }
        }

        ksort($data);
        $d->close();
        sort($R);
        $design->assign('payments',$data);

        $design->assign("l1", array(
                    "mcn" => array("title" => "Эм Си Эн", "colspan" => 2),
                    "all4net" => array("title" => "All4Net", "colspan" => 3),
                    "cmc" => array( "title" => "Си Эм Си", "colspan" => 1),
                    "telekom" => array( "title" => "МСН Телеком", "colspan" => 1)
                    )
                );

        $design->assign("companyes", array(
                    "mcn" => array(
                        "acc" => array("mos", "citi")
                        ),
                    "all4net" => array(
                        "acc" => array("citi_rub", "citi_usd", "ural"),
                        ),
                    "cmc" => array(
                        "acc" => array("ural")
                        ),
                    "telekom" => array(
                        "acc" => array("sber")
                        )
                    )
                );

        $design->assign('filter',$filter);
        $design->AddMain('newaccounts/pay_import_list.tpl');
    }

    function newaccounts_pi_upload($fixclient) {
        global $_FILES,$design;

        $fheader = $this->readFileHeader($_FILES['file']['tmp_name']);

        include INCLUDE_PATH."mt940.php";
        $d = banks::detect($fheader);


        if($d)
        {
            if($this->isPaymentInfo($fheader))
            {
                $date = $this->getPaymentInfoDate($fheader);
                move_uploaded_file($_FILES['file']['tmp_name'],PAYMENTS_FILES_PATH.$d["file"]."_info__".str_replace(".","-", $date).".txt");
            }else{

                $fName = $d["file"]."__";

                switch($d["bank"])
                {
                    case 'sber':
                    case 'ural':
                        $this->saveClientBankExchangePL($_FILES['file']['tmp_name'], $fName);

                         /*
                        $data = $this->getUralSibPLDate($fheader);
                        move_uploaded_file($_FILES['file']['tmp_name'],PAYMENTS_FILES_PATH.$fName.str_replace(".","-", $data).".txt");
                        */
                        break;

                    case 'mos':
                        $data = $this->getMosPLDate($fheader);
                        move_uploaded_file($_FILES['file']['tmp_name'],PAYMENTS_FILES_PATH.$fName.str_replace(".","-", $data).".txt");
                        break;

                    case 'citi':
                        $fName = $_FILES['file']['tmp_name'];
                        $this->saveCitiBankPL($fName);
                        break;
                }
            }
        }
        if ($design->ProcessEx('errors.tpl')) header('Location: ?module=newaccounts&action=pi_list');
    }

    function getPaymentInfoDate($h)
    {
        @preg_match_all("/БИК \d{9}\s+(\d{2}) ([^ ]+) (\d{4})\s+ПРОВЕДЕНО/", iconv("cp1251", "koi8-r//translit", $h), $o, PREG_SET_ORDER);

        $month = "янв фев мар апр май июн июл авг сен окт ноя дек";

        $m = array_search($o[0][2], explode(" ",$month))+1;
        $m .= "";
        if(strlen($m) == 1) $m = "0".$m;

        return  $o[0][1].".".$m.".".$o[0][3];
    }

    function isPaymentInfo($fheader)
    {
        @preg_match_all("/ПОРУЧЕНИЕ/",iconv("cp1251", "koi8-r//translit", $fheader), $f);
        return isset($f[0][0]);
    }

    function getUralSibPLDate($h)
    {
        $h = iconv("cp1251", "koi8-r", $h);
        preg_match_all("@ДатаНачала=(.+)\r?\n@", $h, $o);
        return str_replace("\r", "",$o[1][0]);
    }

    function getMosPLDate($h)
    {
        preg_match_all("@BEGIN_DATE=(.+)\r?\n@", $h, $o);
        return str_replace("\r", "",$o[1][0]);
    }

    function saveCitiBankPL($fPath)
    {
        include_once INCLUDE_PATH."mt940.php";

        $c = file_get_contents($fPath);
        mt940_list_manager::parseAndSave($c);
    }

    function saveClientBankExchangePL($fPath, $fName)
    {
        include_once INCLUDE_PATH."mt940.php";

        $c = file_get_contents($fPath);
        cbe_list_manager::parseAndSave($c, $fName);
    }

    function readFileHeader($fPath)
    {
        $pFile = fopen($fPath,"rb");
        $fheader = fread($pFile, 4096);
        /*
        if(($p = strpos($fheader, "\n")) !== false){
            $fheader = substr($fheader, 0, $p-1);
        }*/
        return $fheader;
    }

    function importPL_citibank_apply()
    {
        $pays = get_param_raw("pays", array());

        print_r($pays);
    }

    function getFirmByPayAccs($pas)
    {
        global $db;

        $firms = array();
        foreach($db->AllRecords("select firma from firma_pay_account where pay_acc in ('".implode("', '", $pas)."')") as $f) {
            $firms[] = $f["firma"];
        }
        return $firms;
    }

    function getCompanyByInn($inn, $firms, $fromAdd = false)
    {
        global $db;

        $v = array();

        if($inn){
            $q = $fromAdd ?
                "select client_id as id from client_inn p, clients c where p.inn = '".$inn."' and p.client_id = c.id and is_active"
                :
                "select id from clients where inn = '".$inn."'";

            foreach($db->AllRecords($qq = $q." and firma in ('".implode("','", $firms)."')") as $c)
                $v[] = $c["id"];

        }

        return $v;
    }

    function importPL_citibank($file, $_payAccs= false, $_pays = array())
    {
        global $design, $db;

        $d = array();
        $sum = array("plus" => 0, "minus" => 0, "imported" => 0, "all" => 0);


        include_once INCLUDE_PATH."mt940.php";

        if($_payAccs === false)
        {
            $c = file_get_contents($file);
            $m = new mt940($c);

            $pays = $m->getPays();
            $payAccs = array($m->getPayAcc());

            $f = explode("/", $file);
            $f = $f[count($f)-1];
            if(preg_match_all("/(citi_mcn)(__\d{2}-\d{2}-\d{4}\.txt)/", $f, $o))
            {
                $infoFile = PAYMENTS_FILES_PATH.$o[1][0]."_info".$o[2][0];
                if(file_exists($infoFile))
                {
                    include_once INCLUDE_PATH."citi_info.php";

                    $c = citiPaymentsInfoParser::parse(file_get_contents($infoFile));

                    citiInfo::add($pays, $c);
                }
            }

        }else{
            $payAccs = $_payAccs;
            $pays = $_pays;
            //$pays=array($pays[0]);
            usort($pays, array("mt940", "sortBySum"));
        }

        $firms = $this->getFirmByPayAccs($payAccs);

        foreach($pays as $pay)
        {
            //if(abs($pay["sum"]) != 7080   ) continue;
            //if($pay["noref"] != 427) continue;

            $clientId = false;
            $billNo = $this->GetBillNoFromComment(@$pay["description"]);

            if($billNo){
                $clientId = $this->getCompanyByBillNo($billNo, $firms);
            }

            $clientId2 = $this->getCompanyByPayAcc(@$pay["from"]["account"], $firms);
            $clientId3 = $this->getCompanyByPayAcc(@$pay["from"]["account"], $firms, true);

            $clientId4 = $clientId5 = array();
            if(isset($pay["inn"]))
            {
                $clientId4 = $this->getCompanyByInn(@$pay["inn"], $firms);
                $clientId5 = $this->getCompanyByInn(@$pay["inn"], $firms, true);
            }

            if($clientId && !$clientId2 && !$clientId3 && !$clientId4 && !$clientId5) { $pay["to_check_bill_only"]=1;}

            $clientIdSum = array();
            if($clientId)  foreach($clientId  as $cId) if($cId) $clientIdSum[$cId] = 1;
            if($clientId2) foreach($clientId2 as $cId) if($cId) $clientIdSum[$cId] = 1;
            if($clientId3) foreach($clientId3 as $cId) if($cId) $clientIdSum[$cId] = 1;
            if($clientId4) foreach($clientId4 as $cId) if($cId) $clientIdSum[$cId] = 1;
            if($clientId5) foreach($clientId5 as $cId) if($cId) $clientIdSum[$cId] = 1;
            $clientIdSum = array_keys($clientIdSum);




            // если счет и клиент различаются
            if ($clientId2 && $clientId && array_search($clientId[0], $clientId2) === false) {
                $pay["to_check"] = 1;
            }

            $pay["bill_no"] = $billNo;
            $pay["clients"] = $this->getClient($clientIdSum);


            if($clientIdSum) 
            {
                $pay["clients_bills"] = $this->getClientBills($clientIdSum, $billNo);

                if($pay["sum"] < 0)
                {
                    $pay["clients_bills"][]  =array("bill_no" => "--Минус счета--", "is_payed" => -1, "is_group"=> 1);

                    foreach($this->getClientMinusBills($clientIdSum, $billNo) as $p)
                        if($this->notInBillList($pay["clients_bills"], $p))
                            $pay["clients_bills"][] = $p;
                }
            }


            if ($pay["clients"][0]['currency']!='RUR')
                $pay['usd_rate'] = $this->getPaymentRate($pay["date"]);

            if (isset($pay['usd_rate']) && $pay['usd_rate'] && $pay['bill_no']) {
                $r = $db->GetRow('select sum as S from newbills where bill_no="'.$pay['bill_no'].'"');
                if ($r && $r['S']!=0) {
                    $rate_bill=round($pay['sum']/$r['S'],4);
                    if (abs($rate_bill-$pay['usd_rate'])/$pay['usd_rate'] <=0.03) $pay['usd_rate']=$rate_bill;
                }
            }

            $this->isPayPass($clientIdSum, $pay);

            $sum["all"] += $pay["sum"];
            $sum["plus"] += $pay["sum"] > 0 ? $pay["sum"] : 0;
            $sum["minus"] += $pay["sum"] < 0 ? -$pay["sum"] : 0;
            if(isset($pay["imported"]) && $pay["imported"]) $sum["imported"] += $pay["sum"];

            $d[] = $pay;
        }

        $bills = array();

        foreach($d as $p)
        {
            if($p["bill_no"] && substr($p["bill_no"], 6,1) == "-")
            {
                $bill = new Bill($p["bill_no"]);

                if(/*substr($bill->Get("bill_date"), 7,3) == "-01" && */$bill->Get("postreg") == "0000-00-00")
                {
                    $c = $bill->Client();
                    if($c["mail_print"] == "yes")
                    {
                        $bills[] = $p["bill_no"];
                    }
                }
            }
        }

        //printdbg(implode("','", $bills));


        $design->assign('file',$file);
        $design->assign("sum", $sum);
        $design->assign("pays", $d);
        $design->assign("bills", $bills);
        $design->AddMain('newaccounts/pay_import_process_citi.tpl');
    }

    function notInBillList(&$pl, $p)
    {
        if(isset($p["is_group"]) && $p["is_group"]) return true;

        foreach($pl as $l)
        {
            if($l["bill_no"] == $p["bill_no"]) return false;
        }
        return true;
    }

    function isPayPass($clientIds, &$pay)
    {
        global $db;

        if (!$clientIds) $clientIds = array(-1);
        if ($pm=$db->GetRow('select comment,bill_no from newpayments where client_id in ("'.implode('","', $clientIds).'") and sum_rub = "'.$pay['sum'].'" and payment_date = "'.$pay['date'].'" and type="bank" and payment_no = "'.$pay["noref"].'"')) {
            $pay['imported']=1;
            $pay["comment"] = $pm["comment"];
            $pay["bill_no"] = $pm["bill_no"];
        }
    }

    function getClient($clientIds)
    {
        global $db;

        if (!$clientIds) return false;
        if (!is_array($clientIds)) $clientIds = array($clientIds);

        $v = array();

        foreach($db->AllRecords(
                    "select id,client, company as name, company_full as full_name, manager, currency
                    from clients where id in ('".implode("','", $clientIds)."')") as $c)
            $v[] = $c;

        return $v;
    }

    function getClientBills($clientIds, $billNo)
    {
        global $db;

        $v = array();
        foreach($clientIds as $clientId)
        {
            if(count($clientIds) > 1){
                $c = $this->getClient($clientId);
                $v[] = array("bill_no" => "--".$c[0]["client"]."--", "is_payed" => -1, "is_group" => true);
            }

            foreach($db->AllRecords($q = '
                        (select bill_no, is_payed,sum from newbills n2
                         where n2.client_id="'.$clientId.'" and n2.is_payed=1
                         /* and (select if(sum(if(is_payed = 1,1,0)) = count(1),1,0) as all_payed from newbills where client_id = "'.$clientId.'")
                         */
                         order by n2.bill_date desc limit 1)
                        union (select bill_no, is_payed,sum from newbills where client_id='.$clientId.' and bill_no = "'.$billNo.'")
                        union (select bill_no, is_payed,sum from newbills where client_id='.$clientId.' and is_payed!="1")
                        '
                        ) as $b){
                $v[] = $b;
            }

        }
        return $v;
    }

    function getClientMinusBills($clientsIds, $billNo)
    {
        global $db;

        $where = "sum < 0 and client_id in ('".implode("','", $clientsIds)."')";

        $v = array();

        // все неоплаченные, и последний оплаченный
        foreach($db->AllRecords("
        (select b.bill_no, b.sum, if((select count(*) from newpayments p where p.bill_no = b.bill_no)>=1,1,0) as is_payed1
            from newbills b where ".$where." having is_payed1 = 0)
        union
        (select b.bill_no, b.sum, if((select count(*) from newpayments p where p.bill_no = b.bill_no)>=1,1,0) as is_payed1
            from newbills b where ".$where." having is_payed1 = 1 order by bill_no desc limit 1)
        ") as $p)
        {
            $p["is_payed"] = $p["is_payed1"] ? -1 : 0;
            $v[] = $p;
        }
        return $v;
    }

    function getCompanyByPayAcc($acc, $firms, $fromAdd = false)
    {
        global $db;

        $v = array();

        if($acc){
            $q = $fromAdd ?
                "select client_id as id from client_pay_acc p, clients c where p.pay_acc = '".$acc."' and p.client_id = c.id"
                :
                "select id from clients where pay_acc = '".$acc."'";

            foreach($db->AllRecords($qq = $q." and firma in ('".implode("','", $firms)."')") as $c)
                $v[] = $c["id"];

        }

        return $v;
    }

    function getCompanyByBillNo($billNo, $firms)
    {
        global $db;

        $r = $db->GetRow("select client_id from newbills b, clients c where b.bill_no = '".$billNo."' and c.id = b.client_id and firma in ('".implode("','", $firms)."')");
        return $r ? array($r["client_id"]) : false;

    }

    function GetBillNoFromComment($c)
    {
        global $db;

        if (
                preg_match('|20\d{4} ?[-/]\d{1,4}(?:-\d+)?|',$c,$m)
                && $db->QuerySelectRow('newbills',array('bill_no'=>str_replace(" ","", $m[0])))
           ) {
            return str_replace(" ","", $m[0]);
        } else {
            if($m){
                if(substr($m[0],6,1) == "/") $m[0][6]="-"; else $m[0][6]="/";
                if($db->QuerySelectRow('newbills',array('bill_no'=>$m[0]))){
                    return $m[0];
                }
            }
        }
        return false;
    }

    function getPaymentRate($date)
    {
        static $d = array();

        if(!isset($d[$date])){
            global $db;
            $r = $db->GetRow('select * from bill_currency_rate where date="'.addslashes($date).'" and currency="USD"');
            $d[$date] = $r['rate'];
        }
        return $d[$date];
    }

    function restructPayments($payAccs, $pp)
    {
        $r = array();

        foreach($pp as  $k=> $p){

            $isIn = !isset($payAccs[$p["account"]]);

            if(!$isIn){
                $p["sum_rub"] = -abs($p["sum_rub"]);
                $p["account"] = $p["geter_acc"];
                $p["bik"] = $p["geter_bik"];
                $p["payer"] = $p["geter"];
                $p["inn"] = $p["geter_inn"];
                $p["oper_date"] = $p["date_dot"];
            }

            $od1 = explode(".", $p["date_dot"]);
            $od = explode(".", $p["oper_date"]);

            $r[] = array(
                    "no" => $k+1,
                    "date_exch" => $od1[2]."-".$od1[1]."-".$od1[0],
                    "date" => $od1[2]."-".$od1[1]."-".$od1[0],
                    "oper_date" => $od[2]."-".$od[1]."-".$od[0],
                    "sum" => $p["sum_rub"],
                    "noref" => $p["pp"],
                    "inn" => $p["inn"],
                    "description" => $p["comment"],
                    "from" => array(
                        "bik" => $p["bik"],
                        "account" => $p["account"],
                        "a2" => $p["a2"]
                        ),
                    "company" => $p["payer"]
                    );
        }
        return $r;
    }


    function newaccounts_pi_process($fixclient) {
        global $design,$db;

        $design->assign('dbg',isset($_REQUEST['dbg']));
        $file=get_param_protected('file');

        $file=str_replace(array('/',"\\"),array('',''),$file);
        if(!file_exists(PAYMENTS_FILES_PATH.$file)){
            //trigger_error('Файл не существует');
            return;
        }

        if(substr($file, 0, 4) == "citi") {
            //, array("40702810700320000882","40702810038110015462","301422002")
            return $this->importPL_citibank(PAYMENTS_FILES_PATH.$file);
        }else{
            list($type, $payAccs, $payments)=PaymentParser::Parse(PAYMENTS_FILES_PATH.$file);
            return $this->importPL_citibank(PAYMENTS_FILES_PATH.$file, $payAccs, $this->restructPayments($payAccs, $payments));
        }


        $firms = $this->getFirmByPayAcc($payAcc);


        $R=array();
        $C=array();
        $min = -1;
        $clients=array();
        $SUM_sum = 0;
        $SUM_plus = 0;
        $SUM_minus = 0;
        $SUM_already = 0;
        $payid = 0;
        $firmaSql = ' and firma in ("'.implode('","', $firms).'")';
        foreach($payments as $p){

            //if($p["sum_rub"] != 3774.82) continue;


            $p['id'] = $payid;
            $payid++;
            $v=explode('.',$p['date_dot']);
            $p['date']=$v[2].'-'.$v[1].'-'.$v[0];
            $p['inn'] = preg_replace('/^0+/','',$p['inn']);
            if(isset($p['oper_date']) && $p['oper_date']){
                $v=explode('.',$p['oper_date']);
                $p['oper_date']=$v[2].'-'.$v[1].'-'.$v[0];
            }else
                $p['oper_date'] = '';

            $r = $db->GetRow('select * from bill_currency_rate where date="'.addslashes($p['date']).'" and currency="USD"');
            $p['usd_rate'] = $r['rate'];
            if(!isset($clients[$p['inn']])){
                $clients[$p['inn']] = $db->AllRecords('select clients.*,0 as is_ext from clients where (inn="'.addslashes($p['inn']).'") and (inn!="")'.$firmaSql);
                $clients[$p['inn']] = array_merge($clients[$p['inn']],$db->AllRecords('select clients.*,1 as is_ext,client_inn.comment from clients inner join client_inn on client_inn.client_id=clients.id and client_inn.is_active=1 where (client_inn.inn="'.addslashes($p['inn']).'") and (client_inn.inn!="")'.$firmaSql));
            }

            if(count($clients[$p['inn']])>=1){

                $v=$clients[$p['inn']][0];
                $p['clients'] = $clients[$p['inn']];
                $p['client']=$v;
                if (
                        preg_match('|20\d{4}[-/]\d{1,4}(?:-\d+)?|',$p['comment'],$m)
                        && $db->QuerySelectRow('newbills',array('bill_no'=>$m[0],'client_id'=>$v["id"]))
                        ) {
                    $p['bill_no']=$m[0];
                } else {
                    if($m){
                        if(substr($m[0],6,1) == "/") $m[0][6]="-"; else $m[0][6]="/";
                        if($db->QuerySelectRow('newbills',array('bill_no'=>$m[0],'client_id'=>$v["id"]))){
                            $p['bill_no']=$m[0];
                        }else
                            $p['bill_no']='';
                    }else
                        $p['bill_no']='';
                }

                foreach ($p['clients'] as $cl) {
                    if (!isset($C[$cl['id']])) {

                        // показываем не оплаченные счета и последний оплаченный, если все счета оплачены
                        $C[$cl['id']]=$db->AllRecords('
                                (select bill_no, is_payed from newbills n2
                                    where n2.client_id="'.$cl["id"].'" and n2.is_payed=1 and
                                    (select if(sum(if(is_payed = 1,1,0)) = count(1),1,0) as all_payed from newbills where client_id = "'.$cl["id"].'")order by n2.bill_date desc limit 1)
                                union
                                (select bill_no, is_payed from newbills where client_id='.$cl['id'].' and is_payed!="1")
                                ');

                    }
                }
                if ($v['currency']=='RUR') {
                    unset($p['usd_rate']);
                } elseif ($p['usd_rate'] && $p['bill_no']) {
                    $r = $db->GetRow('select sum as S from newbills where bill_no="'.$p['bill_no'].'"');
                    if ($r && $r['S']!=0) {
                        $rate_bill=round($p['sum_rub']/$r['S'],4);
                        if (abs($rate_bill-$p['usd_rate'])/$p['usd_rate'] <=0.03) $p['usd_rate']=$rate_bill;
                    }
                }
                if ($pm=$db->QuerySelectRow('newpayments',array('client_id'=>$p['client']['id'],'sum_rub'=>$p['sum_rub'],'payment_date'=>$p['date']))) {
                    $p["bill_no"] = $pm["bill_no"];
                    $p['imported']=1;
                    $SUM_already += $p['sum_rub'];
                }
            } else {
                $k="-".substr(md5($p['inn'].$p['payer']),1,8);
                $p['client']=array('id'=>$k);
            }
            $R[]=$p;
            $SUM_sum += $p['sum_rub'];
            if ($p['sum_rub']>=0) $SUM_plus+=$p['sum_rub']; else $SUM_minus+=$p['sum_rub'];
        }
        $design->assign('file',$file);
        $design->assign('payments',$R);
        $design->assign('clients_bills',$C);
        $design->assign('payments_sum',$SUM_sum);
        $design->assign('payments_plus',$SUM_plus);
        $design->assign('payments_minus',$SUM_minus);
        $design->assign('payments_imported',$SUM_already);
        $design->AddMain('newaccounts/pay_import_process.tpl');
    }

    function newaccounts_pi_apply($fixclient) {
        global $design,$db,$user;
        $file=get_param_raw('file');

        if(strpos($file, "ural") !== false)
            $bank = "ural";
        elseif(strpos($file, "citi") !== false)
            $bank = "citi";
        elseif(strpos($file, "sber") !== false)
            $bank = "sber";
        else
            $bank = "mos";

        $pay=get_param_raw('pay');
        $CL=array();

        //include_once INCLUDE_PATH.'1c_integration.php';
        //$cs = new \_1c\clientSyncer($db);
        $b = 0;
        foreach ($pay as $P) if (isset($P['client']) && $P['client']!='' && $P['bill_no']) {
            if ($client=$db->QuerySelectRow('clients',array('client'=>$P['client']))) {

                $bill = $db->QuerySelectRow("newbills", array("bill_no" => $P["bill_no"]));

                if($bill["client_id"] != $client["id"]) {
                    trigger_error("<br>Платеж #".$P["pay"].", на сумму:".$P["sum_rub"]." не внесен, проверте, что бы счет принадлежал этой компании");
                    continue;
                }

                $CL[$client['id']]=$client['currency'];
                $A = array();
                $curr='';
                $b=0; //вносить ли; 0=нет курса
                $r2 = $db->GetRow('select sum,currency from newbills where bill_no="'.$P['bill_no'].'"');
                $bill_sum = $r2['sum'];
                if ($r2['currency']) $curr=$r2['currency'];
                if (!$curr && $r2=$db->GetRow('select * from newsaldo where ts<"'.addslashes($P['date']).'" order by id desc limit 1')) $curr=$r2['currency'];
                if (!$curr) $curr=$client['currency'];

                if ($curr=='USD') {
                    $r = $db->GetRow('select * from bill_currency_rate where date="'.addslashes($P['date']).'" and currency="USD"');
                    $rate = $r['rate'];
                    $P['sum_rub']=round($P['sum_rub'],2);
                    if ($bill_sum!=0) {
                        $rate_bill=round($P['sum_rub']/$bill_sum,4);
                        if (abs($rate_bill-$rate)/$rate <=0.03) {
                            $rate=$rate_bill;
                            //$A['inv_rur'] = $P['sum_rub'];
                        } else {
                            //$A['inv1_rate'] = $rate;
                            //$A['inv2_rate'] = $rate;
                            //$A['inv3_rate'] = $rate;
                        }
                    }
                    if ($rate) $b=1;
                } else {
                    $rate=1;
                    $b=1;
                }
                if ($b) {
                    $A = array_merge($A,array(
                            'client_id'        => $client['id'],
                            'payment_no'    => intval($P['pay']),
                            'bill_no'        => $P['bill_no'],
                            'bill_vis_no'    => $P['bill_no'],
                            'payment_date'    => $P['date'],
                            'oper_date'        => (isset($P['oper_date']) ? $P['oper_date'] : $P['date']),
                            'payment_rate'    => $rate,
                            'sum_rub'        => round($P['sum_rub'],2),
                            'comment'        => $P['comment'],
                            'add_date'        => array('NOW()'),
                            'add_user'        => $user->Get('id'),
                            'type'            => 'bank',
                            'bank'          => $bank
                            ));
                    $b=$db->QueryInsert('newpayments',$A);
                    #$all4net->payment($P['bill_no']);
                    //$cs->pushClientPayment($b);
                }
                if ($b) {
                    //$CL[$client['id']]=$client['currency'];
                    echo '<br>Платеж '.$P['pay'].' клиента '.$client['client'].' внесён';
                 } else {
                    echo '<br>Платеж '.$P['pay'].' клиента '.$client['client'].' не внесён, так как на '.$P['date'].' отсутствует курс доллара';
                }
            }
        }
#            if ($P['bill_no'] && $bill_sum && ($curr=='USD')) {

        foreach ($CL as $cl_id=>$curr) $this->update_balance($cl_id,$curr);
        //$this->newaccounts_bill_balance_mass($fixclient);
        trigger_error("<br>Баланс обновлён");
        if ($b && $design->ProcessEx('errors.tpl')) {
            header('Location: ?module=newaccounts&action=pi_process&file='.$file);
        } else return $this->newaccounts_pi_process($fixclient);
    }

    function newaccounts_balance_bill($fixclient) {
    global $design,$db;
    $design->assign('b_nedopay',$nedopay=get_param_protected('b_nedopay',0));
    $design->assign('p_nedopay',$p_nedopay=get_param_protected('p_nedopay',1));
    $design->assign('manager',$manager=get_param_protected('manager'));
    $design->assign('date_from',$date_from=get_param_protected('date_from',date('Y-m-01')));
    $design->assign('date_to',$date_to=get_param_protected('date_to',date('Y-m-30')));
    $design->assign('b_pay0',($b_pay0=get_param_protected('b_pay0',0)));
    $design->assign('b_pay1',$b_pay1=get_param_protected('b_pay1',0));
    $design->assign('b_show_bonus',$b_show_bonus=get_param_protected('b_show_bonus',0));
    $design->assign('user_type',$userType =get_param_protected('user_type','manager'));

    $design->assign("l_status", $lStatus = array(
                "work" => "Включенные",
                "income" => "Входящие",
                "once" => "Разовые",
                "work" => "Включенные",
                "closed" => "Отключенные"
                ));

    $design->assign('cl_status',$cl_status=get_param_protected('cl_status', array()));

    $managerInfo = $db->QuerySelectRow("user_users", array("user" => $manager));

    if($managerInfo["usergroup"] == "account_managers")
        $managerInfo["usergroup"] = "manager";

    $newpayments_join = '';

    if($manager && ($b_pay0 || $b_pay1 || $nedopay)){

$W1 = array("and", "~~where_owner~~");





        $W1[] = 'newbills.sum > 0';
        if($cl_status)
            $W1[] = 'status in ("'.implode('", "', $cl_status).'")';
        if($date_from)
            $W1[] = 'newbills.bill_date >= "'.$date_from.'"';
        if($date_to)
            $W1[] = 'newbills.bill_date <= "'.$date_to.'"';
        if(!$b_pay0 || !$b_pay1){
            if($b_pay0)
                $W1[] = 'newbills.is_payed IN (0,2)';
            if($b_pay1)
                $W1[] = 'newbills.is_payed = 1';
        }
        if($nedopay){
            $W1[] = "newbills.is_payed IN (0,2)";
            $newpayments_join .= "
                inner join newpayments on newpayments.bill_no = newbills.bill_no
                and
                if(newbills.currency = 'USD', newbills.`sum`*newpayments.payment_rate, newbills.`sum`) - (select sum(sum_rub) from newpayments where bill_no=newbills.bill_no) >= ".((float)$p_nedopay)."
                    ";
        }else{
        }

        $W1[] = '( newbills.bill_date >= newsaldo.ts OR newsaldo.ts IS NULL)';


        $sql = '
            select
            newbills.*,
            clients.nal,
            clients.client,
            clients.company,
            clients.firma,
            if(clients.currency = newbills.currency, 1,0) as f_currency,
                (SELECT SUM(P.sum_rub) from newpayments as P where P.bill_no=newbills.bill_no) as pay_sum_rur,
                    (SELECT SUM(round(P.sum_rub/P.payment_rate,2)) from newpayments as P where P.bill_no=newbills.bill_no) as pay_sum_usd,
                    clients.manager as client_manager,
                    (select user from user_users where id=nbo.owner_id) as bill_manager'.($b_show_bonus ? ',

                    (SELECT group_concat(concat("#",code_1c, " ",bl.type, if(b.type is null, " -- ", concat(": (", `value`, b.type,") ", round(bl.price*1.18*bl.amount,2), " => ", round(if(b.type = "%",bl.price*1.18*bl.amount*0.01*`value`, `value`*amount),2)))) separator "|\n")  FROM newbill_lines bl
                     left join g_bonus b on b.good_id = bl.item_id and `group` = "'.$managerInfo["usergroup"].'"
                     where bl.bill_no=newbills.bill_no) bonus_info ,
                    (SELECT sum(round(if(b.type = "%",bl.price*1.18*bl.amount*0.01*`value`, `value`*amount),2)) FROM newbill_lines bl
                     left join g_bonus b on b.good_id = bl.item_id and `group` = "'.$managerInfo["usergroup"].'"
                     where bl.bill_no=newbills.bill_no) bonus' : '').'
                        from
                        newbills
                        left join newbill_owner nbo on (nbo.bill_no = newbills.bill_no)
                        '.$newpayments_join.'
                        LEFT JOIN clients ON clients.id = newbills.client_id
                        LEFT JOIN newsaldo ON newsaldo.client_id = clients.id
                        and newsaldo.is_history = 0
                        and newsaldo.currency = clients.currency
                        where '.MySQLDatabase::Generate($W1).'

                        ';
        if($userType == "manager") {
            $sql = str_replace("~~where_owner~~", 'clients.manager="'.$manager.'"', $sql);
        }else{

            if($userType == "creator")
            {
                $sql = str_replace("~~where_owner~~", 'nbo.owner_id="'.$managerInfo["id"].'"', $sql);
            }else{
                $sql = str_replace("~~where_owner~~", 'clients.manager="'.$manager.'" or nbo.owner_id="'.$managerInfo["id"].'"', $sql);
/*
                $sql = str_replace("~~where_owner~~", 'clients.manager="'.$manager.'"', $sql)." union ".
                    str_replace("~~where_owner~~", 'nbo.owner_id="'.$managerInfo["id"].'"', $sql);
*/

                //$W1 = array('AND',' ( nbo.owner_id="'.$managerInfo["id"].'" or clients.manager="'.$manager.'") ');
            }
        }

$sql .= "    order by client, bill_no";

        $R=$db->AllRecords($sql);

        $s_USD = array(
                'pay_sum_rur'=>0,
                'pay_sum_usd'=>0,
                'sum'=>0
                  );
        $s_RUR=array(
                'pay_sum_rur'=>0,
                'sum'=>0,
                'bonus' => 0
                );
        foreach($R as &$r){
            if($r['currency']=='USD'){
                $s_USD['pay_sum_rur'] += $r['pay_sum_rur'];
                $s_USD['pay_sum_usd'] += $r['pay_sum_usd'];
                $s_USD['sum'] += $r['sum'];
            }else{
                $s_RUR['pay_sum_rur'] += $r['pay_sum_rur'];
                $s_RUR['sum'] += $r['sum'];
                @$s_RUR['bonus'] += $r['bonus'];
            }
        }
        $design->assign('bills',$R);
        $design->assign('bills_total_USD',$s_USD);
        $design->assign('bills_total_RUR',$s_RUR);
    }

    $R=array(); $GLOBALS['module_users']->d_users_get($R,array('manager','marketing'));
    if (isset($R[$manager])) $R[$manager]['selected']=' selected';
    $design->assign('users_manager',$R);
    $design->assign('action',$_GET["action"]);
    $design->AddMain('newaccounts/balance_bill.tpl');
}

    function GetDebt($clientId)
    {

        static $mdb = array();

        if (isset($mdb[$clientId])) return $mdb[$clientId];

        global $user,$db;

        $fixclient_data = ClientCS::FetchClient($clientId);

        // saldo
        $sum = array('USD'=>array('delta'=>0,'bill'=>0,'ts'=>''),'RUR'=>array('delta'=>0,'bill'=>0,'ts'=>''));
        $r=$db->GetRow('select * from newsaldo where client_id='.$fixclient_data['id'].' and currency="'.$fixclient_data['currency'].'" and is_history=0 order by id desc limit 1');
        if ($r) {
            $sum[$fixclient_data['currency']]=array('delta'=>0,'bill'=>$r['saldo'],'ts'=>$r['ts'],'saldo'=>$r['saldo']);
        } else {
            $sum[$fixclient_data['currency']]=array('delta'=>0,'bill'=>0,'ts'=>'');
        }
        $R1=$db->AllRecords('select *,'.($sum[$fixclient_data['currency']]['ts']?'IF(bill_date>="'.$sum[$fixclient_data['currency']]['ts'].'",1,0)':'1').' as in_sum from newbills where client_id='.$fixclient_data['id'].' order by bill_no desc');
        $R2=$db->AllRecords('select P.*,(P.sum_rub/P.payment_rate) as sum,U.user as user_name,'.($sum[$fixclient_data['currency']]['ts']?'IF(P.payment_date>="'.$sum[$fixclient_data['currency']]['ts'].'",1,0)':'1').' as in_sum from newpayments as P LEFT JOIN user_users as U on U.id=P.add_user where P.client_id='.$fixclient_data['id'].' order by P.payment_date desc');

        foreach ($R1 as $r) {
            $delta =-$r['sum'];
            foreach ($R2 as $k2=>$r2) if ($r['bill_no']==$r2['bill_no']) {
                $delta+=$r2['sum'];
                unset($R2[$k2]);
            }
            if ($r['in_sum']) {
                $sum[$r['currency']]['bill']+=$r['sum'];
                $sum[$r['currency']]['delta']-=$delta;
            }
        }
        foreach ($R2 as $r2) {
            if ($r2['in_sum']) $sum[$fixclient_data['currency']]['delta']-=$r2['sum'];
        }
        $mdb[$clientId] = array("sum" => $sum[$fixclient_data["currency"]]["delta"], "currency" => $fixclient_data["currency"]);
        return $mdb[$clientId];
    }

    function newaccounts_debt_report($fixclient) {
    global $design,$db;

        $design->assign("l_couriers", array("all" => "--- Все ---","checked"=>"--- Установленные --") + Bill::GetCouriers());
        $design->assign("l_metro", array("all" => "--- Все ---") + ClientCS::GetMetroList());
        $design->assign('courier',$courier=get_param_protected('courier',"all"));
        $design->assign('metro',$metro=get_param_protected('metro',"all"));
        $design->assign('manager',$manager=get_param_protected('manager'));
        $design->assign('date_from',$date_from=get_param_protected('date_from',date('Y-m-01')));
        $design->assign('date_to',$date_to=get_param_protected('date_to',date('Y-m-30')));
        $design->assign('cl_off',$cl_off=get_param_protected('cl_off'));
        $design->assign('zerobills',1);

        $zerobill=get_param_integer('zerobills', 0);

        if (get_param_raw("save", 0) == 1) {
            $s_obj = get_param_protected("obj");
            $s_value = get_param_protected("value");
            $s_billNo = get_param_protected("bill_no");

            if ($s_obj && ($s_value || $s_value == 0) && $s_billNo) {
                $oBill = new Bill($s_billNo);
                if ($dBill = $oBill->GetBill()) {
                    if ($s_obj == "nal" && in_array($s_value, array("nal", "beznal", "prov"))) {
                        $oBill->SetNal($s_value);
                        $db->QueryUpdate("newbills", "bill_no", array("bill_no" => $s_billNo, "nal" => $s_value));
                    }elseif($s_obj == "courier"){
                        $oBill->SetCourier($s_value);
                        $db->QueryUpdate("newbills", "bill_no", array("bill_no" => $s_billNo, "courier_id" => $s_value));
                    }elseif($s_obj == "comment")
                    {
                        $v = array("bill_no" => $s_billNo, "ts"=>array('NOW()'),"user_id"=> $this->GetUserId(), "comment" => $s_value);
                        $R = $db->AllRecords("select id from log_newbills_static where bill_no = '".$s_billNo."'");
                        if($R)
                        {
                            $db->QueryUpdate("log_newbills_static",'bill_no', $v);
                        }else{
                            $db->QueryInsert("log_newbills_static",$v);
                        }
                    }
                }
            }
        }
        $nal = array();
        foreach(get_param_protected("nal", array()) as $n) {
            $nal[$n] = 1;
        }
        $design->assign("nal", $nal);

        $isPrint = get_param_raw("print", 0) == 1;
        if (get_param_raw("go", false) && !empty($nal)) {
            $getURL = "";
            foreach($_GET as $k => $v)
            {
                if (!is_array($v))
                {
                    $getURL .= ($getURL ? "&" : "?").$k."=".urlencode($v);
                }else{
                    foreach($v as $l)
                    {
                        $getURL .= "&".$k."[]=".$l;
                    }
                }
            }

            $design->assign("get_url", $getURL);

            $W1 = array("and");
            if ($manager != "all"){
                $W1[] = 'clients.manager="'.$manager.'"';
            }
            if ($courier != "all") {
                if($courier=='checked') {
                    $W1[] = "courier_id > 0";
                } else {
                    $W1[] = "courier_id = '".$courier."'";
                }
            }
            if ($metro != "all") {
                $W1[] = "metro_id = '".$metro."'";
            }
            $W1[] = "newbills.nal in ('".implode("','", array_keys($nal))."')";
            $W1[] = 'is_payed in (0,2)';
            //$W1[] = 'sum > 0';
            if (!$cl_off) $W1[]='status="work"';
            if ($date_from) $W1[]='newbills.bill_date>="'.$date_from.'"';
            if ($date_to) $W1[]='newbills.bill_date<="'.$date_to.'"';
            if($zerobill) $W1[] = 'newbills.sum<>0';

            //$W1[]='((newbills.bill_date>=newsaldo.ts OR newsaldo.ts IS NULL) AND newbills.currency=clients.currency)';
            $q = '
                select
                    a.*,
                    ls.ts as date,
                    u.user,
                    ls.comment
                from
                    (
                        select
                            `bill_no`,
                            `bill_date`,
                            `newbills`.`client_id`,
                            `newbills`.`currency`,
                            `sum`,
                            `is_payed`,
                            `inv2to1`,
                            `courier_id`,
                            clients.nal,
                            clients.metro_id,
                            clients.payment_comment,
                            clients.address_post as address,
                            newbills.nal as bill_nal,
                            clients.client,
                            clients.company,
                            clients.manager,
                            (SELECT SUM(P.sum_rub) from newpayments as P where P.bill_no=newbills.bill_no) as pay_sum_rur,
                            (SELECT SUM(round(P.sum_rub/P.payment_rate,2)) from newpayments as P where P.bill_no=newbills.bill_no) as pay_sum_usd
                        from newbills
                        LEFT JOIN clients ON clients.id=newbills.client_id
                        LEFT JOIN newsaldo ON newsaldo.client_id=clients.id
                            AND newsaldo.is_history=0 AND newsaldo.currency=clients.currency
                        WHERE
                            '.MySQLDatabase::Generate($W1).'
                            and clients.status not in ("tech_deny","deny")
                        ORDER BY
                            '.($isPrint ? "clients.company, " : "" ).'client,
                            bill_no
                    ) a
                LEFT JOIN log_newbills_static ls USING (bill_no)
                LEFT JOIN user_users u ON u.id = ls.user_id
            ';

            $R=$db->AllRecords($q);

            $s_USD=array('sum'=>0,'saldo'=>0);
            $s_RUR=array('sum'=>0,'saldo'=>0);
            foreach ($R as &$r) {
                /*
                $staticComment = $this->getStaticComment($r["bill_no"]);
                if($staticComment)
                {
                    $r["statcomment"] = $staticComment;
                }
                */
                if ($isPrint) {
                    $r["metro"] = ClientCS::GetMetroName($r["metro_id"]);
                }
                $r["debt"] = $this->GetDebt($r["client_id"]);
                $r["courier"] = Bill::GetCourierName($r["courier_id"]);
                if ($r['currency']=='USD') {
                    $s_USD['sum']+=$r['sum'];
                } else {
                    $s_RUR['sum']+=$r['sum'];
                }
                if ($r["debt"]["currency"] == "USD")
                {
                    $s_USD["saldo"] += $r["debt"]["sum"];
                }else{
                    $s_RUR["saldo"] += $r["debt"]["sum"];
                }

            }
            $design->assign('bills',$R);
            $design->assign('bills_total_USD',$s_USD);
            $design->assign('bills_total_RUR',$s_RUR);
        }
        $m=array();
        $GLOBALS['module_users']->d_users_get($m,'manager');

        $R=array("all" =>array("name" => "Все", "user" => "all"));
        foreach($m as $user => $userData)$R[$user] = $userData;
        if (isset($R[$manager])) $R[$manager]['selected']=' selected';
        $design->assign('users_manager',$R);
        $design->assign("isPrint", $isPrint);
        if ($isPrint)
        {
            $design->ProcessEx('newaccounts/debt_report_print.tpl');
        }else{
            $design->AddMain('newaccounts/debt_report.tpl');
        }
    }

    function getUserId()
    {
        global $user;

        return $user->Get("id");
    }

    function newaccounts_search($fixclient) {
        global $db,$design;
        $search=get_param_protected('search');
        $search = trim($search);
        if ($search) {

            $R=$db->AllRecords(
                    'select newbills.*,clients.nal,clients.client,clients.company
                    FROM newbills
                    INNER JOIN clients ON (clients.id=newbills.client_id)
                    WHERE bill_no LIKE "'.$search.'%" ORDER BY client,bill_no LIMIT 1000');

            if(!$R)
            {
                $R=$db->AllRecords(
                        $q = 'select b.*,c.nal,c.client,c.company
                        FROM newbills b, newbills_add_info i, clients c
                        WHERE c.id=b.client_id
                        and b.bill_no = i.bill_no and i.req_no = "'.$search.'"
                        ORDER BY c.client, b.bill_no LIMIT 1000');
            }

            if (count($R)==1000) trigger_error('Ограничьте условия поиска. Показаны первые 1000 вариантов');
            if (count($R) == 1){
                header("Location: ./?module=newaccounts&action=bill_view&bill=".$R[0]["bill_no"]);
                exit();
            }
            $design->assign('bills',$R);
            $design->assign('search',$search);
        }
        $design->AddMain('newaccounts/search.tpl');

    }

    function newaccounts_balance_client($fixclient) {
        global $design,$db;
        $design->assign('manager',$manager=get_param_protected('manager'));
        $design->assign('cl_off',$cl_off=get_param_protected('cl_off'));
        $design->assign('cl_mar',$cl_mar=get_param_protected('cl_mar'));
        $design->assign('sort',$sort=get_param_protected('sort'));

        if ($manager) {
            $W0 = array('AND');
            if (!$cl_off) $W0[]='clients.status="work"';
            if ($cl_mar) $W0[]='clients.firma="markomnet"';
            if ($manager!='()') $W0[]='clients.manager="'.$manager.'"';

            $W1 = array('AND','newbills.client_id=clients.id');
            $W1[]=array('AND','newbills.currency=clients.currency','saldo_ts IS NULL OR newbills.bill_date>=saldo_ts');

            $W2 = array('AND','P.client_id=clients.id');
            $W2[]=array('OR',
                        array('AND','newbills.bill_no IS NULL',array('OR',
                            'saldo_ts IS NULL',
                            'P.payment_date>=saldo_ts',
                        )),
                        array('AND','newbills.currency=clients.currency',array('OR',
                            'saldo_ts IS NULL',
                            'newbills.bill_date>=saldo_ts',
                        )),
                    );
            /*
            $W2 = array('AND','P.client_id=clients.id', array('OR',
                            'saldo_ts IS NULL',
                            'P.payment_date>=saldo_ts',
                        ));
                        */

            $S=array('client','client','sum_payments');
            if (!isset($S[$sort])) $sort=0;
            $sortK=$S[$sort];

            $balance=$db->AllRecords('select'.
                                ' clients.*'.
                                ', (select ts from newsaldo where client_id=clients.id and newsaldo.is_history=0 and newsaldo.currency=clients.currency order by id desc limit 1) as saldo_ts'.
                                ', (select saldo from newsaldo where client_id=clients.id and newsaldo.is_history=0 and newsaldo.currency=clients.currency order by id desc limit 1) as saldo_sum'.
                                ', (select sum(`sum`) from newbills where '.MySQLDatabase::Generate($W1).') as sum_bills'.
                                ', (select sum(round(sum_rub/payment_rate,2)) from newpayments as P LEFT JOIN newbills ON newbills.bill_no=P.bill_no and P.client_id = newbills.client_id where '.MySQLDatabase::Generate($W2).') as sum_payments'.
                                //', (select sum(round(sum_rub/payment_rate,2)) from newpayments as P  where '.MySQLDatabase::Generate($W2).') as sum_payments'.
                                ', (select bill_date from newbills where '.MySQLDatabase::Generate($W1).' order by bill_date desc limit 1) as lastbill_date'.
                                ', (select bill_no from newbills where '.MySQLDatabase::Generate($W1).' order by bill_date desc limit 1) as lastbill_no'.
                                ', (select round(`sum`) from newbills where '.MySQLDatabase::Generate($W1).' order by bill_date desc limit 1) as lastbill_sum'.
                            ' from clients'.
                            ' WHERE '.MySQLDatabase::Generate($W0).' HAVING lastbill_date IS NOT NULL ORDER by '.$sortK);
            if ($sort==1) {
                usort($balance,create_function('$a,$b','$p=$a["saldo_sum"]+$a["sum_bills"]-$a["sum_payments"]; $q=$b["saldo_sum"]+$b["sum_bills"]-$b["sum_payments"];
                                                        if ($p==$q) return 0; else if ($p>$q) return 1; else return -1;'));
            }
            $design->assign('balance',$balance);
        }
        $R=array(); $GLOBALS['module_users']->d_users_get($R,'manager');
        if (isset($R[$manager])) $R[$manager]['selected']=' selected';
        $design->assign('users_manager',$R);
        $design->AddMain('newaccounts/balance_client.tpl');
    }
    function newaccounts_balance_check($fixclient) {
        global $design,$db,$user,$fixclient_data;

        if (!$fixclient) {trigger_error('Выберите клиента'); return;}


        $date_from=get_param_protected('date_from',date('Y-m-01'));
        $date_from = date("Y-m-d", strtotime($date_from));
        $date_to=get_param_protected('date_to',date('Y-m-31'));
        $date_to = date("Y-m-d", strtotime($date_to));

        $c = ClientCS::getOnDate($fixclient_data['id'], $date_from);

        Company::setResidents($c["firma"], $date_to);

        $saldo=$db->GetRow('select * from newsaldo where client_id="'.$fixclient_data['id'].'" and newsaldo.is_history=0 order by id');
        $design->assign('date_from', $date_from);
        $design->assign('date_to', $date_to);
        $design->assign('saldo', $startsaldo=floatval(get_param_protected('saldo',0)));
        $design->assign('date_from_val',$date_from_val=strtotime($date_from));
        $design->assign('date_to_val',$date_to_val=strtotime($date_to));
        $R=array(); $Rc = 0;
        $S_p=0;
        $S_b=0;

        $R[0]=array('type'=>'saldo','date'=>$date_from_val,'sum_outcome'=>$startsaldo);
        $B = array();

        $W = array('AND','P.client_id="'.$fixclient_data['id'].'"','P.currency="RUR"');
        if ($saldo) $W[]='P.payment_date>="'.$saldo['ts'].'"';
        if ($date_from) $W[]='P.payment_date>="'.$date_from.'"';
        if ($date_to) $W[]='P.payment_date<="'.$date_to.'"';
        $P=$db->AllRecords($sql = '
                select P.*,
                    UNIX_TIMESTAMP(P.payment_date) as payment_date
                from newpayments as P
                where '.MySQLDatabase::Generate($W).'
                order by P.id');

        foreach ($P as &$A) {
            //$R[$A['payment_date'].'-'.($Rc++)] = 
            $R[$A['payment_date']+($Rc++)] = 
                array('type'=>'pay','date'=>$A['payment_date'],'sum_outcome'=>$A['sum_rub'],'pay_no'=>$A['payment_no'],'bill_no'=>$A['bill_no']);
            $S_p+=$A['sum_rub'];
            $B[$A['bill_no']]=1;
        }
        unset($A);

        $W = array('AND','newbills.client_id="'.$fixclient_data['id'].'"','newbills.currency="'.$fixclient_data['currency'].'"');
        if ($saldo) $W[]='newbills.bill_date>="'.$saldo['ts'].'"';
        if ($date_from) $W[]='newbills.bill_date>="'.$date_from.'"-INTERVAL 1 MONTH';
        if ($date_to) $W[]='newbills.bill_date<="'.$date_to.'"+INTERVAL 1 MONTH';
        $P=$db->AllRecords($q = '
                select newbills.*,
                    ifnull((select if(state_id = 21 , 1, 0)
                        from tt_troubles t, tt_stages s
                        where t.bill_no = newbills.bill_no and s.stage_id = t.cur_stage_id limit 1), 0) as is_rejected
                from newbills
                where '.MySQLDatabase::Generate($W).'
                having is_rejected = 0
                order by bill_no
        ');

        $zalog = array();
        $S_zalog = 0;

        foreach ($P as &$p) {
            $bill=new Bill($p['bill_no']);
            $A1 = null;
            for ($I=1;$I<=4;$I++) {
                $A=$this->do_print_prepare($bill,$I==4?'lading':'akt',$I==4?null:$I,'RUR',0);
                if ($I==1) $A1 = $A;
                if($I==4 && $A['bill']){
                    if(!$A1 && $I==4){
                        $d = explode('-',$A['bill']['inv3_date']);
                        $d = mktime(0, 0, 0, $d[1], $d[2], $d[0]);
                    }
                    $A['inv_date'] = ($A1)?$A1['inv_date']:$d;
                    $A['inv_no'] = $A['bill']['bill_no'];
                }
                if(is_array($A) && $A['bill']['tsum']){
                    $k=date('Y-m-d',$A['inv_date']);
                    if(
                        (!$date_from || $k>=$date_from)
                    &&
                        (!$date_to || $k<=$date_to)
                    ){
                        if($I == 3)
                        {
                            /*
                            $iNames = array();
                            foreach($A["bill_lines"] as $l)
                            {
                                $iNames[] = $l["item"];
                            }

                            $zalog[$A['inv_date'].'-'.($Rc++)] = array(
                                'type'       => 'inv',
                                'date'       => $A['inv_date'],
                                'sum_income' => $A['bill']['tsum'],
                                'inv_no'     => $A['inv_no'],
                                'bill_no'    => $A['bill']['bill_no'],
                                'items'      => implode(", ", $iNames),
                                'inv_num'    => $I,
                            );
                            $S_zalog += $A['bill']['tsum'];
                            */
                        //}
                        }else{
                            //$R[$A['inv_date'].'-'.($Rc++)] = array(
                            $sum_in = $A["bill"]["is_rollback"] ? 0 : $A['bill']['tsum'];
                            $sum_out = $A["bill"]["is_rollback"] ? $A['bill']['tsum'] : 0;
                            $R[$A['inv_date']+($Rc++)] = array(
                                'type'       => 'inv',
                                'date'       => $A['inv_date'],
                                'sum_income' => $sum_in,
                                'sum_outcome' => $sum_out,
                                'inv_no'     => $A['inv_no'],
                                'bill_no'    => $A['bill']['bill_no'],
                                'inv_num'    => $I
                            );
                            $S_b+=$sum_in-$sum_out;
                        }
                    } else if (isset($B[$A['bill']['bill_no']])) {

                    }
                }
            }
            unset($bill);
        }

        foreach($db->AllRecords(
            "select 'inv' as type, 3 as inv_num,
                b.bill_no, concat(b.bill_no,'-3') as inv_no,
                unix_timestamp(bill_date) as date,
                l.sum as sum_income, item as items, b.currency, b.inv3_rate, b.gen_bill_rate, b.inv_rur,b.sum as b_sum
            from
                newbills b, newbill_lines l
            where
                    b.bill_no = l.bill_no
                and client_id = '".$fixclient_data['id']."'
                and type='zalog'") as $z)
        {
            $z["sum_income"] = (
                    $z["currency"] == "USD" ? 
                        ($z["inv3_rate"] > 0 ?$z["inv3_rate"] :
                            ($z["gen_bill_rate"] > 0 ? $z["gen_bill_rate"] : ($z["inv_rur"] > 0 ? $z["inv_rur"]/$z["b_sum"] :
                             (99900000+$z["sum_income"]) / $z["sum_income"]) )
                        )*$z["sum_income"]: 
                    $z["sum_income"]);

            $zalog[$z["date"]."-".count($zalog)] = $z;
            $S_zalog += $z["sum_income"];
        }


        ksort($R);
        //tabledbg($R);
        $R[0]['sum_income']=$startsaldo<0?-$startsaldo:0;
        $R[0]['sum_outcome']=$startsaldo>0?$startsaldo:0;
        $S = $startsaldo+$S_p-$S_b;
        $R[]=array('type'=>'total','sum_outcome'=>$S_p,'sum_income'=>$S_b);
        $R[]= $ressaldo = array('type'=>'saldo','date'=>$date_to_val,'sum_income'=>$S>0?0:-$S,'sum_outcome'=>$S>0?$S:0);

        $S -= $S_zalog;
        $formula = sprintf("%.2f", -$S)."=".sprintf("%.2f", $ressaldo["sum_income"]);
        foreach($zalog as $z)
            $formula .= ($z["sum_income"] > 0 ? "+" : "").sprintf("%.2f", $z["sum_income"]);

        $ressaldo = array('type'=>'saldo','date'=>$date_to_val,'sum_income'=>$S>0?0:-$S,'sum_outcome'=>$S>0?$S:0);


        $period_client_data = ClientCS::getOnDate($fixclient_data['id'], $date_from);
        $design->assign("company_full", $period_client_data["company_full"]);

        $design->assign('data',$R);
        $design->assign('zalog',$zalog);
        $design->assign('sum_bill',$S_b);
        $design->assign('sum_pay',$S_p);
        $design->assign('sum_zalog',$S_zalog);
        $design->assign('ressaldo',$ressaldo);
        $design->assign('formula',$formula);

        $fullscreen = get_param_protected('fullscreen',0);
        $design->assign('fullscreen',$fullscreen);
        if ($fullscreen==1) {
            $design->ProcessEx('pop_header.tpl');
            $design->ProcessEx('errors.tpl');
            $design->ProcessEx('newaccounts/balance_check.tpl');
            $design->ProcessEx('pop_footer.tpl');
        } else {
            $design->AddMain('newaccounts/balance_check.tpl');
        }
    }
    function newaccounts_balance_sell($fixclient){
        global $design,$db,$user;
        $design->assign('date_from',$date_from=get_param_protected('date_from',date('Y-m-01')));
        $design->assign('date_to',$date_to=get_param_protected('date_to',date('Y-m-31')));
        $design->assign('date_from_val',$date_from_val=strtotime($date_from));
        $design->assign('date_to_val',$date_to_val=strtotime($date_to));
        $design->assign('paymethod',$paymethod = get_param_protected('paymethod','nal'));
        $design->assign('payfilter',$payfilter = get_param_protected('payfilter','1'));
        $design->assign('firma',$firma = get_param_protected('firma','mcn_telekom'));
        set_time_limit(0);
        $R=array();
        $Rc = 0;
        $S = array(
            'sum'=>0,
            'tsum'=>0,
            'tax'=>0
        );

        if(get_param_raw("do", ""))
        {

        $W = array('AND');//,'C.status="work"');
        $W[] = 'B.sum!=0';
        $W[] = 'P.currency="RUR" OR P.currency IS NULL';

        if($payfilter=='1')     $W[] = 'B.is_payed=1';
        elseif($payfilter=='2') $W[] = 'B.is_payed IN (1,3)';

        if($paymethod) $W[] = 'C.nal="'.$paymethod.'"';
        if($firma)     $W[] = 'C.firma="'.$firma.'"';

        $W[] = "C.type in ('org', 'priv')";

        $W_gds = $W;

        if($date_from)          $W[] = 'B.bill_date>="'.$date_from.'"-INTERVAL 1 MONTH';
        if($date_to)            $W[] = 'B.bill_date<="'.$date_to.'"+INTERVAL 1 MONTH';


        $q_service = '
            select * from (
                select
                    B.*,
                    C.company_full,
                    C.inn,
                    C.kpp,
                    C.type,
                    max(P.payment_date) as payment_date,
                    sum(P.sum_rub) as sum_rub,
                    bill_date as shipment_date,
                    unix_timestamp(bill_date) as shipment_ts,
                    18 as min_nds
                FROM
                    newbills B
                LEFT JOIN newpayments P ON (P.bill_no = B.bill_no AND P.client_id = B.client_id)
                INNER JOIN clients as C ON (C.id = B.client_id)
        WHERE
                '.MySQLDatabase::Generate($W).'
        and B.bill_no like "20____-____"
        GROUP BY
            B.bill_no
        order by
            B.bill_no

        ) f';

        $q_gds = "  
            select *, unix_timestamp(shipment_date) as shipment_ts from (
                    select
                        B.*,
                        C.company_full,
                        C.inn,
                        if(doc_date != '0000-00-00', 
                            doc_date, 
                            (
                                SELECT min(cast(date_start as date)) 
                                FROM tt_troubles t , `tt_stages` s  
                                WHERE t.bill_no = B.bill_no 
                                    and t.id = s.trouble_id 
                                    and state_id in (select id from tt_states where state_1c = 'Отгружен'))) as shipment_date,
                        C.kpp,
                        C.type,
                        max(P.payment_date) as payment_date,
                        sum(P.sum_rub) as sum_rub,
                        (
                            SELECT min(nds) 
                            FROM `newbill_lines` nl, g_goods g 
                            WHERE 
                                    nl.item_id != '' 
                                and nl.bill_no = B.bill_no 
                                and item_id = g.id
                                ) as min_nds
                    FROM
                    (
                        SELECT DISTINCT bill_no 
                        FROM newbills 
                        WHERE doc_date BETWEEN '".$date_from."' and '".$date_to."'  #выбор счетов-фактур с утановленной датой документа
                        
                        UNION 
                        
                        SELECT DISTINCT bill_no 
                        FROM tt_stages s, tt_troubles t 
                        WHERE s.trouble_id = t.id 
                            and date_start between '".$date_from."' and '".$date_to."' 
                            and state_id in (select id from tt_states where state_1c = 'Отгружен') #выбор счетов-фактур по дате отгрузки
                            and t.bill_no is not NULL
                    )t, 
                        newbills B
                    LEFT JOIN newpayments P ON (P.bill_no = B.bill_no AND P.client_id = B.client_id)
                    INNER JOIN clients as C ON (C.id = B.client_id)
                    where
                        t.bill_no = B.bill_no and
                        B.bill_no like '20____/____' and  #только счета с товарами (выставленные через 1С)

                        ".MySQLDatabase::Generate($W_gds)."

                        GROUP BY
                            B.bill_no
                        order by
                            B.bill_no

                        )a 
                        where 
                            (min_nds is null or  min_nds > 0)  ###исключить счета, с товарами без НДС 
                            and shipment_date between '".$date_from."' and '".$date_to."'";


        $AA = array();

        foreach($db->AllRecords($q_service) as $l)
            $AA[] = $l;

        foreach($db->AllRecords($q_gds) as $l)
            $AA[] = $l;


        //$res = mysql_query($q = "select * from (".$q_service." union ".$q_gds.") a order by a.bill_no") or die(mysql_error());

        $t = time();

        $this->bb_cache__init();

        foreach($AA as $p)
        {
        //while(($p = mysql_fetch_assoc($res))!==false){

            $bill=new Bill($p['bill_no']);
            for ($I=1;$I<=3;$I++) {

                $A = $this->bb_cache__get($p["bill_no"]."--".$I);

                if($A === false)
                {
                    $A=$this->do_print_prepare($bill,'invoice',$I,'RUR',0, true);
                    $this->bb_cache__set($p["bill_no"]."--".$I, $A);
                }


                if (is_array($A) && $A['bill']['tsum']) {

                    $A['bill']['shipment_ts'] = $p['shipment_ts'];


                    $invDate = $A['bill']['shipment_ts'] ? date("d.m.Y", $A['bill']['shipment_ts']) : $A['inv_date'];

                    $A['bill']['inv_date'] = $invDate;

                    $k=date('Y-m-d',$A['inv_date']);

                    if ((!$date_from || $k>=$date_from) && (!$date_to || $k<=$date_to)) {
                        $A['bill']['company_full'] = $p['company_full'];
                        if($p["type"] == "priv")
                        {
                            $A['bill']['inn'] = "-----";
                            $A['bill']['kpp'] = "-----";
                        }elseif($p["type"] == "office"){
                            $A['bill']['inn'] = "<span style=\"color: red;\"><b>??????? ".$p['inn']."</b></span>";
                            $A['bill']['kpp'] = $p['kpp'];
                        }else{
                            $A['bill']['inn'] = $p['inn'];
                            $A['bill']['kpp'] = $p['kpp'];
                        }

                        $A['bill']['payment_date'] = $p['payment_date'];
                        $A['bill']['sum_rub'] = $p['sum_rub'];

                        $A['bill']['inv_no'] = $A['inv_no'];



                        if($p["is_rollback"])
                        {
                            foreach(array("ts", "tax", "tsum", "sum") as $f)
                                $A["bill"][$f] = -abs($A["bill"][$f]);
                        }


                        foreach ($S as $sk=>$sv) {
                            $S[$sk]+=$A['bill'][$sk];
                        }


                        $R[$A['inv_date'].'-'.($Rc++)] = $A['bill'];
                    }
                }
            }
            unset($bill);
        } unset($p);
        ksort($R);

        //printdbg($R);

        $this->bb_cache__finish();

        //usort($R, array("self", "bb_sort_sum"));
        }

        $design->assign('data',$R);
        $design->assign('sum',$S);

        $fullscreen = get_param_protected('fullscreen',0);
        $design->assign('fullscreen',$fullscreen);
        if ($fullscreen==1) {
            $design->ProcessEx('newaccounts/pop_header.tpl');
            $design->ProcessEx('newaccounts/errors.tpl');
            $design->ProcessEx('newaccounts/balance_sell.tpl');
            $design->ProcessEx('newaccounts/pop_footer.tpl');
        } else {
            $design->AddMain('newaccounts/balance_sell.tpl');
        }
    }

    function bb_sort_sum($a, $b)
    {
        return $a["sum"] > $b["sum"] ? 1 : 0;
    }

    function bb_cache__init()
    {
        self::$bb_c = array();

    }

    function bb_cache__preload($year, $month)
    {
        $nFile = "/tmp/stat_cache/".$year."-".$month.".dat";

        if(!isset(self::$bb_c[$year][$month]))
            self::$bb_c[$year][$month] = array("data" => array(),"is_modify" => false);

        if(file_exists($nFile))
        {
            self::$bb_c[$year][$month]["data"] = unserialize(file_get_contents($nFile));
        }
    }

    function bb_cache__get($idx)
    {
        if(preg_match("/^(\d{4})(\d{2})-/", $idx, $o))
        {
            if(!isset(self::$bb_c[$o[1]][$o[2]]))
                $this->bb_cache__preload($o[1], $o[2]);

            if(isset(self::$bb_c[$o[1]][$o[2]]["data"][$idx]))
                return self::$bb_c[$o[1]][$o[2]]["data"][$idx];
        }

        return false;
    }

    function bb_cache__set($idx, $val)
    {
        if(preg_match("/^(\d{4})(\d{2})-/", $idx, $o))
        {
            if(!isset(self::$bb_c[$o[1]][$o[2]]))
                $this->bb_cache__preload($o[1], $o[2]);

            if(!isset(self::$bb_c[$o[1]][$o[2]]["data"][$idx]))
            {
                self::$bb_c[$o[1]][$o[2]]["is_modify"] = true;
                self::$bb_c[$o[1]][$o[2]]["data"][$idx] = $val;
            }

            return true;
        }
        return false;
    }

    function bb_cache__finish()
    {
        $dir = "/tmp/stat_cache/";
        if(is_dir($dir) && is_writable($dir))
        {
            foreach(self::$bb_c as $year => $months)
            {
                foreach($months as $month => $data)
                {
                    if($data["is_modify"])
                    {
                        @file_put_contents($dir.$year."-".$month.".dat", serialize($data["data"]));
                    }
                }
            }
        }
    }


    function newaccounts_pay_add($fixclient) {
        global $design,$fixclient_data;
        $oBill = null;
        if ($bill_no=get_param_protected('bill_no')) {
            $oBill = new Bill($bill_no);
            $_SESSION['clients_client'] = $oBill->Get("client_id");
            $fixclient_data = ClientCS::FetchClient($oBill->Get("client_id"));
        }elseif (!$fixclient) {
            trigger_error('Зафиксируйте клиента'); return;
        }
        $dbf = new DbFormNewpayments();
        $dbf->SetDefault('client_id',$fixclient_data['id']);
        $dbf->SetDefault('client',$fixclient_data['client']);
        if ($bill_no=get_param_protected('bill_no')) {
            $dbf->SetDefault('bill_no',$bill_no);
            if($oBill === null)
                $oBill = new Bill($bill_no);
            $dbf->SetDefault("sum_rub", $oBill->Get("sum"));
            $dbf->SetDefault("type", "prov");
        }

        $dbf->Display(array('module'=>'newaccounts','action'=>'pay_apply'),'Платежи','Ручной ввод платежа');
        $design->AddMain('newaccounts/pay_add.tpl');
    }
    function newaccounts_pay_apply($fixclient){
        global $design,$db,$fixclient_data;
        if (!$fixclient) {trigger_error('Не выбран клиент'); return;}
        $bill_no = $_POST['dbform']['bill_no'];
        if ($bill_no==''){trigger_error('Не выбран счет'); return;}


        $b = bill::getDocument($bill_no, $_POST['dbform']['client_id']);

        // каст для БИЛАЙНА

        if(false && $b->is_payed == 1 && $b->client->id != 14043 && $_POST["dbform"]["sum_rub"]>0 && $b->sum > 0) {
            trigger_error("Счет ".$bill_no." оплачен польностью! <br>Не разрешено внесение ручной оплаты полностью оплаченных счетов.");
            return;
        }elseif($b->is_payed == 0){
            $r = $db->GetValue("select client_id from newpayments where bill_no = '".$bill_no."'");
            if($r)
            {
                mail("dga@mcn.ru", "stat", "Оплата счета ".$bill_no.", оплаченного, но is_payed = 0<br>".var_export($_SESSION,true)."<br>".var_export($fixclient_data, true));
                $c = ClientCS::FetchClient($r);
                $this->update_balance($c["id"], $c["currency"]);
                //header('Location: ?module=newaccounts');
                //return;
            }

        }


        $dbf = new DbFormNewpayments();
        $id=get_param_integer('id','');
        if ($id) $dbf->Load($id);
        $result=$dbf->Process();


        $this->update_balance($b->client->id, $b->client->currency);
        /*
        if(include_once(INCLUDE_PATH."1c_integration.php")){
            $clS = new \_1c\clientSyncer($db);
            $f = null;
            if(!$clS->pushClientPayment($dbf->data['id'],$f)){
                echo "Не удалось синхронизировать платеж в 1С!<br />";
                if($f)
                    echo "Ошибка: ".\_1c\getFaultMessage($f);
                echo "<br /><br /><a href='?module=newaccounts'>Баланс</a>";
                exit();
            }
        }
        */
        header('Location: ?module=newaccounts');
        $design->ProcessX();
    }
    function newaccounts_pay_rate($fixclient) {
        global $design,$db,$fixclient_data;
        if (!$fixclient) {trigger_error('Не выбран клиент'); return;}
        $id=get_param_integer('id','');
        $rate=floatval(get_param_raw('rate',''));
        if (!$id) return;
        $db->Query('update newpayments set payment_rate="'.$rate.'" where id='.$id);
        $this->update_balance($fixclient_data['id'],$fixclient_data['currency']);
        header('Location: ?module=newaccounts');
        $design->ProcessX();
    }
    function newaccounts_pay_rebill($fixclient) {
        global $design,$db,$fixclient_data;
        if (!$fixclient) {trigger_error('Не выбран клиент'); return;}
        $pay=get_param_integer('pay');
        $bill=get_param_protected('bill');
        if ($bill) {
            $db->Query('update newpayments set bill_vis_no="'.$bill.'" where id='.$pay);
        } else $db->Query('update newpayments set bill_vis_no=bill_no where id='.$pay);
        /*
        if(include_once(INCLUDE_PATH."1c_integration.php")){
            $clS = new \_1c\clientSyncer($db);
            $f = null;
            if(!$clS->pushClientPayment($pay,$f)){
                echo "Не удалось синхронизировать платеж в 1С!<br />";
                if($f)
                    echo "Ошибка: ".\_1c\getFaultMessage($f);
                echo "<br /><br /><a href='?module=newaccounts'>Баланс</a>";
                exit();
            }
        }
        */
        header('Location: ?module=newaccounts');
        $design->ProcessX();
    }
    function newaccounts_pay_delete($fixclient){
        global $design,$db,$fixclient_data,$user;
        $id = get_param_raw('id');
        if(!$id)
            return;

        $pay = Payment::find($id);

        LogBill::log($pay->bill_no, "Удаление платежа (".$pay->id."), на сумму: ".$pay->sum_rub);

        $pay->delete();

        if(include(INCLUDE_PATH."1c_integration.php")){
            $clS = new \_1c\clientSyncer($db);
            if(!$clS->deletePayment($id)){
                trigger_error("Не удалось удалить платеж из 1С!<br /><a href='?module=newaccounts'>Баланс</a>");
                exit();
            }
        }

        $this->update_balance($fixclient_data['id'], $fixclient_data['currency']);
        header('Location: ?module=newaccounts');
        $design->ProcessX();
    }


    function newaccounts_first_pay($fixclient) {
        global $design,$db;
        $from = get_param_raw('from', date('Y-m-')."1");
        $to = get_param_raw('to', date('Y-m-d'));
        $sort = get_param_raw('sort', 'channel');

        $design->assign('from', $from);
        $design->assign('to', $to);
        $design->assign('sort', $sort);

        if(get_param_raw('process', 'stop') != 'stop') {

            $usersData = array();

            $query = $db->AllRecords('SELECT user, name FROM user_users');
            foreach($query as $row) $usersData[$row['user']] = $row['name'];

            $channels=array(); $query=$db->AllRecords("select * from sale_channels ORDER BY id");
            $channels[''] = 'не определено';
            foreach($query as $key => $value) $channels[$value['id']] = $value['name'];

            $query1 = $db->AllRecords("
                    SELECT
                        client_id, client, company, sum_rub, payment_date, site_req_no
                    FROM
                        `newpayments`, `clients`
                    WHERE
                        payment_date between '".$from."' and '".$to."'
                        and clients.id=newpayments.client_id
                    ORDER BY
                        client,payment_date
                    ");
            $uniqData = array();
            foreach($query1 as $row) {
                if(!isset($uniqData[$row['client']])) $uniqData[$row['client']] = $row;
            }

            $sortedArray = array();
            foreach($uniqData as $client => $row) {
                $query2 = $db->AllRecords("SELECT count(*) as count FROM newpayments WHERE payment_date < '".$from."' and client_id='".$row['client_id']."' ORDER BY payment_date LIMIT 1");
                if($query2[0]['count'] == 0) {
                    $row['telemark'] = "Телемаркетинг";
                    $row['channel'] = "Канал #1";
                    $row['organisation'] = $row['company'];
                    $row['first_pay_data'] = $row['payment_date'];
                    $sortedArray[$client] = $row;
                }
            }

            foreach($sortedArray as $client => $clientData){
                $clientData = $db->AllRecords("SELECT manager, telemarketing, sale_channel FROM clients where client='".$client."'");

                $sortedArray[$client]['manager'] = isset($usersData[$clientData[0]['manager']])?$usersData[$clientData[0]['manager']]:$clientData[0]['manager'];
                $sortedArray[$client]['telemark'] = isset($usersData[$clientData[0]['telemarketing']])?$usersData[$clientData[0]['telemarketing']]:$clientData[0]['telemarketing'];
                $sortedArray[$client]['channel'] =isset($channels[$clientData[0]['sale_channel']])?$channels[$clientData[0]['sale_channel']]:$clientData[0]['sale_channel'];
                $sortedArray[$client]['voip'] = $db->AllRecords("
                        SELECT
                        tarifs_voip.name as tarif,
                        (tarifs_voip.month_line*(usage_voip.no_of_lines-1) + tarifs_voip.month_number) as cost,
                        usage_voip.actual_from,
                        usage_voip.actual_to,
                        log_tarif.date_activation
                        FROM
                        `usage_voip`,
                        `log_tarif`,
                        `tarifs_voip`
                        WHERE
                        usage_voip.client='".$client."'
                        and usage_voip.id = log_tarif.id_service
                        and log_tarif.id_tarif=tarifs_voip.id
                        and service='usage_voip'");
                $sortedArray[$client]['ip_ports'] = $db->AllRecords("
                        SELECT
                        tarifs_internet.name as tarif,
                        tarifs_internet.pay_month as cost,
                        usage_ip_ports.actual_from,
                        usage_ip_ports.actual_to,
                        log_tarif.date_activation
                        FROM
                        `usage_ip_ports`,
                        `log_tarif`,
                        `tarifs_internet`
                        WHERE
                        usage_ip_ports.client='".$client."'
                        and usage_ip_ports.id = log_tarif.id_service
                        and log_tarif.id_tarif=tarifs_internet.id
                        and service='usage_ip_ports'
                        ");
            }
            usort($sortedArray, create_function('$a,$b','return strcmp($a["'.$sort.'"], $b["'.$sort.'"]);'));

            $design->assign('data',$sortedArray);
        }
        $design->AddMain('newaccounts/first_pay.tpl');
    }

    function newaccounts_usd($fixclient) {
        global $design,$db;
        $design->assign('rates',$db->AllRecords('select * from bill_currency_rate order by date desc limit 30'));
        if (($date=get_param_protected('date')) && ($rate=get_param_protected('rate'))) {
            if ($db->QuerySelectRow('bill_currency_rate',array('date'=>$date,'currency'=>'USD'))) {
                trigger_error('Курс на эту дату уже введён');
            } else {
                trigger_error('Курс занесён');
                $db->QueryInsert('bill_currency_rate',array('date'=>$date,'currency'=>'USD','rate'=>$rate));
            }
        }
        $design->assign('cur_date',date('Y-m-d'));
        $design->AddMain('newaccounts/usd.tpl');

    }

    function newaccounts_pay_report() 
    {
        global $design,$db;
        $def=getdate();


        $design->assign("by_day", $byDay = get_param_raw("range_by", "day") == "day");

        if($byDay)
        {
            $from = strtotime(get_param_raw("from_day", date("d-m-Y")));
            $to = strtotime("+1day",$from);
        }else{
            $from = strtotime(get_param_raw("from_period", date("d-m-Y")));
            $to = strtotime(get_param_raw("to_period", date("d-m-Y", strtotime("+1day", strtotime("00:00:00")))));
            $to = strtotime("+1day",$to);
        }

        $design->assign("from_day", date("d-m-Y", $from));
        $design->assign("from_period", date("d-m-Y", $from));
        $design->assign("to_period", date("d-m-Y", strtotime("-1day", $to)));


        $user=(int)get_param_raw('user', false);
        if($user){
            $filter = " and P.add_user=".$user;
        }else{
            $filter = '';
        }

        $type = get_param_raw('type','payment_date');
        if ($type!='payment_date' && $type!='oper_date') $type = 'add_date';
        $design->assign('type',$type);

        $bdefault = array("mos" => true, "citi" => true, "ural" => true, "sber" => true);
        $banks = get_param_raw("banks", $bdefault);
        $design->assign("banks", $banks);
        $filter .= " and P.bank in ('".implode("','", array_keys($banks))."')";

        $types = '';
        foreach (array('bank','prov','neprov') as $k) {
            if ($v = get_param_raw($k)) $types .= ($types?',':'').'"'.$k.'"';
            $design->assign($k,$v);
        }

        if (!$types) $R = array(); else $R = $db->AllRecords($q='select P.*,C.manager,C.client,C.company,B.bill_date,U.user from newpayments as P'.
                        ' INNER JOIN clients as C ON C.id=P.client_id'.
                        ' LEFT JOIN user_users as U ON U.id=P.add_user'.
                        ' LEFT JOIN newbills as B ON B.bill_no=P.bill_no'.
                        ' WHERE '.$type.'>=FROM_UNIXTIME('.$from.') AND '.$type.'<FROM_UNIXTIME('.$to.')'.
                        ' AND P.type IN ('.$types.')'.$filter.' LIMIT 5000');
        $S = array('bRUR'=>0,'pRUR'=>0,'nRUR'=>0,'bUSD'=>0,'pUSD'=>0,'nUSD'=>0,'RUR'=>0,'USD'=>0);

        foreach ($R as &$r) {
            $r['type']=substr($r['type'],0,1);
            $S[$r['type'].$r['currency']] += $r['sum_rub'];
            $S[$r['currency']] += $r['sum_rub'];
        } unset($r);
        $design->assign('user',$user);
        $design->assign('users',$db->AllRecords("select id,user,name from user_users where usergroup in ('admin','manager','account_managers','accounts_department') and enabled = 'yes' order by name",null,MYSQL_ASSOC));
        $design->assign('payments',$R);
        $design->assign('totals',$S);
        $design->assign("fullscreen", $isFullscreen = (get_param_raw("fullscreen", "") != ""));
        if($isFullscreen)
        {
            echo $design->fetch('newaccounts/pay_report.tpl');
            exit();
        }else{
            $design->AddMain('newaccounts/pay_report.tpl');
        }
    }

    function newaccounts_postreg_report() {
        global $design,$db;
        $def=getdate();
        $from=param_load_date('from_',$def);
        $design->AddMain('newaccounts/postreg_report_form.tpl');
    }
    function newaccounts_postreg_report_do() {
        global $design,$db;
        $def=getdate();
        $from=param_load_date('from_',$def);
        $ord = 0;
        $R = $db->AllRecords('select B.*,C.company,C.address_post_real from newbills as B inner join clients as C ON C.id=B.client_id where postreg = "'.date('Y-m-d',$from).'" group by C.id order by B.bill_no');
        foreach ($R as &$r) {
            $r['ord'] = ++$ord;
            if (!preg_match('|^([^,]+),([^,]+),(.+)$|',$r['address_post_real'],$m)) $m = array('','','Москва',$r['address_post_real']);
            $r['_zip'] = $m[1];
            $r['_city'] = $m[2];
            $r['_addr'] = $m[3];
        } unset($r);
        $design->assign('postregs',$R);
        $design->ProcessEx('newaccounts/pop_header.tpl');
        $design->ProcessEx('newaccounts/errors.tpl');
        $design->ProcessEx('newaccounts/postreg_report.tpl');
        $design->ProcessEx('newaccounts/pop_footer.tpl');
    }

    function newaccounts_bill_data(){
        if(!isset($_REQUEST['subaction']))
            return null;
        global $db;
        switch($_REQUEST['subaction']){
            case 'getItemDates':{
                $query = "
                    select
                        date_from,
                        date_to
                    from
                        newbill_lines
                    where
                        bill_no='".addcslashes($_REQUEST['bill_no'], "\\\\'")."'
                    and
                        sort = ".((int)$_REQUEST['sort_number'])."
                ";
                $db->Query($query);
                $ret = $db->NextRecord(MYSQL_ASSOC);
                echo '{date_from:"'.$ret['date_from'].'",date_to:"'.$ret['date_to'].'"}';
                break;
            }case 'setItemDates':{
                $date_from = trim(preg_replace('/[^0-9-]+/','',$_REQUEST['from']));
                $date_to = trim(preg_replace('/[^0-9-]+/','',$_REQUEST['to']));
                $date_patt = '/^\d{4}-\d{2}-\d{2}$/';

                if(!preg_match($date_patt,$date_from) || !preg_match($date_patt,$date_to)){
                    echo "InvalidFormat";
                    break;
                }

                $query = "
                    UPDATE
                        newbill_lines
                    SET
                        date_from = '".$date_from."',
                        date_to = '".$date_to."'
                    where
                        bill_no='".addcslashes($_REQUEST['bill_no'], "\\\\'")."'
                    and
                        sort = ".((int)$_REQUEST['sort_number'])."
                ";
                ob_start();
                $db->Query($query);
                ob_end_clean();
                if(mysql_errno()){
                    echo 'MySQLErr';
                    break;
                }
                echo 'Ok';
                break;
            }
        }
    }

    function newaccounts_make_1c_bill($client_tid){
        global $db, $design, $user;

        $bill_no = (isset($_GET["bill_no"]) ? $_GET["bill_no"] : (isset($_POST["order_bill_no"]) ? $_POST["order_bill_no"]: ""));
        $isRollback = isset($_GET['is_rollback']);

        $bill = null;

        // направляем на нужную страницу редактирования счета
        if(eregi("20[0-9]{4}-[0-9]{4}", $bill_no)) {
            header("Location: ./?module=newaccounts&action=bill_edit&bill=".$bill_no);
            exit();
        }

        //устанавливаем клиента
        if($bill_no) {
            $bill = new Bill($bill_no);
            if(!$bill)
                return false;
            $client_id = $bill->Client("id");

            if($bill->IsClosed()){
                header("Location: ./?module=newaccounts&action=bill_view&bill=".$bill_no);
                exit();
            }
        }else{
            // если форма пересылается
            if(isset($_POST['action']) && $_POST['action'] == 'make_1c_bill'){
                $client_id = urldecode(get_param_raw("client_id"));
                // открывается
            }else{
                $client_id = $client_tid;
            }
        }

        ClientCS::getClientClient($client_id);
        $_SESSION['clients_client'] = $client_id;

        // инициализация
        $lMetro = ClientCS::GetList("metro","std");
        $lLogistic = ClientCS::GetList("logistic");
        $design->assign("l_metro", $lMetro);
        $design->assign("l_logistic", $lLogistic);

        $storeList = array();
        foreach($db->AllRecords("select * from g_store where is_show='yes' order by name") as $l)
            $storeList[$l["id"]] = $l["name"];
        $design->assign("store_list", $storeList);
        $storeId = get_param_raw("store_id", "8e5c7b22-8385-11df-9af5-001517456eb1");
        


        require_once INCLUDE_PATH."clCards.php";
        require_once INCLUDE_PATH."1c_integration.php";

        $cl_c = \clCards\getCard($db, $client_id);

        $bm = new \_1c\billMaker($db);

        //$pts = $bm->getPriceTypes($client_tid);
        $pt = ClientCS::getPriceType($client_id);

        $positions = array(
                'bill_no' =>$bill_no,
                'client_id'=>$client_id,
                'list'=>array(),
                'sum'=>0,
                'number'=>'',
                'comment'=>''
                );

        $isToRecalc = false;


        /** блок загрузки данных::старт **/
        // загружаем данные о позиция заказа
        // из _POST +удаление
        if(isset($_POST['pos'])){
            foreach($_POST['pos'] as $id=>$varr){
                if($_POST["pos"][$id]["quantity"] != $_POST["pos"][$id]["quantity_saved"])
                    $isToRecalc = true;
                if(!isset($_POST['pos'][$id]['del'])){
                    $positions['list'][$id] = $varr;
                    $isToRecalc = true;
                }
            }

        // или из базы
        }elseif(isset($_GET['bill_no'])){
            $positions = $bm->getStatOrder($_GET['bill_no']);
            if($positions['is_rollback'] && !isset($_GET['is_rollback'])){
                header('Location: ?module=newaccounts&action=make_1c_bill&bill_no='.$_GET['bill_no'].'&is_rollback=1');
                exit();
            }
        }elseif(isset($_GET["from_order"])){
            $_POST = $db->GetRow("select * from newbills_add_info where bill_no = '".$_GET["from_order"]."'");
        }

        // добавление новой позиции
        if(isset($_POST['append'])){
            $isToRecalc = true;
            if(!trim($_POST['new']['quantity']) || !is_numeric($_POST['new']['quantity']))
                $_POST['new']['quantity'] = 1;
            $_POST['new']['discount_set'] = 0;
            $_POST['new']['discount_auto'] = 0;
            $_POST['new']['code_1c'] = 0;
            $_POST['new']['price'] = Good::GetPrice($_POST['new']['id'], $pt);

            $positions['list'][] = $_POST['new'];
            $buf = array();
            foreach($positions['list'] as $k=>$p){
                if(isset($buf[$p['id']])){
                    $buf[$p['id']]['quantity'] += $p['quantity'];
                }else
                    $buf[$p['id']] = $p;
            }
            if(count($buf) != count($positions['list'])){
                $nl = array();
                foreach($buf as $p){
                    $nl[] = $p;
                }
                $positions['list'] = $nl;
                unset($nl);
            }
            unset($buf);
        }

        /** блок загрузки данных::конец **/

        // расчет
        if($isToRecalc && !$isRollback) {
            $positions = $bm->calcOrder($client_id, $positions, $pt);
        }elseif($isRollback){
            $bm->calcGetedOrder($positions, true);
        }else{
            $bm->calcGetedOrder($positions);
        }



        // данные заказа (add_info)

        //список полей
        $adds = array(
            'ФИО'=>'fio','Адрес'=>'address','НомерЗаявки'=>'req_no','ЛицевойСчет'=>'acc_no',
            'НомерПодключения'=>'connum','Комментарий1'=>'comment1','Комментарий2'=>'comment2',
            'ПаспортСерия'=>'passp_series','ПаспортНомер'=>'passp_num','ПаспортКемВыдан'=>'passp_whos_given',
            'ПаспортКогдаВыдан'=>'passp_when_given','ПаспортКодПодразделения'=>'passp_code',
            'ПаспортДатаРождения'=>'passp_birthday','ПаспортГород'=>'reg_city',
            'ПаспортУлица'=>'reg_street','ПаспортДом'=>'reg_house','ПаспортКорпус'=>'reg_housing',
            'ПаспортСтроение'=>'reg_build','ПаспортКвартира'=>'reg_flat','Email'=>'email',
            'ПроисхождениеЗаказа'=>'order_given','КонтактныйТелефон'=>'phone'

            ,
            'Метро' => 'metro_id',
            'Логистика' => 'logistic',
            "ВладелецЛинии" => 'line_owner'
        );

        // инициализация из _POST
        $add = array();
        $addcnt = 0;
        foreach($adds as $add_key){
            if(isset($_POST[$add_key])){
                $add[$add_key] = $_POST[$add_key];
                $addcnt++;
            }else
                $add[$add_key] = '';
        }


        // инициализация из базы
        if(isset($_GET['bill_no'])) {
            if(!$addcnt){
                $adds_data = $db->GetRow($q="select * from newbills_add_info where bill_no='".addcslashes($_GET['bill_no'], "\\'")."'");
                $storeId = $adds_data["store_id"];
                if(count($adds_data)){
                    foreach($adds as $add_rkey=>$add_ekey){
                        if(isset($adds_data[$add_ekey])){
                            $add[$add_ekey] = $adds_data[$add_ekey];
                            $add_info[$add_rkey] = $adds_data[$add_ekey];
                        }else
                            $add_info[$add_rkey] = '';
                    }
                }
            }
        }



        //printdbg($storeList,$storeId);
        $design->assign("store_id", $storeId);

        $enableLogistic = true;
        if(\_1c\checkLogisticItems($positions["list"], $add, false)) {
            $enableLogistic = false;
            $_POST["logistic"] = $add["logistic"];
        }
        $design->assign('add',$add);

        if(isset($_POST['order_bill_no']))
            $positions['bill_no'] = $_POST['order_bill_no'];

        if(isset($_POST["order_comment"]))
            $positions['comment'] = $_POST["order_comment"];


        if(isset($_GET['is_rollback']))
            $positions['is_rollback'] = true;
        else
            $positions['is_rollback'] = false;

        // оформить заказ
        if(isset($_POST['make'])){
            // сохранение в 1с
            $add_info = array();
            foreach($adds as $add_rkey=>$add_ekey){
                if(isset($_POST[$add_ekey])){
                    $add_info[$add_rkey] = $_POST[$add_ekey];
                }else
                    $add_info[$add_rkey] = '';
            }
            if(!count($add_info))
                $add_info = null;

            #$ret = $bm->saveOrder($client_tid,$positions['number'],$positions['list'],$positions['comment'],$positions['is_rollback'],$fault);
            $saveIds = array("metro_id" => $add_info["Метро"], "logistic" => $add_info["Логистика"]);

            $add_info["Метро"] = ($add_info["Метро"] == 0 ? "" : $lMetro[$add_info["Метро"]]);
            $add_info["Логистика"] = ($add_info["Логистика"] == "none" ? "" : $lLogistic[$add_info["Логистика"]]);

            $this->compareForChanges($positions, $bm->getStatOrder($positions['bill_no']));

            $a = array(
                'client_tid'=>$client_id,
                'order_number'=>$positions['bill_no'],
                'items_list'=>(isset($positions['list']) ? $positions['list'] : false),
                //'items_list'=> $positions['list'] ,
                'order_comment'=>$positions['comment'],
                'is_rollback'=>$positions['is_rollback'],
                'add_info'=>$add_info,
                "store_id" => $storeId
            );

            $ret = $bm->saveOrder($a,$fault);

            if($ret){
                //сохранение заказа в стате
                $error = '';
                $cl = new stdClass();
                $cl->order = $ret;
                $cl->isRollback = $isRollback;


                $bill_no = $ret->{\_1c\tr('Номер')};

                if(!$bill){
                    $bill = new Bill($bill_no);
                }


                $sh = new \_1c\SoapHandler();
                $sh->statSaveOrder($cl, $bill_no, $error, $saveIds);

                $positions = $bm->getStatOrder($bill_no);
                if($ttt = $db->GetRow("select * from tt_troubles where bill_no='".$bill_no."'")) {
                    if($ttt['state_id'] == 15 && $bill){
                        global $user;
                        $bill->SetManager($user->Get("id"));
                    }
                    if(!$positions['comment']){
                        $comment = $add_info['ПроисхождениеЗаказа']."<br />
                            Телефон: ".$_POST['phone']."<br />
                            Адрес доставки: ".$_POST['address']."<br />
                            Комментарий1: ".$_POST['comment1']."<br />
                            Комментарий2: ".$_POST['comment2'];
                    }else{
                        $comment = $positions['comment'];
                    }
                    if(trim($comment))
                        $db->QueryUpdate("tt_troubles", "bill_no", array( "problem" =>$comment, "bill_no"=>$ttt['bill_no']));

                }elseif(isset($_GET['tty']) && in_array($_GET['tty'],array('shop_orders','mounting_orders','orders_kp'))){
                    $GLOBALS['module_tt']->createTrouble(array(
                                'trouble_type'=>$_GET['tty'],
                                'trouble_subtype' => 'shop',
                                'client'=>$client_id,
                                'problem'=>@$positions['comment'],
                                'bill_no'=>$bill_no,
                                'time'=>date('Y-m-d')
                                ));
                }

                if(!$ttt && $bill)
                    $bill->SetManager($user->Get("id"));

                trigger_error("Счет #".$bill_no." успешно ".($_POST["order_bill_no"] == $bill_no ? "сохранен" : "создан")."!");
                $db->QueryInsert("log_newbills", array( 'bill_no'=>$bill_no, 'ts'=>array('NOW()'), 'user_id'=>$user->Get('id'), 'comment'=>'Создание заказа'));
                header("Location: ./?module=newaccounts&action=bill_view&bill=".$bill_no);
                exit();
            }else{
                trigger_error("Не удалось создать заказ в 1С");
            }
        }


        $R=array(); $GLOBALS['module_users']->d_users_get($R,array('manager','marketing'));
        $userSelect = array(0 => "--- Не установлен ---");
        foreach($R as $u) {
            $userSelect[$u["id"]] = $u["name"]." (".$u["user"].")";
        }
        $design->assign("managers", $userSelect);
        if($bill)
        $design->assign("bill_manager", $bill->GetManager());

        $design->assign('show_adds',
                (in_array($client_id,array('all4net','wellconnect')) || !$cl_c || $cl_c->getAtMask(\clCards\struct_cardDetails::type) <> 'org'));
        $design->assign('order_type',isset($_GET['tty'])?$_GET['tty']:false);
        $design->assign('is_rollback',isset($_GET['is_rollback'])?true:false);
        $positions["client_id"] = $client_id;
        $this->addArt($positions);
        $design->assign('positions',$positions);
        //$design->assign('pts',$pts);
        $design->assign('hide_tts',true);
        $design->assign('enable_logistic', $enableLogistic);
        $design->AddMain('newaccounts/make_1c_bill.html');
    }

    private function compareForChanges(&$posSave, $posDB)
    {
        $lSave = &$posSave["list"];
        $lDB = &$posDB["list"];

        if(count($lSave) == count($lDB))
        {
            $isEqual = true;
            foreach($lSave as $idx => $s)
            {
                $d = $lDB[$idx];
                foreach(array("id", "quantity", "price", "discount_set","discount_auto") as $f)
                {
                    if($f == "id")
                    {
                        list($id, $descrId) = explode(":", $s[$f]);
                        if($descrId == "") $descrId = "00000000-0000-0000-0000-000000000000";
                        $s[$f] = $id.":".$descrId;
                    }
                    if($f == "price")
                    {
                        $d[$f] = round($d[$f], 2);
                    }
                    if($s[$f] != $d[$f]) {
                        $isEqual = false;
                        //echo $f.": ".$s[$f]." => ".$d[$f];
                        break 2;
                    }
                }
            }

            if($isEqual)
            {
                unset($posSave["list"]);
            }
        }
    }

    function addArt(&$pos){
        global $db;

        foreach($pos["list"] as &$p){
            list($gId, $dId)= explode(":", $p["id"]."::");
            $p["art"] = "";
            if($gId){
                $g = $db->GetRow("select art from g_goods where id = '".$gId."'");
                $p["art"] = $g["art"];
            }
        }
    }


    function newaccounts_rpc_findProduct($fixclient){
        global $db;
        if(!trim($_GET['findProduct']))
            exit();

        $prod = get_param_raw('findProduct');
        if(strlen($prod) >= 1)
        {

            $pt = ClientCS::getPriceType($fixclient);

            $prod = iconv("utf-8", "koi8-r", $prod);
            $ret = "";
            $prod = str_replace(array("*","%%"), array("%","%"), mysql_escape_string($prod));

            $storeId = get_param_protected("store_id", "8e5c7b22-8385-11df-9af5-001517456eb1");

            foreach($db->AllRecords($q =
                        "
                        select * from (
                        (
                        SELECT if(d.name is null, concat(g.id,':'), concat(g.id,':',p.descr_id)) as id,
                        g.name as name,
                        if(d.name is not null,d.name ,'') as description,
                        g.group_id, p.descr_id as descr_id,  p.price, d.name as descr_name, qty_free, qty_store, qty_wait, is_service,
                        art, num_id as code, dv.name as division, store
                        FROM (
                            select * from g_goods g1 where g1.art = '".$prod."'
                            union select * from g_goods g1 where g1.num_id = '".$prod."'
                            union select * from g_goods g2 where g2.name like '%".$prod."%'
                            ) g
                        left join g_good_price p on (p.good_id = g.id )
                        left join g_good_description d on (g.id = d.good_id and d.id = p.descr_id)
                        left join g_good_store s on (s.good_id = g.id and s.descr_id = p.descr_id and s.store_id = '".$storeId."')
                        left join g_division dv on (g.division_id = dv.id)

                        where price_type_id = '".$pt."' #or g.is_allowpricezero
                        order by length(g.name)
                        limit 50 )
                        union
                        (
                         SELECT  if(d.name is null, concat(g.id,':'), concat(g.id,':',s.descr_id)) as id,
                        g.name as name,
                        if(d.name is not null,d.name ,'') as description,
                         g.group_id, '' as descr_id,   '--- ' as price, null as descr_name, qty_free, qty_store, qty_wait, is_service,
                         art, num_id as code, dv.name as division,store
                         FROM (
                             select * from g_goods g1 where g1.art = '".$prod."'
                             union select * from g_goods g1 where g1.num_id = '".$prod."'
                             union select * from g_goods g2 where g2.name like '%".$prod."%'
                             ) g
                         left join g_good_store s on (s.good_id = g.id and s.store_id = '".$storeId."')
                         left join g_good_description d on (g.id = d.good_id and d.id = s.descr_id)
                         left join g_division dv on (g.division_id = dv.id)

                         where  g.is_allowpricezero
                         order by length(g.name)
                         limit 50
                        )
                        ) a group by a.id

                        "
                        /*
                           "SELECT id, name, price, quantity, quantity_store, is_service
                           FROM `g_goods`
                           WHERE name like '%".$prod."%'
                           LIMIT 50
                           "*/) as $good)
                    {
                        if(strpos($good["name"], "(Архив)")!==false) continue;

                    $ret .= "{".
                    "id:'".addcslashes($good['id'],"\\'")."',".
                    "name:'".addcslashes($good['name'],"\\'")."',".
                    "description:'".addcslashes($good['description'],"\\'")."',".
                    "division:'".addcslashes($good['division'],"\\'")."',".
                    "price:'".addcslashes($good['price'],"\\'")."',".
                    "qty_free:'".addcslashes($good['qty_free'],"\\'")."',".
                    "qty_store:'".addcslashes($good['qty_store'],"\\'")."',".
                    "qty_wait:'".addcslashes($good['qty_wait'],"\\'")."',".
                    "art:'".addcslashes($good['art'],"\\'")."',".
                    "code:'".addcslashes($good['code'],"\\'")."',".
                    "store:'".addcslashes($good['store'],"\\'")."',".
                    "is_service:".($good['is_service']?'true':'false').
                    "},";
                    }
            $ret = "[".$ret."]";

        }else{
            $ret = "false";
        }

        /*
        if(include_once(INCLUDE_PATH."1c_integration.php")){
            $bm = new \_1c\billMaker($db);
            $prods_list = $bm->findProduct(iconv('utf8','koi8r',trim($_GET['findProduct'])), iconv('utf8','koi8r',trim($_GET['priceType'])));

            $ret = '[';
            for($i=0;$i<count($prods_list);$i++){
                $ret .= "{".
                    "id:'".addcslashes($prods_list[$i]['id'],"\\'")."',".
                    "name:'".addcslashes($prods_list[$i]['name'],"\\'")."',".
                    "price:'".addcslashes($prods_list[$i]['price'],"\\'")."',".
                    "quantity:'".addcslashes($prods_list[$i]['quantity'],"\\'")."',".
                    "is_service:".($prods_list[$i]['is_service']?'true':'false').
                "},";
            }
            $ret .= ']';
        }else{
            $ret = 'false';
        }
        */
        header('Content-Type: text/plain; charset="koi8-r"');
        echo $ret;
        exit();
    }

    function newaccounts_docs($fixclient)
    {
        global $db, $design;

        $R = array();

        $from = get_param_raw("from", date("Y-m-d"));
        $to = get_param_raw("to", date("Y-m-d"));

        if(get_param_raw("do", "") != "")
        {

            $from = @strtotime($from." 00:00:00");
            $to = @strtotime($to." 23:59:59");

            if(!$from || !$to || ($from > $to))
            {
                $from = date("Y-m-d");
                $to = date("Y-m-d");
                // nothing
            }else{
                $R = $db->Allrecords("select * from qr_code where date between '".date("Y-m-d H:i:s", $from)."' and '".date("Y-m-d H:i:s", $to)."' order by file, date");
                $from = date("Y-m-d", $from);
                $to = date("Y-m-d", $to);
            }
            
        }else{
            $this->_qrDocs_check();
        }

        $idx = 1;
        foreach($R as &$r)
        {
            $r["idx"] = $idx++;
            $r["ts"] = $r["date"];


            $qNo = QRCode::decodeNo($r["code"]);

            $r["type"] = $qNo ? $qNo["type"]["name"] : "????";
            $r["number"] = $qNo ? $qNo["number"] : "????";

            $num = "";

            if($pos = strrpos($r["file"], "_"))
            {
                $num = substr($r["file"], $pos+1);
                if($pos = strpos($num, "."))
                {
                    $num = substr($num, 0, strlen($num)-$pos-1);
                }
            }
            $r["prefix"] = $num?:"";


        }

        $design->assign("data", $R);
        $design->assign("from", $from);
        $design->assign("to", $to);
        $design->AddMain("newaccounts/docs.html");
        
    }

    function _qrDocs_check()
    {
        global $db;

        if(!defined("SCAN_DOC_DIR"))
            throw new Exception("Директория с отсканированными документами не задана");

        $dir = SCAN_DOC_DIR;

        if(!is_dir($dir))
            throw new Exception("Директория с отсканированными документами задана не верно (".SCAN_DOC_DIR.")");

        if(!is_readable($dir))
            throw new Exception("В директорию с документами доступ запрещен");


        $d = dir($dir);

        $c = 0;

        $docs = array();
        while($e = $d->read())
        {
            if($e == ".." || $e == ".") continue;
            if(stripos($e, ".pdf") === false) {
                exec("rm ".$dir.$e);
                continue;
            }
            $docs[] = $e;
        }

        sort($docs);

        foreach($docs as $e)
        {
            $qrcode = QRCode::decodeFile($dir.$e);
            $qr = QRCode::decodeNo($qrcode);

            if($qrcode)
            {
                $clientId = 0;
                $billNo = "";
                $type = "";

                if($qr)
                {
                    $billNo = $qr["number"];
                    $clientId = NewBill::find_by_bill_no($billNo)->client_id;
                    $type = $qr["type"]["code"];
                }

                $id = $db->QueryInsert("qr_code", array(
                            "file"      => $e,
                            "code"      => $qrcode,
                            "bill_no"   => $billNo,
                            "client_id" => $clientId,
                            "doc_type"  => $type
                            ));

                exec("mv ".$dir.$e." ../store/documents/".$id.".pdf");
            }else{
                exec("mv ".$dir.$e." ../store/documents/unrecognized/".$e);
            }


        }
        $d->close();
    }

    function newaccounts_docs_unrec($fixclient)
    {
        global $design;

        $dirPath = STORE_PATH."documents/unrecognized/";

        if(!is_dir($dirPath))
            throw new Exception("Директория с нераспознаными документами задана не верно (".$dirPath.")");

        if(!is_readable($dirPath))
            throw new Exception("В директорию с нераспознаными документами доступ запрещен");

        if(($delFile = get_param_raw("del", "")) !== "")
        {
            if(file_exists($dirPath.$delFile))
            {
                exec("rm ".$dirPath.$delFile);
            }
        }

        if(get_param_raw("recognize", "") == "true")
        {
            $this->docs_unrec__recognize();
        }

        $d = dir($dirPath);

        $c = 0;
        $R = array();
        while($e = $d->read())
        {
            if($e == ".." || $e == ".") continue;
            if(stripos($e, ".pdf") === false) continue;

            $R[] = $e;
        }
        $d->close();

        $docType = array();
        foreach(QRCode::$codes as $code => $c)
        {
            $docType[$code] = $c["name"];
        }

        $design->assign("docs", $R);
        $design->assign("doc_type", $docType);
        $design->AddMain("newaccounts/docs_unrec.html");
    }

    function docs_unrec__recognize()
    {
        $dirDoc = STORE_PATH."documents/";
        $dirUnrec = $dirDoc."unrecognized/";

        $file = get_param_raw("file", "");
        if(!file_exists($dirUnrec.$file)) {trigger_error("Файл не найден!"); return;}

        $type = get_param_raw("type", "");
        if(!isset(QRCode::$codes[$type])) {trigger_error("Ошибка в типе!"); return;}

        $number = get_param_raw("number", "");
        if(!preg_match("/^201\d{3}[-\/]\d{4}$/", $number)) { trigger_error("Ошибка в номере!"); return;}

        global $db;


        $qrcode = QRCode::encode($type, $number);
        $qr = QRCode::decodeNo($qrcode);

        $billNo = "";
        $clientId = 0;
        $type = "";

        if($qr)
        {
            $billNo = $qr["number"];
            $clientId = NewBill::find_by_bill_no($billNo)->client_id;
            $type = $qr["type"]["code"];
        }

        $id = $db->QueryInsert("qr_code", array(
                    "file"      => $file,
                    "code"      => $qrcode,
                    "bill_no"   => $billNo,
                    "client_id" => $clientId,
                    "doc_type"  => $type
                    ));


        exec("mv ".$dirUnrec.$file." ".$dirDoc.$id.".pdf");
    }

    function newaccounts_doc_file($fixclient)
    {
        $dirPath = STORE_PATH."documents/";

        if(get_param_raw("unrecognized", "") == "true")
        {
            $file = get_param_raw("file", "");
            $fPath = $dirPath."unrecognized/".$file;

            $this->docs_echoFile($fPath, $file);
        }elseif(($id = get_param_integer("id", 0)) !== 0)
        {
            global $db;

            $r = $db->GetValue("select file from qr_code where id = '".$id."'");

            if($r)
            {
                $this->docs_echoFile($dirPath.$id.".pdf", $r);
            }
        }
    }

    function docs_echoFile($fPath, $fileName)
    {
        if(file_exists($fPath))
        {
            header("Content-Type:application/pdf");
            header('Content-Transfer-Encoding: binary');
            header('Content-Disposition: attachment; filename="'.iconv("KOI8-R","CP1251",$fileName).'"');
            header("Content-Length: " . filesize($fPath));
            echo file_get_contents($fPath);
            exit();
        }else{
            trigger_error("Файл не найден!");
        }
        //
    }
}
?>
