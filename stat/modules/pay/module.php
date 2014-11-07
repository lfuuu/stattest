<?
class m_pay extends IModule {
	var $config_webmoney = array(
			'Z370744040471'		=> array('currency'=>'USD','title'=>'WMZ - Доллары США','secret'=>'Tx$U=/%iOJ${9b','server'=>array('shepik','minho','tiberis')),
			'R440114909513'		=> array('currency'=>'RUR','title'=>'WMR - Рубли','secret'=>'Tx$U=/%iOJ${9b','server'=>array('shepik','minho','tiberis')),
			);
		
	function getWebmoneyConfig($purse) {
		if (!isset($this->config_webmoney[$purse])) return null;
		$r = $this->config_webmoney[$purse];
		if (array_search(SERVER,$r['server'])===false) return null;
		return $r;
	}

	function GetMain($action,$fixclient){
		include_once INCLUDE_PATH.'bill.php';
		include_once INCLUDE_PATH.'payments.php';
		if (!$action) $action=$menu[0][1];
		return parent::GetMain($action,$fixclient);
	}
	function showForm ($fixclient,$paymode,$page) {
		global $design,$db;
		$design->assign('paymode',$paymode);
		$design->AddMain('pay/'.$page.'.tpl');
	}
/*
	function pay_default ($fixclient) {
		global $design,$db,$fixclient_data;	
		$design->assign('totals',$GLOBALS['module_newaccounts']->getClientTotals($fixclient_data));
		$design->AddMain('pay/info.tpl');
	}
*/
	function pay_demoyandex($fixclient) { return $this->pay_yandex($fixclient,'demo'); }
	function pay_yandex($fixclient,$paymode = 'real') { 
		global $design,$db;
		$design->assign('paymode',$paymode);
		$design->AddMain('pay/yandex.tpl');
	}
	function pay_webmoney($fixclient) {
		global $design,$db,$fixclient_data;
		$db->Query('select W.*,P.id as payment_id,P.currency as pay_curr,P.payment_rate,round(P.sum_rub/P.payment_rate,2) as pay_sum from newpayments_webmoney as W LEFT JOIN newpayments as P ON P.id=W.payment_id where W.client_id='.$fixclient_data['id'].' and W.status!="reserved"');
		$R = array(); while ($r = $db->NextRecord()) $R[]=$r;
		$design->assign('operations',$R);
		
		$S = array('client_id'=>$fixclient_data['id'],'status'=>'reserved');
		if (!($r=$db->QuerySelectRow('newpayments_webmoney',$S))) {
			$S['keyword'] = password_gen(6);
			$S['id'] = $db->QueryInsert('newpayments_webmoney', $S);
			$r = $S;
		}
		$design->assign('wmpay',$r);
		$design->assign('wmconfig',$this->config_webmoney);
		$design->AddMain('pay/webmoney.tpl');
	}
	function webmoneySetStatus($status) {
		global $db;
		$v = $this->__webmoneySetStatus($status);
		if (!is_array($v)) return $v;
		if ($v[0]!=0) $db->QueryUpdate('newpayments_webmoney','id',array('id'=>$v[0],'status'=>($status=='payed'?'error_payed':'error'),'extra'=>$v[1].' '.$v[2]));
		return $v[1];
	}
	function __webmoneySetStatus($status) {
		global $db;
		$r = array('id'=>get_param_integer('LMI_PAYMENT_NO'),'keyword'=>get_param_raw('keyword'));
		$r = $db->QuerySelectRow('newpayments_webmoney',$r);
		if (!$r) return array(0,'WMPayment not found');
		if ($r['status']=='payed') return array(0,'WMPayment not found');
		if  ($r['status']=='error' || $r['status']=='error_payed') return 'error';
		if ($status=='check') {
			if ($r['status']!='reserved') return array($r['id'],'status is not reserved','');
			$r['sum'] = floatval(get_param_raw('LMI_PAYMENT_AMOUNT'));
			$conf = $this->getWebmoneyConfig(get_param_raw('LMI_PAYEE_PURSE'));
			if (!$conf) return array($r['id'],'bad purse','');
			$r['currency'] = $conf['currency'];
		} elseif ($status=='payed') {
			$r['extra'] = serialize($_POST);
			if ($r['status']!='check') return array($r['id'],'status is not check',$r['extra']);
			if ($r['sum']!=floatval(get_param_raw('LMI_PAYMENT_AMOUNT'))) return array($r['id'],'bad sum',$r['extra']);
			$t = get_param_raw('LMI_PAYEE_PURSE');
			$conf = $this->getWebmoneyConfig(get_param_raw('LMI_PAYEE_PURSE'));
			if (!$conf) return array($r['id'],'bad purse',$r['extra']);
			if ($r['currency']!=$conf['currency']) return array($r['id'],'bad purse',$r['extra']);
			$str = get_param_raw('LMI_PAYEE_PURSE').get_param_raw('LMI_PAYMENT_AMOUNT').$r['id'].get_param_raw('LMI_MODE').get_param_raw('LMI_SYS_INVS_NO').get_param_raw('LMI_SYS_TRANS_NO').get_param_raw('LMI_SYS_TRANS_DATE').$conf['secret'].get_param_raw('LMI_PAYER_PURSE').get_param_raw('LMI_PAYER_WM');
			if (strtolower(get_param_raw('LMI_HASH'))!=md5($str)) return array($r['id'],'bad hash',$r['extra']);

			$S = array('client_id'=>$r['client_id'],'payment_no'=>$r['id'],'type'=>'webmoney','bill_no'=>'','bill_vis_no'=>'','payment_date'=>date('Y-m-d'),'add_date'=>array('NOW()'),'add_user'=>0);
			if (get_param_integer('LMI_MODE')==1) $S['comment'] = '�������� ���ԣ�';

			$client_data = $db->GetRow('select * from clients where id='.$r['client_id']);
			$S['currency'] = $r['currency'];
			$pdate = date('Y-m-d',strtotime(get_param_raw('LMI_SYS_TRANS_DATE')));
			if ($client_data['currency']==$r['currency']) {			//RUR=>RUR, USD=>USD
				if ($r['currency']=='USD') {
					$S['sum_rub'] = $r['sum']*2;
					$S['payment_rate'] = 2;
				} else {
					$S['sum_rub'] = $r['sum'];
					$S['payment_rate'] = 1;
				}
			} elseif ($r['currency']=='RUR') {						//RUR=>USD
				$S['sum_rub'] = $r['sum'];
				$S['payment_rate'] = get_payment_rate_by_bill($pdate);
			} elseif ($r['currency']=='USD') {						//USD=>RUR
				$S['sum_rub'] = $r['sum'] * get_payment_rate_by_bill($pdate);
				$S['payment_rate'] = 1;
			}
			$r['payment_id'] = $db->QueryInsert('newpayments',$S);
			$r['ts'] = date('Y-m-d H:i:s',strtotime(get_param_raw('LMI_SYS_TRANS_DATE')));
			global $module_newaccounts;
			if (isset($module_newaccounts)) $module_newaccounts->update_balance($r['client_id'],$r['currency']);
		} else return array($r['id'],'bad newstatus',$status);
		$r['status'] = $status;
		$db->QueryUpdate('newpayments_webmoney','id',$r);
		return 'YES';
	}
}
	

?>
