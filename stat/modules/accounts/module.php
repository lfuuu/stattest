<?php

function month2days($period)
{
 $month=substr($period,5,2);
 //echo "<br>month=".$month."<br>";
 $monthes=array("01"=>31,"02"=>28,"03"=>31,"04"=>30, "05"=>31, "06"=>30, "07"=>31, "08"=>31, "09"=>30,
    "10"=>31, "11"=>30, "12"=>31);
 return $monthes[$month];
};

class m_accounts{
    var $actions=array('accounts_bills'=>array("Счета", "n,r,w,auto_bills",""),
               'accounts_invoices'=>array("Счета фактур", "n,r,w",""),
               'accounts_payments'=>array("Платежи", "n,r,w,delete,edit_saldo,correct",""),
               'accounts_report_balance'=>array("Баланс с клиентами","n,r",""),
               'accounts_view_balance'=>array("Просмотр таблицы баланса","n,r",""),
//               'accounts_account'=>array("Текущий баланс", "n,r,w",""),

               'accounts_not_paied_bills'=>array("Неоплаченные счета", "n,r,w",""),
               'accounts_reports'=>array("Бухгалтерские отчеты", "n,r,w",""),
               'accounts_del_payment'=>array("Удаление платежей", "n,r,w",""),
               'accounts_add_usd_rate'=>array("Ввод курса доллара","n,r,w",""),
               'accounts_import_payments'=>array("Импорт платежей","n,r,w",""),
    			);

    function m_accounts(){
    }
    function Install(){
        return $this->actions;
    }
    function GetPanel(){
        global $design,$user;
        $R=array();
        //добавляем в массив только те действия к которым есть права у юзера
        foreach($this->actions as $key=>$val){
            if (access($key,"r")){
              $R[]=array($val[0],'module=accounts&action='.$key);
            }
        }
        // Если нет доступа ни к одному действию то эту панель и выводить не требуется
        if (count($R)>0){
            // добавляем разделители
            array_splice($R,5,0,'');
            // выводим панель
            $design->AddMenu('Бухгалтерия',$R);

        }
    }
    function GetMain($action,$fixclient){
        global $design;
        if ($action=="default"){
            $action='accounts_payments';// действие по умолчанию
        }
        if (!access($action,'r')) return;
        call_user_func(array($this,$action),$fixclient);
        // Временно вызывам функцию отсюда? а вообще говоря каждая функция будет
        // иметь свой шаблон

    }
    function accounts_bills($fixclient){
        global $design, $db;
        $todo=get_param_protected('todo');

        switch ($todo){
        case "auto_bills_print":
            $this->auto_bills();
            break;
        case "showbills":
            require("showbills.php");
            break;


        default:
            $customer=get_param_protected('clients_client','');
            if ($customer=="" ) $customer="Для всех клиентов";
            $design->assign("customer",$customer);

            $this_year=date("Y");
            $this_month=date("m");

            $last_year = $this_year-1;
            $last_month=$this_month-1;
            if ($last_month<10) $last_month="0$last_month";

            if ($this_month=="01"){
                $fact_month="12";
                $fact_year="$last_year";
            }else{
                    $fact_month="$last_month";
                    $fact_year="$this_year";
            };
            $pre_month="$this_month";
            $pre_year="$this_year";

            $design->assign("this_year",$this_year);
            $design->assign("this_month",$this_month);
            $design->assign("fact_month",$fact_month);
            $design->assign("fact_year",$fact_year);
            $design->assign("pre_month",$pre_month);
            $design->assign("pre_year",$pre_year);

            $db->Query('select * from tech_routers order by router');
            $R=array(); while ($r=$db->NextRecord()) $R[$r['router']]=$r;
            $design->assign('routers',$R);

            $R=array(); $GLOBALS['module_users']->d_users_get($R,'manager');
            $design->assign('managers',$R);

            $design->AddMain("accounts/main.tpl");
            break;

        };

        //$design->assign('name_func',$this->actions['accounts_bills'][0]);
    }
    function accounts_invoices($fixclient){
        global $design,$db;
        // вывод счетов фактур
        //$todo=get_param_protected("todo","");
        $customer=get_param_protected('clients_client','');
        if (!$customer) {
            $now=date("Y-m-d");
            $design->assign('now',$now);
            $design->AddMain('accounts/print_invoices.tpl');
            return;
        }

        $query="SELECT i.*,SUM(p.sum_rub) as sum_pay from bill_invoices as i
                LEFT JOIN bill_payments as p ON (p.bill_no=i.bill_no)
                WHERE (i.client='$customer')
                GROUP BY i.invoice_no,i.bill_no,i.client,i.bill_date
                ORDER BY i.bill_no DESC,i.invoice_no ASC";
        $db->Query($query);
        $R=array();
        $sum=0;
        while ($r=$db->NextRecord()){
            if (!isset($R[$r['bill_no']])) $R[$r['bill_no']]=array('data'=>array(),'count'=>0,'sum'=>0);
            $r["invoice_date"]=convert_date($r["invoice_date"]);
            $r["pay_date"]=convert_date($r["pay_date"]);
            $R[$r['bill_no']]['count']++;
            $R[$r['bill_no']]['data'][]=$r;
            $R[$r['bill_no']]['sum']+=floatval($r['sum_plus_tax']);
            $sum+=floatval($r['sum_plus_tax']);
        }
        $design->assign("inv",$R);
        $design->assign("customer",$customer);
        $design->assign("acc_sum",$sum);

        $design->AddMain("accounts/list_inv.tpl");
    }
    function accounts_payments($fixclient){
        global $db,$design;
        $client=get_param_protected('clients_client',$fixclient);
        if (!$client) {trigger_error("Не выбран клиент");return;}
        $design->assign('client',$client);
        $todo=get_param_protected('todo');
        if ($todo == "rerate") {
        	$rate=get_param_protected("rate");
        	$pay_id	= get_param_protected("pay_id");
        	$client = get_param_protected("client");
//        	$db->Query("select * from bill_payments where client='{$client}' and id={$pay_id}");
//        	$r=$db->NextRecord();
        	$db->Query("update bill_payments set rate='{$rate}' where id={$pay_id}");
        	$db->Query("update bill_payments set sum_usd=sum_rub/rate where id={$pay_id}");
      	
        	$todo="";
        }
        if ($todo=="add_pay"){
            $pay_sum=get_param_protected('pay_sum');
            $pay_pp=get_param_protected('pay_pp');
            $pay_date=get_param_protected('pay_date');
            $db->Query("SELECT * from bill_currency_rate where date='$pay_date'");
            if ($db->mErrno != 0) {
				trigger_error("не установлен курс на $pay_date");
				return;
            }
            $r=$db->NextRecord();
            $sum_usd=$pay_sum/$r['rate'];

            make_balance_correction_db($client,$sum_usd);
            $query = "insert into bill_payments (client,payment_no, payment_date, sum_rub, sum_usd,rate) values ('$client','$pay_pp','$pay_date',$pay_sum,$sum_usd,{$r['rate']})";
            $db->Query($query);
            if ($db->mErrno != 0) {
				trigger_error(mysql_error());
				return;
            }


        };
        if ($todo=='cancel_bill'){
            $bill_no=get_param_protected('bill');
            $db->Query("select * from bill_bills where bill_no='$bill_no'");
            $r=$db->NextRecord();
            if ($r['type']!='advance' && $r['must_pay']) make_balance_correction_db($client,$r['sum']);
            $query="Update bill_bills set state='cancelled' where bill_no='$bill_no'";
            $db->Query($query);
            if ($db->mErrno != 0) {
             trigger_error(mysql_error());
             return;
            }
        };
        $made_cancel_payment=0;
        if ($todo=='cancel_payment' && access('accounts_del_payment',"w")){
            $id=get_param_protected('pay_id');
            $query="select * from bill_payments where id=$id and client='$client'";
            $db->Query($query);
            $r=$db->NextRecord();
            $bill_no=$r['bill_no'];
            if ($bill_no) {
	            $query="update bill_bills set state='ready' where bill_no='$bill_no' and client='$client'";
	            $db->Query($query);
    	        if ($db->mErrno != 0) {
        	    	trigger_error(mysql_error());
            		return;
            	}
	            make_balance_correction_db($client,-$r['sum_usd']);
            }
            $query="delete from bill_payments where id=$id and client='$client'";
            $db->Query($query);
            if ($db->mErrno != 0) {
             trigger_error(mysql_error());
             return;
            }
            $made_cancel_payment=1;

        }
        if ($todo=="bill_dont_payed"){
            $bill=get_param_protected('bill');
            $req="update bill_bills set state='ready' where bill_no='$bill'";
            $db->Query($req);
        };

		$db->Query("select sum from balance where client='$client'");
		$r=$db->NextRecord();
		$design->assign('balance',$r['sum']);

        //получаем инфу из таблицы сальдо
        //выводим базу сальдо
        $query="select * from saldo where client='$client'";
        $db->Query($query);
        if ($db->NumRows()==0){
                $db->Query("INSERT into saldo values ('$client','2001-01-01',0,0,0,0,'')");
                $db->Query($query);
        }
        if(!($r=$db->NextRecord())){ trigger_error("cannot connect to database".mysql_error());}
        $saldo=$r;
        $design->assign("saldo",$saldo);
        extract($r);

        //выбираем все счета этого клиента
        $query="SELECT * from bill_bills
            where client='$client' and NOT(state ='cancelled')
            order by bill_date desc";
        $db->Query($query);
        $bills=array();
        $bill_sum_usd=0;

    //  printdbg($saldo,'saldo2');
        while($r=$db->NextRecord()){
            $tmp_date=$r['bill_date'];
            $r['bill_date']=convert_date($r['bill_date']);
            $bills[]=$r;
            // суммируем счета только с даты последней сверки
            if($tmp_date>=$date_of_last_saldo){
                $bill_sum_usd+=$r['sum'];
            };
        };

    //  printdbg($saldo,'saldo3');
        // все платежи клиента
        $query="SELECT * from bill_payments
            where client='$client'
            order by payment_date";

        $db->Query($query);
        $payment=array();
        $payments_sum_usd=0;
        while ($r=$db->NextRecord()){
            $tmp_date=$r['payment_date'];
            $r['payment_date']=convert_date($r['payment_date']);
            $payment[$r['bill_no']][]=$r;
            // суммируем только платежи с даты последней сверки
            if($tmp_date>=$date_of_last_saldo){
                $payments_sum_usd+=$r['sum_usd'];
            }
        }

        $total_delta=0;
        $total_debt=0;
        for($i=0;$i<count($bills);$i++){
            $bill_no=$bills[$i]['bill_no'];
            if (isset($payment[$bill_no])){
                $bills[$i]['payments']=$payment[$bill_no];
                // посчитаем общую сумму платежей по этому счету вычисляем переплату-недоплату
                $sum_pays_for_bill=0;
                foreach( $payment[$bill_no] as $pays){
                    $sum_pays_for_bill+=$pays['sum_usd'];
                }
                $delta_sum=$sum_pays_for_bill-$bills[$i]['sum'];
                // если дельта ментше 3% то считаем ее нулевой
                if ($bills[$i]['sum']!=0 && (abs($delta_sum)/$bills[$i]['sum'])<=0.03) $delta_sum=0;
                $bills[$i]['payments_sum']=$sum_pays_for_bill;
                $bills[$i]['delta_sum']=round($delta_sum,2);
                $total_delta+=$bills[$i]['delta_sum'];
                unset($payment[$bill_no]);
            } else $bills[$i]['payments']=array();
        }

		$sum_pays_for_bill=0;
		foreach ($payment as $bn=>$pay) {
			foreach ($pay as $p) {
				$sum_pays_for_bill+=$p['sum_usd'];
			}
			$bills[$i]['payments_sum']=$sum_pays_for_bill;
			$bills[$i]['delta_sum']='';
			$bills[$i]['payments']=$pay;
			$i++;
		}
        // если мы удаляли платежи то надо правильно обновить сальдо - непроведенных платежей
        // или у нас задана функция пересчитать переплаты
        if ($made_cancel_payment==1 or $todo="over_pays"){
            //обновляем сальдо
            $query="Update saldo set saldo=$total_delta where client='$client'";
            $db->Query($query);

            //повторно получаем инфу из таблицы сальдо
            $query="select * from saldo where client='$client'";
            $db->Query($query);
            if(!($r=$db->NextRecord())){ trigger_error("cannot connect to database".mysql_error());}
            $saldo=$r;
            $design->assign("saldo",$r);
            extract($r);
        };
		
        $design->assign('bill_sum_usd',$bill_sum_usd);
        $design->assign('payments_sum_usd',$payments_sum_usd);

        $design->assign('bills',$bills);

        //echo "<pre>";print_r($bills);echo "</pre>";
        $debet=round(($payments_sum_usd-$bill_sum_usd+$fix_saldo),2);

        //printdbg($saldo,'saldo');
        //echo "pay-$payments_sum_usd <br> bill-$bill_sum_usd<br>";

        //echo "fix saldo{$saldo['fix_saldo']}<br>";
        //echo "debet".($payments_sum_usd-$bill_sum_usd+$saldo['fix_saldo'])."<br>";
        //echo "debet_round=$debet";

        $design->assign('debet',$debet);

        $design->AddMain("accounts/payments_test.tpl");
    }
    function accounts_not_paied_bills($fixclient){
        global $design;
        $design->assign('name_func',$this->actions['accounts_not_paied_bills'][0]);
    }
    
    function accounts_view_balance($fixclient){
        global $db,$design;
        $db->Query('select b.sum,c.* from balance as b INNER JOIN clients as c on c.client=b.client order by c.client');
        $R=array(); while ($r=$db->NextRecord()) $R[]=$r;
        $design->assign('balance',$R);
        $design->AddMain('accounts/balance_view.tpl');

    }

    function accounts_report_balance($fixclient){
        global $db,$design,$user;
        set_time_limit(0);
        $save=get_param_protected('save');

        $client=get_param_protected('client');
        $todo=get_param_protected('todo');
        if ($todo=="show_payments") {
            if ($client!=''){
                $_GLOBAL['fixclient']=$client;
                $_SESSION['clients_client'] = $client;
            };
            header("Location: index.php?module=accounts&action=accounts_payments");
            return;
        }

        $manager=get_param_protected('manager');
        if ($manager=="") $manager=$user->_Login;
        if ($manager=="all"){
            $where ="";
        } else {
            $where = " and manager='$manager' ";
        }

        $query="SELECT c.client, c.company,c.nal,b.sum as sum from clients as c LEFT JOIN balance as b on c.client=b.client where c.status='work' $where order by c.client  ";
        $db->Query($query);
        $clients=array();
        $firms=array();
        $nal=array();
        $balance_table = array();
        while ($r=$db->NextRecord()){
            $clients[]=$r['client'];
            $firms[$r['client']]=$r['company'];
            $nal[$r['client']]=$r['nal'];
            $balance_table[$r['client']]=$r['sum'];
        }
        $balance=array();
        $total_balance=0;
        $total_debet=0;
        $date_balanceF=get_param_protected('date_balanceF','2001-01-01');
        $date_balanceT=get_param_protected('date_balanceT',date('Y-m-d'));

        foreach ($clients as $client){
            // получаем дату сверки баланса и сальдо на эту дату
            $query="SELECT * from saldo where client='$client'";
            $db->Query($query);
            if ($db->NumRows()==0){
                $db->Query("INSERT into saldo values ('$client','2001-01-01',0,0,0,0,'')");
                $db->Query($query);
            }
            $saldo=$db->NextRecord();
            $date_saldo=$saldo['date_of_last_saldo'];
            $fix_saldo=$saldo['fix_saldo'];

            // все счета клиента начиная с даты последней сверки
            $query="SELECT * from bill_bills
                    where client='$client' and NOT(state ='cancelled')
                    and bill_date>='$date_balanceF'
                    and bill_date>='$date_saldo'
                    and bill_date<='$date_balanceT'
                    order by bill_date desc";

            $db->Query($query);
            $bills=array();
            $bill_sum_usd=0;
            while($r=$db->NextRecord()){
                $r['bill_date']=convert_date($r['bill_date']);
                $bills[]=$r;
                $bill_sum_usd+=$r['sum'];
            };
            // все платежи клиента по счетам с момента последней сверки
            $query="SELECT * from bill_payments
                    where client='$client'
                    and payment_date>='$date_balanceF'
                    and payment_date>='$date_saldo'
                    and payment_date<='$date_balanceT'
                    order by payment_date";

            $db->Query($query);

            $payment=array();
            $payments_sum_usd=0;
            while ($r=$db->NextRecord()){
                $r['payment_date']=convert_date($r['payment_date']);
                $payment[$r['bill_no']][]=$r;
                $payments_sum_usd+=$r['sum_usd'];
            };

            $debet=($date_balanceT>$date_saldo?$fix_saldo:0)+$payments_sum_usd-$bill_sum_usd;
            $debet=round($debet,2);
            $balance[]['data']=array(
                    'client'=>$client,
                    'saldo'=>$debet,
                    'firma'=>$firms[$client],
                    'date_saldo'=>$date_saldo,
                    'fix_saldo'=>$fix_saldo,
                    'nal'=>$nal[$client],
                    'balance_table'=>$balance_table[$client],
                    );
            if ($save) {
                $db->Query("select * from balance where client='{$client}'");
                if (!($r=$db->NextRecord())) $db->Query("insert into balance (client) values ('{$client}');");
                $db->Query("update balance set sum={$debet} WHERE client='{$client}'");
            }
            $total_balance+=$debet;
            if ($debet<0) $total_debet-=$debet;
        }

        $design->assign('balance',$balance);
        $design->assign('total_debet',$total_debet);
        $design->assign('nal',$nal);
        $design->assign('total_balance',$total_balance);
        $design->assign('date_balanceF',$date_balanceF);
        $design->assign('date_balanceT',$date_balanceT);
        $design->AddMain("accounts/balance.tpl");
    }

    function accounts_reports($fixclient){
        global $design;

        $report=get_param_protected('report');
        //echo "varibale report='$report' <br>";
        switch ($report){
            case "sale_book":
                $this->sale_book();
                break;
            case "services_month":
            case "services_quartal":
                $this->report_services($report);
                break;
            case "report_month":
                $this->report_services_month();
                break;

            default:
                $design->AddMain('accounts/reports.tpl');
                break;

        }

    }
    function sale_book(){
        global $design, $db;
        //error_reporting(E_ALL);
        $firma=get_param_protected('firma');
        $provod=get_param_protected('provod');
        $provod_where='';
        switch ($provod){
            case 'pr': $provod_where='((b.type in (0,3)) and (b.payment_no not like "п%"))';
                break;
            case 'all':
            default: $provod_where='(b.type in (0,1,2,3))';
                break;

        }

        if ($firma == "mcn" or $firma=='markomnet' or $firma=='all'){
            // выбираем работающих клиентов
            $firma_where=" and c.firma='$firma' ";
            if ($firma=='all') $firma_where =' ';
            $period=get_param_protected('period');
            if ($period=='') return;

            $db->Connect();
            $query="SELECT c.client as client, c.company_full as company_full,
                    c.inn as inn, c.kpp as kpp,
                    b.sum_rub as sum_rub, b.payment_no as payment_no,
                    b.payment_date as payment_date, b.bill_no as bill_no
                FROM clients as c, bill_payments as b
                WHERE  b.payment_date>='$period-01'
                    and b.payment_date<='$period-31'
                    and $provod_where
                    $firma_where
                    and c.client=b.client
                ORDER BY b.payment_date";
            $db->Query($query);
            $payments=array();
            $total_sum=0;
            while ($r=$db->NextRecord()){
                $payments[]=$r;
                $total_sum+=$r['sum_rub'];

            }
            // теперь к каждолму платежу находим счета фактур
            foreach ($payments as $key=>&$pay){
                    $query="SELECT * from bill_invoices
                        where bill_no='{$pay['bill_no']}'
                        order by invoice_no";
                    $db->Query($query);
                    $invoice=array();
                    while ($r=$db->NextRecord()){
                        $invoice[]=$r;

                    }
                    //printdbg($invoice,'invoice');
                    $pay['invoice']=$invoice;

            }




            //printdbg($payments,'payments');
            $design->assign('period',$period);
            $total_sum=number_format($total_sum, 2, ',', ' ');
            $design->assign('total',$total_sum);
            $design->assign('firma', $firma);

            $design->assign('payments', $payments);
            $design->AddMain('accounts/sale_book.tpl');
        }
        return;

    }
    function report_services($report){
        GLOBAL $design,$db;
        $total_zalog=0;
        $clients=array();
        $total_payments=0;
        $total_services=0;
        //echo "вошли в сервисес репорт $report<br>";
        //printdbg($_POST);
        $firma=get_param_protected('firma');
        $firma_where=" and c.firma='$firma' ";
            if ($firma=='all') $firma_where =' ';


        $provod=get_param_protected('provod');
        $provod_where='';
        switch ($provod){
            case 'pr': $provod_where='((b.type in (0,3)) and (b.payment_no not like "п%"))';
                break;
            case 'all':
            default: $provod_where='(b.type in (0,1,2,3))';
                break;

        }


        $period=get_param_protected('period');
            if ($period=='') return;
        $period_from="$period-01";
        $period_to="$period-31";


        //echo "report $report <br>";
        if ($report == 'services_quartal'){

            $period_to=get_param_protected('period_to');
            //echo "period to  from report quartal $period_to <br>";
            if ($period_to=='') return;
            $period_to.="-31";
        }
        //echo "from $period_from to $period_to <br>";


        $db->Connect();
        $query="SELECT  c.client as client, c.company_full as company_full
            FROM clients as c
            WHERE 1 $firma_where
            ORDER BY c.client";
        //  echo "<br>$query<br>";

            $db->Query($query);
            $payments=array();
            $total_sum=0;
            while ($r=$db->NextRecord()){
                $clients[]=$r;


            }


        foreach ($clients as $key=>&$client){

            $id=$client['client'];

            $query="SELECT  i.invoice_no as invoice_no,
                    i.sum_plus_tax as sum_plus_tax,
                    i.invoice_date as invoice_date
                FROM  bill_invoices as i, bill_payments as b
                WHERE   b.bill_no=i.bill_no
                    and $provod_where
                    and ( i.invoice_date>='$period_from'
                    and i.invoice_date<='$period_to')
                    and i.client='$id'";

            $db->Query($query);
            $invoices=array();
            $sum=0;
            $invoice_numbers='';
            while ($r=$db->NextRecord()){
                $invoices[]=$r;
                $sum+=$r['sum_plus_tax'];
                $invoice_numbers.="'".$r['invoice_no']."', ";
            }
            $invoice_numbers="(".substr($invoice_numbers,0,strlen($invoice_numbers)-2).")";

            $client['invoice']=$invoices;
            $client['sum']=$sum;

            // считаем залоги
            if ($invoice_numbers<>"()"){
            $query="SELECT sum(sum_plus_tax) as zalog
                FROM    bill_invoice_lines
                WHERE   invoice_no in $invoice_numbers
                    AND item LIKE 'Залог%'";
            $db->Query($query);
            $z=$db->NextRecord();
            if (!isset($z['zalog'])) $z['zalog']=0;
            $client['zalog']=$z['zalog'];
            $total_zalog+=$z['zalog'];
            }else{$client['zalog']=0;}



        // платежи
            $query="SELECT payment_no, payment_date, sum_rub
                from bill_payments as b
                where   b.payment_date>='$period_from'
                    and b.payment_date<='$period_to'
                    and b.client='$id'
                    and $provod_where";
            $db->Query($query);
            $payments=array();
            $total=0;
            while ($r=$db->NextRecord()){
                $payments[]=$r;
                $total+=$r['sum_rub'];
            }
            $client['payments']=$payments;
            $client['total']=$total;
            $client['debet']=round($client['total']-$client['sum'],2);
            $total_payments+=$client['total'];
            $total_services+=$client['sum'];




        }

        //printdbg($clients,'clients');
        $design->assign('period',"$period_from/$period_to");
        $design->assign('firma',$firma);

        $total_debet=number_format($total_payments-$total_services, 2, ',', ' ');
        $total_payments=number_format($total_payments, 2, ',', ' ');
        $total_services=number_format($total_services, 2, ',', ' ');
        $total_zalog=number_format($total_zalog, 2, ',', ' ');
        $design->assign('total_zalog',$total_zalog);
        $design->assign('total_payments',$total_payments);
        $design->assign('total_services',$total_services);
        $design->assign('total_debet',$total_debet);

        $design->assign('clients',$clients);
        $design->AddMain('accounts/service_report.tpl');



    }
    function report_services_month(){
        GLOBAL $design,$db;
        $total_zalog=0;
        $clients=array();
        $total_payments=0;
        $total_services=0;
        //echo "вошли в сервисес репорт $report<br>";
        //printdbg($_POST);
        $firma=get_param_protected('firma');
        $firma_where=" and c.firma='$firma' ";
            if ($firma=='all') $firma_where =' ';


        $provod=get_param_protected('provod');
        $provod_where='';
        switch ($provod){
            case 'pr': $provod_where='((b.type in (0,3)) and (b.payment_no not like "п%"))';
                break;
            case 'all':
            default: $provod_where='(b.type in (0,1,2,3))';
                break;

        }
$db->Connect();

        $period=get_param_protected('period');
            if ($period=='') return;
        $period_from="$period-01";
        $period_to="$period-".month2days($period);
        $rate=0;
        $sql="SELECT rate from bill_currency_rate where date='$period_to'";
        $db->Query($sql);
        $r=$db->NextRecord();
        if (!isset($r['rate']) or $r['rate']==0){
            trigger_error("НЕ установлен курс доллара на $period_to");
            return;
        };
        $rate=$r['rate'];



        $query="SELECT  c.client as client, c.company_full as company_full
            FROM clients as c
            WHERE 1 $firma_where
            ORDER BY c.client";
        //  echo "<br>$query<br>";

            $db->Query($query);
            $payments=array();
            $total_sum=0;
            while ($r=$db->NextRecord()){
                $clients[]=$r;


            }

        $result=array();
        $total['zalog']=0;
        $total['new']=0;
        $total['traf']=0;
        $total['ab']=0;
        $total['else']=0;
        $total['payments']['bnal']=0;
        $total['payments']['nal']=0;
        $total['payments']['black']=0;


        foreach ($clients as $key=>&$client){

            $id=$client['client'];

            $query="SELECT b.bill_no, l.sum, l.item, b.state, c.nal
                    FROM bill_bill_lines as l, bill_bills as b , clients as c
                    WHERE b.bill_no=l.bill_no
                    AND c.client=b.client
                    AND (l.item_date>='$period_from')
                    AND (l.item_date<='$period_to')
                    AND (b.must_pay=1 or b.state='payed')
                    AND (b.state<>'cancelled')
                    AND b.client='$id'";

            $db->Query($query);
            $services=array();
            $sum=0;
            $invoice_numbers='';
            $services['zalog']=0;
            $services['new']=0;
            $services['traff']=0;
            $services['ab']=0;
            $services['else']=0;
            while ($r=$db->NextRecord()){
      //      printdbg($r);
      //      printdbg(strpos($r['item'],'Абонентская'),'strpos');
                if (strpos($r['item'],'Залог')!==FALSE)$services['zalog']+=$r['sum'];
                elseif (strpos($r['item'],'Подключение')!==FALSE)$services['new']+=$r['sum'];
                elseif (strpos($r['item'],'Превышение')!==FALSE)  $services['traff']+=$r['sum'];
                elseif (strpos($r['item'],'Абонентская')!==FALSE) $services['ab']+=$r['sum'];
                else $services['else']+=$r['sum'];

            };
            $services['zalog']=round($services['zalog']*$rate,2);
            $services['new']=round($services['new']*$rate,2);
            $services['traff']=round($services['traff']*$rate,2);
            $services['ab']=round($services['ab']*$rate,2);
            $services['else']=round($services['else']*$rate,2);
            $services['total']=$services['else']+$services['ab']+$services['traff']+$services['new']+$services['zalog'];


            $client['services']=$services;
        //    printdbg($services,"services");
            $total['zalog']+=$services['zalog'];
            $total['new']+=$services['new'];
            $total['traf']+=$services['traff'];
            $total['ab']+=$services['ab'];
            $total['else']+=$services['else'];


            $sql="SELECT sum(if(type=0,(sum_rub),0)) as bnal, sum(if(type=3,sum_rub,0)) as nal, sum(if((type=1 or type=2),sum_rub,0)) as black from bill_payments
                    WHERE   client='$id'
                    AND     payment_date>='$period_from'
                    AND     payment_date<='$period_to'";
            $db->Query($sql);
            $r=$db->NextRecord();
            $client['payments']['nal']=$r['nal'];
            $client['payments']['bnal']=$r['bnal'];
            $client['payments']['black']=$r['black'];
            $client['payments']['total']=$r['black']+$r['bnal']+$r['nal'];

            $total['payments']['bnal']+=$r['bnal'];
            $total['payments']['nal']+=$r['nal'];
            $total['payments']['black']+=$r['black'];



        };
        $res=array();
        foreach($clients as $key=>$client){
            if(!($client['payments']['total']==0 and $client['services']['total']==0)) $res[$key]=$client;
        }


$total['total']=($total['zalog']+$total['new']+$total['traf']+$total['ab']+$total['else']);
$total['payments']['total']=$total['payments']['bnal']+$total['payments']['nal']+$total['payments']['black'];
$balance=$total['payments']['total']-$total['total'];



        //printdbg($clients,'clients');
        $design->assign('period',"$period_from/$period_to");
        $design->assign('firma',$firma);
        $design->assign('rate',$rate);
        $design->assign('balance',$balance);
        $design->assign('total',$total);
        $design->assign('clients',$res);

        $design->AddMain('accounts/service_report_month.tpl');


    }

    function accounts_del_payment($fixclient){
        global $design;
        $design->assign('name_func',$this->actions['accounts_del_payment'][0]);
    }
    function accounts_account($fixclient){
        global $design;
        $design->assign('name_func',$this->actions['accounts_account'][0]);
    }
    function accounts_add_usd_rate($fixclient){
        global $db,$design;
        //echo "вошли в функцию ввода курса<br>";
        $todo=get_param_protected('todo');
        $date=get_param_protected('date');
        $rate=get_param_protected('rate');
        $error='';
        if($todo=="add_rate")
        {
            if (!is_numeric($rate)){
             //echo"не число '$rate'";
             trigger_error("Введено не числовое значение курса доллара");
             return;
            };
            $query="SELECT * from bill_currency_rate where date='$date'";
            $db->Query($query);

            if ($db->NumRows()>0)
            {
                $r=$db->NextRecord();
                $error="на $date уже установлен курс доллара<b> ${r['rate']} </b><br>";
            }else{
                $query="INSERT into bill_currency_rate values(NULL,'$date','USD',$rate)";
                $db->Query($query);
                if ($db->mErrno>0)
                {
                    $error="Ошибка вставки в базу<br>$query<br>".mysql_error();
                }else $error="Курс на <b>$date</b> установлен, и равен <b>$rate</b><br>";
            }
        };
        $design->assign('message',$error);
        $design->AddMain('accounts/currency.tpl');
    }

    function accounts_import_payments(){
        GLOBAL $design, $DOCUMENT_ROOT;
        $todo=get_param_protected('todo');
        IF ($todo=="import"){
            if (isset($_FILES['payments'])) $payments=$_FILES['payments'];
            else return false;
            $today=date("Ymd");

            $script_name=$_SERVER['SCRIPT_NAME'];
            $dir=dirname($script_name);
            $path=$_SERVER['DOCUMENT_ROOT'].$dir;

            $newname="$path"."/modules/accounts/1c/".$payments['name'];
        //  echo "<br>".$newname."<br>";
            if (!move_uploaded_file($payments['tmp_name'], $newname)){
              trigger_error("Не могу переместить файл".$payments['error']."<br>");
              return false;
            };
            //$design->assign('filename',$payments['name']);
            //$design->AddMain('accounts/link_auto_pay.tpl');
//$total['payments']['bnal']+=$r['bnal'];
  //          $total['payments']['nal']+=$r['nal'];
    //        $total['payments']['black']+=$r['black'];


        }
            $todo=get_param_protected('todo');
            $day=date("d");
            $month=date('m');
            $year=date('Y');
        //  printdbg("$day-$month-$year");
            if ($todo=='search'){
                $day=get_param_protected('day');
                $month=get_param_protected('month');
                $year=get_param_protected('year');
                $reg="|.+$day.+$month.+$year.+|";
            //  printdbg($reg);
            }
            $script_name=$_SERVER['SCRIPT_NAME'];
            $dir=dirname($script_name);
            $path=$_SERVER['DOCUMENT_ROOT'].$dir;
            $path="$path"."/modules/accounts/1c/";

        //  printdbg($path);
            if (!($dir=opendir($path))) die("ne mogu otkrit dir");
            $files=array();
            $serchers=array();
        //  printdbg($dir);
            while(($d=readdir($dir)) !== false ){
        //      printdbg($d);
                if (is_file($path.$d)) {
                    if ($todo=='search'){
                        if (preg_match($reg,$d)){
                            $serchers[$d]=1;
                        }else {$serchers[$d]=0;};
                    };
                    $files[]=$d;
                }
            }

            $design->assign('day',$day);
            $design->assign('month',$month);
            $design->assign('year',$year);
            $design->assign('searches',$serchers);


            $design->assign('files',$files);
            $design->AddMain('accounts/upload.tpl');



    }
// Дополнительные функции которых нет в меню

    //

    function auto_bills(){
    GLOBAL $design,$db;
    $managers=array('pma',"bnv","gms");
    $now=date("Y-m")."-01";
    $pages=array();
    foreach ($managers as $m){
        $req="SELECT count(*) as num
            from bill_bills as b, clients as c
            where b.bill_date>='$now'
            AND c.client=b.client
            AND c.manager='$m'
            AND b.state='ready'";
        //printdbg($req);
        $db->Query($req);
        $row=$db->NextRecord();
        $c=(int)($row['num']/90)+1;
        //printdbg($row);
        for($i=0;$i<$c;$i++){$pages[$m][]=$i*90;};


    }
    $design->assign('managers',$pages);
    $design->AddMain('accounts/auto_bills.tpl');



    }
    function new_bill(){}//новый счет
    function bill_for_new_connection(){}
    function bill_for_new_phone(){}//
    function edit_bill(){}//





}
?>
