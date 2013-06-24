<?
//вывод данных мониторинга
class m_monitoring {
	var $rights=array(
					'monitoring'		=>array('Просмотр данных мониторинга','view,top,edit','просмотр,панелька сверху,редактирование списка VIP-клиентов')
				);
	var $actions=array(
					'default'			=> array('monitoring','view'),
					'edit'				=> array('monitoring','edit'),
					'add'				=> array('monitoring','edit'),
					'view'				=> array('monitoring','view'),
					'top'				=> array('monitoring','top'),
				);
	var $menu=array(
					array('VIP-клиенты',			'default'),
				);
	function m_monitoring(){	
		
	
	}
	function Install($p){
		return $this->rights;
	}
	function GetPanel($fixclient){
		global $design,$user;
		$R=array(); $p=0;
		foreach($this->menu as $val){
			if ($val=='') {
				$p++;
				$R[]='';
			} else {
				$act=$this->actions[$val[1]];
				if (access($act[0],$act[1])) $R[]=array($val[0],'module=monitoring&action='.$val[1].(isset($val[2])?$val[2]:''), (isset($val[3])?$val[3]:''),(isset($val[4])?$val[4]:''));
			}
		}
		if (count($R)>$p){
			$design->AddMenu('Мониторинг',$R);
		}
	}
	function GetMain($action,$fixclient){
		if (!isset($this->actions[$action])) return;
		$act=$this->actions[$action];
		if (!access($act[0],$act[1])) return;
		call_user_func(array($this,'monitoring_'.$action),$fixclient);		
	}
	
	function monitoring_default($fixclient){
		global $design,$db,$user;
		$ip = get_param_protected('ip' , '');
		if ($ip) return $this->monitoring_view($fixclient);
		include INCLUDE_PATH.'db_view.php';
		$view = new DbViewMonitorClients();
		$view->Display('module=monitoring','module=monitoring&action=edit');
	}
	function monitoring_edit($fixclient) {
		include INCLUDE_PATH.'db_view.php';
		$dbf = new DbFormMonitorClients();
		if (($id=get_param_integer('id')) && !($dbf->Load($id))) return;
		$result=$dbf->Process();
                if ($result=='delete') {
                        header('Location: ?module=monitoring');
                        $design->ProcessX('empty.tpl');
		} else $dbf->Display(array('module'=>'monitoring','action'=>'edit','id'=>$id),'Мониторинг',$id?'Редактирование':'Добавление');
	}
	function monitoring_add($fixclient) {
		include INCLUDE_PATH.'db_view.php';
		$dbf = new DbFormMonitorClients();
		$dbf->SetDefault('client',$fixclient);
		$dbf->Process();
                $dbf->Display(array('module'=>'monitoring','action'=>'edit','id'=>0),'Мониторинг','Добавление');
	}
	function monitoring_top($fixclient){
		global $design,$db,$user;
/*		$db->Query('select * from clients_vip where (num_unsucc>=3)');
		$C=array(); while ($r=$db->NextRecord()) $C[$r['id']]=$r;
		$design->assign('monitoring_bad',$C);
		$design->ProcessEx('monitoring/top.tpl');*/
	}
	
	function monitoring_view($fixclient){
		global $design,$db,$user;
		$design->assign('ip',$ip=get_param_protected('ip'));
		if (!$ip) return $this->monitoring_default($fixclient);
		$design->assign('period',get_param_protected('period'));
		$design->assign('m',$m=get_param_protected('m',date('m')));
		$design->assign('d',$d=get_param_protected('d',date('d')));
		$design->assign('y',$y=get_param_protected('y',date('Y')));
		$design->assign('curdate',mktime(0,0,0,$m,$d,$y));
		//необязательные данные, нужны для отладки и для удобства
		$r=$db->GetRow('select min(time300)*300 as A,max(time300)*300 as B,count(*) as C from monitor_5min where ip_int=INET_ATON("'.$ip.'")');
		$d=1+($r['B']-$r['A'])/300; $r['C']=$r['C']/$d;
		$design->assign('data1',$r);
		$r=$db->GetRow('select min(time3600)*3600 as A,max(time3600)*3600 as B,count(*) as C from monitor_1h where ip_int=INET_ATON("'.$ip.'")');
		$d=1+($r['B']-$r['A'])/3600; $r['C']=$r['C']/$d;
		$design->assign('data2',$r);
		$D=array();	for ($i=1;$i<=31;$i++) $D[$i]=$i;
		$design->assign('D',$D);
		$design->AddMain('monitoring/view_day.tpl');
	}
	function monitoring_view2() {
		global $design,$db,$user;
		$design->assign('ip',$ip=get_param_protected('ip'));
		if (!$ip) return $this->monitoring_default($fixclient);
		
		//необязательные данные, нужны для отладки и для удобства
		$r=$db->GetRow('select min(time300)*300 as A,max(time300)*300 as B,count(*) as C from monitor_5min where ip_int=INET_ATON("'.$ip.'")');
		$d=1+($r['B']-$r['A'])/300; $r['C']=$r['C']/$d;
		$design->assign('data1',$r);
		$r=$db->GetRow('select min(time3600)*3600 as A,max(time3600)*3600 as B,count(*) as C from monitor_1h where ip_int=INET_ATON("'.$ip.'")');
		$d=1+($r['B']-$r['A'])/3600; $r['C']=$r['C']/$d;
		$design->assign('data2',$r);
		$design->AddMain('monitoring/view.tpl');
	}
		
/*		$period=get_param_integer('period',0);
		$skip=get_param_integer('skip',0);
		$year=get_param_integer('year',0);
		$month=get_param_integer('month',0);
		$day=get_param_integer('day',0);

		$design->assign('ip',$ip);
		$design->assign('period',$period);
		$design->assign('skip',$skip);
		$design->assign('year',$year);
		$design->assign('month',$month);
		$design->assign('day',$day);

		$years=array(2003,2004,2005,2006);
		foreach ($years as $i=>$v) $years[$i]=array('val'=>$v,'selected'=>($v==$year?1:0));
		$design->assign('years',$years);

		$months=array();
		for ($i=1;$i<=12;$i++) $months[]=array('val'=>$i,'selected'=>($i==$month?1:0));
		$design->assign('months',$months);

		$days=array();
		for ($i=1;$i<=31;$i++) $days[]=array('val' => $i,'selected' => ($i==$day?1:0));
		$design->assign('days',$days);

		$design->AddMain('monitoring/ip.tpl');*/
		
		
/*
	function monitoring_edit($fixclient){
		global $db,$design;
		$this->dbmap=new Db_map_nispd();	
		$this->dbmap->SetErrorMode(2,0);
		$id = get_param_protected('id' , '');
		$this->dbmap->ApplyChanges('clients_vip');
		$this->dbmap->ShowEditForm('clients_vip','clients_vip.id="'.$id.'"',array(),1);
		$design->assign('id',$id);
		$design->AddMain('monitoring/db_edit.tpl');
	}
	function monitoring_add($fixclient){
		global $design,$db;
		$client=get_param_protected('id');
		if ($client){
			$db->Query('select * from clients where client="'.$client.'"');
			if (!($r=$db->NextRecord())) return;
			$db->Query('select * from user_users where user="'.$r['support'].'"');
			$r=$db->NextRecord();
		}
		if (!isset($r) || !is_array($r)) $r=array('email'=>'','phone'=>'');
		$this->dbmap=new Db_map_nispd();
		$this->dbmap->SetErrorMode(2,0);
		$this->dbmap->ShowEditForm('clients_vip','',array('client'=>$client,'email'=>$r['email'],'phone'=>$r['phone_work'],'important_period'=>'8-20','num_unsucc'=>0),1);
		$design->AddMain('monitoring/db_add.tpl');
	}
	function monitoring_apply($fixclient){
		global $db,$design;
		$this->dbmap=new Db_map_nispd();	
		$this->dbmap->SetErrorMode(2,0);
		if (($this->dbmap->ApplyChanges('clients_vip')!="ok") && (get_param_protected('dbaction','')!='delete')) {
			$this->dbmap->ShowEditForm('clients_vip','',get_param_raw('row',array()));
			$design->AddMain('monitoring/db_add.tpl');
		} else {
			trigger_error('<script language=javascript>window.location.href="?module=monitoring";</script>');
		}
	}
*/

//public function
	function get_image($IPs){
		global $db;
		$Q=''; foreach ($IPs as $ip) $Q.=($Q?',':'').'INET_ATON("'.$ip.'")';
		$Q='ip_int IN ('.$Q.') AND time300>=FLOOR(UNIX_TIMESTAMP()/300)-3';
		$R=$db->AllRecords('select ip_int,value from monitor_5min where '.$Q);

		$v=count($IPs);
		$P1=0; $P2=0; $C1 = 0; $C2 = 0;
		foreach ($R as $val) {
			if (($v==1) || ((@$val["ip_int"]&2)==0)) {
				$P1+=$val["value"]?5:1;
				$C1++;
			} else {
				$P2+=$val["value"]?5:1;
				$C2++;
			}
		}
		
		if (!$C1) $P1='#808080';
		elseif ($P1==$C1*5) $P1='#00e000';
		elseif ($P1>2*$C1) $P1='#c0c000';
		else $P1='#ff0000';
		if (!$C2) $P2='#808080';
		elseif ($P2==$C2*5) $P2='#00e000';
		elseif ($P2>2*$C2) $P2='#c0c000';
		else $P2='#ff0000';
		return '<div class=ping style="background-color:'.$P1.'">&nbsp;</div>'.
				($v==1?'':
						'<div class=ping2 style="background-color:'.$P2.'">&nbsp;</div>');


/*		if (!$C1) $P1=0;
		elseif ($P1==$C1*5) $P1=3;
		elseif ($P1>2*$C1) $P1=2;
		else $P1=1;
		if (!$C2) $P2=0;
		elseif ($P2==$C2*5) $P2=3;
		elseif ($P2>2*$C2) $P2=2;
		else $P2=1;

		$alt=''; if ($C1+$C2==0) $alt=' alt="Ошибка. Обратитесь к администратору."';
		if ($v>=2 && $P1+$P2>0) $img=$P1.'_'.$P2; else $img=$P1;
		return '<img width=12 height=12 src="'.WEB_IMAGES_PATH.'stat/'.$img.'.png"'.$alt.'>';*/
	}
}
?>
