<?php

use app\classes\StatModule;
use app\classes\BillContract;
use app\classes\BillQRCode;
use app\models\Courier;
use app\models\ClientAccount;
use app\models\ClientCounter;
use app\models\Payment;
use app\models\Param;
use app\models\BillDocument;
use app\models\Transaction;
use app\classes\documents\DocumentReportFactory;
use app\classes\bill\ClientAccountBiller;
use app\models\Organization;

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
        $db->Query('insert into newsaldo (client_id,saldo,currency,ts,is_history,edit_user,edit_time) values ('.$fixclient_data['id'].',"'.$saldo.'","'.$fixclient_data['currency'].'","'.$date.'",0,"'.$user->Get('id').'",NOW())');
        ClientAccount::dao()->updateBalance($fixclient_data['id']);
        if ($design->ProcessEx('errors.tpl')) {
            header("Location: ".$design->LINK_START."module=newaccounts&action=bill_list");
            exit();
        }
    }
    function newaccounts_bill_balance($fixclient){
        global $design,$db,$user,$fixclient_data;
        $client_id=$fixclient_data['id'];
        ClientAccount::dao()->updateBalance($client_id);
        if ($design->ProcessEx('errors.tpl')) {
            header("Location: ".$design->LINK_START."module=newaccounts&action=bill_list");
            exit();
        }
    }
    function newaccounts_bill_balance_mass($fixclient){
        global $design,$db,$user,$fixclient;
        $design->ProcessEx('errors.tpl');
        $R=$db->AllRecords("select c.id, c.client, c.currency from clients c where status not in ( 'closed', 'trash', 'once', 'tech_deny', 'double', 'deny') ");
        set_time_limit(0);
        session_write_close();

        while (ob_get_level() > 0)
            ob_end_clean();


        foreach ($R as $r) {
            echo date("d-m-Y H:i:s").": ".$r['client'];
            try{
                ClientAccount::dao()->updateBalance($r['id']);
            }catch(Exception $e)
            {
                echo "<h1>!!! ".$e->getMessage()."</h1>";
            }
            echo "<br>\n";flush();
        }
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
            "service" => array("USD" => 0, "RUB" => 0),
            "zalog"   => array("USD" => 0, "RUB" => 0),
            "zadatok" => array("USD" => 0, "RUB" => 0),
            "good"    => array("USD" => 0, "RUB" => 0)

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

        $sum_l["payments"] = $db->GetValue("select sum(p.sum) from newpayments p where p.client_id ='".$fixclient_data["id"]."'");

        $design->assign("sum_l", $sum_l);


        
        try{
            $billingCounter = ClientCS::getBillingCounters($fixclient_data["id"]);
        }catch(Exception $e)
        {
            trigger_error2($e->getMessage());
        }

        $design->assign("counters", $billingCounter);
        $design->assign("subscr_counter", ClientCounter::dao()->getOrCreateCounter($fixclient_data["id"]));

        $design->assign(
            'notLinkedtransactions',
            Transaction::find()
                ->andWhere(['client_account_id' => $fixclient_data["id"], 'source' => 'stat', 'deleted' => 0])
                ->andWhere('bill_id is null')
                ->all()
        );



        if($user->Flag('balance_simple')){
            return $this->newaccounts_bill_list_simple($get_sum);
        }else{
            return $this->newaccounts_bill_list_full($get_sum);
        }
    }

    function _getSwitchTelekomDate($clientId)
    {
        $res = \app\models\HistoryChanges::find()
            ->orWhere(['like', 'data_json', '"organization_id":"' . Organization::MCN_TELEKOM . '"'])
            ->orWhere(['like', 'data_json', '"organization_id":"' . Organization::MCM_TELEKOM . '"'])
            ->one();
        return $res ? date('Y-m-d', strtotime($res->created_at)) : '0000-00-00';
    }

    function newaccounts_bill_list_simple($get_sum=false){
        global $design, $db, $user, $fixclient, $fixclient_data;

        $isMulty = ClientAccount::findOne($fixclient)->contract->contract_type_id == \app\models\ClientContract::CONTRACT_TYPE_MULTY;
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

        $params = array(
            "client_id" => $fixclient_data["id"],
            "client_currency" => $fixclient_data["currency"],
            "is_multy" => $isMulty,
            "is_view_canceled" => $isViewCanceled,
            "get_sum" => $get_sum
            );

        $R = BalanceSimple::get($params);

        if($get_sum)
            return $R;

        list($R, $sum, $sw) = $R;

        ksort($sw);

        $stDates = $this->_getSwitchTelekomDate($fixclient_data["id"]);

        if($stDates)
        {
            foreach($stDates as $stDate => $stFirma)
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
                {
                    $organization = Organization::findOne(["firma" => $stFirma]);
                    $R[$ks]["switch_to_mcn"] = $organization->name;
                }
            }
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

    function newaccounts_show_income_goods()
    {
	global $design;
	$_SESSION['get_income_goods_on_bill_list'] = get_param_raw('show', 'N') == "Y";
	header("Location: ".$design->LINK_START."module=newaccounts&action=bill_list");
    exit();
    }
    function newaccounts_bill_list_full($get_sum=false)
    {
        global $design, $db, $user, $fixclient, $fixclient_data;

        $isMulty = ClientAccount::findOne($fixclient)->contract->contract_type_id == \app\models\ClientContract::CONTRACT_TYPE_MULTY;
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
            'RUB'=>array(
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

        $get_income_goods_on_bill_list = get_param_integer('get_income_goods_on_bill_list', false);
        $design->assign('get_income_goods_on_bill_list', $get_income_goods_on_bill_list);

        $R1 = $db->AllRecords($q='
                select * from (
            select
                bill_no, bill_no_ext, bill_date, client_id, currency, sum, is_payed, P.comment, postreg, nal,
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
                ) bills  ' . 
                (($get_income_goods_on_bill_list) ? 'union
                (
                    ### incomegoods
                 SELECT 
                    number as bill_no, 
                    "" as bill_no_ext,
                    cast(date as date) as bill_date, 
                    client_card_id as client_id, 
                    if(currency = "RUB", "RUB", currency) as currency,
                    sum, 
                    is_payed,
                    "" `comment`, 
                    "0000-00-00" postreg , 
                    "" nal, 
                    1 in_sum 

                  FROM `g_income_order` where client_card_id = "'.$fixclient_data['id'].'"
                )' : ' ' ) . 
            'order by
                bill_date desc,
                bill_no desc
            limit 1000
        ','',MYSQL_ASSOC);

        if (isset($sum[$fixclient_data['currency']]['saldo']) && $sum[$fixclient_data['currency']]['saldo'] > 0){
            array_unshift($R1, Array
                                (
                                    'bill_no' => 'saldo',
                                    'bill_date' => $sum[$fixclient_data['currency']]['ts'],
                                    'client_id' => $fixclient_data['id'],
                                    'currency' => $fixclient_data['currency'],
                                    'sum' => $sum[$fixclient_data['currency']]['saldo'],
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
                P.id, P.client_id, P.payment_no, P.payment_date, P.oper_date, P.type, P.sum, P.comment, P.add_date, P.add_user, P.p_bill_no, P.p_bill_vis_no,
                U.user as user_name,
                '.(
                    $sum[$fixclient_data['currency']]['ts']
                        ?    'IF(P.payment_date>="'.$sum[$fixclient_data['currency']]['ts'].'",1,0)'
                        :    '1'
                ).' as in_sum,
                P.payment_id,
                P.bill_no,
                P.sum_pay,
                P.bank
            from (    SELECT P.id, P.client_id, P.payment_no, P.payment_date, P.oper_date, P.type, P.sum, P.comment, P.add_date, P.add_user, P.bill_no as p_bill_no, P.bill_vis_no as p_bill_vis_no,
                        L.payment_id, L.bill_no, L.sum as sum_pay, P.bank
                    FROM newpayments P LEFT JOIN newpayments_orders L ON L.client_id='.$fixclient_data['id'].' and P.id=L.payment_id
                    WHERE P.client_id='.$fixclient_data['id'].'
                    UNION
                    SELECT P.id, P.client_id, P.payment_no, P.payment_date, P.oper_date, P.type, P.sum, P.comment, P.add_date, P.add_user, P.bill_no as p_bill_no, P.bill_vis_no as p_bill_vis_no,
                        L.payment_id, L.bill_no, L.sum as sum_pay, P.bank
                    FROM newpayments P RIGHT JOIN newpayments_orders L ON P.client_id='.$fixclient_data['id'].' and P.id=L.payment_id
                    WHERE L.client_id='.$fixclient_data['id'].'
                ) as P

            LEFT JOIN user_users as U on U.id=P.add_user

            order by
                P.payment_date desc
            limit 1000
                ',
        '',MYSQL_ASSOC);
        $R=array();

        $bill_total_add = array('p'=>0,'n'=>0);
        foreach($R1 as $k=>$r){
            if ($r['sum'] > 0) $bill_total_add['p']+=$r['sum'];
            if ($r['sum'] < 0) $bill_total_add['n']+=$r['sum'];
            $v=array(
                'bill'=>$r,
                'date'=>$r['bill_date'],
                'pays'=>array(),
                'delta'=>-$r['sum'],
                'delta2'=>-$r['sum']
            );

            foreach($R2 as $k2=>$r2){

                if (strpos($r2['payment_id'], '-'))
                {
			$r2['currency'] = 'RUB';
			$r2['id'] = $r2['payment_id'];
			$R2[$k2]['currency'] = 'RUB';
			$R2[$k2]['id'] = $r2['payment_id'];
                }
                if($r['bill_no'] == $r2['p_bill_no']){
                    if (!isset($v['pays'][$r2['id']])){
                        $v['delta']+=$r2['sum'];
                        $v['pays'][$r2['id']]=$r2;
                    }
                }
            }

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

        $stDates = $this->_getSwitchTelekomDate($fixclient_data["id"]);

        if($stDates)
        {
            foreach($stDates as $stDate => $stFirma)
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
                {
                    $organization = Organization::findOne(["firma" => $stFirma]);
                    $R[$ks]["switch_to_mcn"] = $organization->name;
                }
            }
        }

        $qrs = array();
        $qrsDate = array();
        foreach($db->QuerySelectAll("qr_code", array("client_id" => $fixclient_data["id"])) as $q)
        {
            $qrs[$q["bill_no"]][$q["doc_type"]] = $q["id"];
            $qrsDate[$q["bill_no"]][$q["doc_type"]] = $q["date"];
        }

        $design->assign("qrs", $qrs);
        $design->assign("qrs_date", $qrsDate);
        $bill_total_add['t'] = $bill_total_add['n']+$bill_total_add['p'];
        $design->assign('bill_total_add',$bill_total_add);
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

        //$design->assign("fixclient_data", $fixclient_data);
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

        if ($design->ProcessEx('errors.tpl')) {
            header("Location: ".$design->LINK_START."module=newaccounts&action=bill_view&bill=".$no); 
            exit();
            }
    }

    function newaccounts_bill_view($fixclient){
        global $design, $db, $user, $fixclient_data;

        
        //old all4net bills
        if(isset($_POST['bill_no']) && preg_match('/^\d{6}-\d{4}-\d+$/',$_POST['bill_no'])){

            //set doers
            if(isset($_POST['select_doer'])){
                $d = (int)$_POST['doer'];
                $bill = \app\models\Bill::findOne(['bill_no' => $_POST['bill_no']]);
                if ($bill) {
                    $bill->courier_id = $d;
                    $bill->save();
                }
            }
            // 1c || all4net bills
		}elseif(isset($_GET['bill']) && preg_match('/^(\d{6}\/\d{4}|\d{6,7})$/',$_GET['bill'])){
			$design->assign('1c_bill_flag',true);
			if(isset($_POST['select_doer'])){
				$d = (int)$_POST['doer'];
                $bill = \app\models\Bill::findOne(['bill_no' => $_POST['bill_no']]);
                if ($bill) {
                    $bill->courier_id = $d;
                    $bill->save();
                }
			}

       //income orders
	   //}elseif(isset($_GET["bill"]) && preg_match("/\w{8}-\w{4}-\w{4}-\w{4}-\w{12}/", $_GET["bill"])){ // incoming orders
       }elseif(isset($_GET["bill"]) && preg_match("/\d{2}-\d{8}/", $_GET["bill"])){ // incoming orders

           //find last order
           $order = GoodsIncomeOrder::first(array(
                       "conditions" => array("number" => $_GET["bill"]),
                       "order" => "date desc",
                       "limit" => 1
                       )
                   );

           if (!$order)
               die("Неизвестный тип документа");

            header("Location: ./?module=incomegoods&action=order_view&id=".urlencode($order->id));
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
        $newbill = \app\models\Bill::findOne(['bill_no'=>$bill_no]);
        if(get_param_raw('err')==1)
            trigger_error2('Невозможно добавить строки из-за несовпадния валют');
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

        $design->assign('bill',$bill->GetBill());
        $design->assign('bill_manager',getUserName(\app\models\Bill::dao()->getManager($bill->GetNo())));
        $design->assign('bill_comment',$bill->GetStaticComment());
        $design->assign('bill_courier',$bill->GetCourier());
        $design->assign('bill_lines',$L = $bill->GetLines());
        $design->assign('bill_bonus',$this->getBillBonus($bill->GetNo()));

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

        list($bill_akts, $bill_invoices, $bill_upd) = $this->get_bill_docs($bill, $L);

        $design->assign('bill_akts', $bill_akts);

        $design->assign('bill_invoices', $bill_invoices);

        $design->assign('bill_upd', $bill_upd);

        $design->assign('template_bills',
            $db->AllRecords('
                SELECT *
                FROM newbills
                WHERE client_id=2818
                    AND currency="'.$bill->Get('currency').'"
                ORDER BY bill_no
            ')
        );

        $r = $bill->Client();
        ClientCS::Fetch($r);

        if ($r) {
            $r["client_orig"] = $r["client"];
            $contractTypeId = ClientAccount::findOne($r['id'])->contract->contract_type_id;
            if (access("clients", "read_multy"))
                if ($contractTypeId != \app\models\ClientContract::CONTRACT_TYPE_MULTY) {
                    trigger_error2('Доступ к клиенту ограничен');
                    return;
                }

            if ($contractTypeId == \app\models\ClientContract::CONTRACT_TYPE_MULTY && isset($_GET["bill"])) {
                $ai = $db->GetRow("select fio from newbills_add_info where bill_no = '" . $_GET["bill"] . "'");
                if ($ai) {
                    $r["client"] = $ai["fio"] . " (" . $r["client"] . ")";
                }
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

        $availableDocuments = DocumentReportFactory::me()->availableDocuments($newbill);
        $documents = [];
        foreach ($availableDocuments as $document) {
            $documents[] = [
                'class' => $document->getDocType(),
                'title' => $document->getName(),
            ];
        }
        $design->assign('available_documents', $documents);

        $design->AddMain('newaccounts/bill_view.tpl');

        $tt = $db->GetRow("SELECT * FROM tt_troubles WHERE bill_no='".$bill_no."'");
        if($tt){
            StatModule::tt()->dont_filters = true;
            StatModule::tt()->cur_trouble_id = $tt['id'];
            StatModule::tt()->tt_view($fixclient);
            StatModule::tt()->dont_again = true;
        }
    }

    function get_bill_docs(Bill &$bill, $L = null)
    {
        $bill_akts = $bill_invoices = $bill_upd = array();

        if (($doctypes = BillDocument::dao()->getByBillNo($bill->GetNo())) == false) {
            $doctypes = BillDocument::dao()->updateByBillNo($bill->GetNo(), $L, true);
        }

        if ($doctypes && count($doctypes) > 0) {
            for ($i=1;$i<=3;$i++) $bill_akts[$i] = $doctypes['a'.$i];
            for ($i=1;$i<=7;$i++) $bill_invoices[$i] = $doctypes['i'.$i];
            for ($i=1;$i<=2;$i++) $bill_upd[$i] = $doctypes['ia'.$i];
        }

        return array($bill_akts, $bill_invoices, $bill_upd);
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

        $bill_no=$_POST['bill_no'];
        $bill = \app\models\Bill::findOne(['bill_no' => $bill_no]);

        $bill->is_approved = $bill->is_approved ? 0 : 1;
        $bill->sum = $bill->is_approved ? $bill->sum_with_unapproved : 0;
        $bill->save();
        $bill->dao()->recalcBill($bill);
        ClientAccount::dao()->updateBalance($bill->client_id);

        header('Location: index.php?module=newaccounts&action=bill_view&bill='.$bill_no);
        exit();
    }

    function newaccounts_bill_edit($fixclient){
        global $design,$db,$user,$fixclient_data;
        $bill_no = get_param_protected("bill");
        if(!$bill_no)
            return;

        if(!preg_match("/20[0-9]{4}-[0-9]{4}/i", $bill_no)) {
            header("Location: ./?module=newaccounts&action=make_1c_bill&bill_no=".$bill_no);
            exit();
        }


        $bill = new Bill($bill_no);
        if($bill->IsClosed()){
            header("Location: ./?module=newaccounts&action=bill_view&bill=".$bill_no);
            exit();
        }
        $_SESSION['clients_client'] = $bill->Get("client_id");
        $fixclient_data = ClientAccount::findOne($bill->Get("client_id"));
        if(!$bill->CheckForAdmin())
            return;

        $design->assign('show_bill_no_ext', in_array($fixclient_data['status'], array('distr', 'operator')));
        $design->assign('bills_list',$db->AllRecords("select `bill_no`,`bill_date` from `newbills` where `client_id`=".$fixclient_data['id']." order by `bill_date` desc",null,MYSQL_ASSOC));
        $design->assign('bill',$bill->GetBill());
        $design->assign('bill_date', date('d-m-Y', $bill->GetTs()));
        $design->assign('l_couriers',Courier::dao()->getList(true));
        $lines = $bill->GetLines();
        $lines[$bill->GetMaxSort()+1] = array();
        $lines[$bill->GetMaxSort()+2] = array();
        $lines[$bill->GetMaxSort()+3] = array();
        $design->assign('bill_lines',$lines);
        $design->AddMain('newaccounts/bill_edit.tpl');
    }

    function newaccounts_bill_comment($fixclient) {
        $billNo = get_param_protected("bill");

        $bill = \app\models\Bill::findOne(['bill_no' => $billNo]);
        $bill->comment = get_param_raw("comment");
        $bill->save();

        header("Location: /?module=newaccounts&action=bill_view&bill=" . $billNo);
        exit();
    }

    function newaccounts_bill_nal($fixclient) {
        $billNo = get_param_protected("bill");

        $bill = \app\models\Bill::findOne(['bill_no' => $billNo]);
        $bill->nal = get_param_raw("nal");
        $bill->save();

        header("Location: /?module=newaccounts&action=bill_view&bill=" . $billNo);
        exit();
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
            $bill = \app\models\Bill::findOne(['bill_no' => $bill_no]);
            if ($bill) {
                $bill->postreg = $option?'':date('Y-m-d');
                $bill->save();
            }
        }
        if ($design->ProcessEx('errors.tpl')) {
            header("Location: ".$design->LINK_START."module=newaccounts&action=bill_view&bill=".$bill_no);
            exit();
        }
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
        $bill_nal = get_param_raw("nal");
        $billCourier = get_param_raw("courier");
        $bill_no_ext = get_param_raw("bill_no_ext");
        $date_from_active = get_param_raw("date_from_active", 'N');
        $price_include_vat = get_param_raw("price_include_vat", 'N');
        $dateFrom = new DatePickerValues('bill_no_ext_date', 'today');
        $bill_no_ext_date = $dateFrom->getSqlDay();
        
        $bill = new Bill($bill_no);
        if(!$bill->CheckForAdmin())
            return;
        $bill_date = new DatePickerValues('bill_date', $bill->Get('bill_date'));
        $bill->Set('bill_date',$bill_date->getSqlDay());
        $bill->SetCourier($billCourier);
        $bill->SetNal($bill_nal);
        $bill->SetExtNo($bill_no_ext);

        if ($date_from_active == 'Y') {
		    $bill->SetExtNoDate($bill_no_ext_date);
        } else {
            $bill_data = $bill->GetBill();
            if ($bill_data['bill_no_ext_date'] > 0) {
                $bill->SetExtNoDate();
            }
        }

        $bill->SetPriceIncludeVat($price_include_vat == 'Y' ? 1 : 0);

        $lines = $bill->GetLines();
        $lines[$bill->GetMaxSort()+1] = array();
        $lines[$bill->GetMaxSort()+2] = array();
        $lines[$bill->GetMaxSort()+3] = array();
        foreach($lines as $k=>$arr_v){
            if(((!isset($item[$k]) || (isset($item[$k]) && !$item[$k])) && isset($arr_v['item'])) || isset($del[$k])){
                $bill->RemoveLine($k);
            }elseif(isset($item[$k]) && $item[$k] && isset($arr_v['item'])){
                $bill->EditLine($k,$item[$k],$amount[$k],$price[$k],$type[$k]);
            }elseif(isset($item[$k]) && $item[$k]){
                $bill->AddLine($item[$k],$amount[$k],$price[$k],$type[$k],'','','','');
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
        ClientAccount::dao()->updateBalance($bill->Client('id'));
        unset($bill);
        if ($design->ProcessEx('errors.tpl')) {
            header("Location: ?module=newaccounts&action=bill_view&bill=".$bill_no);
            exit();
        }
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
                ),'RUB'=>array(
                    'avans' =>            array("Аванс за подключение интернет-канала",1,500,'zadatok'),
                    'deposit' =>        array("Задаток за подключение интернет-канала",1,SUM_ADVANCE*27,'zadatok'),
                    'deposit_back' =>    array("Возврат задатка за подключение интернет-канала",1,-SUM_ADVANCE*27,'zadatok'),
                    'deposit_sub' =>    array("За вычетом ранее оплаченного задатка",1,-SUM_ADVANCE*27,'zadatok'),
                ));
        $err=0;
        if ($obj=='connecting' || $obj=='connecting_ab') {
            $clientAccount = ClientAccount::findOne($fixclient_data['id']);

            if ($clientAccount->price_include_vat == $bill->Get('price_include_vat')) {

                $periodicalDate = new DateTime(\app\classes\Utils::dateBeginOfMonth($bill->Get('bill_date')), $clientAccount->timezone);

                $connectingTransactions =
                    ClientAccountBiller::create($clientAccount, $periodicalDate, $onlyConnecting = true, $connecting = $obj == 'connecting', $periodical = true, $resource = false)
                        ->createTransactions()
                        ->getTransactions();

                $connectingServices = [];

                foreach ($connectingTransactions as $transaction) {
                    $year = substr($transaction->billing_period, 0, 4);
                    $month = substr($transaction->billing_period, 5, 2);

                    $period_from = $year . '-' . $month . '-01';
                    $period_to = $year . '-' . $month . '-' . cal_days_in_month(CAL_GREGORIAN, $month, $year);

                    $bill->AddLine($transaction->name, $transaction->amount, $transaction->price, 'service', $transaction->service_type, $transaction->service_id, $period_from, $period_to);
                    $connectingServices[] = ['type' => $transaction->service_type, 'id' => $transaction->service_id];
                }

                $b = \app\models\Bill::findOne(['bill_no' => $bill->GetNo()]);
                $b->dao()->recalcBill($b);
                BillDocument::dao()->updateByBillNo($bill->GetNo());

                foreach ($connectingServices as $connectingService) {
                    $db->Query("update " . $connectingService['type'] . " set status='working' where id='" . $connectingService['id'] . "'");
                }

            } else {
                trigger_error2('Параметр "Цена включает НДС" счета отличается от лицевого счета');
            }

        } elseif ($obj=='regular') {
            $clientAccount = ClientAccount::findOne($fixclient_data['id']);

            if ($clientAccount->price_include_vat == $bill->Get('price_include_vat')) {

                $periodicalDate = new DateTime(\app\classes\Utils::dateBeginOfMonth($bill->Get('bill_date')), $clientAccount->timezone);
                $resourceDate = new DateTime(\app\classes\Utils::dateEndOfPreviousMonth($bill->Get('bill_date')), $clientAccount->timezone);

                $periodicalTransactions =
                    ClientAccountBiller::create($clientAccount, $periodicalDate, $onlyConnecting = false, $connecting = false, $periodical = true, $resource = false)
                        ->createTransactions()
                        ->getTransactions();

                $resourceTransactions =
                    ClientAccountBiller::create($clientAccount, $resourceDate, $onlyConnecting = false, $connecting = false, $periodical = false, $resource = true)
                        ->createTransactions()
                        ->getTransactions();


                foreach ($periodicalTransactions as $transaction) {
                    $year = substr($transaction->billing_period, 0, 4);
                    $month = substr($transaction->billing_period, 5, 2);

                    $period_from = $year . '-' . $month . '-01';
                    $period_to = $year . '-' . $month . '-' . cal_days_in_month(CAL_GREGORIAN, $month, $year);

                    $bill->AddLine($transaction->name, $transaction->amount, $transaction->price, 'service', $transaction->service_type, $transaction->service_id, $period_from, $period_to);
                }

                foreach ($resourceTransactions as $transaction) {
                    $year = substr($transaction->billing_period, 0, 4);
                    $month = substr($transaction->billing_period, 5, 2);

                    $period_from = $year . '-' . $month . '-01';
                    $period_to = $year . '-' . $month . '-' . cal_days_in_month(CAL_GREGORIAN, $month, $year);

                    $bill->AddLine($transaction->name, $transaction->amount, $transaction->price, 'service', $transaction->service_type, $transaction->service_id, $period_from, $period_to);
                }

                $b = \app\models\Bill::findOne(['bill_no' => $bill->GetNo()]);
                $b->dao()->recalcBill($b);

            } else {
                trigger_error2('Параметр "Цена включает НДС" счета отличается от лицевого счета');
            }

        } elseif ($obj=='template') {
            $tbill=get_param_protected("tbill");
            foreach ($db->AllRecords('select * from newbill_lines where bill_no="'.$tbill.'" order by sort') as $r) {
                $bill->AddLine($r['item'],$r['amount'],$r['price'],$r['type']);
            }
        } elseif (isset($L[$bill->Get('currency')][$obj])) {
            $D=$L[$bill->Get('currency')][$obj];
            if (!is_array($D[0])) $D=array($D);
            foreach ($D as $d) $bill->AddLine($d[0],$d[1],$d[2],$d[3]);
        }
        $bill->Save();
        $client=$bill->Client('client');
        ClientAccount::dao()->updateBalance($bill->Client('id'));
        unset($bill);

        if (!$err && $design->ProcessEx('errors.tpl')) {
            header("Location: ".$design->LINK_START."module=newaccounts&action=bill_view&err=".$err."&bill=".$bill_no);
            exit();
        } else return $this->newaccounts_bill_list($client);
    }
    function newaccounts_bill_mass($fixclient) {
        global $design,$db;
        set_time_limit(0);
        session_write_close();
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        $obj=get_param_raw('obj');
        if ($obj=='create') {
            echo "Запущено выставление счетов<br/>"; flush();

            $partSize = 500;
            $date = new DateTime();
            //$date->modify('+1 month');
            $totalCount = 0;
            $totalAmount = 0;
            $totalErrorsCount = 0;
            try {
                $count = $partSize;
                $offset = 0;
                while ($count >= $partSize) {
                    $clientAccounts =
                        ClientAccount::find()
                            ->andWhere('status NOT IN ("closed","deny","tech_deny", "trash", "once")')
                            ->limit($partSize)->offset($offset)
                            ->orderBy('id')
                            ->all();

                    foreach ($clientAccounts as $clientAccount) {
                        $offset++;
                        echo "$offset. Лицевой счет: <a target='_blank' href='/client/view?id={$clientAccount->id}'>{$clientAccount->id}</a>"; flush();

                        try {

                            $bill =
                                \app\classes\bill\BillFactory::create($clientAccount, $date)
                                    ->process();

                            if ($bill) {
                                $totalCount++;
                                $totalAmount = $totalAmount + $bill->sum;
                                echo ", создан счет: <a target='_blank' href='/?module=newaccounts&action=bill_view&bill={$bill->bill_no}'>{$bill->bill_no}</a> на сумму {$bill->sum}<br/>\n"; flush();
                            } else {
                                echo "<br/>\n"; flush();
                            }

                        } catch (\Exception $e) {
                            echo "<b>ОШИБКА</b><br/>\n"; flush();
                            Yii::error($e);
                            $totalErrorsCount++;
                        }
                    }

                    $count = count($clientAccounts);
                }


            } catch (\Exception $e) {
                echo "<b>ОШИБКА Выставления счетов</b>\n"; flush();
                Yii::error($e);
                exit;
            }

            echo "Закончено выставление счетов<br/>";
            echo "<b>Всего создано {$totalCount} счетов на сумму {$totalAmount}</b><br/>";
            if ($totalErrorsCount) {
                echo "<b>Всего {$totalErrorsCount} ошибок!</b><br/>";
            }
            exit;
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
                    if (($doctypes = BillDocument::dao()->getByBillNo($bill->GetNo())) == false) {
                        $doctypes = BillDocument::dao()->updateByBillNo($bill->GetNo(), $L, true);
                    }
                    $p1 = $doctypes['i1'];
                    $p2 = $doctypes['i2'];
                    $p3 = $doctypes['i3'];

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

        trigger_error2("Опубликованно счетов: ".mysql_affected_rows());

        return;
    }

    function newaccounts_bill_email($fixclient) {
        global $design,$db,$_GET;
        $this->do_include();
        $bill_no=get_param_protected("bill"); if (!$bill_no) return;
        $bill = new Bill($bill_no);
        $is_pdf = get_param_raw('is_pdf', 0);

        $template = 'Уважаемые господа!<br>Отправляем Вам следующие документы:<br>';
        $template = array($template,$template);
        $D = array(
                    'Конверт: '=>array('envelope'),
                    'Счет: '=>array('bill-1-RUB','bill-2-RUB'),
                    'Счет-фактура: '=>array('invoice-1','invoice-2','invoice-3','invoice-4'),
                    'Акт: '=>array('akt-1','akt-2','akt-3'),
                    'Накладная: '=>array('lading'),
                    'Приказ о назначении: ' => array("order"),
                    'Уведомление о назначении: ' => array("notice"),
                    'УПД: ' => array('upd-1', 'upd-2', 'upd-3'),
                    'Соглашение о передачи прав: ' => array('sogl_mcm_telekom')
        );

        foreach ($D as $k=>$rs) {
            foreach ($rs as $r) {
                if (get_param_protected($r)) {

                    if ($r == "sogl_mcm_telekom")
                    {
                        $is_pdf = 1;
                    }

                    $R = array('bill'=>$bill_no,'object'=>$r,'client'=>$bill->Get('client_id'), 'is_pdf' => $is_pdf);
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


                    $R['emailed'] = '1';
                    $link[] = Yii::$app->params['LK_PATH'].'docs/?bill='.udata_encode_arr($R);
                    $R['emailed'] = '0';
                    $link[] = Yii::$app->params['LK_PATH'].'docs/?bill='.udata_encode_arr($R);
                    foreach ($template as $tk=>$tv) $template[$tk].=$k.'<a href="'.$link[$tk].'">'.$link[$tk].'</a><br>';
                }
            }
        }

        $documentReports = get_param_raw('document_reports', []);
        $document_link = [];
        for ($i=0, $s=sizeof($documentReports); $i<$s; $i++) {
            $link_params = [
                'bill'      => $bill_no,
                'client'    => $bill->Get('client_id'),
                'doc_type'  => $documentReports[$i],
                'is_pdf'    => $is_pdf,
            ];

            $document_link[] = Yii::$app->params['LK_PATH'] . 'docs/?bill=' . udata_encode_arr($link_params + ['emailed' => 1]);
            $document_link[] = Yii::$app->params['LK_PATH'] . 'docs/?bill=' . udata_encode_arr($link_params + ['emailed' => 0]);

            foreach ($template as $pos => &$item) {
                switch ($documentReports[$i]) {
                    case 'bill':
                        $item .= 'Счет: ';
                        break;
                }
                $item .= '<a href="' . $document_link[$pos] . '">' . $document_link[$pos] . '</a>';
            }
        }

        $design->ProcessEx();

        $c= ClientAccount::findOne($bill->Client('id'));
        $contact = $c->officialContact;
        $this->_bill_email_ShowMessageForm('с печатью',$contact['email'],"Счет за телекоммуникационные услуги",$template[0]);
        $this->_bill_email_ShowMessageForm('без печати',$contact['email'],"Счет за телекоммуникационные услуги",$template[1]);
        echo "<hr><br>Шаблон с печатью <br><br>";
        echo $template[0];
        echo "<br><hr><br>\n\n Шаблон без печати <br><br>";
        echo $template[1];
        $design->ProcessEx('errors.tpl');
    }

    function _bill_email_ShowMessageForm($submit,$to,$subject,$msg) {
        global $design,$user;

        // Исключения для пользователей, у которые отправляет почту из стата не с ящика по умолчанию
        $_SPECIAL_USERS = array(
                "istomina" => 191 /* help@mcn.ru */
                );
        $_DEFAULT_MAIL_TRUNK_ID = 5; /* info@mcn.ru */
               


        $design->assign('subject',$subject);
        $design->assign('new_msg',$msg);
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
        $design->assign('to',$s);
        $design->assign('submit',$submit);
        $design->ProcessEx('comcenter_msg.tpl');
    }


    function newaccounts_bill_mprint($fixclient) {
        global $design,$db,$user;
        $this->do_include();
        $bills=get_param_raw("bill",array()); if (!$bills) return;
        if(!is_array($bills)) $bills = array($bills);

        $is_pdf = get_param_raw("is_pdf", 0);
        $one_pdf = get_param_raw("one_pdf", 0);
        $R = array();
        $P = '';

        $isFromImport = get_param_raw("from", "") == "import";
        $isToPrint = true;//get_param_raw("to_print", "") == "true";
        $stamp = get_param_raw("stamp", "");

        $documentReports = get_param_raw('document_reports', array());

        $L = array('envelope','bill-1-RUB','bill-2-RUB','lading','lading','gds','gds-2','gds-serial');
        $L = array_merge($L, array('invoice-1','invoice-2','invoice-3','invoice-4','invoice-5','akt-1','akt-2','akt-3','upd-1', 'upd-2', 'upd-3'));
        $L = array_merge($L, array('akt-1','akt-2','akt-3', 'order','notice', 'upd-1', 'upd-2', 'upd-3'));
        $L = array_merge($L, array('nbn_deliv','nbn_modem','nbn_gds', 'sogl_mcm_telekom'));

        //$L = array("invoice-1");

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
                $isUPD = get_param_raw("upd-1", "") == "1";
                $isAktImport = get_param_raw("akt-1", "") == "1";

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
                {
                    $reCode = $r;
                }

                if($r == "akt-2" && $isFromImport && $isAkt2 && !$isSF && !$isUPD && $isAktImport)
                {
                    $reCode = $r;
                }

                $isDeny = false;
                if($r == "akt-1" && $isFromImport && !$isAkt1 && !$isSF)
                {
                    $isDeny = true;
                }

                if($r == "invoice-1" && $isFromImport && !$isAkt1 && $isSF)
                {
                    //$isDeny = true;
                }

                if($r == "upd-1" && $isFromImport && !$isAkt1)
                {
                    $isDeny = true;
                }

                if($r == "upd-2" && $isFromImport && !$isAkt2)
                {
                    $isDeny = true;
                }

                if ((get_param_protected($r) || $reCode) && !$isDeny) {

                    if($reCode)
                        $r = $reCode;

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

                    if ($isFromImport)
                        $r .= "&from=import";
                    
                    if ($isFromImport || $isToPrint)
                        $r .= "&to_print=true";

                    $ll = array(
                            "bill_no" => $bill_no, 
                            "obj" => $r, 
                            "bill_client" => $bill->Get("client_id"), 
                            "g" => get_param_protected($r), 
                            "r"  => $reCode
                            );

                    $R[] = $ll;
                    $P.=($P?',':'').'1';
                }
            }

            if (sizeof($documentReports)) {
                $idxs[$bill_no . '==bill'] = count($R);
                foreach ($documentReports as $documentReport) {
                    $R[] = [
                        'bill_no' => $bill_no,
                        'doc_type' => $documentReport,
                    ];
                    $P .= ($P ? ',' : '') . '1';
                }
            }

            unset($bill);
        }

        //printdbg($R);


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
        if ($one_pdf == '1') {
            $this->create_pdf_from_docs($fixclient, $R);
        }

        $design->assign('is_pdf',$is_pdf);
        $design->assign('rows',$P);
        $design->assign('objects',$R);
        $design->ProcessEx('newaccounts/print_bill_frames.tpl');
        #$design->ProcessEx('errors.tpl');
    }

    function create_pdf_from_docs($fixclient, $bills = array())
    {
        global $user, $db, $design;

        if (count($bills) == 0) return;

        $fnames = array();
        $fbasename = '/tmp/'.mktime().$user->_Data['id'];
        $i=0;
        $is_invoice = false;
        $is_upd = false;

        foreach ($bills as $b) {
            $fname = $fbasename . (++$i) . '.html';
            if ($b['obj'] == 'envelope') {
                if (($r = $db->GetRow('select * from clients where (id="'.$b['bill_client'].'") limit 1'))) {
                    ClientCS::Fetch($r,null);
                    $content = $design->fetch('../store/acts/envelope.tpl');
                }
            } else {
                if (($pos = strpos($b['obj'], '&to_client=true'))) {
                    $to_client = true;
                    $obj = substr($b['obj'], 0, $pos);
                } else {
                    $to_client = false;
                    $obj = $b['obj'];
                }
                if (strpos($obj, 'invoice')!==false) $is_invoice = true;
                if (strpos($obj, 'upd')!==false) $is_upd = true;
                $content = $this->newaccounts_bill_print($fixclient, array('object'=>$obj,'bill'=>$b['bill_no'], 'only_html'=>'1','to_client'=>$to_client, 'is_pdf'=>1));
            }
            if (strlen($content)) {
                file_put_contents($fname, $content);
                $fnames[] = $fname;
            }
        }

        $options = ' --quiet -L 10 -R 10 -T 10 -B 10';
        if ($is_invoice || $is_upd) $options .= ' --orientation Landscape ';
        passthru("/usr/bin/wkhtmltopdf $options ".implode(' ', $fnames)." $fbasename.pdf");
        $pdf = file_get_contents($fbasename . '.pdf');
        foreach ($fnames as $f) unlink($f);
        unlink($fbasename.'.pdf');

        header('Content-Type: application/pdf');
        ob_clean();
        flush();
        echo $pdf;
        exit;
        
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

        $bill->Save(0,0);
        
        $client = $bill->Client();
        ClientAccount::dao()->updateBalance($client['id']);

        if ($design->ProcessEx('errors.tpl')) {
            header("Location: ".$design->LINK_START."module=newaccounts&action=bill_view&bill=".$bill_no);
            exit();
        }
    }
    function newaccounts_line_delete($fixclient) {
        global $design,$db;
        $bill_no=get_param_protected("bill"); if (!$bill_no) return;
        $bill = new Bill($bill_no);
        if (!$bill->CheckForAdmin()) return;
        $sort=get_param_integer("sort"); if (!$sort) return;
        $bill->RemoveLine($sort);
        if ($design->ProcessEx('errors.tpl')) {
            header("Location: ".$design->LINK_START."module=newaccounts&action=bill_view&bill=".$bill_no);
            exit();
        }
    }
    function newaccounts_bill_delete($fixclient) {
        global $design,$db;
        $bill_no=get_param_protected("bill"); if (!$bill_no) return;

        /** @var \app\models\Bill $bill */
        $bill = \app\models\Bill::find()->andWhere(['bill_no' => $bill_no])->one();

        if ($bill->isClosed()) {
            header("Location: ./?module=newaccounts&action=bill_view&bill=" . $bill_no);
            exit();
        }

        $clientAccountId = $bill->client_id;
        $bill->delete();

        ClientAccount::dao()->updateBalance($clientAccountId);

        if ($design->ProcessEx('errors.tpl')) {
            header("Location: ".$design->LINK_START."module=newaccounts&action=bill_list");
            exit();
        }
    }

    //эта функция готовит счёт к печати. ФОРМИРОВАНИЕ СЧЁТА
    function newaccounts_bill_print($fixclient, $params = array()){
        global $design,$db,$user;
        $this->do_include();

        $object = (isset($params['object'])) ? $params['object'] : get_param_protected('object');

        $mode = get_param_protected('mode', 'html');

        $is_pdf = (isset($params['is_pdf'])) ? $params['is_pdf'] : get_param_raw('is_pdf', 0);
        $is_word = get_param_raw('is_word', false);

        $design->assign("is_pdf", $is_pdf);

        $isToPrint = (isset($params['to_print'])) ? (bool)$params['to_print'] : get_param_raw('to_print', 'false') == 'true';
        $design->assign("to_print", $isToPrint);

        $only_html = (isset($params['only_html'])) ? $params['only_html'] : get_param_raw('only_html', 0);

        $bill_no = (isset($params['bill'])) ? $params['bill'] : get_param_protected("bill");
        if(!$bill_no)
            return;


        self::$object = $object;
        if ($object) {
            list($obj,$source,$curr) = explode('-',$object.'---');
        } else {
            $obj=get_param_protected("obj");
            $source = get_param_integer('source',1);
            $curr = get_param_raw('curr','RUB');
        }

        if($obj == "receipt")
        {
            $this->_print_receipt();
            exit();
        } elseif ($obj == "sogl_mcm_telekom")
        {
            $bill = app\models\Bill::findOne(['bill_no' => $bill_no]);

            if ($bill)
            {
                $report = DocumentReportFactory::me()->getReport($bill, $obj);
                if ($is_pdf)
                {
                    echo $report->renderAsPDF();
                } else {
                    echo $report->render();
                }
                exit();
            }
        }



        $bill = new Bill($bill_no);
        $bb = $bill->GetBill();

        $design->assign('without_date_date', $bill->getShipmentDate());

        $to_client = (isset($params['to_client'])) ? $params['to_client'] : get_param_raw("to_client", "false");
        $design->assign("to_client", $to_client);
        $design->assign("stamp", $this->get_import1_name($bill, get_param_raw("stamp", "false")));

        if(get_param_raw("emailed", "0") != "0")
            $design->assign("emailed", get_param_raw("emailed", "0"));


        if(in_array($obj,array('nbn_deliv', 'nbn_modem','nbn_gds'))){
            $this->do_print_prepare($bill,'bill',1,'RUB');
            $design->assign('cli',$cli=$db->GetRow("select * from newbills_add_info where bill_no='".$bill_no."'"));
            if(preg_match("/([0-9]{2})\.([0-9]{2})\.([0-9]{4})/i",$cli["passp_birthday"], $out))
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

        if (!in_array($obj, array('invoice', 'akt', 'upd', 'lading', 'gds', 'order', 'notice','new_director_info','envelope'))) {
            $obj = 'bill';
        }

        if ($obj!='bill')
            $curr = 'RUB';

        $cc = $bill->Client();

        if(in_array($obj, array("order","notice")))
        {
            $t = ($obj == "order" ?
                    "Приказ (Телеком) (Пыцкая)":
                    ($obj == "notice" ?
                        "Уведомление (Телеком)":""));
                        
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

        }

        if($obj == "new_director_info")
        {
            $this->docs_echoFile(STORE_PATH."new_director_info.pdf", "Смена директора.pdf");
            exit();
        }

        if($obj == "order")
        {
            $this->docs_echoFile(STORE_PATH."order2.pdf", "Смена директора МСН Телеком.pdf");
            exit();
        }

        if ($this->do_print_prepare($bill,$obj,$source,$curr) || in_array($obj, array("order","notice"))){

            $design->assign("bill_no_qr", ($bill->GetTs() >= strtotime("2013-05-01") ? BillQRCode::getNo($bill->GetNo()) : false));
            $design->assign("source", $source);

            if($source==3 && $obj=='akt')
            {
                if($mode=='html')
                    $design->ProcessEx('newaccounts/print_akt_num3.tpl');
            }else{
                if(in_array($obj, array('invoice','upd'))){

                    $design->assign("client_contract", BillContract::getString($bill->Client("id"), $bill->getTs()));

                    $id = $db->QueryInsert(
                        "log_newbills",
                        array(
                            'bill_no'=>$bill_no,
                            'ts'=>array('NOW()'),
                            'user_id'=>$user->Get('id'),
                            'comment'=>'Печать с/ф &#8470;'.$source
                        )
                    );
                    
                    if ($obj == "upd")
                    {
                        $design->assign("print_upd", printUPD::getInfo(count($design->_tpl_vars["bill_lines"])));
                    }

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
                
                if ($only_html == '1') {
                    return $design->fetch('newaccounts/print_'.$obj.'.tpl');
                }

                if ($is_word) {
                    $result = (new \app\classes\Html2Mhtml)
                        ->addContents(
                            'index.html',
                            $design->fetch('newaccounts/print_'.$obj.'.tpl')
                        )
                        ->addImages(function($image_src) {
                            $file_path = '';
                            $file_name = '';

                            if (preg_match('#\/[a-z]+(?![\.a-z]+)\?.+?#i', $image_src, $m)) {
                                $file_name = 'host_img_' . mt_rand(0, 50);
                                $file_path = Yii::$app->request->hostInfo . preg_replace('#^\.+#', '', $image_src);
                            }

                            return [$file_name, $file_path];
                        })
                        ->addMediaFiles(function($src) {
                            $file_name = 'host_media_' . mt_rand(0, 50);
                            $file_path = Yii::$app->request->hostInfo . preg_replace('#^\.+#', '', $src);

                            return [$file_name, $file_path];
                        })
                        ->getFile();

                    Yii::$app->response->sendContentAsFile($result, time() . Yii::$app->user->id . '.doc');
                    Yii::$app->end();
                    exit;
                }

                if ($is_pdf) {
                    /*wkhtmltopdf*/
                    $options = ' --quiet -L 10 -R 10 -T 10 -B 10';
                    switch ($obj) {
                        case 'upd':
                            $options .= ' --orientation Landscape ';
                        break;
                        case 'invoice':
                            $options .= ' --orientation Landscape ';
                        break;
                    }
                    $content = $design->fetch('newaccounts/print_'.$obj.'.tpl');
                    $file_name = '/tmp/' . time().$user->_Data['id'];
                    $file_html = $file_name.'.html';
                    $file_pdf = $file_name.'.pdf';

                    file_put_contents($file_name . '.html', $content);

                    passthru("/usr/bin/wkhtmltopdf $options $file_html $file_pdf");
                    $pdf = file_get_contents($file_pdf);
                    unlink($file_html);unlink($file_pdf);

                    header('Content-Type: application/pdf');
                    ob_clean();
                    flush();
                    echo $pdf;
                    exit;

                } else {
                    if($mode=='html')
                    {
                        $design->ProcessEx('newaccounts/print_'.$obj.'.tpl');
                    }elseif($mode=='xml'){
                        $design->ProcessEx('newaccounts/print_'.$obj.'.xml.tpl');
                    }elseif($mode=='pdf'){
                        include(INCLUDE_PATH.'fpdf/model/'.$obj.'.php');
                    }
                }
            }
        }else{
            if ($only_html == '1') return '';
            trigger_error2('Документ не готов');
        }
        $design->ProcessEx('errors.tpl');
    }

    function _print_receipt()
    {
        global $design;
        $clientId = get_param_raw("client", 0);
        $sum = get_param_raw("sum", 0);
        $sum = (float)$sum;

        if($clientId && $sum)
        {
            $tax_rate = ClientAccount::findOne($clientId)->getTaxRate();
            list($rub, $kop) = explode(".", sprintf("%.2f", $sum));

            $sumNds = (($sum / (1 + $tax_rate/100)) * $tax_rate/100);
            list($ndsRub, $ndsKop) = explode(".", sprintf("%.2f", $sumNds));

            $sSum = array(
                    "rub" => $rub,
                    "kop" => $kop,
                    "nds" => array(
                        "rub" => $ndsRub,
                        "kop" => $ndsKop
                        )
                    );
            $design->assign("sum", $sSum);
            $design->assign("client", ClientCS::FetchClient($clientId));
            echo $design->fetch("newaccounts/print_receipt.tpl");

        }

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

            if(preg_match("/w300/i", $l["item"])) $idx = "w300";
            if(preg_match("/декодер/i", $l["item"])) $idx = "decoder";
            if($l["num_id"] == 11243) $idx = "cii";
            if($l["num_id"] == 11241) $idx = "fonera";

            $s[$idx][] = $l["serial"];
        }
        unset($l);

        foreach($s as &$l)
            $l = implode(", ", $l);

        return $s;
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
            }elseif($source == 3){
                $M['all4net']=1;
                $M['service']=0;
                $M['zalog']= ($isViewOnly)?0:1;
                $M['zadatok']=0;
                $M['good']=$inv3Full ? 1 :0;
                $M['_']=0;
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
            } else {
                return [];
            }
        }

        // счета из 1С выводим полностью.
        $Lkeys = array_keys($L);
        $is1Cbill = $Lkeys && isset($L[$Lkeys[0]]) && isset($L[$Lkeys[0]]["bill_no"]) && preg_match("/^\d{6}\/\d{4}$/i", $L[$Lkeys[0]]["bill_no"]);

        $R = array();
        foreach($L as &$li){
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
                            preg_match("/^Аренд/i", $li["item"]) ||
                            ($li["sum"] == 0 && preg_match("|^МГТС/МТС|i", $li["item"])) ||
                            $is1Cbill
                            )
                    {
                        if($li["sum"] == 0){
                            $li["outprice"] = 0;
                            $li["price"] = 0;
                        }
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
        }else
            $is_four_order = false;
        $design->assign('is_four_order',$is_four_order);

        if(is_null($source))
            $source=3;

        $curr=$bill->Get('currency');


        $bdata=$bill->GetBill();


        // Если счет 1С, на товар, 
        if($bill->is1CBill())
        {
            //то доступны только счета
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
            $period_date = get_inv_period($inv_date);
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

        if(in_array($obj, array('invoice','akt','upd')))
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




        if(in_array($obj, array('invoice','upd')) && (in_array($source, array(1,3)) || ($source==2 && $bill->Get('inv2to1'))) && $do_assign) {//привязанный к фактуре счет
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
                    `sum`>=0
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


        $L_prev=$bill->GetLines((preg_match('/bill-\d/',self::$object))?'order':false);//2 для фактур значит за прошлый период


        $design->assign_by_ref('negative_balance', $bill->negative_balance); // если баланс отрицательный - говорим, что недостаточно средств для проведения авансовых платежей

        foreach($L_prev as $k => $li){
            if (!($obj=='bill' || ($li['type']!='zadatok' || $is_four_order))) {
                unset($L_prev[$k]);
            }
        }
        unset($li);

        $L = self::do_print_prepare_filter($obj,$source,$L_prev,$period_date,(($obj == "invoice" || $obj == "upd") && $source == 3), $isSellBook ? true : false, $origObj);

        if($is_four_order){
            $L =& $L_prev;
            $bill->refactLinesWithFourOrderFacure($L);
        }

        //подсчёт итоговых сумм, получить данные по оборудованию для акта-3

        $cpe = array();
        $bdata["sum"] = 0;
        $bdata['sum_without_tax'] = 0;
        $bdata['sum_tax'] = 0;


        $account = $bill->Client();

        foreach ($L as &$li) {

            $bdata['sum']               += $li['sum'];
            $bdata['sum_without_tax']   += $li['sum_without_tax'];
            $bdata['sum_tax']           += $li['sum_tax'];

            if ($obj=='akt' && $source==3 && $do_assign) {			//связь строчка>устройство или строчка>подключение>устройство
                $id = null;
                if ($li['service']=='tech_cpe') {
                    $id = $li['id_service'];
                } elseif ($li['service']=='usage_ip_ports') {
                    $account = $db->GetRow('select id_service from tech_cpe where id_service='.$li['id_service'].' AND actual_from<"'.$inv_date.'" AND actual_to>"'.$inv_date.'" order by id desc limit 1');
                    if ($account) $id = $account['id_service'];
                }
                if ($id) {
                    $account=$db->GetRow('select tech_cpe.*,model,vendor,type from tech_cpe INNER JOIN tech_cpe_models ON tech_cpe_models.id=tech_cpe.id_model WHERE tech_cpe.id='.$id);
                    $account['amount'] = floatval($li['amount']);
                    $cpe[]=$account;
                } else {
                    $cpe[]=array('type'=>'','vendor'=>'','model'=>$li['item'],'serial'=>'','amount'=>floatval($li['amount']), "actual_from" => $li["date_from"]);
                }
            }
        }
		unset($li);

        $b = $bill->GetBill();

        if ($do_assign){
            $design->assign('cpe',$cpe);
            $design->assign('curr',$curr);
            if (in_array($obj, array('invoice','akt','upd'))) {
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

            $docDate = $obj == "bill" ? $b["bill_date"] : $inv_date;


            $clientAccount = ClientAccount::findOne($account['id'])->loadVersionOnDate($bill->Get('bill_date')); /** @var ClientAccount $clientAccount */
            $organization = $clientAccount->contract->organization;

            $organization_info = $organization->getOldModeInfo();

            $design->assign('firm', $organization_info);
            $design->assign('firma', $organization_info);
            $design->assign('firm_director', $organization->director->getOldModeInfo());
            $design->assign('firm_buh', $organization->accountant->getOldModeInfo());
            //** /Выпилить */

            ClientCS::Fetch($account['id']);
            $design->assign('bill_client',$account);
            return true;
        } else {
            if (in_array($obj, array('invoice','akt','upd'))) {
                return array('bill'=>$bdata,'bill_lines'=>$L,'inv_no'=>$bdata['bill_no'].'-'.$source,'inv_date'=>$inv_date);
            } else return array('bill'=>$bdata,'bill_lines'=>$L);
        }
    }

    function newaccounts_pi_list($fixclient) {
        global $design,$db;

        $param = Param::findOne(["param" => "pi_list_last_info"]);
        if ($param)
        {
            foreach(json_decode($param->value) as $line)
            {
                trigger_error2($line);
            }
        }


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
                    "telekom" => array( "title" => "МС( Н )Телеком", "colspan" => 1),
                    "mcm_telekom" => array( "title" => "МС(_М_) Телеком", "colspan" => 1)
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
                        ),
                    "mcm" => array(
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

                        $this->saveClientBankExchangePL($_FILES['file']['tmp_name'], $d["file"]/*$fName*/);

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
        if ($design->ProcessEx('errors.tpl')) {
            header('Location: ?module=newaccounts&action=pi_list');
            exit();
        }
    }

    function getPaymentInfoDate($h)
    {
        @preg_match_all("/БИК \d{9}\s+(\d{2}) ([^ ]+) (\d{4})\s+ПРОВЕДЕНО/", iconv("cp1251", "utf-8//translit", $h), $o, PREG_SET_ORDER);

        $month = "янв фев мар апр май июн июл авг сен окт ноя дек";

        $m = array_search($o[0][2], explode(" ",$month))+1;
        $m .= "";
        if(strlen($m) == 1) $m = "0".$m;

        return  $o[0][1].".".$m.".".$o[0][3];
    }

    function isPaymentInfo($fheader)
    {
        @preg_match_all("/ПОРУЧЕНИЕ/",iconv("cp1251", "utf-8//translit", $fheader), $f);
        return isset($f[0][0]);
    }

    function getUralSibPLDate($h)
    {
        $h = iconv("cp1251", "utf-8", $h);
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

    function saveClientBankExchangePL($fPath, $prefix)
    {
        $import = new importCBE($prefix, $_FILES['file']['tmp_name']);
        $info = $import->save();

        $lines = [];
        if ($info)
        {
            $totalPlus = $totalMinus = 0;
            foreach($info as $day => $count)
            {
                $new = isset($count["new"]) ? $count["new"] : 0;
                $skiped = isset($count["skiped"]) ? $count["skiped"] : 0;
                $plus = isset($count["sum_plus"]) ? $count["sum_plus"] : 0;
                $minus = isset($count["sum_minus"]) ? $count["sum_minus"] : 0;


                $lines[] = "За ".mdate("d месяца Y", strtotime($day))." найдено платежей: ".$count["all"].
                    //($skiped ? ", пропущено: ".$skiped : "").
                    //($new ? ", новых: ".$new: "").
                    "&nbsp;&nbsp;&nbsp;&nbsp;+".number_format($plus,2, ".", "`")."/-".number_format($minus,2, ".", "`");
            }

        }
        $param = Param::findOne(["param" => "pi_list_last_info"]);

        if (!$param)
        {
            $param = new Param;
            $param->param = "pi_list_last_info";
        }

        $param->value = json_encode($lines);
        $param->save();

        /*
        include_once INCLUDE_PATH."mt940.php";

        $c = file_get_contents($fPath);
        cbe_list_manager::parseAndSave($c, $fName);
         */
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

        $date = date('Y-m-d');
        $organizations = Organization::find()
            ->andWhere(['>', 'actual_from', $date])
            ->andWhere(['<', 'actual_to', $date])
            ->andWhere(['firma' => $firms])
            ->all();
        $organizations = \yii\helpers\ArrayHelper::map($organizations, 'firma', 'organization_id');

        if($inn){
            $q = $fromAdd ?
                "select client_id as id from client_inn p, clients c
INNER JOIN `client_contract` cr ON cr.id=c.contract_id
INNER JOIN `client_contragent` cg ON cg.id=cr.contragent_id
where p.inn = '".$inn."' and p.client_id = c.id and p.is_active"
                :
                "select c.id from clients c
 INNER JOIN `client_contract` cr ON cr.id=c.contract_id
 INNER JOIN `client_contragent` cg ON cg.id=cr.contragent_id
where cg.inn = '".$inn."'";

            foreach($db->AllRecords($qq = $q." and cr.organization_id in ('".implode("','", $organizations)."')") as $c)
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
        $date_formats = array('d.m.Y', 'd.m.y', 'd-m-Y', 'd-m-y');

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

                $extBills = array();
                foreach($pay["clients_bills"] as &$b)
                {
                    $b['is_selected'] = false;
                    if (!isset($b["bill_no_ext"]) || !$b["bill_no_ext"]) continue;
                    if (isset($b["is_group"]) && $b["is_group"]) continue;

                    $extBills[$b["bill_no_ext"]][] = $b["bill_no"];

                    $description = preg_replace_callback("@\d[\d\/ -]{3,}@", function($m) {return str_replace(" ", "", $m[0]);}, $pay["description"]);
                    if (strpos($description, $b["bill_no_ext"]) !== false)
                    {
                        $b["ext_no"] = $b["bill_no_ext"];
                    }
                    if (!isset($b["ext_no"]) || !isset($b["bill_no_ext_date"]) || !$b["bill_no_ext_date"]) continue;

                    foreach ($date_formats as $format) {
                        $bill_no_ext_date = date($format, $b["bill_no_ext_date"]);
                        if (strpos($description, $bill_no_ext_date) !== false)
                        {
                            $b["ext_no_date"] = $bill_no_ext_date;
                            break;
                        }
                    }
                }
                unset($b);
            }


            if ($pay["clients"][0]['currency']!='RUB')
                $pay['usd_rate'] = $this->getPaymentRate($pay["date"]);

            if (isset($pay['usd_rate']) && $pay['usd_rate'] && $pay['bill_no']) {
                $r = $db->GetRow('select sum as S from newbills where bill_no="'.$pay['bill_no'].'"');
                if ($r && $r['S']!=0) {
                    $rate_bill=round($pay['sum']/$r['S'],4);
                    if (abs($rate_bill-$pay['usd_rate'])/$pay['usd_rate'] <=0.03) $pay['usd_rate']=$rate_bill;
                }
            }

            $this->isPayPass($clientIdSum, $pay);

            if (!empty($pay["clients_bills"])) 
            {
                $rank = 0;
                $selected = null;
                foreach($pay["clients_bills"] as $k=>$b)
                {
                    if ($pay['bill_no'] == $b['bill_no']) 
                    {
                        $selected = $k;
                        break;
                    } elseif ($rank < 2 && $pay['sum'] < 0 && isset($b['sum']) && $pay['sum'] == $b['sum']) {
                        if (isset($b['ext_no']) && isset($b['ext_no_date'])) 
                        {
                            $rank = 2;
                            $selected = $k;
                        } elseif (isset($b['ext_no'])) {
                            $selected = $k;
                            $rank = 1;
                        } elseif (!$rank) {
                            $selected = $k;
                        }
                    }
                }
                if (!is_null($selected)) 
                {
                    $pay["clients_bills"][$selected]['is_selected'] = true;
                }
            }

            $sum["all"] += $pay["sum"];
            $sum["plus"] += $pay["sum"] > 0 ? $pay["sum"] : 0;
            $sum["minus"] += $pay["sum"] < 0 ? -$pay["sum"] : 0;
            if(isset($pay["imported"]) && $pay["imported"]) $sum["imported"] += $pay["sum"];

            $d[] = $pay;
        }

        $bills = array();

        foreach($d as $p)
        {
            if(isset($p["imported"]) && $p["imported"] && $p["bill_no"] && substr($p["bill_no"], 6,1) == "-")
            {
                $bill = new Bill($p["bill_no"]);

                if(/*substr($bill->Get("bill_date"), 7,3) == "-01" && */$bill->Get("postreg") == "0000-00-00" && !$bill->isOneZadatok())
                {
                    $c = $bill->Client();
                    if($c["mail_print"] == "yes")
                    {
                        $bills[] = $p["bill_no"];
                    }
                }
            }
        }

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
        if ($pm=$db->GetRow('select comment,bill_no from newpayments where client_id in ("'.implode('","', $clientIds).'") and sum = "'.$pay['sum'].'" and payment_date = "'.$pay['date'].'" and type="bank" and payment_no = "'.$pay["noref"].'"')) {
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
                    "select c.id,c.client, cg.name, cg.name_full as full_name, cr.manager, c.currency
                    from clients c
                     INNER JOIN `client_contract` cr ON cr.id=c.contract_id
                     INNER JOIN `client_contragent` cg ON cg.id=cr.contragent_id
                    where c.id in ('".implode("','", $clientIds)."')") as $c)
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
                $v[] = array("bill_no" => "--".$c[0]["client"]."--", "is_payed" => -1, "is_group" => true, "bill_no_ext" => false);
            }

            foreach($db->AllRecords($q = '
                        (select bill_no, is_payed,sum,bill_no_ext,UNIX_TIMESTAMP(bill_no_ext_date) as bill_no_ext_date from newbills n2
                         where n2.client_id="'.$clientId.'" and n2.is_payed=1
                         /* and (select if(sum(if(is_payed = 1,1,0)) = count(1),1,0) as all_payed from newbills where client_id = "'.$clientId.'")
                         */
                         order by n2.bill_date desc limit 1)
                        union (select bill_no, is_payed,sum,bill_no_ext,UNIX_TIMESTAMP(bill_no_ext_date) as bill_no_ext_date from newbills where client_id='.$clientId.' and bill_no = "'.$billNo.'")
                        union (select bill_no, is_payed,sum,bill_no_ext,UNIX_TIMESTAMP(bill_no_ext_date) as bill_no_ext_date from newbills where client_id='.$clientId.' and is_payed!="1")
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
        (select b.bill_no, b.sum, b.bill_no_ext, UNIX_TIMESTAMP(b.bill_no_ext_date) as bill_no_ext_date, if((select count(*) from newpayments p where p.bill_no = b.bill_no)>=1,1,0) as is_payed1
            from newbills b where ".$where." having is_payed1 = 0)
        union
        (select b.bill_no, b.sum, b.bill_no_ext, UNIX_TIMESTAMP(b.bill_no_ext_date) as bill_no_ext_date, if((select count(*) from newpayments p where p.bill_no = b.bill_no)>=1,1,0) as is_payed1
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
            $date = date('Y-m-d');
            $organizations = Organization::find()
                ->andWhere(['>', 'actual_from', $date])
                ->andWhere(['<', 'actual_to', $date])
                ->andWhere(['firma' => $firms])
                ->all();
            $organizations = \yii\helpers\ArrayHelper::map($organizations, 'firma', 'organization_id');

            $q = $fromAdd ?
                "select client_id as id from client_pay_acc p, clients c
 INNER JOIN `client_contract` cr ON cr.id=c.contract_id
 INNER JOIN `client_contragent` cg ON cg.id=cr.contragent_id
where p.pay_acc = '".$acc."' and p.client_id = c.id"
                :
                "select c.id from clients c
 INNER JOIN `client_contract` cr ON cr.id=c.contract_id
 INNER JOIN `client_contragent` cg ON cg.id=cr.contragent_id
where c.pay_acc = '".$acc."'";

            foreach($db->AllRecords($qq = $q." and cr.organization_id in ('".implode("','", $organizations)."')") as $c)
                $v[] = $c["id"];

        }

        return $v;
    }

    function getCompanyByBillNo($billNo, $firms)
    {
        global $db;

        $date = date('Y-m-d');
        $organizations = Organization::find()
            ->andWhere(['>', 'actual_from', $date])
            ->andWhere(['<', 'actual_to', $date])
            ->andWhere(['firma' => $firms])
            ->all();
        $organizations = \yii\helpers\ArrayHelper::map($organizations, 'firma', 'organization_id');

        $r = $db->GetRow("select client_id from newbills b, clients c
 INNER JOIN `client_contract` cr ON cr.id=c.contract_id
where b.bill_no = '".$billNo."' and c.id = b.client_id and cr.organization_id in ('".implode("','", $organizations)."')");
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
                $p["sum"] = -abs($p["sum"]);
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
                    "sum" => $p["sum"],
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
            //trigger_error2('Файл не существует');
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


        $date = date('Y-m-d');
        $organizations = Organization::find()
            ->andWhere(['>', 'actual_from', $date])
            ->andWhere(['<', 'actual_to', $date])
            ->andWhere(['firma' => $firms])
            ->all();
        $organizations = \yii\helpers\ArrayHelper::map($organizations, 'firma', 'organization_id');

        $firmaSql = ' and cr.organization_id in ("'.implode('","', $organizations).'")';
        foreach($payments as $p){

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
            if(!isset($clients[$p['inn']])){
                $clients[$p['inn']] = $db->AllRecords('
select cr.*, cg.*, c.*, c.client as client_orig, cg.name AS company, cg.name_full AS company_full, cg.legal_type AS type,
cg.position AS signer_position, cg.fio AS signer_fio, cg.positionV AS signer_positionV, cg.fioV AS signer_fioV, cg.legal_type AS type,
0 as is_ext from clients c
 INNER JOIN `client_contract` cr ON cr.id=c.contract_id
 INNER JOIN `client_contragent` cg ON cg.id=cr.contragent_id
where (cg.inn="'.addslashes($p['inn']).'") and (inn!="")'.$firmaSql);
                $clients[$p['inn']] = array_merge($clients[$p['inn']],$db->AllRecords('select
cr.*, cg.*, c.*, c.client as client_orig, cg.name AS company, cg.name_full AS company_full, cg.legal_type AS type,
cg.position AS signer_position, cg.fio AS signer_fio, cg.positionV AS signer_positionV, cg.fioV AS signer_fioV, cg.legal_type AS type,
1 as is_ext,client_inn.comment from clients c
inner join client_inn on client_inn.client_id=clients.id and client_inn.is_active=1 where (client_inn.inn="'.addslashes($p['inn']).'") and (client_inn.inn!="")'.$firmaSql));
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
                if ($pm=$db->QuerySelectRow('newpayments',array('client_id'=>$p['client']['id'],'sum'=>$p['sum'],'payment_date'=>$p['date']))) {
                    $p["bill_no"] = $pm["bill_no"];
                    $p['imported']=1;
                    $SUM_already += $p['sum'];
                }
            } else {
                $k="-".substr(md5($p['inn'].$p['payer']),1,8);
                $p['client']=array('id'=>$k);
            }
            $R[]=$p;
            $SUM_sum += $p['sum'];
            if ($p['sum']>=0) $SUM_plus+=$p['sum']; else $SUM_minus+=$p['sum'];
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
                    trigger_error2("Платеж #".$P["pay"].", на сумму:".$P["sum"]." не внесен, проверте, что бы счет принадлежал этой компании");
                    continue;
                }

                $CL[$client['id']]=$client['currency'];

                $b=1;
                $r2 = $db->GetRow('select currency from newbills where bill_no="'.$P['bill_no'].'"');
                if (isset($r2['currency']) && $r2['currency'] != 'RUB') {
                    $b = 0;
                }

                if ($b) {
                    $payment = new Payment();
                    $payment->client_id = $client['id'];
                    $payment->payment_no = intval($P['pay']);
                    $payment->bill_no = $P['bill_no'];
                    $payment->bill_vis_no = $P['bill_no'];
                    $payment->payment_date = $P['date'];
                    $payment->oper_date = isset($P['oper_date']) ? $P['oper_date'] : $P['date'];
                    $payment->sum = $P['sum'];
                    $payment->currency = 'RUB';
                    $payment->payment_rate = 1;
                    $payment->original_sum = $P['sum'];
                    $payment->original_currency = 'RUB';
                    $payment->comment = $P['comment'];
                    $payment->add_date = date('Y-m-d H:i:s');
                    $payment->add_user = $user->Get('id');
                    $payment->type = 'bank';
                    $payment->bank = $bank;
                    $payment->save();
                }
                if ($b) {
                    echo '<br>Платеж '.$P['pay'].' клиента '.$client['client'].' внесён';
                 } else {
                    echo '<br>Платеж '.$P['pay'].' клиента '.$client['client'].' не внесён, так как на '.$P['date'].' отсутствует курс доллара';
                }
            }
        }

        trigger_error2("Баланс обновлён");
        if ($b && $design->ProcessEx('errors.tpl')) {
            header('Location: ?module=newaccounts&action=pi_process&file='.$file);
            exit();
        } else return $this->newaccounts_pi_process($fixclient);
    }

    function newaccounts_balance_bill($fixclient) {
    global $design,$db;
    $design->assign('b_nedopay',$nedopay=get_param_protected('b_nedopay',0));
    $design->assign('p_nedopay',$p_nedopay=get_param_protected('p_nedopay',1));
    $design->assign('manager',$manager=get_param_protected('manager'));
    $design->assign('b_pay0',($b_pay0=get_param_protected('b_pay0',0)));
    $design->assign('b_pay1',$b_pay1=get_param_protected('b_pay1',0));
    $design->assign('b_show_bonus',$b_show_bonus=get_param_protected('b_show_bonus',0));
    $design->assign('user_type',$userType =get_param_protected('user_type','manager'));

    $design->assign("report_by", $reportBy = get_param_protected("report_by", "bill_created"));

	$dateFrom = new DatePickerValues('date_from', 'first');
	$dateTo= new DatePickerValues('date_to', 'last');
	$dateFrom->format='Y-m-d';$dateTo->format='Y-m-d';
	$date_from=$dateFrom->getDay();
	$date_to=$dateTo->getDay();
    
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

        if ($reportBy == "bill_created")
        {
            $W1[] = 'newbills.bill_date >= "'.$date_from.'"';
            $W1[] = 'newbills.bill_date <= "'.$date_to.'"';
        } else { // report_by == bill_closed
            $W1[] = 'trouble_stage.date_start between "'.$date_from.'" and "'.$date_to.'"';
            $W1[] = 'trouble_stage.state_id = 20'; //closed
        }

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
                newbills.`sum` - (select sum(p.sum) from newpayments p where bill_no=newbills.bill_no) >= ".((float)$p_nedopay)."
                    ";
        }

        $W1[] = '( newbills.bill_date >= newsaldo.ts OR newsaldo.ts IS NULL)';


        $sql = '
            select
            newbills.*,
            c.nal,
            c.client,
            cg.name AS company,
            cr.organization_id AS firma,
            if(c.currency = newbills.currency, 1,0) as f_currency,
                    cr.manager as client_manager,
                    (select user from user_users where id=nbo.owner_id) as bill_manager'.($b_show_bonus ? ',

                    (SELECT group_concat(concat("#",code_1c, " ",bl.type, if(b.type is null, " -- ", concat(": (", `value`, b.type,") ", bl.sum, " => ", round(if(b.type = "%",bl.sum*0.01*`value`, `value`*amount),2)))) ORDER BY bl.`code_1c` separator "|\n")  FROM newbill_lines bl
                     left join g_bonus b on b.good_id = bl.item_id and `group` = "'.$managerInfo["usergroup"].'"
                     where bl.bill_no=newbills.bill_no) bonus_info ,
                    (SELECT sum(round(if(b.type = "%",bl.sum*0.01*`value`, `value`*amount),2)) FROM newbill_lines bl
                     left join g_bonus b on b.good_id = bl.item_id and `group` = "'.$managerInfo["usergroup"].'"
                     where bl.bill_no=newbills.bill_no) bonus' : '').'
                        from
                        newbills '.
                        
                        ($reportBy == "bill_closed" ? '
                        inner join tt_troubles trouble using (bill_no)
                        inner join tt_stages trouble_stage on (trouble.cur_stage_id = trouble_stage.stage_id)
                        ' : '').'

                        left join newbill_owner nbo on (nbo.bill_no = newbills.bill_no)
                        '.$newpayments_join.'
                        LEFT JOIN clients c ON c.id = newbills.client_id
                         LEFT JOIN `client_contract` cr ON cr.id=c.contract_id
                         LEFT JOIN `client_contragent` cg ON cg.id=cr.contragent_id
                        LEFT JOIN newsaldo ON newsaldo.client_id = c.id
                        and newsaldo.is_history = 0
                        and newsaldo.currency = c.currency
                        where '.MySQLDatabase::Generate($W1).'

                        ';

        if($userType == "manager") {
            $sql = str_replace("~~where_owner~~", 'cr.manager="'.$manager.'"', $sql);
        }else{

            if($userType == "creator")
            {
                $sql = str_replace("~~where_owner~~", 'nbo.owner_id="'.$managerInfo["id"].'"', $sql);
            }else{
                $sql = str_replace("~~where_owner~~", 'cr.manager="'.$manager.'" or nbo.owner_id="'.$managerInfo["id"].'"', $sql);
            }
        }

        $sql .= "    order by client, bill_no";

        $R=$db->AllRecords($sql);

        $totalAmount = [];
        $totalBonus = [];

        $date = date('Y-m-d');
        $organizations = Organization::find()
            ->andWhere(['>', 'actual_from', $date])
            ->andWhere(['<', 'actual_to', $date])
            ->all();
        $organizations = \yii\helpers\ArrayHelper::map($organizations, 'organization_id', 'firma');

        $clients = array();
        foreach($R as &$r){
            $r['firma'] = $organizations[$r['firma']];

            $clients[$r['client_id']] =1;
            if ($r['sum']) {
                if (!isset($totalAmount[$r['currency']])) {
                    $totalAmount[$r['currency']] = 0;
                }
                $totalAmount[$r['currency']] += $r['sum'];
            }
            if ($r['bonus']) {
                if (!isset($totalBonus[$r['currency']])) {
                    $totalBonus[$r['currency']] = 0;
                }
                $totalBonus[$r['currency']] += $r['bonus'];
            }
        }
        $design->assign('clients_count', count($clients));
        $design->assign('bills',$R);
        $design->assign('totalAmount',$totalAmount);
        $design->assign('totalBonus',$totalBonus);
    }

    $R=array(); StatModule::users()->d_users_get($R,array('manager','marketing'));
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

        $fixclient_data = ClientAccount::findOne($clientId);

        // saldo
        $sum = array('USD'=>array('delta'=>0,'bill'=>0,'ts'=>''),'RUB'=>array('delta'=>0,'bill'=>0,'ts'=>''));
        $r=$db->GetRow('select * from newsaldo where client_id='.$fixclient_data['id'].' and currency="'.$fixclient_data['currency'].'" and is_history=0 order by id desc limit 1');
        if ($r) {
            $sum[$fixclient_data['currency']]=array('delta'=>0,'bill'=>$r['saldo'],'ts'=>$r['ts'],'saldo'=>$r['saldo']);
        } else {
            $sum[$fixclient_data['currency']]=array('delta'=>0,'bill'=>0,'ts'=>'');
        }
        $R1=$db->AllRecords('select *,'.($sum[$fixclient_data['currency']]['ts']?'IF(bill_date>="'.$sum[$fixclient_data['currency']]['ts'].'",1,0)':'1').' as in_sum from newbills where client_id='.$fixclient_data['id'].' order by bill_no desc');
        $R2=$db->AllRecords('select P.*,U.user as user_name,'.($sum[$fixclient_data['currency']]['ts']?'IF(P.payment_date>="'.$sum[$fixclient_data['currency']]['ts'].'",1,0)':'1').' as in_sum from newpayments as P LEFT JOIN user_users as U on U.id=P.add_user where P.client_id='.$fixclient_data['id'].' order by P.payment_date desc');

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

        $design->assign("l_couriers", array("all" => "--- Все ---","checked"=>"--- Установленные --") + Courier::dao()->getList(false));
        $design->assign("l_metro", array("all" => "--- Все ---") + \app\models\Metro::getList());
        $design->assign('courier',$courier=get_param_protected('courier',"all"));
        $design->assign('metro',$metro=get_param_protected('metro',"all"));
        $design->assign('manager',$manager=get_param_protected('manager'));
        $design->assign('cl_off',$cl_off=get_param_protected('cl_off'));
        $design->assign('zerobills',1);
        $dateFrom = new DatePickerValues('date_from', 'first');
	$dateTo= new DatePickerValues('date_to', 'last');
	$dateFrom->format='Y-m-d';$dateTo->format='Y-m-d';
        $date_from=$dateFrom->getDay();
        $date_to=$dateTo->getDay();
        

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
                $W1[] = 'cr.manager="'.$manager.'"';
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

            if (!$cl_off) $W1[]='status="work"';
            if ($date_from) $W1[]='newbills.bill_date>="'.$date_from.'"';
            if ($date_to) $W1[]='newbills.bill_date<="'.$date_to.'"';
            if($zerobill) $W1[] = 'newbills.sum<>0';

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
                            c.nal,
                            c.metro_id,
                            c.address_post as address,
                            newbills.nal as bill_nal,
                            c.client,
                            cg.name AS company,
                            cr.manager
                        from newbills
                        LEFT JOIN clients c ON c.id=newbills.client_id
                         LEFT JOIN `client_contract` cr ON cr.id=c.contract_id
                         LEFT JOIN `client_contragent` cg ON cg.id=cr.contragent_id
                        LEFT JOIN newsaldo ON newsaldo.client_id=c.id
                            AND newsaldo.is_history=0 AND newsaldo.currency=c.currency
                        WHERE
                            '.MySQLDatabase::Generate($W1).'
                            and c.status not in ("tech_deny","deny")
                        ORDER BY
                            '.($isPrint ? "cg.name, " : "" ).'client,
                            bill_no
                    ) a
                LEFT JOIN log_newbills_static ls USING (bill_no)
                LEFT JOIN user_users u ON u.id = ls.user_id
            ';

            $R=$db->AllRecords($q);

            $totalAmount = [];
            $totalSaldo = [];

            foreach ($R as &$r) {
                if ($isPrint) {
                    $r["metro"] = \app\models\Metro::getList()[$r["metro_id"]];
                }
                $r["debt"] = $this->GetDebt($r["client_id"]);
                $r["courier"] = Courier::dao()->getNameById($r["courier_id"]);
                if ($r['sum']) {
                    if (!isset($totalAmount[$r['currency']])) {
                        $totalAmount[$r['currency']] = 0;
                    }
                    $totalAmount[$r['currency']] += $r['sum'];
                }
                if ($r["debt"]["sum"]) {
                    if (!isset($totalSaldo[$r["debt"]["currency"]])) {
                        $totalSaldo[$r["debt"]["currency"]] = 0;
                    }
                    $totalSaldo[$r["debt"]["currency"]] += $r["debt"]["sum"];
                }
            }
            $design->assign('bills',$R);
            $design->assign('totalAmount',$totalAmount);
            $design->assign('totalSaldo',$totalSaldo);
        }
        $m=array();
        StatModule::users()->d_users_get($m,'manager');

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
                    'select newbills.*,clients.nal,clients.client,cg.name AS company
                    FROM newbills
                    INNER JOIN clients c ON (clients.id=newbills.client_id)
                     INNER JOIN `client_contract` cr ON cr.id=c.contract_id
                     INNER JOIN `client_contragent` cg ON cg.id=cr.contragent_id
                    WHERE bill_no LIKE "'.$search.'%" OR bill_no_ext LIKE "'.$search.'%" ORDER BY client,bill_no LIMIT 1000');

            if(!$R)
            {
                $R=$db->AllRecords(
                        $q = 'select b.*,c.nal,c.client,cg.name AS company
                        FROM newbills b, newbills_add_info i, clients c
                         INNER JOIN `client_contract` cr ON cr.id=c.contract_id
                         INNER JOIN `client_contragent` cg ON cg.id=cr.contragent_id
                        WHERE c.id=b.client_id
                        and b.bill_no = i.bill_no and i.req_no = "'.$search.'"
                        ORDER BY c.client, b.bill_no LIMIT 1000');
            }

            if (count($R)==1000) trigger_error2('Ограничьте условия поиска. Показаны первые 1000 вариантов');
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
        $design->assign('sort',$sort=get_param_protected('sort'));

        if ($manager) {
            $W0 = array('AND');
            if (!$cl_off) $W0[]='clients.status="work"';
            if ($manager!='()') $W0[]='cr.manager="'.$manager.'"';

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

            $balances=$db->AllRecords('select'.
                                ' cr.*, cg.*, clients.*, clients.client as client_orig, cg.name AS company, cg.name_full AS company_full, cg.legal_type AS type, cr.organization_id AS firma,
cg.position AS signer_position, cg.fio AS signer_fio, cg.positionV AS signer_positionV, cg.fioV AS signer_fioV, cg.legal_type AS type '.
                                ', (select ts from newsaldo where client_id=clients.id and newsaldo.is_history=0 and newsaldo.currency=clients.currency order by id desc limit 1) as saldo_ts'.
                                ', (select saldo from newsaldo where client_id=clients.id and newsaldo.is_history=0 and newsaldo.currency=clients.currency order by id desc limit 1) as saldo_sum'.
                                ', (select sum(`sum`) from newbills where '.MySQLDatabase::Generate($W1).') as sum_bills'.
                                ', (select sum(P.sum) from newpayments as P LEFT JOIN newbills ON newbills.bill_no=P.bill_no and P.client_id = newbills.client_id where '.MySQLDatabase::Generate($W2).') as sum_payments'.
                                ', (select bill_date from newbills where '.MySQLDatabase::Generate($W1).' order by bill_date desc limit 1) as lastbill_date'.
                                ', (select bill_no from newbills where '.MySQLDatabase::Generate($W1).' order by bill_date desc limit 1) as lastbill_no'.
                                ', (select round(`sum`) from newbills where '.MySQLDatabase::Generate($W1).' order by bill_date desc limit 1) as lastbill_sum'.
                            ' from clients '.
                "  INNER JOIN `client_contract` cr ON cr.id=clients.contract_id".
                "  INNER JOIN `client_contragent` cg ON cg.id=cr.contragent_id".
                            ' WHERE '.MySQLDatabase::Generate($W0).' HAVING lastbill_date IS NOT NULL ORDER by '.$sortK);
            if ($sort==1) {
                usort($balances,create_function('$a,$b','$p=$a["saldo_sum"]+$a["sum_bills"]-$a["sum_payments"]; $q=$b["saldo_sum"]+$b["sum_bills"]-$b["sum_payments"];
                                                        if ($p==$q) return 0; else if ($p>$q) return 1; else return -1;'));
            }

            $date = date('Y-m-d');
            $organizations = Organization::find()
                ->andWhere(['>', 'actual_from', $date])
                ->andWhere(['<', 'actual_to', $date])
                ->all();
            $organizations = \yii\helpers\ArrayHelper::map($organizations, 'organization_id', 'firma');

            foreach($balances as &$balance){
                $balance['firma'] = $organizations[$balance['firma']];
            }

            $design->assign('balance',$balances);
        }
        $R=array(); StatModule::users()->d_users_get($R,'manager');
        if (isset($R[$manager])) $R[$manager]['selected']=' selected';
        $design->assign('users_manager',$R);
        $design->AddMain('newaccounts/balance_client.tpl');
    }
    function newaccounts_balance_check($fixclient) {
        global $design,$db,$user,$fixclient_data;

        if (!$fixclient) {trigger_error2('Выберите клиента'); return;}

	$dateFrom = new DatePickerValues('date_from', 'first');
	$dateTo= new DatePickerValues('date_to', 'last');
	$dateFrom->format='Y-m-d';$dateTo->format='Y-m-d';
        $date_from=$dateFrom->getDay();
        $date_to=$dateTo->getDay();
       
        $c = \app\models\HistoryVersion::getVersionOnDate(ClientAccount::className(), $fixclient_data['id'], $date_from);

        //** Todo:  */
        $organization = Organization::find()->byId($c['organization_id'])->actual($date_to)->one();
        $design->assign('firma', $organization->getOldModeInfo());
        $design->assign('firm_director', $organization->director->getOldModeInfo());
        $design->assign('firm_buh', $organization->accountant->getOldModeInfo());
        //** Todo:  */

        $saldo=$db->GetRow('select * from newsaldo where client_id="'.$fixclient_data['id'].'" and newsaldo.is_history=0 order by id');
        $design->assign('saldo', $startsaldo=floatval(get_param_protected('saldo',0)));
        $design->assign('date_from_val',$date_from_val=strtotime($date_from));
        $design->assign('date_to_val',$date_to_val=strtotime($date_to));
        $R=array(); $Rc = 0;
        $S_p=0;
        $S_b=0;

        $R[0]=array('type'=>'saldo','date'=>$date_from_val,'sum_outcome'=>$startsaldo);
        $B = array();

        $W = array('AND','P.client_id="'.$fixclient_data['id'].'"','P.currency="RUB"');
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

            $sum_outcome = $A['sum'];
            if ($sum_outcome < 0) {
                $sum_income = - $sum_outcome;
                $sum_outcome = 0;
                $S_b+=$sum_income;
            } else {
                $sum_income = 0;
                $S_p+=$sum_outcome;
            }

            $R[$A['payment_date']+($Rc++)] = 
                array('type'=>'pay','date'=>$A['payment_date'],'sum_outcome'=>$sum_outcome,'sum_income'=>$sum_income, 'pay_no'=>$A['payment_no'],'bill_no'=>$A['bill_no']);
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
                $A=$this->do_print_prepare($bill,$I==4?'lading':'akt',$I==4?null:$I,'RUB',0);
                if ($I==1) $A1 = $A;
                if($I==4 && $A['bill']){
                    $A['inv_date'] = ($A1)?$A1['inv_date']:$A['inv_date'];
                    $A['inv_no'] = $A['bill']['bill_no'];
                }
                if($I != 3 && is_array($A) && $A['bill']['sum']){
                    $k=date('Y-m-d',$A['inv_date']);
                    if(
                        (!$date_from || $k>=$date_from)
                    &&
                        (!$date_to || $k<=$date_to)
                    ){
                        $sum_in = $A["bill"]["is_rollback"] ? 0 : $A['bill']['sum'];
                        $sum_out = $A["bill"]["is_rollback"] ? $A['bill']['sum'] : 0;
                        $R[$A['inv_date']+($Rc++)] = array(
                            'type'       => 'inv',
                            'date'       => $A['inv_date'],
                            'sum_income' => $sum_in,
                            'sum_outcome' => $sum_out,
                            'inv_no'     => $A['inv_no'],
                            'bill_no'    => $A['bill']['bill_no'],
                            'inv_num'    => $I,
                        );
                        $S_b+=$sum_in-$sum_out;
                    }
                }
            }
            unset($bill);
        }

        foreach($db->AllRecords(
            "select 'inv' as type, 3 as inv_num,
                b.bill_no, concat(b.bill_no,'-3') as inv_no,
                unix_timestamp(bill_date) as date,
                l.sum as sum_income, item as items, b.currency, b.sum as b_sum
            from
                newbills b, newbill_lines l
            where
                    b.bill_no = l.bill_no
                and client_id = '".$fixclient_data['id']."'
                and type='zalog'
                and b.bill_date<='".$date_to."'") as $z)
        {
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


        $period_client_data = \app\models\HistoryVersion::getVersionOnDate(ClientAccount::className(), $fixclient_data['id'], $date_from);
        $design->assign("company_full", $period_client_data["company_full"]);
        $design->assign("client_id", $fixclient_data['id']);

        $contractId = ClientAccount::findOne($fixclient_data['id'])->contract_id;
        $design->assign("last_contract", BillContract::getLastContract($contractId, $date_from_val));
        $design->assign('data',$R);
        $design->assign('zalog',$zalog);
        $design->assign('sum_bill',$S_b);
        $design->assign('sum_pay',$S_p);
        $design->assign('sum_zalog',$S_zalog);
        $design->assign('ressaldo',$ressaldo);
        $design->assign('formula',$formula);

        $fullscreen = get_param_protected('fullscreen',0);
        $is_pdf = get_param_protected('is_pdf',0);
        $sign = get_param_protected('sign','');
        $design->assign('fullscreen',$fullscreen);
        $design->assign('is_pdf',$is_pdf);
        $design->assign('sign',$sign);

        if ($is_pdf == 1) {
            /*wkhtmltopdf*/
            $options = ' --quiet -L 10 -R 10 -T '.get_param_protected('pdf_top_padding', 10).' -B 10';
            $content = $design->fetch('newaccounts/print_balance_check.tpl');
            $file_name = '/tmp/' . time().$user->_Data['id'];
            $file_html = $file_name.'.html';
            $file_pdf = $file_name.'.pdf';

            file_put_contents($file_name . '.html', $content);

            exec("/usr/bin/wkhtmltopdf $options $file_html $file_pdf");
            $pdf = file_get_contents($file_pdf);

            //Create file
            $V = array(
                    'name'=>str_replace(array('"'), "", $period_client_data["company_full"]).' Акт сверки (на '.$date_to.').pdf',
                    'ts'=>array('NOW()'),
                    'contract_id'=>$fixclient_data['contract_id'],
                    'comment'=>$period_client_data["company_full"].' Акт сверки (на '.$date_to.')',
                    'user_id'=>$user->Get('id')
                );
            $id = $db->QueryInsert('client_files',$V);
            copy($file_pdf, STORE_PATH.'files/'.$id);

            unlink($file_html);unlink($file_pdf);

            header('Content-Type: application/pdf');
            ob_clean();
            flush();
            echo $pdf;
            exit;
        }

        if ($fullscreen==1) {
            $design->ProcessEx('newaccounts/print_balance_check.tpl');
            //$design->ProcessEx('pop_header.tpl');
            //$design->ProcessEx('errors.tpl');
            //$design->ProcessEx('newaccounts/balance_check.tpl');
            //$design->ProcessEx('pop_footer.tpl');
        } else {
            $design->AddMain('newaccounts/balance_check.tpl');
        }
    }
    function newaccounts_balance_sell($fixclient){
        global $design,$db,$user;
        $dateFrom = new DatePickerValues('date_from', 'first');
	$dateTo= new DatePickerValues('date_to', 'last');
	$dateFrom->format='Y-m-d';$dateTo->format='Y-m-d';
	$date_from=$dateFrom->getDay();
	$date_to=$dateTo->getDay();
        $design->assign('date_from_val',$date_from_val=$dateFrom->getTimestamp());
        $design->assign('date_to_val',$date_to_val=$dateTo->getTimestamp());
        $design->assign('firma',$firma = get_param_protected('firma','mcn_telekom'));
        set_time_limit(0);
        $R=array();
        $Rc = 0;
        $S = array(
            'sum_without_tax'=>0,
            'sum'=>0,
            'sum_tax'=>0
        );

        if(get_param_raw("do", ""))
        {

        $W = array('AND');//,'C.status="work"');
        $W[] = 'B.sum!=0';
        $W[] = 'P.currency="RUB" OR P.currency IS NULL';

        if($payfilter=='1')     $W[] = 'B.is_payed=1';
        elseif($payfilter=='2') $W[] = 'B.is_payed IN (1,3)';

        if($paymethod) $W[] = 'C.nal="'.$paymethod.'"';
        if($firma) {
            $firma = Organization::findOne(['firma' => $firma])->organization_id;
            $W[] = 'cr.organization_id="' . $firma . '"';
        }


        $W[] = "cg.legal_type in ('ip', 'legal')";

        $W_gds = $W;
        

        if($date_from)          $W[] = 'B.bill_date>="'.$date_from.'"-INTERVAL 1 MONTH';
        if($date_to)            $W[] = 'B.bill_date<="'.$date_to.'"+INTERVAL 1 MONTH';


        $q_service = '
            select * from (
                select
                    B.*,
                    cg.name_full AS company_full,
                    cg.inn,
                    cg.kpp,
                    cg.legal_type AS type,
                    max(P.payment_date) as payment_date,
                    sum(P.sum) as pay_sum,
                    bill_date as shipment_date,
                    0 as shipment_ts,
                    18 as min_nds
                FROM
                    newbills B
                LEFT JOIN newpayments P ON (P.bill_no = B.bill_no AND P.client_id = B.client_id)
                INNER JOIN clients as C ON (C.id = B.client_id)
                 INNER JOIN `client_contract` cr ON cr.id=C.contract_id
                 INNER JOIN `client_contragent` cg ON cg.id=cr.contragent_id
        WHERE
                '.MySQLDatabase::Generate($W).'
            and B.bill_no like "20____-____"
            and if(B.sum < 0, cr.contract_type_id =2, true) ### only telekom clients with negative sum
            and cr.contract_type_id != 6 ## internal office
            and cr.business_process_status_id != 22 ## trash
        GROUP BY
            B.bill_no
        order by
            B.bill_no

        ) f';

        $q_gds = "  
            select *, unix_timestamp(shipment_date) as shipment_ts from (
                    select
                        B.*,
                        cg.name_full AS company_full,
                        cg.inn,
                        if(doc_date != '0000-00-00', 
                            doc_date, 
                            (
                                SELECT min(cast(date_start as date)) 
                                FROM tt_troubles t , `tt_stages` s  
                                WHERE t.bill_no = B.bill_no 
                                    and t.id = s.trouble_id 
                                    and state_id in (select id from tt_states where state_1c = 'Отгружен'))) as shipment_date,
                        cg.kpp,
                        cg.legal_type AS type,
                        max(P.payment_date) as payment_date,
                        sum(P.sum) as `pay_sum`,
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
                            and date_start between '".$date_from." 00:00:00' and '".$date_to." 23:59:59' 
                            and state_id in (select id from tt_states where state_1c = 'Отгружен') #выбор счетов-фактур по дате отгрузки
                            and t.bill_no is not NULL
                    )t, 
                        newbills B
                    LEFT JOIN newpayments P ON (P.bill_no = B.bill_no AND P.client_id = B.client_id)
                    INNER JOIN clients as C ON (C.id = B.client_id)
                     INNER JOIN `client_contract` cr ON cr.id=C.contract_id
                     INNER JOIN `client_contragent` cg ON cg.id=cr.contragent_id
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

                $A = false;//$this->bb_cache__get($p["bill_no"]."--".$I);

                if($A === false)
                {
                    $A=$this->do_print_prepare($bill,'invoice',$I,'RUB',0, true);
                    //$this->bb_cache__set($p["bill_no"]."--".$I, $A);
                }


                if (is_array($A) && $A['bill']['sum']) {

                    $A['bill']['shipment_ts'] = $p['shipment_ts'];
                    $A["bill"]["contract"] = $p["contract"];
                    $A["bill"]["contract_status"] = $p["contract_status"];


                    $invDate = $A['bill']['shipment_ts'] ? 
                        $A['bill']['shipment_ts'] : 
                        $A['inv_date'];

                    // get property from history
                    $c = \app\models\HistoryVersion::getVersionOnDate(ClientAccount::className(), $p['client_id'], date("Y-m-d", $invDate));
                    $p["company_full"] = trim($c["company_full"]);
                    $p["inn"] = $c["inn"];
                    $p["kpp"] = $c["kpp"];
                    //$p["type"] = $c["type"];

                    $A['bill']['inv_date'] = $invDate;

                    $k=date('Y-m-d',$A['inv_date']);

                    if ((!$date_from || $k>=$date_from) && (!$date_to || $k<=$date_to)) {
                        $A['bill']['company_full'] = $p['company_full'];
                        $A['bill']['type'] = $c['type'];

                        if($p["type"] == "person")
                        {
                            $A['bill']['inn'] = "-----";
                            $A['bill']['kpp'] = "-----";
                        }elseif($p["type"] == "legal"){
                            $A['bill']['inn'] = "<span style=\"color: red;\"><b>??????? ".$p['inn']."</b></span>";
                            $A['bill']['kpp'] = $p['kpp'];
                        }else{
                            if (
                                $p["type"] == "ip" ||
                                preg_match("/(И|и)ндивидуальный[ ]+(П|п)редприниматель/", $p["company_full"]) ||
                                preg_match("/^ИП/", $p["company_full"])
                            )
                            {
                                $p["kpp"] = "-----";
                            }
                            $A['bill']['inn'] = $p['inn'];
                            $A['bill']['kpp'] = $p['kpp'];
                        }

                        $A['bill']['payment_date'] = $p['payment_date'];
                        $A['bill']['pay_sum'] = $p['pay_sum'];

                        $A['bill']['inv_no'] = $A['inv_no'];



                        if($p["is_rollback"])
                        {
                            foreach(array("ts", "sum_tax", "sum_without_tax", "sum") as $f)
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

        if(get_param_raw("csv", "0") == "1")
        {
            header('Content-type: application/csv');
            header('Content-Disposition: attachment; filename="'.iconv("utf-8", "windows-1251", "Книга продаж").'.csv"');

            ob_start();

            echo "Книга продаж;;;;;;;;;;;;;;;;\n";
            echo ";;;;;;;;;;;;;;;;;;\n";
            echo ";;;;;;;в том числе продажи, облагаемые налогом по ставке;;;;;;;;;;;\n";
            echo "Номер счета-фактуры продавца;Дата счета-фактуры продавца;Наименование покупателя;ИНН покупателя;КПП покупателя;Дата оплаты счета-фактуры продавца;Всего продаж, включая НДС;18 процентов стоимость продаж без НДС;18 процентов сумма НДС;10 процентов стоимость продаж без НДС;10 процентов сумма НДС;20 процентов стоимость продаж без НДС; 20 процентов сумма НДС;продажи, освобождаемые от налога;cId;\n";

            foreach($R as $r)
            {
                echo $r["inv_no"].";";
                echo date("d.m.Y",$r["inv_date"]).";";
                echo html_entity_decode(str_replace(["&#171;","&#187;"], "\"", $r["company_full"])).";";
                echo $r["inn"].";";
                echo $r["kpp"].";";
                echo ($r["payment_date"] ? date("d.m.Y", strtotime($r["payment_date"])) : "").";";
                echo number_format(round($r["sum"],2), 2, ",", "").";";
                echo number_format(round($r["sum_without_tax"],2), 2, ",", "").";";
                echo number_format(round($r["sum_tax"],2), 2, ",", "").";";
                echo "0;";
                echo "0;";
                echo "0;";
                echo "0;";
                echo "0;";
                echo $r["client_id"].";";


                echo "\n";
            }
            echo iconv('utf-8', 'windows-1251', ob_get_clean());
            exit();
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

    function newaccounts_pay_rebill($fixclient) {
        global $design,$db,$fixclient_data;
        if (!$fixclient) {trigger_error2('Не выбран клиент'); return;}
        $pay=get_param_integer('pay');
        $bill=get_param_protected('bill');
        if ($bill) {
            $db->Query('update newpayments set bill_vis_no="'.$bill.'" where id='.$pay);
        } else $db->Query('update newpayments set bill_vis_no=bill_no where id='.$pay);

        header('Location: ?module=newaccounts');
        exit();
    }

    function newaccounts_first_pay($fixclient) {
        global $design,$db;
        $dateFrom = new DatePickerValues('date_from', 'first');
        $dateTo = new DatePickerValues('date_to', 'today');
        $dateFrom->format = 'Y-m-d';$dateTo->format = 'Y-m-d';
        $from = $dateFrom->getDay();
        $to = $dateTo->getDay();
        
        $sort = get_param_raw('sort', 'channel');
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
                        newpayments.client_id, c.client, c.id AS clientid, cg.name AS company, newpayments.`sum`, newpayments.payment_date, c.site_req_no, cr.organization_id
                    FROM
                        `newpayments`, `clients` c
                         INNER JOIN `client_contract` cr ON cr.id=c.contract_id
                         INNER JOIN `client_contragent` cg ON cg.id=cr.contragent_id
                    WHERE
                        payment_date between '".$from."' and '".$to."'
                        and c.id=newpayments.client_id
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
                    $row['organization'] = $row['company'];
                    $row['first_pay_data'] = $row['payment_date'];
                    $sortedArray[$client] = $row;
                }
            }

            foreach($sortedArray as $client => $clientData){
                $clientData = $db->AllRecords("
SELECT cr.manager, c.sale_channel FROM clients c
 INNER JOIN `client_contract` cr ON cr.id=c.contract_id
 INNER JOIN `client_contragent` cg ON cg.id=cr.contragent_id
 where client='".$client."'");

                $sortedArray[$client]['manager'] = isset($usersData[$clientData[0]['manager']])?$usersData[$clientData[0]['manager']]:$clientData[0]['manager'];
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
                trigger_error2('Курс на эту дату уже введён');
            } else {
                trigger_error2('Курс занесён');
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

        $filterBank = $filterEcash = "";

        $type = get_param_raw('type','payment_date');
        if ($type!='payment_date' && $type!='oper_date') $type = 'add_date';
        $design->assign('type',$type);

        $bdefault = array("mos" => true, "citi" => true, "ural" => true, "sber" => true);
        $banks = get_param_raw("banks", $bdefault);
        $design->assign("banks", $banks);

        $edefault = array("cyberplat"=> true, "yandex" => true, "paypal" => true);
        $ecashs = get_param_raw("ecashs", $edefault);
        $design->assign("ecashs", $ecashs);
        
        $order_by = get_param_raw('order_by', 'add_date');
        $design->assign("order_by", $order_by);

        $types = '';
        foreach (array('bank','prov','neprov', 'ecash') as $k) {
            if ($v = get_param_raw($k)) $types .= ($types?',':'').'"'.$k.'"';
            $design->assign($k,$v);
        }

        $filterBank = " P.bank in ('".implode("','", array_keys($banks))."')";

        if (isset($types["ecash"]))
        {
            $filterEcash = " P.ecash_operator in ('".implode("','", array_keys($ecashs))."')";
        }

        $filter .= " and (".$filterBank.($filterEcash ? " OR ".$filterEcash : "").")";

        if (!$types) $R = array(); else $R = $db->AllRecords($q='select P.*,cr.manager,C.client,cg.name AS company,B.bill_date,U.user from newpayments as P
                         INNER JOIN clients as C ON C.id=P.client_id
                         INNER JOIN `client_contract` cr ON cr.id=C.contract_id
                         INNER JOIN `client_contragent` cg ON cg.id=cr.contragent_id
                         LEFT JOIN user_users as U ON U.id=P.add_user
                         LEFT JOIN newbills as B ON B.bill_no=P.bill_no
                         WHERE '.$type.'>=FROM_UNIXTIME('.$from.') AND '.$type.'<FROM_UNIXTIME('.$to.')
                         AND P.type IN ('.$types.')'.$filter.' ORDER BY '. $order_by . ' LIMIT 5000');

        $S = array(
            'bRUB'=>0, 'pRUB'=>0, 'nRUB'=>0, 'eRUB'=>0,
            'bUSD'=>0, 'pUSD'=>0, 'nUSD'=>0,'RUB'=>0,'USD'=>0);

        foreach ($R as &$r) {
            $r['type']=substr($r['type'],0,1);
            $S[$r['type'].$r['currency']] += $r['sum'];
            $S[$r['currency']] += $r['sum'];
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
        $dateFrom = new DatePickerValues('date_from', 'today');
        $from=$dateFrom->getTimestamp();
        $design->AddMain('newaccounts/postreg_report_form.tpl');
    }
    function newaccounts_postreg_report_do() {
        global $design,$db;
        $dateFrom = new DatePickerValues('date_from', 'today');
        $from=$dateFrom->getTimestamp();
        $ord = 0;
        $R = $db->AllRecords('select B.*,cg.name AS company,C.address_post_real from newbills as B inner join clients as C ON C.id=B.client_id
 INNER JOIN `client_contract` cr ON cr.id=C.contract_id
 INNER JOIN `client_contragent` cg ON cg.id=cr.contragent_id
where postreg = "'.date('Y-m-d',$from).'" group by C.id order by B.bill_no');
        foreach ($R as &$r) {
            $r['ord'] = ++$ord;
            if (!preg_match('|^([^,]+),([^,]+),(.+)$|',$r['address_post_real'],$m)) $m = array('','','Москва',$r['address_post_real']);
            $r['_zip'] = $m[1];
            $r['_city'] = $m[2];
            $r['_addr'] = $m[3];
        } unset($r);
        $design->assign('postregs',$R);
        $design->ProcessEx('pop_header.tpl');
        $design->ProcessEx('errors.tpl');
        $design->ProcessEx('newaccounts/postreg_report.tpl');
        $design->ProcessEx('pop_footer.tpl');
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

                //Обновление списка документов
                BillDocument::dao()->updateByBillNo($_REQUEST['bill_no']);

                ob_end_clean();
                if(mysql_errno()){
                    echo 'MySQLErr';
                    break;
                }
                echo 'Ok';
                break;
            }
        }
        exit();
    }

    function newaccounts_make_1c_bill($client_tid){
        global $db, $design, $user;

        $bill_no = (isset($_GET["bill_no"]) ? $_GET["bill_no"] : (isset($_POST["order_bill_no"]) ? $_POST["order_bill_no"]: ""));
        $isRollback = isset($_GET['is_rollback']);

        $bill = null;

        // направляем на нужную страницу редактирования счета
        if(preg_match("/20[0-9]{4}-[0-9]{4}/i", $bill_no)) {
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

        $account = ClientAccount::find()->where('id = :id or client = :id', [':id' => $client_id])->one();
        $_SESSION['clients_client'] = $account->id;

        // инициализация
        $lMetro = \app\models\Metro::getList();
        $lLogistic = array(
            "none" => "--- Не установленно ---",
            "selfdeliv" => "Самовывоз",
            "courier" => "Доставка курьером",
            "auto" => "Доставка авто",
            "tk" => "Доставка ТК",
        );
        $design->assign("l_metro", $lMetro);
        $design->assign("l_logistic", $lLogistic);

        $storeList = array();
        foreach($db->AllRecords("select * from g_store where is_show='yes' order by name") as $l)
            $storeList[$l["id"]] = $l["name"];
        $design->assign("store_list", $storeList);
        $storeId = get_param_raw("store_id", "8e5c7b22-8385-11df-9af5-001517456eb1");
        


        require_once INCLUDE_PATH."clCards.php";
        require_once INCLUDE_PATH."1c_integration.php";

        $bm = new \_1c\billMaker($db);

        $pt = $account->price_type;

        $positions = array(
                'bill_no' =>$bill_no,
                'client_id'=>$account->id,
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
            $positions = $bm->calcOrder($account->client, $positions, $pt);
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
                'client_tid'=>$account->client,
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
                        \app\models\Bill::dao()->setManager($bill->GetNo(), $user->Get("id"));
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
                    StatModule::tt()->createTrouble(array(
                                'trouble_type'=>$_GET['tty'],
                                'trouble_subtype' => 'shop',
                                'client'=>$client_id,
                                'problem'=>@$positions['comment'],
                                'bill_no'=>$bill_no,
                                'time'=>date('Y-m-d')
                                ));
                }

                if(!$ttt && $bill) {
                    \app\models\Bill::dao()->setManager($bill->GetNo(), $user->Get("id"));
                }

                trigger_error2("Счет #".$bill_no." успешно ".($_POST["order_bill_no"] == $bill_no ? "сохранен" : "создан")."!");
                header("Location: ./?module=newaccounts&action=bill_view&bill=".$bill_no);
                exit();
            }else{
                trigger_error2("Не удалось создать заказ в 1С");
            }
        }


        $R=array(); StatModule::users()->d_users_get($R,array('manager','marketing'));
        $userSelect = array(0 => "--- Не установлен ---");
        foreach($R as $u) {
            $userSelect[$u["id"]] = $u["name"]." (".$u["user"].")";
        }
        $design->assign("managers", $userSelect);
        if($bill)
        $design->assign("bill_manager", \app\models\Bill::dao()->getManager($bill->GetNo()));

        $design->assign('show_adds',
                (in_array($account->client,array('all4net','wellconnect')) || $account->contract->contragent->legal_type != 'legal'));
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
            $ret = "";
            $prod = str_replace(array("*","%%"), array("%","%"), $db->escape($prod));

            $storeId = get_param_protected("store_id", "8e5c7b22-8385-11df-9af5-001517456eb1");
            
            $priceType = '739a53ba-8389-11df-9af5-001517456eb1';

            if (get_param_raw('priceType', 'NO') != 'NO')
            {
                $priceType =  ClientAccount::findOne($fixclient)->price_type;
            } else {
                $store_info = $db->GetRow('SELECT id, name FROM g_store WHERE id = "'. $storeId.'"');
            }

            foreach($db->AllRecords($q =
                        "
                        select * from (
                        (
                        SELECT if(d.name is null, concat(g.id,':'), concat(g.id,':',p.descr_id)) as id,
                        g.id as good_id,
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

                        where price_type_id = '".$priceType."'

                        order by length(g.name)
                        limit 50 )
                        union
                        (
                         SELECT  if(d.name is null, concat(g.id,':'), concat(g.id,':',s.descr_id)) as id,
                         g.id as good_id,
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
                    if (!empty($store_info))
                    {
                        $add_fields = "store_id:'".addcslashes($store_info['id'],"\\'")."',".
                        "store_name:'".addcslashes($store_info['name'],"\\'")."',";
                    } else {
                        $add_fields = '';
                    }

                    $ret .= "{".
                    "id:'".addcslashes($good['id'],"\\'")."',".
                    "good_id:'".addcslashes($good['good_id'],"\\'")."',".
                    "name:'".str_replace(array("\r", "\n"), "", addcslashes($good['name'],"\\'"))."',".
                    "description:'".addcslashes($good['description'],"\\'")."',".
                    "division:'".addcslashes($good['division'],"\\'")."',".
                    "price:'".addcslashes($good['price'],"\\'")."',".
                    "qty_free:'".addcslashes($good['qty_free'],"\\'")."',".
                    "qty_store:'".addcslashes($good['qty_store'],"\\'")."',".
                    "qty_wait:'".addcslashes($good['qty_wait'],"\\'")."',".
                    "art:'".addcslashes($good['art'],"\\'")."',".
                    "code:'".addcslashes($good['code'],"\\'")."',".
                    "store:'".addcslashes($good['store'],"\\'")."',".
                    $add_fields . 
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
            $prods_list = $bm->findProduct(trim($_GET['findProduct']), trim($_GET['priceType']));

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
        header('Content-Type: text/plain; charset="utf-8"');
        echo $ret;
        exit();
    }

    function newaccounts_docs($fixclient)
    {
        global $db, $design;

        $R = array();

        $dateFrom = new DatePickerValues('date_from', 'today');
        $dateTo = new DatePickerValues('date_to', 'today');
        $dateFrom->format = 'Y-m-d';$dateTo->format = 'Y-m-d';
        $from = $dateFrom->getDay();
        $to = $dateTo->getDay();

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


            $qNo = BillQRCode::decodeNo($r["code"]);

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
            $qrcode = BillQRCode::decodeFile($dir.$e);
            $qr = BillQRCode::decodeNo($qrcode);

            if($qrcode && $qr)
            {
                $billNo = $qr["number"];
                $clientId = NewBill::find_by_bill_no($billNo)->client_id;
                $type = $qr["type"]["code"];

                $id = $db->QueryInsert("qr_code", array(
                            "file"      => $e,
                            "code"      => $qrcode,
                            "bill_no"   => $billNo,
                            "client_id" => $clientId,
                            "doc_type"  => $type
                            ));

                exec("mv ".$dir.$e." ".STORE_PATH."documents/".$id.".pdf");
            }else{
                exec("mv ".$dir.$e." ".STORE_PATH."documents/unrecognized/".$e);
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
        foreach(BillQRCode::$codes as $code => $c)
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
        if(!file_exists($dirUnrec.$file)) {trigger_error2("Файл не найден!"); return;}

        $type = get_param_raw("type", "");
        if(!isset(BillQRCode::$codes[$type])) {trigger_error2("Ошибка в типе!"); return;}

        $number = get_param_raw("number", "");
        if(!preg_match("/^201\d{3}[-\/]\d{4}$/", $number)) { trigger_error2("Ошибка в номере!"); return;}

        global $db;


        $qrcode = BillQRCode::encode($type, $number);
        $qr = BillQRCode::decodeNo($qrcode);

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
            header('Content-Disposition: attachment; filename="'.iconv("UTF-8","CP1251",$fileName).'"');
            header("Content-Length: " . filesize($fPath));
            echo file_get_contents($fPath);
            exit();
        }else{
            trigger_error2("Файл не найден!");
        }
        //
    }

    function newaccounts_doc_file_delete($fixclient)
    {
        $dirPath = STORE_PATH."documents/";

        if(($id = get_param_integer("id", 0)) !== 0)
        {
            global $db;

            if($db->Query("delete from qr_code where id = '".$id."'"))
            {
                if (file_exists($dirPath.$id.".pdf")) unlink($dirPath.$id.".pdf");
                echo 'ok';
            } else echo 'Ошибка удаления!';


        } else echo 'Файл не задан!';

        exit();
    }

    private function getBillBonus($billNo)
    {
        global $db;

        $r = array();
        $q = $db->AllRecords("
                SELECT code_1c as code, round(if(b.type = '%', bl.sum*0.01*`value`, `value`*amount),2) as bonus
                FROM newbill_lines bl
                inner join g_bonus b on b.good_id = bl.item_id
                    and `group` = (select if(usergroup='account_managers', 'manager', usergroup) from newbill_owner nbo, user_users u where nbo.bill_no = bl.bill_no and u.id=nbo.owner_id) where bl.bill_no='".$billNo."'");
        if($q)
            foreach($q as $l)
                $r[$l["code"]] = $l["bonus"];
        return $r;
    }
}
?>
