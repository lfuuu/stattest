<?
//вывод данных мониторинга
class m_monitoring {
	var $actions=array(
					'edit'				=> array('monitoring','edit'),
					'add'				=> array('monitoring','edit'),
					'view'				=> array('monitoring','view'),
					'top'				=> array('monitoring','top'),
					'report_bill_graph' 		=> array('monitoring','graphs'),
					'report_move_numbers'           => array('services_voip','edit'),
				);
	var $menu=array(
					array('Отчет: Динамика счетов',	'report_bill_graph'),
					array('Перемещаемые услуги',    'report_move_numbers'),
				);
	function m_monitoring(){	
		
	
	}
	function GetPanel($fixclient){
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
            return array('Мониторинг',$R);
		}
	}
	function GetMain($action,$fixclient){
		if (!isset($this->actions[$action])) return;
		$act=$this->actions[$action];
		if (!access($act[0],$act[1])) return;
		call_user_func(array($this,'monitoring_'.$action),$fixclient);		
	}
	
	function monitoring_edit($fixclient) {
		include INCLUDE_PATH.'db_view.php';
		$dbf = new DbFormMonitorClients();
		if (($id=get_param_integer('id')) && !($dbf->Load($id))) return;
		$result=$dbf->Process();
                if ($result=='delete') {
                        header('Location: ?module=monitoring');
                    exit;
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
		if (!$ip) return;
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
		if (!$ip) return;
		
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
			trigger_error2('<script language=javascript>window.location.href="?module=monitoring";</script>');
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


	}
	
	/**
	 * Функция возвращает обобщенную статистику по региону.
	 *
	 * @param $regionId int ид региона
	 * @param $from int(unix_timestamp) дата начала выборки
	 * @param $to   int(unix_timestamp) дата окончания выборки
	 * @return array
	 */
	function getVoipSummaryRegionStatistic($regionId, $from, $to)
	{
		global $pg_db;

		$data = array();

		foreach($pg_db->AllRecords(
			"SELECT 
				day, 
				direction_out, 
				SUM(len) AS sum_len, 
				COUNT(*) AS call_count
			FROM 
				calls_raw.calls_raw
			WHERE 
				connect_time BETWEEN  '".date("Y-m-d", $from)."' AND '".date("Y-m-d", $to)."'
				AND srv_region_id = '".$regionId."'
			GROUP BY 
				srv_region_id, 
				day, 
				direction_out
			ORDER BY 
				day,
				direction_out") as $l)
		{
			$day = strtotime($l["day"]);

			if (!isset($data[$day]))
				$data[$day] = array(
				"time" => $day, 
				"day" => $l["day"], 
				"len" => 0, 
				"count" => 0, 
				"data_in" => array(
					"len" => 0, 
					"count" => 0
				), 
				"data_out" => array(
					"len" => 0, 
					"count" => 0
					)
				);

			$direction = $l["direction_out"] == "f" ? "in" : "out";

			$data[$day]["len"] += $l["sum_len"];
			$data[$day]["data_".$direction]["len"] = $l["sum_len"];

			$data[$day]["count"] += $l["call_count"];
			$data[$day]["data_".$direction]["count"] = $l["call_count"];
		}

		return $data;
        
	}
	/**
	 * Функция возвращает обобщенную статистику счетов.
	 *
	 * @param $regionId int ид региона
	 *	если задан то берется информация по заданому региону
	 *	иначе берется информация по всем регионам кроме Москвы
	 * @param $from int(unix_timestamp) дата начала выборки
	 * @param $to   int(unix_timestamp) дата окончания выборки
	 * @return array
	 */
	function getBillsStatistic($regionId, $from, $to)
	{
		$options = array();
		$options['select'] = '
			DATE_FORMAT(B.bill_date, "%c") as month,
			DATE_FORMAT(B.bill_date, "%Y") as year,
			SUM(L.sum) as sum,
			C.region as region,
			SUM(
				IF(
					L.type = "good" OR C.status = "once", 
					L.sum, 
					0
				)
			) as good,
			SUM(
				IF (
					L.type = "service" 
					AND
					DATE_FORMAT(B.bill_date, "%m") = DATE_FORMAT(L.date_from, "%m") 
					AND 
					DATE_FORMAT(B.bill_date, "%m") = DATE_FORMAT(L.date_to,"%m"),
					L.sum,
					0
				)
			) as abon,
			SUM(
				IF (
					L.type = "service" 
					AND
					DATE_FORMAT(B.bill_date, "%m") <> DATE_FORMAT(L.date_from, "%m") 
					AND  
					DATE_FORMAT(B.bill_date, "%m") <> DATE_FORMAT(L.date_to,"%m"),
					L.sum,
						0
				)
			) as overrun
		';
		$options['from'] = 'newbills as B';
		$options['joins'] = '
			LEFT JOIN clients as C ON C.id = B.client_id 
			LEFT JOIN newbill_lines as L ON B.bill_no = L.bill_no
		';
		$options['conditions'] = array(
			'C.region > 0 AND C.status = ? AND C.type IN (?) AND B.bill_date >= ? AND B.bill_date <= ? AND B.currency = ? AND B.sum > ?',
			'work',
			array('org', 'priv'),
			date('Y-m-d', $from),
			date('Y-m-d', $to),
			'RUB',
			0
		);
		if ($regionId)
		{
			$options['conditions'][0] .= ' AND C.region = ?';
			$options['conditions'][] = $regionId;
		} else {
			$options['conditions'][0] .= ' AND C.region <> ?';
			$options['conditions'][] = 99;
		}
		$options['group'] = 'region, month';
		$options['order'] = 'region DESC, year ASC, month ASC';
		$_bills = NewBill::find('all', $options);
		$min_month = date('n', $from);$max_month = date('n', $to);$year = date('Y');
		if ($min_month > $max_month)
		{
			$periods = array(array('start' => $min_month, 'end' => 12, 'year' => $year-1), array('start' => 1, 'end' => $max_month, 'year' => $year));
		} else {
			$periods = array(array('start' => $min_month, 'end' => $max_month, 'year' => $year));
		}
		$total = array();
		foreach ($_bills as $b)
		{
			if (!isset($total[$b->region]))
			{
				$total[$b->region] = array();
				foreach ($periods as $period)
				{
					$year = $period['year'];
					for($month=$period['start'];$month<=$period['end'];$month++)
					{
						$created = false;
						foreach ($_bills as $v)
						{
							if ($b->region == $v->region && $v->month == $month && $v->year == $year)
							{
								$created = true;
								$total[$b->region]['bills']['abons'][] =round($v->abon/1000, 2);
								$total[$b->region]['bills']['overruns'][] =round($v->overrun/1000, 2);
								$total[$b->region]['bills']['goods'][] =round($v->good/1000, 2);
								$total[$b->region]['bills']['diff'][] =round(($v->sum-$v->abon-$v->overrun-$v->good)/1000, 2);
								
								$total[$b->region]['bills_by_month'][$v->month][] =round($v->abon/1000, 2);
								$total[$b->region]['bills_by_month'][$v->month][] =round($v->overrun/1000, 2);
								$total[$b->region]['bills_by_month'][$v->month][] =round($v->good/1000, 2);
								$total[$b->region]['bills_by_month'][$v->month][] =round(($v->sum-$v->abon-$v->overrun-$v->good)/1000, 2);
							}
						}
						if (!$created)
						{
							$total[$b->region]['bills']['abons'][] = 0;
							$total[$b->region]['bills']['overruns'][] =0;
							$total[$b->region]['bills']['goods'][] =0;
							$total[$b->region]['bills']['diff'][] =0;
							
							$total[$b->region]['bills_by_month'][$month][] =0;
							$total[$b->region]['bills_by_month'][$month][] =0;
							$total[$b->region]['bills_by_month'][$month][] =0;
							$total[$b->region]['bills_by_month'][$month][] =0;
						}
					}
				}
			}
		}
		return $total;
	}
	function monitoring_report_bill_graph($fixclient)
	{
		global $design,$db;
		require_once ('JpGraphsInit.php');
		require_once (PATH_TO_ROOT.'libs/jpgraph/jpgraph.php');
		require_once (PATH_TO_ROOT.'libs/jpgraph/jpgraph_bar.php');
		$regionId = get_param_integer('region', 99);
		$design->assign('region', $regionId);
		$from = strtotime('first day of this month 00:00:00');
		$from = strtotime('-5 month',$from);
		$to = strtotime('last day of this month 23:59:59');
		
		$_data = $this->getBillsStatistic($regionId, $from, $to);
		$graphs = array();
		if (!empty($_data))
		{
			foreach ($_data as $r_id => $region_data)
			{
				$data_by_month = $region_data['bills_by_month'];
				$data = $region_data['bills'];
				if (!empty($data_by_month))
				{
					if ($regionId)
					{
                                            $graph = JpGraphsInit::getBarGraph('Информация по счетам, тыс. рублей', 800);
					} else {
                                            $graph = JpGraphsInit::getBarGraph('Информация по счетам, тыс. рублей', 480, 480);
					}
					$graph->xaxis->SetTickLabels(array(
						'абонентская плата',
						'Превышение',
						'Товары',
						'Остальное'
					));
					$colors = array('#0000CD','#B0C4DE','#8B008B', 'yellow', 'red', 'green');
					$bplots = array();
					$ts = $to+1;
					for ($i=0;$i<=5;$i++)
					{
						$ts = strtotime('-1 month', $ts);
						$month_key = date('n', $ts);
						if (!isset($data_by_month[$month_key]))
						{
							$data_by_month[$month_key] = array(0.001,0.001,0.001,0.001);
						}
						
						$bplot = new BarPlot($data_by_month[$month_key]);
						$bplot->SetLegend(mdate('Месяц', $ts));
						$bplots[] = $bplot;
					}

					$gbplot = new GroupBarPlot(array_reverse($bplots));
					$graph->Add($gbplot);
					foreach ($bplots as $k=>$v)
					{
						$v->SetColor($colors[$k]);
						$v->SetFillColor($colors[$k]);
					}
					
					$filename = './images/graphs/bills_details_'. $r_id .'.png';
					// Display the graph
					$graph->Stroke($filename);
					$graphs[$r_id]['bill_details'] = $filename;
					unset($graph);
				}
				if (!empty($data))
				{
					if ($regionId)
					{
                                            $graph = JpGraphsInit::getBarGraph('Информация по счетам, тыс. рублей', 800);
					} else {
                                            $graph = JpGraphsInit::getBarGraph('Информация по счетам, тыс. рублей', 480, 480);
					}

					$ts = $from;
					for ($i=0;$i<=5;$i++)
					{
						if ($i) $ts = strtotime('+1 month', $ts);
						$xaxis[] = mdate('Месяц', $ts);
					}
					
					$graph->xaxis->SetTickLabels($xaxis);
					$colors = array('#0000CD','#B0C4DE','#8B008B', '#000000');
					$legends = array('abons'=>'Абоненская плата', 'overruns'=>'Превышение', 'goods'=>'Товары', 'diff'=>'Остальное');
					$bplots = array();
					$i=0;
					foreach ($data as $k=>$v)
					{
						$bplot = new BarPlot($v);
						$bplot->SetLegend($legends[$k]);
						$bplots[] = $bplot;
						$i++;
					}
					
					$gbbplot = new AccBarPlot($bplots);
					$gbplot = new GroupBarPlot(array($gbbplot));
					$graph->Add($gbplot);
					$graph->legend->setReverse(true);
					foreach ($bplots as $k=>$v)
					{
						$v->SetColor($colors[$k]);
						$v->SetFillColor($colors[$k]);
					}
					$filename = './images/graphs/bills_totals_' . $r_id . '.png';
					// Display the graph
					$graph->Stroke($filename);
					$graphs[$r_id]['bill_totals'] = $filename;
				}
			}
		}
		$regions = $db->AllRecords("select id, name from regions where id <> 99 order by id desc ",'id');
		$design->assign('regions', $regions);
		$design->assign('graphs', $graphs);
		$design->AddMain('monitoring/report_bill_graph.tpl');
	}
	function monitoring_report_move_numbers($fixclient)
        {
            include 'ReportMovedNumbers.php';
            ReportMovedNumbers::getReport();
        }
}
?>
