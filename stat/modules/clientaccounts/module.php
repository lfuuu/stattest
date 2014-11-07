<?

	
class m_clientaccounts extends IModule{
	private static $object;
	function do_include() {
		static $inc = false;
		if ($inc) return;
		$inc = true;
		include_once INCLUDE_PATH.'bill.php';
		include_once INCLUDE_PATH.'uniteller.php';
		//include_once INCLUDE_PATH.'payments.php';
	}
	function GetMain($action,$fixclient){
		$this->do_include();
		if (!$action || $action=='default') $action='bill_list';
		if (!isset($this->actions[$action])) return;
		$act=$this->actions[$action];
		if ($act!=='' && !access($act[0],$act[1])) return;
		call_user_func(array($this,'clientaccounts_'.$action),$fixclient);		
	}

	function clientaccounts_bill_list($fixclient,$get_sum=false){
		global $design, $db, $user, $fixclient_data;
		if(!$fixclient)
			return;

		set_time_limit(0);
		
		$_SESSION['clients_client'] = $fixclient;

		$design->assign('client',$db->GetRow("select * from clients where id='{$fixclient_data['id']}'"));

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
						?	'IF(bill_date >= "'.$sum[$fixclient_data['currency']]['ts'].'",1,0)'
						:	'1'
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
						?	'IF(P.payment_date>="'.$sum[$fixclient_data['currency']]['ts'].'",1,0)'
						:	'1'
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
		krsort($sk);

		foreach($sk as $bn){
			krsort($bn);
			foreach($bn as $billno=>$v)
				$buf[$billno] = $R[$billno];
		}

		$R = $buf;
		
		#krsort($R);
		$design->assign('billops',$R);
		$design->assign('sum',$sum);
		$design->assign('sum_cur',$sum[$fixclient_data['currency']]);

		$design->AddMain('clientaccounts/bill_list.tpl');
	}

	function clientaccounts_bill_view($fixclient){
		global $design, $db, $user, $fixclient_data;
        //stat bills
		if(isset($_POST['bill_no']) && preg_match('/^\d{6}-\d{4}-\d+$/',$_POST['bill_no'])){

            //set doers
			if(isset($_POST['select_doer'])){
				$d = (int)$_POST['doer'];
				$db->Query("select name from courier where id=".$d);
				$row = $db->NextRecord(MYSQL_ASSOC);
				$db->Query("update newbills set courier_id=".$d." where bill_no='".$_POST['bill_no']."'");
				$db->Query("insert into log_newbills set `bill_no` = '".$_POST['bill_no']."', ts=now(), user_id=".$user->Get('id').", comment='�������� ������ ".$row['name']."'");
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
				$db->Query("insert into log_newbills set `bill_no` = '".$_POST['bill_no']."', ts=now(), user_id=".$user->Get('id').", comment='�������� ������ ".$row['name']."'");
				unset($row);
			}elseif(isset($_POST['select_nal'])){
				$n = addcslashes($_POST['nal'],"\\\\'");
				$db->Query("update newbills set nal='".$n."' where bill_no='".$_POST['bill_no']."'");
			}
		}

		$bill_no=get_param_protected("bill");
		if(!$bill_no)
			return;
		$bill = new Bill($bill_no);
		if(get_param_raw('err')==1)
			trigger_error2('���������� �������� ������ ��-�� ����������� �����');

		$design->assign('bill',$bill->GetBill());
		$design->assign('bill_lines',$L = $bill->GetLines());


		$period_date = get_inv_date_period($bill->GetTs());

		$r = $bill->Client();
		ClientCS::Fetch($r);

        $r["client_orig"] = $r["client"];

        /*
        if(access("clients", "read_multy")) 
            if($r["type"] != "multi"){
			trigger_error2('������ � ������� ���������');
			return;
        }
        */
        if($r["type"] == "multi" && isset($_GET["bill"])){
            $ai = $db->GetRow("select fio from newbills_add_info where bill_no = '".$_GET["bill"]."'");
            if($ai){
                $r["client"] = $ai["fio"]." (".$r["client"].")";
            }
        }
		$design->assign('bill_client',$r);

		$design->AddMain('clientaccounts/bill_view.tpl');

	}

	function clientaccounts_pay(){
		global $db, $design, $fixclient_data;
		$shop_id = '5879813581-557';
		$password = 'nxbIHHJYU3GWVJFtYxtcpfw2dp1Pte15f0iNqEe74TifwXaXz92jXes7rQrcL54b0FsL1pXVGHZKfuFZ';
		$client_id = $fixclient_data['id'];
		$order_id = '1';
		$back_ok = WEB_ADDRESS.WEB_PATH.$design->LINK_START.'module=clientaccounts&action=pay&res=ok';
		$back_err = WEB_ADDRESS.WEB_PATH.$design->LINK_START.'module=clientaccounts&action=pay&res=err';
		$pay_sum = '';
		$signature = '';
		$res = '';
		$pay_type = '';
		if (isset($_POST['pay_card'])){
			$pay_sum = number_format($_POST['sum'], 2, '.', '');			
			$order_id = $db->QueryInsert('payments_orders', array('type'=>'card','client_id'=>$client_id,'sum'=>$pay_sum));
			if ($order_id > 0){ $pay_type = 'card';}
			$signature = strtoupper(md5($shop_id . $order_id . $pay_sum . $password));
		}elseif(isset($_GET['res']) && $_GET['res'] == 'ok'){
			$gw = new uniteller();
			$gw->updateOrderInfo($_GET['Order_ID']);
			$res = 'ok';
		}elseif(isset($_GET['res']) && $_GET['res'] == 'err'){
			$gw = new uniteller();
			$gw->updateOrderInfo($_GET['Order_ID']);
			$res = 'err';
		}
		$design->assign('shop_id',$shop_id);
		$design->assign('order_id',$order_id);
		$design->assign('client_id',$client_id);
		$design->assign('signature',$signature);
		$design->assign('pay_type',$pay_type);
		$design->assign('pay_sum',$pay_sum);
		$design->assign('back_ok',$back_ok);
		$design->assign('back_err',$back_err);
		$design->assign('res',$res);
		$design->AddMain('clientaccounts/pay.tpl');
	}
	function clientaccounts_rawpayments(){
		global $design, $db;
		$payments = $db->AllRecords('select * from payments_orders order by id desc');
		foreach($payments as $k => $v){
//			$data = unserialize($v['details']);
//			$payments[$k]['comment'] = $data['recommendation']; 
		}
		$design->assign('payments',$payments);
		$design->AddMain('clientaccounts/rawpayments.tpl');
	}

	
	function clientaccounts_update_status(){
		global $design, $db;
		
		$gw = new uniteller();
		$gw->updateOrderInfo(intval($_GET['order_id']));		
									
		header('location: ?module=clientaccounts&action=rawpayments');
		exit();
	}
		
	function clientaccounts_cancel(){
		global $design, $db;
		
		$gw = new uniteller();
		$gw->cancelOrder(intval($_GET['order_id']));

		header('location: ?module=clientaccounts&action=rawpayments');
		exit();
	}	
	
	function clientaccounts_details(){
		global $design, $db;
		
		$order = $db->GetRow('select * from payments_orders where id='.intval($_GET['order_id']));
		$data = unserialize($order['details']);
		echo "<pre>";
		print_r($data);		
		echo "</pre>";
		die();
	}		
}
?>
