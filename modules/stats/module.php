<?php

class m_stats extends IModule{

    private $_inheritances = array();

    public function __construct()
    {
//        $this->_addInheritance(new m_stats_);
    }

    public function __call($method, array $arguments = array())
    {
        foreach ($this->_inheritances as $inheritance) {
            $inheritance->invoke($method, $arguments);
        }
    }

    protected function _addInheritance(Inheritance $inheritance)
    {
        $this->_inheritances[get_class($inheritance)] = $inheritance;
        $inheritance->module = $this;
    }

	function stats_default($fixclient){
		$this->stats_internet($fixclient);
	}

	function stats_internet($fixclient){
		global $db,$design;
		if(!$fixclient){
			trigger_error('Выберите клиента');
			return;
		}
		
		$route=get_param_raw('route','');
		
		$dateFrom = new DatePickerValues('date_from', 'first');
		$dateTo = new DatePickerValues('date_to', 'last');

		$from = $dateFrom->getTimestamp();
		$to = $dateTo->getTimestamp();
		
		DatePickerPeriods::assignStartEndMonth($dateFrom->day, 'prev_', '-1 month');
		DatePickerPeriods::assignPeriods(new DateTime());

		$ip_group = get_param_integer('ip_group',0);
		$design->assign('ip_group',$ip_group);

		$detality = get_param_protected('detality','day');
		$design->assign('detality',$detality);

		list($routes_all,$routes_allB)=$this->get_routes_list($fixclient);

		//если сеть не задана, выводим все подсети клиента.
		if($route){
			if(isset($routes_all[$route])){
				$routes=array($routes_all[$route]);
			}else{
				trigger_error('Выбрана неправильная сеть');
				return;
			}
		}else{
			$client=$fixclient;
			$routes=array();
			foreach($routes_allB as $r)
				$routes[] = $r;
		}

		$is_coll = get_param_integer('is_coll',0);
		$design->assign('is_collocation',$is_coll);

		$stats = $this->GetStatsInternet($fixclient,$from,$to,$detality,$routes,$is_coll);
		if(!$stats)
			return;

		$design->assign('stats',$stats);
		$design->assign('route',$route);
		$design->assign('routes_all',$routes_all);
		if (get_param_raw('xml')){
			header("Content-type: text/xml");
			$design->ProcessEx('stats/internet_xml.tpl');
		} else {
			$design->AddMain('stats/internet.tpl');
			$design->AddMain('stats/internet_form.tpl');
		}
	}


	function stats_vpn($fixclient) {
		global $db,$design;
		if (!$fixclient) {trigger_error('Выберите клиента'); return;}
		$ip=get_param_raw('ip','');

		$dateFrom = new DatePickerValues('date_from', 'first');
		$dateTo = new DatePickerValues('date_to', 'last');

		$from = $dateFrom->getTimestamp();
		$to = $dateTo->getTimestamp();
		
		DatePickerPeriods::assignStartEndMonth($dateFrom->day, 'prev_', '-1 month');
		DatePickerPeriods::assignPeriods(new DateTime());

		$detality=get_param_protected('detality','day');
		$design->assign('detality',$detality);

		$IPs=array();
		$db->Query('
			select
				ip,
				D.actual_from,
				D.actual_to
			from
				usage_ip_ports as S
			INNER JOIN
				tech_cpe as D
			ON
				D.service="usage_ip_ports"
			and
				D.id_service=S.id
			and
				D.client=S.client
			INNER JOIN
				tarifs_internet as T
			ON
				T.id=get_tarif_internet(S.id)
			where
				S.client="'.$fixclient.'"
			AND
				T.type="V"
			and
				serial != ""
			and
				ip != ""
		');

		while ($r=$db->NextRecord()) $IPs[$r['ip']]=$r;

		if ($ip){
			if (!isset($IPs[$ip])) {trigger_error('Выбрана неправильная сеть'); return;}
			$IPs=array($IPs[$ip]);
		}
		$stats=$this->GetStatsVPN($fixclient,$from,$to,$detality,$IPs);
		if (!$stats) return;

		$design->assign('stats',$stats);
		$design->assign('ip',$ip);
		$design->assign('IPs',$IPs);
		$design->AddMain('stats/vpn.tpl');
		$design->AddMain('stats/vpn_form.tpl');
	}

	function stats_rtsaldo($fixclient) {
		global $db,$design,$fixclient_data;
		if (!$fixclient) {trigger_error('Выберите клиента'); return;}
		$def=getdate();
		$def['mday']=1; $from=param_load_date('from_',$def);
		$def['mday']=31; $to=param_load_date('to_',$def);

		$def['mday']=1; $cur_from=param_load_date('cur_from_',$def);
		$def['mday']=31; $cur_to=param_load_date('cur_to_',$def);
		$def['mon']--; if ($def['mon']==0) {$def['mon']=12; $def['year']--; }
		$def['mday']=1; $prev_from=param_load_date('prev_from_',$def);
		$def['mday']=31; $prev_to=param_load_date('prev_to_',$def);

		$db->Query("select * FROM newbills_rtsaldo_changes WHERE (client_id='{$fixclient_data['id']}') and (ts>=FROM_UNIXTIME({$from})) and (ts<FROM_UNIXTIME({$to}))");
		$stats=array(); while ($r=$db->NextRecord()) $stats[]=$r;
		$design->assign('stats',$stats);
		$design->AddMain('stats/rtsaldo.tpl');
		$design->AddMain('stats/rtsaldo_form.tpl');
	}
	function stats_ppp($fixclient){
		global $db,$design;

		$login=get_param_integer('login',0);
		if (!$fixclient) {trigger_error('Выберите клиента'); return;}

		if ($login){
			$db->Query('select * from usage_ip_ppp where (client="'.$fixclient.'") and (id="'.$login.'")');
			if (!($r=$db->NextRecord())) {trigger_error('Логин не существует'); return; }
			$logins=array($r['login']);

			$db->Query('select * from usage_ip_ppp where (client="'.$fixclient.'") and (login!="") order by login');
			$logins_all=array();
			while ($r=$db->NextRecord()){
				$logins_all[]=$r;
			}
		} else {
			//список всех сетей, нужен для вывода их списка.
			$db->Query('select * from usage_ip_ppp where (client="'.$fixclient.'") and (login!="") order by login');
			$logins_all=array(); $logins=array();
			while ($r=$db->NextRecord()){
				$logins[]=$r['login'];
				$logins_all[]=$r;
			}
		}

		$dateFrom = new DatePickerValues('date_from', 'first');
		$dateTo = new DatePickerValues('date_to', 'last');

		$from = $dateFrom->getTimestamp();
		$to = $dateTo->getTimestamp();
		
		DatePickerPeriods::assignStartEndMonth($dateFrom->day, 'prev_', '-1 month');
		DatePickerPeriods::assignPeriods(new DateTime());

		$detality=get_param_protected('detality','day');

		$stats=$this->GetStatsPPP($from,$to,$detality,$logins);

		$design->assign('detality',$detality);
		$design->assign('stats',$stats);
		$design->assign('login',$login);
		$design->assign('logins_all',$logins_all);
		if (get_param_raw('xml')){
			header("Content-type: text/xml");
			$design->ProcessEx('stats/ppp_xml.tpl');
		} else {
			$design->AddMain('stats/ppp.tpl');
			$design->AddMain('stats/ppp_form.tpl');
		}
	}

	function stats_voip_sell(){
		global $db,$design;
		$choice = get_param_protected('choice',array());
		$date_from = param_load_date('date_from_', array('mday'=>1,'mon'=>date('m')-1,'year'=>date('Y')), true);
		$date_to = param_load_date('date_to_', array('mday'=>30,'mon'=>date('m'),'year'=>date('Y')), true);

		if(in_array('tarifs',$choice)){
			$query = "
				select
					group_concat(distinct `nbl`.`bill_no`) `bills`,
					`cl`.`id` `client_id`,
					`cl`.`client`,
					`tv`.`name` `tarif`,
					`tv`.`id` `tarif_id`,
					`nb`.`currency`,
					round(sum(`nbl`.`amount`*`nbl`.`price`)*1.18,2) total
				from
					`usage_voip` `uip`
				inner join
					`clients` `cl`
				on
					`cl`.`client` = `uip`.`client`
				left join
					`log_tarif` `lt`
				on
					`lt`.`service` = 'usage_voip'
				and
					`lt`.`id_service` = `uip`.`id`
				inner join
					`tarifs_voip` `tv`
				on
					`tv`.`id`=`lt`.`id_tarif`
				inner join
					`newbills` `nb`
				on
					`nb`.`bill_date` between '".$date_from."' and '".$date_to." 23:59:59'
				and
					`nb`.`client_id` = `cl`.`id`
				inner join
					`newbill_lines` `nbl`
				on
					`nbl`.`service`='usage_voip'
				and
					`nbl`.`bill_no` = `nb`.`bill_no`
				and
					`nbl`.`id_service` = `uip`.`id`
				where
					(
						`uip`.`actual_from` <= '".$date_to."'
					or
						`uip`.`actual_to` >= '".$date_from." 23:59:59'
					)
				group by
					`cl`.`client`,
					`cl`.`id`,
					`tv`.`name`,
					`tv`.`id`,
					`nb`.`currency`
				order by
					`cl`.`client`,
					`nb`.`currency`,
					`tv`.`name`,
					`nbl`.`bill_no`
			";

			$db->Query($query);
			$tarifs_stat = array();
			$total_sum_rur = 0;
			$total_sum_usd = 0;
			$total_sum_other = 0;
			while($row=$db->NextRecord(MYSQL_ASSOC)){
				$tarifs_stat[] = $row;
				if($row['currency']=='RUR')
					$total_sum_rur += $row['total'];
				elseif($row['currency']=='USD')
					$total_sum_usd += $row['total'];
				else
					$total_sum_other += $row['total'];
			}
			$design->assign(
				'tarifs_stats',
				array(
					'totals'=>array(
						'rur'=>$total_sum_rur,
						'usd'=>$total_sum_usd,
						'oth'=>$total_sum_other
					),
					'rows'=>$tarifs_stat
				)
			);
		}
		$design->assign('choice',$choice);
		$design->AddMain('stats/voip_sell.tpl');
	}

    function stats_voip($fixclient){
        global $db,$design;
        if(!$fixclient){
            trigger_error('Клиент не выбран');
            return;
        }

        $client = $db->GetRow("select * from clients where '".addslashes($fixclient)."' in (id, client)");

        $client_id = $client['id'];
        $usages = $db->AllRecords("select u.id, u.E164 as phone_num, u.region, r.name as region_name from usage_voip u
                                       left join regions r on r.id=u.region
                                       where u.client='".addslashes($client['client'])."'
                                       order by u.region desc, u.id asc");
        $regions = array();
        foreach ($usages as $u)
            if (!isset($regions[$u['region']]))
                $regions[$u['region']] = $u['region'];

        $regions_cnt = count($regions);

        $design->assign('regions_cnt',$regions_cnt);
        $design->assign('phone',$phone=get_param_protected('phone',''));
        $phones = array();
        $phones_sel = array();

        $regions = array();

        $last_region = $region = '';
        if ($phone == '' && count($usages) > 0) {
            $phone = $usages[0]['region'];
            if ($regions_cnt > 1) $region = 'all';
        }
        if ($region != 'all') {
            $region = explode('_', $phone);
            $region = $region[0];
        }

        foreach ($usages as $r) {
            if ($region == 'all') {
                if (!isset($regions[$r['region']])) $regions[$r['region']] = array();
                if (!isset($regions[$r['region']][$r['id']])) $regions[$r['region']][$r['id']] = $r['id'];
            }
            if (substr($r['phone_num'],0,4)=='7095') $r['phone_num']='7495'.substr($r['phone_num'],4);
            if ($last_region != $r['region']){
                $phones[$r['region']] = $r['region_name'].' (все номера)';
                $last_region = $r['region'];
            }
            $phones[$r['region'].'_'.$r['phone_num']]='&nbsp;&nbsp;'.$r['phone_num'];
            if ($phone==$r['region'] || $phone==$r['region'].'_'.$r['phone_num']) $phones_sel[]=$r['id'];
        }
        $design->assign('phones',$phones);

	$dateFrom = new DatePickerValues('date_from', 'first');
	$dateTo = new DatePickerValues('date_to', 'last');

	$from = $dateFrom->getTimestamp();
	$to = $dateTo->getTimestamp();
	
	DatePickerPeriods::assignStartEndMonth($dateFrom->day, 'prev_', '-1 month');
	DatePickerPeriods::assignPeriods(new DateTime());

        $destination = get_param_raw('destination', 'all');
        if(!in_array($destination,array('all','0','0-m','0-f','1','1-m','1-f','2','3')))
            $destination = 'all';

        $direction = get_param_raw('direction','both');
        if(!in_array($direction,array('both','in','out')))
            $direction = 'both';

        $design->assign('destination',$destination);
        $design->assign('direction',$direction);
        $design->assign('detality',$detality=get_param_protected('detality','day'));
        $design->assign('paidonly',$paidonly=get_param_integer('paidonly',0));
        if ($region == 'all') {
            $stats = array();
            foreach ($regions as $region=>$phones_sel) {
                $stats[$region] = $this->GetStatsVoIP($region,$from,$to,$detality,$client_id,$phones_sel,$paidonly,0,$destination,$direction, $regions);
            }
            $stats = $this->prepareStatArray($stats, $detality);
        } else
            if (!($stats=$this->GetStatsVoIP($region,$from,$to,$detality,$client_id,$phones_sel,$paidonly,0,$destination,$direction, $regions))) return;

        $design->assign('stats',$stats);
        $design->AddMain('stats/voip_form.tpl');
        $design->AddMain('stats/voip.tpl');
	}

    /*функция формирует единый массив для разных регионов,
     * входной массив вида: array('region_id1'=>array(), 'region_id2'=>array(), ...);
    */
    function prepareStatArray($data = array(), $detality = '', $all_regions = array()) {

        if (!count($data)) return $data;
        $Res = array();
        $rt = array('price'=>0, 'cnt'=>0, 'ts2'=>0, 'len'=>0);

        switch ($detality) {
            case 'dest':
                foreach ($data as $r_id=>$reg_data) {
                    foreach ($reg_data as $k=>$r) {
                        if ($r['is_total'] == false) {
                            if (!isset($Res[$k])) $Res[$k] = array('tsf1'=>$r['tsf1'], 'reg_id'=>$r_id, 'cnt'=>0, 'price'=>0, 'len'=>0);

                            $Res[$k]['cnt'] += $r['cnt'];
                            $Res[$k]['len'] += $r['len'];
                            $Res[$k]['price'] += $r['price'];
                            $Res[$k]['price'] = number_format($Res[$k]['price'], 2, '.','');

                            if ($Res[$k]['len']>=24*60*60) $d=floor($Res[$k]['len']/(24*60*60)); else $d=0;
                            $Res[$k]['tsf2']=($d?($d.'d '):'').gmdate("H:i:s",$Res[$k]['len']-$d*24*60*60);

                            if (isset($r['price'])) $rt['price']+=$r['price'];
                            if (isset($r['cnt'])) $rt['cnt']+=$r['cnt'];
                            if (isset($r['len'])) $rt['len']+=$r['len'];
                        }
                    }
                }
                $rt['tsf1']='Итого';
                if ($rt['len']>=24*60*60) $d=floor($rt['len']/(24*60*60)); else $d=0;
                $rt['tsf2']=($d?($d.'d '):'').gmdate("H:i:s",$rt['len']-$d*24*60*60);
                $rt['price']=number_format($rt['price'], 2, '.','') .' (<b>'.number_format($rt['price']*1.18, 2, '.','').' - Сумма с НДС</b>)';

                break;
            case 'call':
                foreach ($data as $r_id=>$reg_data) {
                    foreach ($reg_data as $r) {
                       if ($r['is_total'] == false) {
                            $r['price'] = number_format($r['price'], 2, '.','');
                            $Res[] = array('mktime'=>$r['mktime'],'reg_id'=>(isset($all_regions[$r_id])?$all_regions[$r_id]:$r_id))+$r;

                            if (isset($r['price'])) $rt['price']+=$r['price'];
                            if (isset($r['cnt'])) $rt['cnt']+=$r['cnt'];
                            if (isset($r['ts2'])) $rt['ts2']+=$r['ts2'];

                        }
                    }
                }
                array_multisort($Res);

                $rt['ts1']='Итого';
                $rt['tsf1']='Итого';
                $rt['num_to']='&nbsp;';
                $rt['num_from']='&nbsp;';
                if ($rt['ts2']>=24*60*60) $d=floor($rt['ts2']/(24*60*60)); else $d=0;
                $rt['tsf2']=($d?($d.'d '):'').gmdate("H:i:s",$rt['ts2']-$d*24*60*60);
                $rt['price']=number_format($rt['price'], 2, '.','') .' (<b>'.number_format($rt['price']*1.18, 2, '.','').' - Сумма с НДС</b>)';
                break;
            default:
                foreach ($data as $r_id=>$reg_data) {
                    foreach ($reg_data as $k=>$r) {
                        if ($r['is_total'] == false) {
                            if (!isset($Res[$r['ts1']]))
                                $Res[$r['ts1']] = array(
                                    'ts1'=>$r['ts1'],
                                    'tsf1'=>$r['tsf1'],
                                    'mktime'=>$r['mktime'],
                                    'geo'=>$r['geo'],
                                    'reg_id'=>$r_id,
                                    'cnt'=>0,
                                    'price'=>0,
                                    'ts2'=>0
                                );

                            $Res[$r['ts1']]['cnt'] += $r['cnt'];
                            $Res[$r['ts1']]['ts2'] += $r['ts2'];
                            $Res[$r['ts1']]['price'] += $r['price'];
                            $Res[$r['ts1']]['price'] = number_format($Res[$r['ts1']]['price'], 2, '.','');

                            if ($Res[$r['ts1']]['ts2']>=24*60*60) $d=floor($Res[$r['ts1']]['ts2']/(24*60*60)); else $d=0;
                            $Res[$r['ts1']]['tsf2']=($d?($d.'d '):'').gmdate("H:i:s",$Res[$r['ts1']]['ts2']-$d*24*60*60);

                            if (isset($r['price'])) $rt['price']+=$r['price'];
                            if (isset($r['cnt'])) $rt['cnt']+=$r['cnt'];
                            if (isset($r['ts2'])) $rt['ts2']+=$r['ts2'];
                        }
                    }
                }
                ksort($Res);

                $rt['tsf1']='Итого';
                if ($rt['ts2']>=24*60*60) $d=floor($rt['ts2']/(24*60*60)); else $d=0;
                $rt['tsf2']=($d?($d.'d '):'').gmdate("H:i:s",$rt['ts2']-$d*24*60*60);
               $rt['price']=number_format($rt['price'], 2, '.','') .' (<b>'.number_format($rt['price']*1.18, 2, '.','').' - Сумма с НДС</b>)';
            break;
        }

        $Res['total'] = $rt;

        return $Res;
    }

    function stats_voip_free_stat($fixclient)
    {
        global $db, $pg_db, $design;

        
        $ns = array();
        $groups = array("used" => "Используется", "free" => "Свободный", "our" => "ЭмСиЭн", "reserv" => "Резерв", "stop" => "Отстойник");
        $beautys = array("0" => "Стандартные", "4" => "Бронза", "3" => "Серебро", "2" => "Золото", "1" => "Платина (договорная цена)");

        $numberRanges = array(
                "74996850000" => array("74996850000", "74996850199", "Москва"),
                "74996851000" => array("74996851000", "74996851999", ""),
                "74992130000" => array("74992130000", "74992130499", ""),
                "74992133000" => array("74992133000", "74992133999", ""),

                "74956380000" => array("74956380000", "74956389999", ""),
                "74959500000" => array("74959500000", "74959509999", ""),
                "74951059000" => array("74951059000", "74951059999", ""),

                "78612040000" => array("78612040000", "78612040499", "Краснодар"), //КРАСНОДАР
                "78123726500" => array("78123726500", "78123726999", "Санкт-Петербург"), //САНКТ-ПЕТЕРБУРГ
                "78462150000" => array("78462150000", "78462150499", "Самара"), //САМАРА
                "73433020000" => array("73433020000", "73433022999", "Екатеринбург"), //ЕКАТЕРИНБУРГ
                "73833120000" => array("73833120000", "73833120499", "Новосибирск"), //НОВОСИБИРСК
                "78633090000" => array("78633090000", "78633090499", "Ростов-на-дону"), //РОСТОВ-НА-ДОНУ
                "78432070000" => array("78432070000", "78432070499", "Казань"), //КАЗАНЬ
                "74232060000" => array("74232060000", "74232060499", "Владивосток"), //ВЛАДИВОСТОК
                );

        $rangeFrom = get_param_raw("range_from", '74996850000');
        $rangeTo = $numberRanges[$rangeFrom][1];

        $group = get_param_raw("group",array_keys($groups));
        $beauty = get_param_raw("beauty",array_keys($beautys));

        $design->assign("ranges", $numberRanges);
        $design->assign("range_from", $rangeFrom);
        $design->assign("group", $group);
        $design->assign("groups", $groups);
        $design->assign("beauty", $beauty);
        $design->assign("beautys", $beautys);

        $design->assign("minCalls", 10); //минимальное среднее кол-во звоноков за 3 месяца в месяц, для возможности публиковать номер минуя "отстойник"


        $unsetPublish = array();
        if (get_param_raw("do",""))
        {
            
            if (get_param_raw("publish"))
            {
                $nums = get_param_raw("publish_phones");
                $setNums = get_param_raw("published_phones");

                $nums = $nums ? $nums : array();
                $setNums = $setNums ? $setNums : array();

                $add = array_diff($nums, $setNums);
                $del = array_diff($setNums, $nums);

                if ($add)
                {
                    $db->Query($q = "update voip_numbers set site_publish ='Y' where number in ('".implode("','", $add)."')");
                }

                if ($del)
                {
                    $db->Query($q = "update voip_numbers set site_publish ='N' where number in ('".implode("','", $del)."')");
                }
            }

            $ns = $db->AllRecords($q = "
                        SELECT 
                            a.*, c.company, c.client,
                            IF(client_id IN ('9130', '764'), 'our', 
                                IF(date_reserved IS NOT NULL, 'reserv', 
                                    IF(active_usage_id IS NOT NULL, 'used', 
                                        IF(max_date >= (now() - INTERVAL 6 MONTH), 'stop', 'free'
                                        )
                                    )
                                )
                            ) AS status
                        FROM (
                            SELECT 
                                number, 
                                region, 
                                price, 
                                client_id, 
                                usage_id, 
                                reserved_free_date, 
                                cast(used_until_date as date) used_until_date, 
                                beauty_level, 
                                site_publish, 
                                (
                                    SELECT 
                                        MAX(actual_to) 
                                    FROM 
                                        usage_voip u 
                                    WHERE 
                                        u.e164 = v.number AND 
                                        actual_from <= DATE_FORMAT(now(), '%Y-%m-%d')
                                ) AS max_date,
                                (
                                    SELECT 
                                        MAX(id) 
                                    FROM 
                                        usage_voip u 
                                    WHERE 
                                        u.e164 = v.number AND 
                                        (
                                            (
                                                actual_from <= DATE_FORMAT(now(), '%Y-%m-%d') AND 
                                                actual_to >= DATE_FORMAT(now(), '%Y-%m-%d')
                                            ) OR 
                                            actual_from >= '2029-01-01'
                                        )
                                ) as active_usage_id,
                               
                                ( 
                                    SELECT 
                                        MAX(ts) 
                                    FROM 
                                        log_tarif lt, usage_voip u  
                                    WHERE 
                                        u.e164 = v.number AND 
                                        lt.service = 'usage_voip' AND  
                                        u.id = lt.id_service AND 
                                        u.actual_from = '2029-01-01' AND 
                                        u.actual_to = '2029-01-01' AND 
                                        u.status = 'connecting' 
                                    GROUP BY lt.id_service
                                ) AS date_reserved
                            FROM
                                voip_numbers v
                            WHERE 
                                number BETWEEN '".$rangeFrom."' AND '".$rangeTo."' 
                        )a 
                        LEFT JOIN clients c ON (c.id = a.client_id)
                        WHERE beauty_level IN ('".implode("','", $beauty)."')
                        HAVING status IN ('".implode("','", $group)."')
                    ");

            $fromTime = strtotime("first day of -3 month, midnight");

            foreach($ns as &$n)
            {
                if ($n["site_publish"] == "Y" && $n["status"] != "stop")
                    $unsetPublish[] = $n["number"];

                $n["calls"] = "";
                $n["count_3m"] = 0;

                if($n["status"] == "stop")
                {
                    foreach($pg_db->AllRecords("
                    select to_char(time, 'Mon') as mnth_s, to_char(time, 'MM') as mnth, 
                        sum(1) as count_calls,
                        sum(case when time between now() - interval '3 month' and now() then 1 else 0 end) count_3m
                    from calls.calls_".$n['region']."
                    where time > '".date("Y-m-d H:i:s", $fromTime)."'
                    and usage_id is null 
                    and region=".$n['region']." 
                    and usage_num = '".$n["number"]."'
                    group by mnth, mnth_s
                    order by mnth
                    ") as $c)
                    {
                        $n["calls"] .= ($n["calls"] ? ", " : "").$c["mnth_s"].": ".$c["count_calls"];
                        $n["count_3m"] += $c["count_3m"];
                    }
                }

                if($n["count_3m"])
                {
                    $n["count_avg3m"] = round($n["count_3m"]/3, 2);
                }
            }
        }

        if ($unsetPublish)
        {
            $db->Query($q = "update voip_numbers set site_publish ='N' where number in ('".implode("','", $unsetPublish)."')");
        }

        $design->assign("ns", $ns);
        $design->assign("ns_count", count($ns));
        $design->AddMain("stats/voip_free_stat.htm");

    }
	function stats_callback($fixclient){
		global $db,$design,$fixclient_data;
		if (!$fixclient) {trigger_error('Выберите клиента');return;}

		$dateFrom = new DatePickerValues('date_from', 'first');
		$dateTo = new DatePickerValues('date_to', 'last');

		$from = $dateFrom->getTimestamp();
		$to = $dateTo->getTimestamp();
		
		DatePickerPeriods::assignStartEndMonth($dateFrom->day, 'prev_', '-1 month');
		DatePickerPeriods::assignPeriods(new DateTime());

		$detality=get_param_protected('detality','day');

		$stats=$this->GetStatsCallback($from,$to,$detality,$fixclient_data['id']);
		if (!$stats) return;
		$design->assign('detality',$detality);
		$design->assign('stats',$stats);
		$design->AddMain('stats/callback.tpl');
		$design->AddMain('stats/callback_form.tpl');
	}

	function GetStatsInternet($client,$from,$to,$detality,$routes,$is_collocation=0){
		global $db;
		if(date('Y-m-d',$from)=='2029-01-01'){
			$r=array('in_bytes'=>0, 'out_bytes'=>0,'ts'=>0,'tsf'=>0);
			return array($r,$r);
		}

		$group='';

		if($detality=='year'){
			$tbl='traf_flows_1d';
			$group=' group by YEAR(time)';
			$format='Y г.';
			$order='time';
		}elseif($detality=='month'){
			$tbl='traf_flows_1d';
			$group=' group by YEAR(time), MONTH(time)';
			$format='Месяц Y г.';
			$order='time';
		}elseif($detality=='day'){
			$tbl='traf_flows_1d';
			$group=' group by time';
			$format='d месяца Y г.';
			$order='time';
		}elseif($detality=='hour'){
			$tbl='traf_flows_1h';
			$group=' group by time';
			$format='d месяца Y г. H:i';
			$order='time';
		}elseif($detality=='ip'){
			$tbl='traf_flows_1d';
			$group=' group by ip';
			$format='&\nb\sp;';
			$order='ip';
		}elseif($detality=='no'){
			$tbl='traf_flows_1d';
			$group=' group by time=0';
			$format='';
			$order='time';
		}else
			return;
//printdbg($routes);
		$P = array('OR');
		foreach($routes as $k=>$R)
			if(
					$R[1]!='9999-00-00'
				&&
					$R[1]!='2029-01-01'
				&&
					$R[2]>=date('Y-m-d',$from)
			){

				$res = netmask_to_ip_sum($R[0]);
				if($res)
				{
					list($ip,$sum)=$res;

					$t = array('AND','time>="'.$R[1].'"');
					if($R[2]!="9999-00-00" && $R[2]!="2029-01-01")
						$t[]='time<="'.$R[2].'"';
					if($sum<=128){
						$cnt = 0;
						$s='ip_int IN (';
						for($i=0;$i<$sum;$i++){
							$s.=($i?',':'').($ip+$i);
							$cnt++;
						}
						if($cnt>0)
							$t[]=$s.')';
					}else{
						$t[]='ip_int>='.$ip;
						$t[]='ip_int<='.($ip+$sum-1);
					}
					$P[]=$t;
				}else{
					$P = array("AND", "1=2");
				}

			}



		$R=array();
		$T=array(
			'in_bytes'=>0,
			'out_bytes'=>0,
			'in_r'=>0,
			'in_r2'=>0,
			'in_f'=>0,
			'out_r'=>0,
			'out_r2'=>0,
			'out_f'=>0
		);
		//define("print_sql",1);
		if(count($P)>1){
			$W=array('AND',$P,'router="rubicon"','time>=FROM_UNIXTIME('.$from.')');
			if(date('Y-m-d',$to)!="2029-01-01")
				$W[]='time<FROM_UNIXTIME('.$to.'+86400)';
			//printdbg($W);
			$whsql=MySQLDatabase::Generate($W);

			if($is_collocation){
				if($group){
					$db->Query($q="
						select
							inet_ntoa(ip_int) as ip,
							sum(in_r) as in_r,
							sum(in_r2) as in_r2,
							sum(in_f) as in_f,
							sum(out_r) as out_r,
							sum(out_r2) as out_r2,
							sum(out_f) as out_f,
							UNIX_TIMESTAMP(time) as ts
						from
							$tbl
						where
							".$whsql.$group."
						ORDER BY
							".$order."
						ASC LIMIT
							5000
					");
				}else{
					$db->Query($q="
						select
							inet_ntoa(ip_int) as ip,
							in_r,
							in_r2,
							in_f,
							out_r,
							out_r2,
							out_f,
							UNIX_TIMESTAMP(time) as ts
						from
							$tbl
						where
							".$whsql.$group."
						ORDER BY
							".$order." ASC
						LIMIT 5000
					");
				}
			}else{
				$db->Query($q="
					select
						inet_ntoa(ip_int) as ip,
						".($group?'sum':'')."(in_r+in_r2+in_f) as in_bytes,
						".($group?'sum':'')."(out_r+out_r2+out_f) as out_bytes,
						UNIX_TIMESTAMP(time) as ts
					from
						$tbl
					where
						".$whsql.$group."
					ORDER BY
						".$order." ASC
					LIMIT 5000
				");
			}

			//printdbg($db->NumRows(), $q);
			if ($db->NumRows()==5000) trigger_error('Статистика отображается не полностью. Сделайте ее менее детальной или сузьте временной период');
			while ($r=$db->NextRecord()){
				$r['tsf']=mdate($format,$r['ts']);
				$R[]=$r;
				//printdbg($r);
				if ($is_collocation) {
					$T['in_bytes']+=$r['in_r2']+$r['in_f'];
					$T['out_bytes']+=$r['out_r2']+$r['out_f'];
					$T['in_r']+=$r['in_r'];
					$T['in_r2']+=$r['in_r2'];
					$T['in_f']+=$r['in_f'];
					$T['out_r']+=$r['out_r'];
					$T['out_r2']+=$r['out_r2'];
					$T['out_f']+=$r['out_f'];
				} else {
					$T['in_bytes']+=$r['in_bytes'];
					$T['out_bytes']+=$r['out_bytes'];
				}
			}
		}
		$T['ts']='<b>Итого</b>';
		$T['tsf']='<b>Итого</b>';
		$T['ip']='&nbsp;';
		$R[]=$T;
		return $R;
	}
	function GetStatsVPN($client,$from,$to,$detality,$IPs){
		global $db;
		if (date('Y-m-d',$from)=='2029-01-01') {
			$r=array('in_bytes'=>0, 'out_bytes'=>0,'ts'=>0,'tsf'=>0);
			return array($r,$r);
		}
		$group='';
		if ($detality=='year'){
			$tbl='mod_traf_1d';
			$group=' group by YEAR(datetime)';
			$format='Y г.';
			$order='datetime';
		} elseif ($detality=='month'){
			$tbl='mod_traf_1d';
			$group=' group by MONTH(datetime)';
			$format='Месяц Y г.';
			$order='datetime';
		} elseif ($detality=='day'){
			$tbl='mod_traf_1d';
			$group=' group by datetime';
			$format='d месяца Y г.';
			$order='datetime';
		} elseif ($detality=='hour'){
			$tbl='mod_traf_5m';
			$group=' group by DATE(datetime),HOUR(datetime)';
			$format='d месяца Y г. H:i';
			$order='datetime';
		} elseif ($detality=='ip'){
			$tbl='mod_traf_1d';
			$group=' group by ip_int';
			$format='&\nb\sp;';
			$order='ip';
		} elseif ($detality=='no') {
			$tbl='mod_traf_1d';
			$group=' group by 1';
			$format='';
			$order='datetime';
		} else return;

		$whsql='';
		foreach ($IPs as $k=>$R) if ($R['actual_from']!='9999-00-00' && $R['actual_from']!='2029-01-01'){
			if ($whsql) $whsql.=' OR ';
			$whsql.='(ip_int=INET_ATON("'.$R['ip'].'") AND (datetime>="'.$R['actual_from'].'")'.
					($R['actual_to']=="9999-00-00" || $R['actual_to']=="2029-01-01" ?'':' AND (datetime<="'.$R['actual_to'].'")').')';
		}

		$R=array();
		$rt=array('in_bytes'=>0, 'out_bytes'=>0);
		if ($whsql) {
			$whsql= '(datetime>=FROM_UNIXTIME('.$from.') AND datetime<FROM_UNIXTIME('.$to.'+86400)) AND ('.$whsql.')';
			$db->Query("select inet_ntoa(ip_int) as ip,sum(transfer_rx) as in_bytes,sum(transfer_tx) as out_bytes,UNIX_TIMESTAMP(datetime) as ts from $tbl where ".$whsql.$group." ORDER by ".$order." ASC LIMIT 5000");

			if ($db->NumRows()==5000) trigger_error('Статистика отображается не полностью. Сделайте ее менее детальной или сузьте временной период');
			while ($r=$db->NextRecord()){
				$r['tsf']=mdate($format,$r['ts']);
				$R[]=$r;
				$rt['in_bytes']+=$r['in_bytes'];
				$rt['out_bytes']+=$r['out_bytes'];
			}
		}
		$rt['ts']='<b>Итого</b>';
		$rt['tsf']='<b>Итого</b>';
		$rt['ip']='&nbsp;';
		$R[]=$rt;
		return $R;
	}

    function FindByNumber($region , $from, $to, $find)
    {
        global $pg_db;
      $R = array();
      $geo = array();
        foreach($pg_db->AllRecords($q =
                  "SELECT direction_out,usage_num,phone_num,len,time, geo_id FROM calls.calls
                  WHERE \"time\" BETWEEN '".date("Y-m-d", $from)." 00:00:00' AND '".date("Y-m-d", $to)." 23:59:59'
                  AND phone_num = '".$find."'
                  AND region = '".$region."'
                  AND operator_id < 50
                  LIMIT 1000") as $l)
        {
          $l["time"] = mdate("d месяца Y г. H:i:s", strtotime($l["time"]));

          if ($l['len']>=24*60*60) $d=floor($l['len']/(24*60*60)); else $d=0;
          $l["len"]=($d?($d.'d '):'').gmdate("H:i:s",$l['len']-$d*24*60*60);

          if (isset($l['geo_id']))
          {
            if (!isset($geo[$l['geo_id']]))
              $geo[$l['geo_id']] = $pg_db->GetValue('select name from geo.geo where id='.((int)$l['geo_id']));

            $l['geo'] = $geo[$l['geo_id']];

            if ($l['mob'] == 't')
              $l['geo'] .= ' (mob)';
          } else
            $l['geo'] = '';


          $R[] = $l;
        }

      return $R;
    }

    function GetStatsVoIP($region,$from,$to,$detality,$client_id,$usage_arr,$paidonly = 0,$skipped = 0, $destination='all',$direction='both', $regions = array()){
        global $pg_db;

        /*
         $db_calls = new PgSQLDatabase(	str_replace('[region]', $region, R_CALLS_HOST),
                 R_CALLS_USER, R_CALLS_PASS,
                 str_replace('[region]', $region, R_CALLS_DB)	);
        */
        $from = date('Y-m-d', $from);
        $to = date('Y-m-d', $to);

        if ($detality=='call'){
            $group='';
            $format='d месяца Y г. H:i:s';
        } elseif ($detality=='year'){
            $group=" group by date_trunc('year',month)";
            $format='Y г.';
        } elseif ($detality=='month'){
            $group=" group by date_trunc('month',month)";
            $format='Месяц Y г.';
        } elseif ($detality=='day'){
            $group=' group by day';
            $format='d месяца Y г.';
        } else {
            $group='';
            $format='d месяца Y г. H:i:s';
        }
        $W=array('AND');

        $W[] = "time>='".$from."'";
        $W[] = "time<='".$to." 23:59:59'";



        if($destination<>'all'){
            $dg = explode("-", $destination);
            $dest = intval($dg[0]);
            if ($dest == 0)
                $W[] = 'dest<='.$dest;
            else
                $W[] = 'dest='.$dest;
            if(count($dg)>1){
                if ($dg[1] == 'm') {
                    $W[] = 'mob=true';
                }elseif ($dg[1] == 'f') {
                    $W[] = 'mob=false';
                }
            }
        }

        if($direction <> 'both'){
            if($direction == 'in')
                $W[] = 'direction_out=false';
            else
                $W[] = 'direction_out=true';
        }

        $W[]=(isset($usage_arr) && count($usage_arr) > 0) ? 'usage_id IN (' . implode($usage_arr, ',') . ')' : 'FALSE';

        if ($paidonly) {
            $W[]='amount!=0';
        }

        //$Trans0= array('BUSY'=>'номер занят','FAILED'=>'ошибка','NO ANSWER'=>'нет ответа','CONGESTION'=>'номер занят');
        //$Trans=array();
        //$db->Query('select * from usage_nvoip_result');
        //while ($r=$db->NextRecord()) if (isset($Trans0[$r['param']])) $Trans[$r['id']]=$Trans0[$r['param']];

        if ($detality != 'dest') {
            $R=array();
            $sql="
                            select
                                    ".($group?'':'id,')."
                                    ".($group?'':'phone_num,')."
                                    ".($group?'':'geo_id,')."
                                    ".($group?'':'mob,')."
                                    ".($group?'':'usage_num,')."
                                    ".($group?'':'redirect_num,')."
                                    ".($group?'':'usage_id,')."
                                    ".($group?'':'direction_out,');
            if ($detality == 'day') $sql.= ' day as ts1, ';
            elseif ($detality == 'month') $sql.= " date_trunc('month',month) as ts1, ";
            elseif ($detality == 'year') $sql.= " date_trunc('year',month) as ts1, ";
            else $sql.= ' time as ts1, ';
            $sql .=
            'cast('.($group?'sum':'').'(amount)/100.0 as NUMERIC(10,2)) as price,
                                    '.($group?'sum':'').'('.($paidonly?'case amount>0 when true then len else 0 end':'len').') as ts2,
                                    '.($group?'sum('.($paidonly?'case amount>0 when true then 1 else 0 end':1).')':'1').' as cnt
                            from
                                    calls.calls_'.intval($region).'
                            where '.MySQLDatabase::Generate($W).$group."
                            ORDER BY
                                    ts1 ASC
                            LIMIT 5000";

            $pg_db->Query($sql);

            if ($pg_db->NumRows()==5000) trigger_error('Статистика отображается не полностью. Сделайте ее менее детальной или сузьте временной период');
            $rt=array('price'=>0, 'ts2'=>0,'cnt'=>0,'is_total'=>true);
            $geo = array();

            //while ($r=$db_calls->NextRecord()){
            $records = $pg_db->AllRecords();
            foreach($records as $r)
            {
                if (isset($r['geo_id']))
                {
                    if (!isset($geo[$r['geo_id']]))
                        $geo[$r['geo_id']] = $pg_db->GetValue('select name from geo.geo where id='.((int)$r['geo_id']));
                    $r['geo'] = $geo[$r['geo_id']];
                    if ($r['mob'] == 't') $r['geo'] .= ' (mob)';
                } else $r['geo'] = '';

                $dt = explode(' ', $r['ts1']);
                $d = explode('-', $dt[0]);
                if (count($dt)>1)
                    $t = explode(':', $dt[1]);
                else $t=array('0','0','0');
                $ts = mktime($t[0],$t[1],intval($t[2]),$d[1],$d[2],$d[0]);
                $r['tsf1']=mdate($format,$ts);
                $r['mktime'] = $ts;
                $r['is_total'] = false;

                if ($r['ts2']>=24*60*60) $d=floor($r['ts2']/(24*60*60)); else $d=0;
                $r['tsf2']=($d?($d.'d '):'').gmdate("H:i:s",$r['ts2']);
                $r['price'] = number_format($r['price'], 2, '.','');
                //if (!$group && !$paidonly && ($r['ts2']<0) && isset($Trans[-$r['ts2']])) $r['cause']=$Trans[-$r['ts2']]; else $r['cause']='';

                $R[]=$r;
                $rt['price']+=$r['price'];
                $rt['cnt']+=$r['cnt'];
                $rt['ts2']+=$r['ts2'];
            }
            $rt['ts1']= 'Итого';
            $rt['tsf1']='<b>Итого</b>';
            $rt['num_to']='&nbsp;';
            $rt['num_from']='&nbsp;';
            if ($rt['ts2']>=24*60*60) $d=floor($rt['ts2']/(24*60*60)); else $d=0;
            $rt['tsf2']='<b>'.($d?($d.'d '):'').gmdate("H:i:s",$rt['ts2']-$d*24*60*60).'</b>';
            $rt['price']=number_format($rt['price'], 2, '.','') .' (<b>'.number_format($rt['price']*1.18, 2, '.','').' - Сумма с НДС</b>)';

            $R['total']=$rt;
        }else{
            $sql="  select dest, mob, cast(sum(amount)/100.0 as NUMERIC(10,2)) as price, sum(len) as len, sum(1) as cnt
                            from calls.calls_".intval($region)."
                            where ".MySQLDatabase::Generate($W)."
                            GROUP BY dest, mob";
            $R = array(     'mos_loc'=>  array('tsf1'=>'Местные Стационарные','cnt'=>0,'len'=>0,'price'=>0,'is_total'=>false),
                            'mos_mob'=> array('tsf1'=>'Местные Мобильные','cnt'=>0,'len'=>0,'price'=>0,'is_total'=>false),
                            'rus_fix'=> array('tsf1'=>'Россия Стационарные','cnt'=>0,'len'=>0,'price'=>0,'is_total'=>false),
                            'rus_mob'=> array('tsf1'=>'Россия Мобильные','cnt'=>0,'len'=>0,'price'=>0,'is_total'=>false),
                            'int'=>     array('tsf1'=>'Международка','cnt'=>0,'len'=>0,'price'=>0,'is_total'=>false));
            //$db_calls->Query($sql);
            $pg_db->Query($sql);
            //while ($r=$db_calls->NextRecord()){
            while ($r=$pg_db->NextRecord()){
                if ($r['dest'] <= 0 && $r['mob'] == 'f'){
                    $R['mos_loc']['len'] += $r['len'];
                    $R['mos_loc']['price'] += $r['price'];
                    $R['mos_loc']['cnt'] += $r['cnt'];
                }elseif ($r['dest'] <= 0 && $r['mob'] == 't'){
                    $R['mos_mob']['len'] += $r['len'];
                    $R['mos_mob']['price'] += $r['price'];
                    $R['mos_mob']['cnt'] += $r['cnt'];
                }elseif ($r['dest'] == 1 && $r['mob'] == 'f'){
                    $R['rus_fix']['len'] += $r['len'];
                    $R['rus_fix']['price'] += $r['price'];
                    $R['rus_fix']['cnt'] += $r['cnt'];
                }elseif ($r['dest'] == 1 && $r['mob'] == 't'){
                    $R['rus_mob']['len'] += $r['len'];
                    $R['rus_mob']['price'] += $r['price'];
                    $R['rus_mob']['cnt'] += $r['cnt'];
                }elseif ($r['dest'] == 2 || $r['dest'] == 3){
                    $R['int']['len'] += $r['len'];
                    $R['int']['price'] += $r['price'];
                    $R['int']['cnt'] += $r['cnt'];
                }
            }
            $cnt = 0; $len = 0; $price = 0;
            foreach($R as $k => $r){
                $cnt += $r['cnt'];
                $len += $r['len'];
                $price += $r['price'];
                if ($r['len']>=24*60*60) $d=floor($r['len']/(24*60*60)); else $d=0;
                $R[$k]['tsf2']='<b>'.($d?($d.'d '):'').gmdate("H:i:s",$r['len']-$d*24*60*60).'</b>';
                $R[$k]['price'] = number_format($r['price'], 2, '.','');
            }
            $rt['is_total']=true;
            $rt['tsf1']='<b>Итого</b>';
            if ($len>=24*60*60) $d=floor($len/(24*60*60)); else $d=0;
            $rt['tsf2']='<b>'.($d?($d.'d '):'').gmdate("H:i:s",$len-$d*24*60*60).'</b>';
            $rt['price']= number_format($price, 2, '.','') .' (<b>'.number_format($price*1.18, 2, '.','').' - Сумма с НДС</b>)';
            $rt['cnt']=$cnt;
            $R['total'] = $rt;
        }
        return $R;
    }

	function GetStatsCallback($from,$to,$detality,$client_id){
		global $db;
		$group='';
		if ($detality=='no'){
			$group=' group by 1';
			$format='';
		} else if ($detality=='year'){
			$group=' group by YEAR(ts)';
			$format='Y г.';
		} elseif ($detality=='month'){
			$group=' group by MONTH(ts)';
			$format='Месяц Y г.';
		} elseif ($detality=='day'){
			$group=' group by DATE(ts)';
			$format='d месяца Y г.';
		} else {
			$group='';
			$format='d месяца Y г. H:i:s';
		}
		$groupQ=$group?'sum':'';
		$whsql='(C.ts>=FROM_UNIXTIME('.$from.')) AND (C.ts<FROM_UNIXTIME('.$to.'+86400)) AND (client_id='.$client_id.')';

		$R=array();
		$sql="select C.*,PA.phone_num as num_from,PB.phone_num as num_to,".
				$groupQ."(A.tarif_sum) as priceFrom,".
				$groupQ."(B.tarif_sum) as priceTo,".
				$groupQ."(A.tarif_sum+B.tarif_sum) as price,".
				"UNIX_TIMESTAMP(ts) as ts1,".
				$groupQ."(B.lengthResult) as ts2 ".
				"from usage_callback_sess as C INNER JOIN usage_nvoip_sess as A ON A.id=C.sess_id_from INNER JOIN usage_nvoip_sess as B ON B.id=C.sess_id_to ".
				"INNER JOIN usage_nvoip_phone as PA ON PA.phone_id=A.phone_id ".
				"INNER JOIN usage_nvoip_phone as PB ON PB.phone_id=B.phone_id ".
				"where ".$whsql.$group." ORDER by C.ts ASC LIMIT 5000";
		$db->Query($sql);
		if ($db->NumRows()==5000) trigger_error('Статистика отображается не полностью. Сделайте ее менее детальной или сузьте временной период');
		$rt=array('price'=>0,'priceFrom'=>0,'priceTo'=>0, 'ts2'=>0);
		while ($r=$db->NextRecord()){
			$r['tsf1']=mdate($format,$r['ts1']);
			if ($r['ts2']>=24*60*60) $d=floor($r['ts2']/(24*60*60)); else $d=0;
			$r['tsf2']=($d?($d.'d '):'').gmdate("H:i:s",$r['ts2']);
			$R[]=$r;
			$rt['price']+=$r['price'];
			$rt['priceFrom']+=$r['priceFrom'];
			$rt['priceTo']+=$r['priceTo'];
			$rt['ts2']+=$r['ts2'];
		}
		$rt['ts1']='Итого';
		$rt['tsf1']='<b>Итого</b>';
		$rt['price']='<b>'.$rt['price'].'</b>';
		$rt['num_to']='&nbsp;';
		$rt['num_from']='&nbsp;';
		if ($rt['ts2']>=24*60*60) $d=floor($rt['ts2']/(24*60*60)); else $d=0;
		$rt['tsf2']='<b>'.($d?($d.'d '):'').gmdate("H:i:s",$rt['ts2']-$d*24*60*60).'</b>';
		$R[]=$rt;
		return $R;
	}

	function GetStatsPPP($from,$to,$detality,$logins){
		global $db;
		$group='';
		if ($detality=='year'){
			$group=' group by YEAR(AcctStartTime)';
			$format='Y г.';
		} elseif ($detality=='month'){
			$group=' group by MONTH(AcctStartTime)';
			$format='Месяц Y г.';
		} elseif ($detality=='day'){
			$group=' group by DATE(AcctStartTime)';
			$format='d месяца Y';
		} elseif ($detality=='login') {
			$group=' group by UserName';
			$format='&\nb\sp;';
		} else {
			$group='';
			$format='d месяца Y г. H:i:s';
		}

		$whsql='(AcctStartTime>=FROM_UNIXTIME('.$from.')) AND (AcctStartTime<FROM_UNIXTIME('.$to.'+86400))';
		if (!count($logins)) return array();
		if (count($logins)==1){
			foreach ($logins as $r) $whsql.=' AND (UserName="'.$r.'")';
		} else {
			$whsql.='';
			foreach ($logins as $k=>$r) $logins[$k]='"'.$r.'"';
			$p=implode(',',$logins);
			$whsql.=' AND (UserName IN ('.$p.'))';
		}

		$R=array();
		$sql="select UserName as login,".
					($group?'sum':'')."(AcctInputOctets) as in_bytes,".
					($group?'sum':'')."(AcctOutputOctets) as out_bytes,".
					"UNIX_TIMESTAMP(AcctStartTime) as ts1,".
					($group?'sum':'')."(AcctSessionTime) as ts2 ".
					"from radacct where ".$whsql.$group." ORDER by AcctStartTime ASC LIMIT 5000";
		$db->Query($sql);
		if ($db->NumRows()==5000) trigger_error('Статистика отображается не полностью. Сделайте ее менее детальной или сузьте временной период');
		$rt=array('ts2'=>0,'in_bytes'=>0,'out_bytes'=>0);
		while ($r=$db->NextRecord()){
			$r['tsf1']=mdate($format,$r['ts1']);
			if ($r['ts2']>=24*60*60) $d=floor($r['ts2']/(24*60*60)); else $d=0;
			$r['tsf2']=($d?($d.'d '):'').gmdate("H:i:s",$r['ts2']);
			$R[]=$r;
			$rt['in_bytes']+=$r['in_bytes'];
			$rt['out_bytes']+=$r['out_bytes'];
			$rt['ts2']+=$r['ts2'];
		}
		$rt['ts1']='Итого';
		$rt['tsf1']='<b>Итого</b>';
		$rt['login']='&nbsp;';
		if ($rt['ts2']>=24*60*60) $d=floor($rt['ts2']/(24*60*60)); else $d=0;
		$rt['tsf2']='<b>'.($d?($d.'d '):'').gmdate("H:i:s",$rt['ts2']-$d*24*60*60).'</b>';
		$R[]=$rt;
		return $R;
	}

	function GetRouteListByClient($client,$val = ''){
		global $db;
		$R=array();// and usage_ip_ports.trafcounttype="flows"
		$db->Query('select usage_ip_routes.* from usage_ip_routes left join usage_ip_ports on usage_ip_ports.id=usage_ip_routes.port_id where usage_ip_ports.client="'.$client.'" order by usage_ip_routes.net');
		$i=0;
		while ($r=$db->NextRecord()){
			$i++; $r['parity']=($i%2==0 ? 'odd' : 'even');
			$r['selected']=($r['net']==$val ? 'selected' : '');
			netmask_to_net_sum($r['net'],$r['ip'],$r['sum'],$r['ip_max']);
			if ($r['sum']!=1) $r['sum']=' (' . $r['sum'] . ')'; else $r['sum']='';
			$R[$r['id']]=$r;
		}
		return $R;
	}

	function get_routes_list_ip($routes_allB){
		$routes_all=$routes_allB;
		foreach ($routes_allB as $k=>$R){
			$r=$R[0];
			if(!$r)
				continue;
			if (!preg_match("/(\d+)\.(\d+)\.(\d+)\.(\d+)(\/(\d+))?/",$r,$m))
				return;
			$ip="{$m[1]}.{$m[2]}.{$m[3]}.{$m[4]}";
			$sum=1;
			if (isset($m[6]) && $m[6]>0) for ($i=$m[6];$i<32;$i++) $sum*=2;
			$n=$m;
			for ($i=0;$i<$sum;$i++){
				$routes_all["{$n[1]}.{$n[2]}.{$n[3]}.{$n[4]}"]=array("{$n[1]}.{$n[2]}.{$n[3]}.{$n[4]}",$R[1],$R[2]);
				$n[4]++;
				if ($n[4]>=256) {$n[3]+=(int)($n[4]/256); $n[4]=$n[2]%256; $k=3;}
				if ($n[3]>=256) {$n[2]+=(int)($n[3]/256); $n[3]=$n[2]%256; $k=2;}
				if ($n[2]>=256) {$n[1]+=(int)($n[2]/256); $n[2]=$n[2]%256; $k=1;}
			}
		}
		return $routes_all;
	}

	function get_routes_list($client){
		global $db;
		$routes_all=array();
		//список всех сетей, нужен для вывода их списка.
		$db->Query('
			select
				*
			from
				usage_ip_ports
			where
				client="'.$client.'"
			order by
				id
		');
		$V=array();
		while($r=$db->NextRecord())
			$V[]=$r['id'];
		if(!count($V))
			return array(array(),array());
		$db->Query('
			select
				*
			from
				usage_ip_routes
			where
				port_id IN ("'.implode('","',$V).'")
			order by
				net,id
		');
		while ($r=$db->NextRecord()){
            if ($r['net'])
                $routes_all[$r['net']]=array($r['net'],$r['actual_from'],$r['actual_to']);
		}
		$routes_all_f=$this->get_routes_list_ip($routes_all);
		return array($routes_all_f,$routes_all);
	}
	function get_client_stats($client,$from,$to){
		list($a,$b)=$this->get_routes_list($client);
		$stats=$this->GetStatsInternet($client,$from,$to,'no',$b);
		$c=count($stats); $r=$stats[$c-1];
		return array('in_bytes'=>$r['in_bytes'],'out_bytes'=>$r['out_bytes']);
	}

	function stats_send_add($fixclient){
		global $db,$design;
		$clients=get_param_raw('clients');
		$year = get_param_integer('year');
		$month = get_param_integer('month');
		$bytes=get_param_raw('bytes');
		$max_bytes=get_param_raw('max_bytes');
		$port_id=get_param_raw('port_id');
		$flag=get_param_raw('flag');
		if (!$year || !$month || !is_array($flag) || !is_array($in_bytes) || !is_array($out_bytes) || !is_array($max_bytes) || !is_array($port_id) || !is_array($clients)) return;// $this->stats_send_view($fixclient);
		foreach ($clients as $i=>$c) if (isset($flag[$i]) && $flag[$i]){
			@$db->Query('insert into stats_send (client,state,year,month,port_id,bytes,max_bytes,message) value ("'.$c.'","ready",'.$year.','.$month.','.$port_id[$i].','.$bytes[$i].','.$max_bytes[$i].',"'.$email[$i].'")');
		}
		return $this->stats_send_view($fixclient);
	}

	function stats_send_process($fixclient){
		global $design,$db;
		$is_test=get_param_integer('test',1);
		$cont=get_param_integer('cont',0);
		$db->Query('select client from stats_send where (!last_send || (last_send+INTERVAL 1 DAY < NOW())) AND (state!="sent") group by client order by state,last_send desc,client LIMIT 5');
		$C=array(); while ($r=$db->NextRecord()) $C[$r['client']]=$r['client'];
		foreach ($C as $client){
			$this->to_client($client,$is_test);
		}

		if (count($C)) $q='IF (client IN ("'.implode('","',$C).'"),1,0)'; else $q='0';
		$db->Query('select *,'.$q.' as cur_sent from stats_send order by cur_sent desc,state,last_send desc,client');
		$R=array(); while ($r=$db->NextRecord()) {
			$r['cur_sent']=(isset($C[$r['client']]))?1:0;
			if (isset($R[$r['client']])){
				$R[$r['client']][]=$r;
			} else $R[$r['client']]=array($r);
		}

		$design->assign('send_clients',$R);
		$design->assign('refresh',30*$cont);
		if ($cont) {
			trigger_error('Отправка следующих 5ти уведомлений произойдёт через 30 секунд');
		}
		$design->AddMain('stats/send.tpl');
	}
	function stats_send_view($fixclient){
		global $db,$design;
		$db->Query('select * from stats_send order by state,last_send desc,client');
		$R=array(); while ($r=$db->NextRecord()) {
			if (isset($R[$r['client']])){
				$R[$r['client']][]=$r;
			} else $R[$r['client']]=array($r);
		}

		$design->assign('send_clients',$R);
		$design->AddMain('stats/send.tpl');
	}

	function to_client($client,$is_test = 1){
		global $db;
		$db->Query('select *,ROUND(bytes/(1024*1024),1) as mbytes,ROUND(max_bytes/(1024*1024),1) as mmax from stats_send where (client="'.$client.'") and (!last_send || (last_send+INTERVAL 1 DAY < NOW())) AND (state!="sent") group by client order by state,last_send desc,client LIMIT 5');
		$R=array(); while ($r=$db->NextRecord()) $R[]=$r;

		$db->Query('select * from clients where client="'.$client.'"');
		$C=$db->NextRecord();

		$subj="оПЕБШЬЕМХЕ РПЮТТХЙЮ";
		$body="сБЮФЮЕЛШЕ цНЯОНДЮ!" . "\n" . "яННАЫЮЕЛ бЮЛ, ВРН бШ ОПЕБШЯХКХ РПЮТТХЙ МЮ НДМНЛ ХГ БЮЬХУ ХМРЕПМЕР-ОНДЙКЧВЕМХИ.\nоН ЙЮФДНЛС ХГ ОНДЙКЧВЕМХИ РПЮТТХЙ ЯНЯРЮБКЪЕР:\n";
		foreach ($R as $r) {
			$body.=$r['mbytes'].' / '.$r['mmax'].'\n';
		}
		$body.="\n";

		$headers = "From: MCN Info <info@mcn.ru>\n";
		$headers.= "Content-Type: text/plain; charset=windows-1251\n";

		if ((defined('MAIL_TEST_ONLY') && (MAIL_TEST_ONLY==1)) || $is_test) $C['email']='andreys75@mcn.ru, shepik@yandex.ru';

		error_close();
		ob_start();
		$msg='Адрес получателя: '.$C['email'].'<br>';
		if (!$C['email']) $msg='Адрес получателя не указан<br>';

		if ($C['email'] && (mail ($C['email'],$subj,$body,$headers))){
			$db->Query('update stats_send set state="sent",last_send=NOW(),message="'.$msg.'" where (client="'.$client.'") and (state!="sent")');
		} else {
			$db->Query('update stats_send set state="error",last_send=NOW(),message="'.$msg.AddSlashes(ob_get_contents()).'" where (client="'.$client.'") and (state!="sent")');
		}
		ob_end_clean();
		error_init();
	}

	function stats_report_traff_less() {
		global $db,$design;
		$managers=array('anyone'=>'Все');
		$mtmp = array();
		$GLOBALS['module_users']->d_users_get($mtmp,'manager');
		foreach($mtmp as $key=>$val){
			$managers[$key] = $val['name']." (".$key.")";
		}
		unset($mtmp);

		$offclients_flag = get_param_protected('offclients', false);
		$design->assign('offclients',$offclients_flag);
		$design->assign('managers',$managers);
		$design->assign('manager',$manager=get_param_protected('manager', 'anyone'));

		$dateFrom = new DatePickerValues('date_from', 'first');
		$dateTo = new DatePickerValues('date_to', 'today');
		$dateFrom->format = 'Y-m-d'; $dateTo->format = 'Y-m-d';
		$date = $dateFrom->getDay();
		$date2 = $dateTo->getDay();

		$trafLess = (float)(get_param_raw('traf_less',10));

		$R = array();

        if(get_param_raw("make_report", "false") != "false")
        {
			$manager_inj = ($manager<>'anyone')?'AND c.manager = \''.addcslashes($manager,"'\\").'\' ':'';
			$q = "
				SELECT
					uip.*,
					tfr.*,
					tfr.sum_in+tfr.sum_out total_sum,
					ti.name tarif_name,
					ti.mb_month,
					ti.pay_month,
					ti.pay_mb
				FROM
					usage_ip_ports uip
				INNER JOIN
					clients c
				ON
					c.client = uip.client
				AND
					c.status = 'work'
				AND
					c.client <> ''
				".$manager_inj."
				INNER JOIN
					usage_ip_routes uir
				ON
					uir.port_id = uip.id
				INNER JOIN
					log_tarif lt
				ON
					lt.id_service = uip.id
				AND
					lt.service = 'usage_ip_ports'
				AND
					lt.date_activation <= NOW()
				AND
					lt.id_tarif<>0
				INNER JOIN
					tarifs_internet ti
				ON
					ti.id = lt.id_tarif
				AND
					ti.type = 'I'
				AND
					ti.name NOT LIKE 'cdma%'
				AND
					ti.name NOT LIKE 'Резервирование%'
				AND
					ti.type_internet <> 'wimax'
				LEFT JOIN
					(
						SELECT
							id_port,
							sum(in_bytes) as sum_in,
							sum(out_bytes) as sum_out
						FROM
							traf_flows_report
						WHERE
							date BETWEEN '".$date."' AND '".$date2."'
						GROUP BY
							id_port
					) tfr
				ON
					tfr.id_port = uip.id
				WHERE
					uip.client <> ''
				AND
					uip.actual_from < '".$date2."'
				AND
					uip.actual_to > '".(($offclients_flag)?$date:$date2)."'
				AND
					lt.id = (
						SELECT
							id
						FROM
							log_tarif
						WHERE
							service = 'usage_ip_ports'
						AND
							date_activation <= NOW()
						AND
							id_service = uip.id
						ORDER BY
							date_activation desc,
							ts desc,
							id desc
						LIMIT 1
					)
				HAVING
					total_sum < ".($trafLess*1024*1024)."
				OR
					total_sum IS NULL
				ORDER BY
					uip.client
			";

            $R = $db->AllRecords($q,'id',MYSQL_ASSOC);

            /*foreach ($T as $r) { // ugly code...
                $r['tarif'] = get_tarif_current('usage_ip_ports',$r['id_port']);
                $R[$r['id']] = $r;
            }
            usort($R,create_function('$a,$b','return $a["client"]>=$b["client"];'));*/
        }

		$design->assign('newgen',true);
		$design->assign('stats',$R);
		$design->assign('traf_less',$trafLess);
		$design->AddMain('stats/report_traff_less.tpl');
		$design->AddMain('stats/report_traff_less_form.tpl');
	}
	function stats_report(){
		global $db,$design;
		$date=param_load_date('',getdate(),true);

		if(isset($_GET['d']) && (int)$_GET['d']===0){
			$date_sql_filter = "tfr.date BETWEEN '".
				date('Y-m-d',mktime(0, 0, 0, (int)$_GET['m'], 1, (int)$_GET['y'])).
				"' and '".
				date('Y-m-d',mktime(0,0,0,((int)$_GET['m']==12)?1:(int)$_GET['m']+1,1,((int)$_GET['m']==12)?(int)$_GET['y']+1:(int)$_GET['y'])-60*60*24).
				"'";
			$design->assign('d',0);
		}else
			$date_sql_filter = "tfr.date='".$date."'";

		$isInLessOut = get_param_raw('is_in_less_out',"nan");
        $isInLessOut = $isInLessOut == "true";

		$isOver = get_param_raw('is_over',"nan");
        $isOver = $isOver == "true";

        if(get_param_raw("over", "nan") == "nan"){
            $isInLessOut = $isOver = true;
        }

		$over = (float)(get_param_raw('over',0.3));

		$isTrafLess = get_param_raw('is_traf_less',"nan") == "true";
		$trafLess = (float)(get_param_raw('traf_less',10));
		$show_unlim = get_param_protected('show_unlim',false);

		$R = array();

        if($isInLessOut || $isOver || $isTrafLess){
			/*$q = " // ugly code ...
				select
					P.*,
					R.in_bytes,
					R.out_bytes
				from
					traf_flows_report as R
				INNER JOIN
					usage_ip_ports as P
				ON
					P.id=R.id_port
				WHERE
					".$date_sql_filter;

			$T = $db->AllRecords($q);

            foreach($T as $r){
				$r['tarif'] = get_tarif_current('usage_ip_ports',$r['id']);
				if(!$show_unlim){
					if(preg_match('/Безлимитный/',$r['tarif']['name']))
						continue;
				}
                $traf = ($r['in_bytes']+$r['out_bytes'])/(1024*1024);
                if(
					(
						$isOver
					&&
						$traf>10
					&&
						$traf*$over > $r['tarif']['mb_month']
					)
				||
					(
						$isInLessOut
					&&
						$traf>10
					&&
						$r['out_bytes'] > $r['in_bytes']
					)
				||
					(
						$isTrafLess
					&&
						$traf < $trafLess
					)
				){
					$r["flags"]["over"] = $traf>10 && $traf*$over>$r['tarif']['mb_month'];

					$r["flags"]["in_less_out"] = $traf>10 && $r['out_bytes']>$r['in_bytes'];
					$r["flags"]["traf_less"] = $traf < $trafLess;

                    //$r["client"] .= " ".$r['tarif']['mb_month'];
					$R[$r['id']] = $r;
                }
            }
            usort($R,create_function('$a,$b','return $a["client"]>=$b["client"];'));
        }*/

			/**
			 * Устанавливаем флаги.
			 * Не показываем клиентов, трафик которых меньше 10 мегабайт, но только
			 * если не установлен флаг $isTrafLess.
			 */
			if($show_unlim){ // показывать ли безлимитные тарифы
				$unlim_flag = 'AND ti.pay_mb = 0';
			}else{
				$unlim_flag = "AND ti.pay_mb > 0";
			}

			$flags = array();

			if($isOver){ // превышение трафика в "небезлимитных" тарифах
				$flags['over_flag'] = "
						(
							((sum(tfr.in_bytes)+sum(tfr.out_bytes))/(1024*1024)) > 10
						AND
							(((sum(tfr.in_bytes)+sum(tfr.out_bytes))/(1024*1024)) * ".$over.") > ti.mb_month
						AND
							ti.name NOT LIKE 'Безлимитный%'
						)";
			}

			if($isInLessOut){
				$flags['less_out_flag'] = "
						(
							((sum(tfr.in_bytes)+sum(tfr.out_bytes))/(1024*1024)) > 10
						AND
							sum(tfr.out_bytes) > sum(tfr.in_bytes)
						)";
			}

			if($isTrafLess){
				$flags['traf_less_flag'] = "
						((sum(tfr.in_bytes)+sum(tfr.out_bytes))/(1024*1024)) < ".$trafLess;
			}

			$query = "
				SELECT
					uip.id,
					uip.client,
					sum(tfr.in_bytes) in_bytes,
					sum(tfr.out_bytes) out_bytes,
					ti.name tarif_name,
					ti.mb_month,
					ti.pay_month,
					ti.pay_mb,
					IF((
							((sum(tfr.in_bytes)+sum(tfr.out_bytes))/(1024*1024)) > 10
						AND
							(((sum(tfr.in_bytes)+sum(tfr.out_bytes))/(1024*1024)) * ".$over.") > ti.mb_month
						AND
							ti.name NOT LIKE 'Безлимитный%'
					),'Y','N') over_flag,
					IF((
							((sum(tfr.in_bytes)+sum(tfr.out_bytes))/(1024*1024)) > 10
						AND
							sum(tfr.out_bytes) > sum(tfr.in_bytes)
					),'Y','N') less_out_flag,
					IF(
						((sum(tfr.in_bytes)+sum(tfr.out_bytes))/(1024*1024)) < ".$trafLess.",
					'Y','N') traf_less_flag
				FROM
					traf_flows_report as tfr
				INNER JOIN
					usage_ip_ports as uip
				ON
					uip.id=tfr.id_port
				LEFT JOIN
					log_tarif lt
				ON
					lt.id_service = uip.id
				AND
					lt.service = 'usage_ip_ports'
				INNER JOIN
					tarifs_internet ti
				ON
					ti.id = lt.id_tarif
				".$unlim_flag."
				WHERE
					".$date_sql_filter."
				AND
					lt.id = (
						SELECT
							id
						FROM
							log_tarif
						WHERE
							id_service = uip.id
						AND
							service = 'usage_ip_ports'
						ORDER BY
							date_activation desc,
							ts desc,
							id desc
						LIMIT 1
					)
				GROUP BY
					uip.id,
					uip.client,
					ti.name,
					ti.mb_month,
					ti.pay_month,
					ti.pay_mb
				HAVING
					".implode("\n\t\t\t\t\tOR",$flags)."
				ORDER BY
					uip.client ASC
			";

			$query_total = "
				select
					id_port,
					sum(in_bytes) in_bytes,
					sum(out_bytes) out_bytes
				from
					traf_flows_report
				where
					date between date_format('".$date."','%Y-%m-1') and date_format('".$date."','%Y-%m-1')+interval 1 month-interval 1 day
				group by
					id_port";

			$R = $db->AllRecords($query,'id',MYSQL_ASSOC);
			$T = $db->AllRecords($query_total,'id_port',MYSQL_ASSOC);
			$design->assign('newgen',true);
		}

		if(isset($_GET['show_tarif_traf']) && $_GET['show_tarif_traf']=='true')
			$design->assign('show_tarif_traf',true);
		$design->assign('stats',$R);
		$design->assign('totals',$T);
		$design->assign('is_in_less_out',$isInLessOut);
		$design->assign('is_over',$isOver);
		$design->assign('is_traf_less',$isTrafLess);
		$design->assign('show_unlim',$show_unlim);
		$design->assign('over',$over);
		$design->assign('traf_less',$trafLess);
		$design->AddMain('stats/report.tpl');
		$design->AddMain('stats/report_form.tpl');
	}

	function stats_report_voip_e164_free(){
		global $db,$design;

		$query = "
			SELECT
				`int`.`e164`,
				`cl`.`client`,
				`cl`.`id`
			FROM
				(
					SELECT
						`uv`.`e164`,
						MAX(`uv`.`actual_to`) `to`
					FROM
						`usage_voip` `uv`
					WHERE
						`e164` NOT IN (
							SELECT SQL_CACHE
								`e164`
							FROM
								`usage_voip`
							WHERE
								DATE(NOW()) BETWEEN `actual_from` AND `actual_to`
							AND
								LENGTH(`e164`) = 11
						)
					AND
						LENGTH(`uv`.`e164`) = 11
					AND
						SUBSTRING(`uv`.`e164` FROM 1 FOR 4) <> '7095'
					AND
						SUBSTRING(`uv`.`e164` FROM 5 FOR 3) in ('638','950')
					GROUP BY
						`uv`.`e164`
				) `int`
			LEFT JOIN
				`usage_voip` `v`
			ON
				`v`.`e164` = `int`.`e164`
			AND
				`v`.`actual_to` = `int`.`to`
			LEFT JOIN
				`clients` `cl`
			ON
				`cl`.`client` = CAST(`v`.`client` AS CHAR)
		";
		$ret = $db->AllRecords($query,null,MYSQL_ASSOC);

		$design->assign('e164',$ret);
		$design->AddMain('stats/voip_e164_free.tpl');
	}

	function stats_report_sms_gate($fixuser){
		global $db,$design;

		$dateFrom = new DatePickerValues('date_from', 'today');
		$dateTo = new DatePickerValues('date_to', 'today');
		$dateFrom->format = 'Y-m-d';$dateTo->format = 'Y-m-d';
		$date_from = $dateFrom->getDay();
		$date_for = $dateTo->getDay();

		$cf = (isset($_REQUEST['client_fil']))?(int)$_REQUEST['client_fil']:0;
		$clients = array();
		// <editor-fold defaultstate="collapsed" desc="clients_query">
		$query_clients = "
			select
				`u`.`client`,
				`u`.`client_id`,
				IF(`u`.`client`='".addcslashes($fixuser, "\\'")."' or `u`.`client_id`=".$cf.",'1','0') `current`
			from
				`usage_sms_gate` `u`
			order by
				`u`.`client`
		";
		// </editor-fold>
		$db->Query($query_clients);
		while($row=$db->NextRecord(MYSQL_ASSOC)){
			$clients[$row['client_id']] = array(
				'client'=>$row['client'],
				'id'=>$row['client_id'],
				'current'=>$row['current']
			);
			if($row['current']==1)
				$curc = $row['client_id'];
		}
		if(!isset($curc)){
			$curc = 0;
		}
		$design->assign('clients',$clients);

		$thiamis = new MySQLDatabase('thiamis.mcn.ru','sms_stat','yeeg5oxGa','sms2');

		// <editor-fold defaultstate="collapsed" desc="stat_query">
		$query_stat = "
			select
				`client_id` `sender`,
				`smses` `count`,
				`date` `date_hour`
			from
				`sms_send_byday`
			where
				`client_id` = ".$curc."
			and
				`date` between '".$date_from."' and '".$date_for."'
		";
		// </editor-fold>
		$thiamis->Query($query_stat);
		$stat = array(
			'rows'=>array(),
			'total'=>0
		);
		while($row = $thiamis->NextRecord(MYSQL_ASSOC)){
			$stat['rows'][] = $row;
			$stat['total'] += $row['count'];
		}
		$design->assign('stat',$stat);
		$design->AddMain('stats/sms_gate.tpl');
	}

	function stats_report_services(){
		global $db,$design;
		if(isset($_REQUEST['s_i'])){
			$idxs = array('s_i','s_p','s_v','s_c','s_e');
			foreach($idxs as $key){
				$$key = $_REQUEST[$key];
			}
			foreach($idxs as $key){
				if($$key == 'unset')
					unset($$key);
				elseif($$key == 'on')
					$$key = true;
				else
					$$key = false;
				if(isset($$key)){
					$design->assign($key,$$key);
				}
			}
		}

		$def_statuses = array('work','negotiations','testing','connecting','debt','suspended','operator','distr','blocked');
		$tarif_extra_id = get_param_raw("s_s_e", 0);
		$tarif_internet_id = get_param_raw("s_s_i", 0);
		$statuses = get_param_raw("status", $def_statuses);
		$design->assign("s_s_e", $tarif_extra_id);
		$design->assign("s_s_i", $tarif_internet_id);
		$design->assign("statuses", $statuses);

		$query = "
			select
				`c`.`id`,
				`c`.`client`,
				`uip`.`id` `iport_id`,
				`uv`.`id` `voip_id`,
				`ue`.`id` `extra_id`,
				`ti`.`id` `itarif_id`,
				`ti`.`name` `itarif`,
				`ti`.`type` `itype`,
				`tv`.`id` `ptarif_id`,
				`tv`.`name` `ptarif`,
				`te`.`id` `etarif_id`,
				`te`.`description` `etarif`
			from
				`clients` `c`
			left join
				`usage_ip_ports` `uip`
			on
				`uip`.`client` = `c`.`client`
			and
				now() between `uip`.`actual_from` and `uip`.`actual_to`
			left join
				`log_tarif` `lti`
			on
				`lti`.`id_service` = `uip`.`id`
			and
				`lti`.`service` = 'usage_ip_ports'
			left join
				`tarifs_internet` `ti`
			on
				`ti`.`id` = `lti`.`id_tarif`
			left join
				`usage_voip` `uv`
			on
				`uv`.`client` = `c`.`client`
			and
				now() between `uv`.`actual_from` and `uv`.`actual_to`
			left join
				`log_tarif` `ltv`
			on
				`ltv`.`id_service` = `uv`.`id`
			and
				`ltv`.`service` = 'usage_voip'
			left join
				`tarifs_voip` `tv`
			on
				`tv`.`id` = `ltv`.`id_tarif`
			left join
				`usage_extra` `ue`
			on
				`ue`.`client` = `c`.`client`
			and
				now() between `ue`.`actual_from` and `ue`.`actual_to`
			left join
				`tarifs_extra` `te`
			on
				`te`.`id` = `ue`.`tarif_id`
			where
				`c`.`status` IN ('".implode("','", $statuses)."')
		        ".(($tarif_extra_id > 0) ? ' and `te`.`id`=' . $tarif_extra_id : '') . "
		        ".(($tarif_internet_id > 0) ? ' and `ti`.`id`=' . $tarif_internet_id : '') . "
			and
				(`lti`.`id` is null or `lti`.`id` = (
					SELECT
						`id`
					FROM
						`log_tarif`
					WHERE
						`service` = 'usage_ip_ports'
					AND
						`date_activation` <= NOW()
					AND
						`id_service` = `uip`.`id`
					ORDER BY
						`date_activation` desc,
						`ts` desc,
						`id` desc
					LIMIT 1
				))
			and
				(`ltv`.`id` is null or `ltv`.`id` = (
					SELECT
						`id`
					FROM
						`log_tarif`
					WHERE
						`service` = 'usage_voip'
					AND
						`date_activation` <= NOW()
					AND
						`id_service` = `uv`.`id`
					ORDER BY
						`date_activation` desc,
						`ts` desc,
						`id` desc
					LIMIT 1
				))
			group by
				`c`.`id`,
				`c`.`client`,
				`uip`.`id`,
				`uv`.`id`,
				`ue`.`id`,
				`ti`.`id`,
				`ti`.`name`,
				`ti`.`type`,
				`tv`.`id`,
				`tv`.`name`,
				`te`.`id`,
				`te`.`description`
			order by
				`c`.`client`
		";

		if(isset($_REQUEST['fix']))
			$db->Query($query);
		$clients = array();
		$appending_scheme = array(
			'E'=>array(),
			'P'=>array(),
			'I'=>array(),
			'V'=>array(),
			'C'=>array()
		);
		while($row=$db->NextRecord(MYSQL_ASSOC)){
			if(!isset($clients[$row['id']])){
				$clients[$row['id']] = array(
					'id'=>$row['id'],
					'client'=>$row['client'],
					'I'=>array('flag'=>false,'tarifs'=>array()),
					'P'=>array('flag'=>false,'tarifs'=>array()),
					'V'=>array('flag'=>false,'tarifs'=>array()),
					'C'=>array('flag'=>false,'tarifs'=>array()),
					'E'=>array('flag'=>false,'tarifs'=>array())
				);
			}
			if(isset($s_e)){
				if($row['etarif_id']){
					/*$clients[$row['id']]['E']['flag'] = true;
					$clients[$row['id']]['E']['tarifs'][] = array(
						'id'=>$row['etarif_id'],
						'name'=>$row['etarif']
					);*/
					$fl = true;
					if(!isset($appending_scheme['E'][$row['id']])){
						$appending_scheme['E'][$row['id']] = array();
						$appending_scheme['E'][$row['id']][] = $row['extra_id'];
					}elseif(!in_array($row['extra_id'],$appending_scheme['E'][$row['id']])){
						$appending_scheme['E'][$row['id']][] = $row['extra_id'];
					}else{
						$fl = false;
					}
					if($fl){
						$clients[$row['id']]['E']['flag'] = true;
						$clients[$row['id']]['E']['tarifs'][] = array(
							'id'=>$row['etarif_id'],
							'name'=>$row['etarif']
						);
					}
				}
			}if(isset($s_p)){
				if($row['ptarif_id']){
					/*$clients[$row['id']]['P']['flag'] = true;
					$clients[$row['id']]['P']['tarifs'][] = array(
						'id'=>$row['ptarif_id'],
						'name'=>$row['ptarif']
					);*/
					$fl = true;
					if(!isset($appending_scheme['P'][$row['id']])){
						$appending_scheme['P'][$row['id']] = array();
						$appending_scheme['P'][$row['id']][] = $row['voip_id'];
					}elseif(!in_array($row['voip_id'],$appending_scheme['P'][$row['id']])){
						$appending_scheme['P'][$row['id']][] = $row['voip_id'];
					}else{
						$fl = false;
					}
					if($fl){
						$clients[$row['id']]['P']['flag'] = true;
						$clients[$row['id']]['P']['tarifs'][] = array(
							'id'=>$row['ptarif_id'],
							'name'=>$row['ptarif']
						);
					}
				}
			}if(isset($s_i)){
				if($row['itype'] == 'I'){
					/*$clients[$row['id']]['I']['flag'] = true;
					$clients[$row['id']]['I']['tarifs'][] = array(
						'id'=>$row['itarif_id'],
						'name'=>$row['itarif']
					);
					continue;*/
					$fl = true;
					if(!isset($appending_scheme['I'][$row['id']])){
						$appending_scheme['I'][$row['id']] = array();
						$appending_scheme['I'][$row['id']][] = $row['iport_id'];
					}elseif(!in_array($row['iport_id'],$appending_scheme['I'][$row['id']])){
						$appending_scheme['I'][$row['id']][] = $row['iport_id'];
					}else{
						$fl = false;
					}
					if($fl){
						$clients[$row['id']]['I']['flag'] = true;
						$clients[$row['id']]['I']['tarifs'][] = array(
							'id'=>$row['itarif_id'],
							'name'=>$row['itarif']
						);
						continue;
					}
				}
			}if(isset($s_v)){
				if($row['itype'] == 'V'){
					/*$clients[$row['id']]['V']['flag'] = true;
					$clients[$row['id']]['V']['tarifs'][] = array(
						'id'=>$row['itarif_id'],
						'name'=>$row['itarif']
					);
					continue;*/
					$fl = true;
					if(!isset($appending_scheme['V'][$row['id']])){
						$appending_scheme['V'][$row['id']] = array();
						$appending_scheme['V'][$row['id']][] = $row['iport_id'];
					}elseif(!in_array($row['iport_id'],$appending_scheme['V'][$row['id']])){
						$appending_scheme['V'][$row['id']][] = $row['iport_id'];
					}else{
						$fl = false;
					}
					if($fl){
						$clients[$row['id']]['V']['flag'] = true;
						$clients[$row['id']]['V']['tarifs'][] = array(
							'id'=>$row['itarif_id'],
							'name'=>$row['itarif']
						);
						continue;
					}
				}
			}if(isset($s_c)){
				if($row['itype'] == 'C'){
					/*$clients[$row['id']]['C']['flag'] = true;
					$clients[$row['id']]['C']['tarifs'][] = array(
						'id'=>$row['itarif_id'],
						'name'=>$row['itarif']
					);
					continue;*/
					$fl = true;
					if(!isset($appending_scheme['C'][$row['id']])){
						$appending_scheme['C'][$row['id']] = array();
						$appending_scheme['C'][$row['id']][] = $row['iport_id'];
					}elseif(!in_array($row['iport_id'],$appending_scheme['C'][$row['id']])){
						$appending_scheme['C'][$row['id']][] = $row['iport_id'];
					}else{
						$fl = false;
					}
					if($fl){
						$clients[$row['id']]['C']['flag'] = true;
						$clients[$row['id']]['C']['tarifs'][] = array(
							'id'=>$row['itarif_id'],
							'name'=>$row['itarif']
						);
						continue;
					}
				}
			}
		}

		$show = array();
		foreach($clients as $k=>$c){
			if(
				(!isset($s_i) || (isset($s_i) && (($s_i && $c['I']['flag']) || (!$s_i && !$c['I']['flag']))))
			&&
				(!isset($s_p) || (isset($s_p) && (($s_p && $c['P']['flag']) || (!$s_p && !$c['P']['flag']))))
			&&
				(!isset($s_v) || (isset($s_v) && (($s_v && $c['V']['flag']) || (!$s_v && !$c['V']['flag']))))
			&&
				(!isset($s_c) || (isset($s_c) && (($s_c && $c['C']['flag']) || (!$s_c && !$c['C']['flag']))))
			&&
				(!isset($s_e) || (isset($s_e) && (($s_e && $c['E']['flag']) || (!$s_e && !$c['E']['flag']))))
			){
				$show[] =& $clients[$k];
			}
		}
		unset($clients);
		$tarifs = array(
			'I'=>array(),
			'P'=>array(),
			'V'=>array(),
			'C'=>array(),
			'E'=>array()
		);
		$tarifs_map = array(
			'I'=>array(),
			'P'=>array(),
			'V'=>array(),
			'C'=>array(),
			'E'=>array()
		);
		foreach($show as $k=>$v){
			foreach(array('I','P','V','C','E') as $tt){
				if($v[$tt]['flag']){
					foreach($v[$tt]['tarifs'] as $t){
						$tarifs[$tt][] = array(
							'id'=>$t['id'],
							'name'=>$t['name'],
							'client'=>&$show[$k]
						);
						$tarifs_map[$tt][count($tarifs[$tt])-1] = $t['name'];
					}
				}
			}
		}

		foreach($tarifs_map as $tt=>$v){
			asort($tarifs_map[$tt]);
		}

		//clients name sort
		$tarifs_map_new = array();
		$tarifs_buf = array();
		$cur_tar = null;
		foreach($tarifs_map as $ttype=>&$tars){
			$tarifs_map_new[$ttype] = array();
			foreach($tarifs_map[$ttype] as $record_key=>$tname){
				if(is_null($cur_tar))
					$cur_tar = $tname;
				if($tname <> $cur_tar){
					asort($tarifs_buf);
					foreach($tarifs_buf as $rkey=>&$cl){
						$tarifs_map_new[$ttype][$rkey] = $tarifs_map[$ttype][$rkey];
					}
					$tarifs_buf = array();
					$cur_tar = $tname;
				}
				$tarifs_buf[$record_key] = $tarifs[$ttype][$record_key]['client']['client'];
			}
			asort($tarifs_buf);
			foreach($tarifs_buf as $rkey=>&$cl){
				$tarifs_map_new[$ttype][$rkey] = $tarifs_map[$ttype][$rkey];
			}
			$tarifs_buf = array();
		}
		unset($tarifs_map);
		$tarifs_map =& $tarifs_map_new;

		$design->assign('i_tarifs',$db->AllRecords("select id, name from tarifs_internet where status not in ('archive') order by name"));
		$design->assign('e_tarifs',$db->AllRecords("select id, description, code, price, currency from tarifs_extra where status not in ('archive') order by code, description"));

		$design->assign('fix',isset($_REQUEST['fix']));
		$design->assign('scount',count($show));
		$design->assign('icount',count($tarifs_map['I']));
		$design->assign('pcount',count($tarifs_map['P']));
		$design->assign('vcount',count($tarifs_map['V']));
		$design->assign('ccount',count($tarifs_map['C']));
		$design->assign('ecount',count($tarifs_map['E']));
		$design->assign_by_ref('tarifs',$tarifs);
		$design->assign_by_ref('tarifs_map',$tarifs_map);
		$design->assign_by_ref('show',$show);
		$design->AddMain('stats/report_services.html');
	}

    function stats_report_inn()
    {
        global $design;

        $managers = array();
        $all = $this->_stat_report_inn();

        $statuses = ClientCS::$statuses;
        foreach($all as &$l)
        {
            $l["client_color"] = isset($statuses[$l["status"]]) ? $statuses[$l["status"]]["color"] : false;
            $managers[$l["manager"]] = $l["manager"];
        }
        sort($managers);

        $manager = get_param_raw("manager", false);

        if($manager === false)
        {
            $R = array();
        }elseif($manager == ""){
            $R = $all;
        }else{
            $R = array();
            foreach($all as $l)
            {
                if($l["manager"] == $manager)
                {
                    $R[] = $l;
                }
            }
        }

        $design->assign("inns", $R);
        $design->assign("managers", $managers);
        $design->assign("manager", $manager);

        $design->AddMain("stats/report_inn.html");
    }

    function _stat_report_inn($manager = false)
    {
        global $db;

        $R = $db->AllRecords(
            "SELECT c.client, c.company, c.status, c.manager, unix_timestamp(l.ts) ts, f.*
             FROM clients c, `log_client` l, log_client_fields f where c.id = l.client_id and f.ver_id = l.id
             and ts >= '2012-04-01 00:00:00'
             and field = 'inn'
             and value_from != ''
             ".($manager ? "and c.manager = '".$manager."'" : "")."
             order by c.client, ts
             limit 1000");

        return $R;
    }

    function stats_report_agent()
    {
        global $db;
        global $design;

        $agents = array();
        $agent = false;
        $agent_id = get_param_raw("agent", false);
        $export = get_param_raw("export", false);
        $agents = $db->AllRecords('SELECT id, name FROM sale_channels WHERE is_agent=1');
        if ($agent_id && $agent_id > 0) $agent = $db->GetRow('SELECT * FROM sale_channels WHERE id=' . $agent_id);

        $cur_m = get_param_raw("from_m", date('m'));
        $cur_y = get_param_raw("from_y", date('Y'));

        $mm = array();
        for($i=1;$i<=12;$i++) $mm[date('m', mktime(0,0,0,$i,1,date('Y')))] = mdate('Месяц', mktime(0,0,0,$i,1,date('Y')));
        $yy = array(date('Y'), date('Y')-1);

        $from = date("01.m.Y", mktime(0,0,0,$cur_m,1,$cur_y));
        $to = date("t.m.Y", mktime(0,0,0,$cur_m,1,$cur_y));

        list($R, $T) = $this->_stat_report_agent($agent, $from, $to);

        if ($export) {
            header('Content-type: application/csv');
            header('Content-Disposition: attachment; filename="agent_report_'.date("01mY", mktime(0,0,0,$cur_m,1,$cur_y)).'_'.date("tmY", mktime(0,0,0,$cur_m,1,$cur_y)).'.csv"');

            ob_start();
            
            echo 'Агент:;'.$agent['name'].';Расчетный период с;'.$from.'г.;по;'.$to.'г.;';
            echo "\n";
            echo ';;;;;;';
            echo "\n";
            echo 'Компания;Абон плата, с учетом НДС;оплаченный период (мес.);Сумма полученных платежей;Вознаграждение;Сумма вознаграждения;';
            echo "\n";
            foreach ($R as $r) {
                echo '"' . $r['company'] . '";';
                echo '"' . number_format($r['isum'], 2, ',', '') . '";';
                echo '"' . $r['period'] . '";';
                echo '"' . number_format($r['psum'], 2, ',', '') . '";';
                echo '"' . $agent['interest'] . ' %";';
                echo '"' . number_format($r['fsum'], 2, ',', '') . '";';
                echo "\n";
            }
            echo '"Итого";;;';
            echo '"' . number_format($T['psum'], 2, ',', '') . '";;';
            echo '"' . number_format($T['fsum'], 2, ',', '') . '";';
            echo "\n";
            echo iconv('koi8-r', 'windows-1251', ob_get_clean());
            exit;
        } else {
            $params = array(
                            'mm'=>$mm,
                            'yy'=>$yy,
                            'inns'=>$R,
                            'agent'=>$agent,
                            'agents'=>$agents,
                            'cur_m'=>$cur_m,
                            'cur_y'=>$cur_y,
                            'total'=>$T,
                            'from'=>$from,
                            'to'=>$to
            );
            $design->assign($params);
            $design->AddMain("stats/report_agent.tpl");
        }
    }

    function stats_report_sale_channel($fixclient)
    {
        global $db,$design,$user;

        $date_begin = get_param_raw('date_from', date('Y-m-d'));
        $date_end = get_param_raw('date_to',  date('Y-m-d'));
        $design->assign(array('date_begin'=>$date_begin, 'date_end'=>$date_end));
        $doer_filter = $doer_filter_ = get_param_protected('doer_filter','null');
        $design->assign('doer_filter_selected',$doer_filter);
        
        $all_doers = array();
        $dDoers = array('null' => 'Все');
        foreach($db->AllRecords("
                        SELECT
                            `id`,
                            `depart`,
                            `name`
                        FROM
                            `courier`
                        WHERE
                            `enabled`='yes' and `depart`='Региональный представитель'
                        ORDER BY
                            `depart`,
                            `name`
                    ", null, MYSQL_ASSOC) as $id => $d)
        {
            $dDoers[$d["id"]] = $d["name"];
            $all_doers[] = $d["id"];
            if ($user->_Data['courier_id'] == $d["id"]) {
                $dDoers = array($d["id"]=>$d["name"]);
                break;
            }
        }
        $design->assign('doer_filter', $dDoers);

        $doerId = 0;
        if($doer_filter == 'null'){
            $doer_filter = ' AND `cr`.`id` IN('.implode(',', $all_doers).')';
        }else{
            $doerId = (int)$doer_filter;
            $doer_filter = '
                    AND `cr`.`id` = '.((int)$doer_filter);
        }

        $state_filter = $state_filter_ = get_param_protected('state_filter', 'null');
        
        $design->assign("state_filter_selected", $state_filter);
        
        if($state_filter == "null")
        {
            $state_filter = " not in (2,20,21,39,40)";
        }elseif($state_filter == 2 || $state_filter == 20){
            $state_filter = " in (2,20,39,40)";
        }else{
            $state_filter = ' = "'.$state_filter.'"';
        }
        
        $query = "
                SELECT distinct
                    DATE(`date`) `date`,
                    `courier_name`,
                    `company`,
                    `task`,
                    `cur_state`,
                    `client_id`,
                    `tt_id`,
                    `type`,
                    trouble_cur_state,
                    `bill_no`
                FROM
                    (
                        SELECT distinct
                            `ts`.`date_start` `date`,
                            `cr`.`name` `courier_name`,
                            `cl`.`company` `company`,
                            `tt`.`problem` `task`,
                            `ts`.`state_id` `cur_state`,
                            `tt`.`id` `tt_id`,
                            `cl`.`id` `client_id`,
                            'ticket' `type`,
                            cts.state_id `trouble_cur_state`,
                            `tt`.`bill_no`
                        FROM `tt_stages` `ts`
        
                        INNER JOIN `tt_doers` `td` ON `td`.`stage_id` = `ts`.`stage_id`
                        INNER JOIN `courier` `cr` ON `cr`.`id` = `td`.`doer_id` ".$doer_filter."
        
                        LEFT JOIN `tt_troubles` `tt`  ON `tt`.`id` = `ts`.`trouble_id`
        
                        LEFT JOIN `tt_stages`   `cts` ON cts.stage_id = tt.cur_stage_id
                        LEFT JOIN `clients`     `cl`  ON `cl`.`client` = `tt`.`client`
        
                        WHERE
                            /*`ts`.`state_id` = 4
                        AND */
                            cts.state_id ".$state_filter."
                        and
                            `ts`.`date_start` BETWEEN '".$date_begin." 00:00:00' AND '".$date_end." 23:59:59'
                    ) `tbl`
                ORDER BY
                    `date`,
                    `company`,
                    `courier_name`
            ";
        $ret = array();

        $db->Query($query);
        $sumBonus = 0;
        $count = 0;
        
        while($row=$db->NextRecord(MYSQL_ASSOC)){
            if(!isset($ret[$row['date']])){
                $ret[$row['date']] = array('rowspan'=>0,'doers'=>array());
            }
        
            $ret[$row['date']]['rowspan']++;
        
            $bonus = 0;
            $row["bill_sum"] = $row["sum_good"] = $row["sum_service"] = $row["count_service"] = $row["count_good"] = 0;
        
            $ret[$row['date']]['doers'][] = array(
                            'name'=>$row['courier_name'],
                            'company'=>$row['company'],
                            'task'=>stripslashes($row['task']),
                            'cur_state'=>$row['cur_state'],
                            'tt_id'=>$row['tt_id'],
                            'client_id'=>$row['client_id'],
                            'type'=>$row['type'],
                            'trouble_cur_state'=>$row['trouble_cur_state'],
                            'bill_no' => $row["bill_no"],
                            'bill_sum' => $row["bill_sum"],
                            'sum_good' => $row["sum_good"],
                            'count_good' => $row["count_good"],
                            'sum_service' => $row["sum_service"],
                            'count_service' => $row["count_service"],
                            'bonus' => $bonus
            );
            $count++;
        
        }

        $design->assign('sum_bonus',$sumBonus);
        $design->assign('count',$count);
        $design->assign_by_ref('report_data',$ret);
        if(get_param_protected('print',false)){
            $design->assign('print',true);
            $design->ProcessEx('stats/sale_channel_report.tpl');
        }else{
            if(count($_POST)>0)
                $design->assign(
                        'print_report',
                        '?module=stats'.
                        '&action=report_sale_channel'.
                        '&print=yes'.
                        '&date_from='.$date_begin.
                        '&date_to='.$date_end.
                        (($doer_filter_<>'null')?'&doer_filter='.$doer_filter_:'').
                        (($state_filter_<>'null')?'&state_filter='.$state_filter_:'')
                );
        
            $design->assign(
                    'l_state_filter',
                    array_merge(
                            array(
                                array('id'=>'null','name'=>'Все (кроме: закрыт, отказ)'),
                            ),
                            $db->AllRecords("
                        SELECT
                            `id`,
                            `name`
                        FROM
                            `tt_states`
                        where
                            pk & 17703 #2047
                        ORDER BY
                            `name`
                    ", null, MYSQL_ASSOC)
                    )
            );
        
            $design->assign('tt_states_list',$db->AllRecords('select * from tt_states','id',MYSQL_ASSOC));
        
            $design->AddMain('stats/sale_channel_report.tpl');
        }
        
    }

    function _stat_report_agent($agent = false, $from = false, $to = false)
    {
        if ($agent === false) return array(array(),array());
        global $db;
        $ret = array(); $total = array('psum'=>0, 'fsum'=>0, 'nds'=>0);

        $from = date("Y-m-d", strtotime($from));
        $to = date("Y-m-d", strtotime($to));
        $prev_from = date("Y-m-01", mktime(0,0,0,date('m', strtotime($from))-3,1,date('Y', strtotime($from))));

        $R = $db->AllRecords($q = "
                SELECT
                    c.id, c.client, c.company, sum(l.sum) as sum
                FROM
                    clients c
                LEFT JOIN newbills b ON (b.client_id = c.id)
                LEFT JOIN newbill_lines l ON (b.bill_no = l.bill_no)
                WHERE
                    c.sale_channel = ".$agent['id']."
                AND b.bill_date >= '".date("Y-m-d", strtotime($from))."'
                AND b.bill_date <= '".date("Y-m-d", strtotime($to))."'
                AND l.item LIKE ('Абонентская%')
                GROUP BY c.id
             ");

        foreach ($R as $r) {
            $ret[$r['id']] = array('id'=>$r['id'],'client'=>$r['client'],'company'=>$r['company'],'isum'=>$r['sum'],'psum'=>0,'fsum'=>0, 'period'=>0);
        }

        $R2 = $db->AllRecords($q = "
                SELECT
                    c.id, sum(l.sum) AS sum, (MONTH(p.payment_date)-MONTH(b.bill_date)+1) AS period
                FROM
                    clients c
                LEFT JOIN newbills b ON (b.client_id = c.id)
                LEFT JOIN newbill_lines l ON (b.bill_no = l.bill_no)
                LEFT JOIN newpayments p ON (b.bill_no = p.bill_no)
                WHERE
                    c.sale_channel = ".$agent['id']."
                AND
                (
                    (
                        p.payment_date >= '".$from."' AND
                        p.payment_date <= '".$to."' AND
                        b.bill_date >= '".$from."' AND
                        b.bill_date <= '".$to."')
                    OR
                    (
                        (
                            p.payment_date >= '".$from."' AND
                            p.payment_date <= '".$to."'
                        )
                        AND
                        (
                            b.bill_date >= '".$prev_from."' AND b.bill_date < '".$from."'
                        )
                    )
                )
                AND l.item LIKE ('Абонентская%')
                GROUP BY c.id
                ");

        foreach ($R2 as $r) {
            if ($r['sum'] > 0) {
                $ret[$r['id']]['psum'] += $r['sum'];
                $ret[$r['id']]['fsum'] += round($r['sum']*$agent['interest']/100, 2);
                $ret[$r['id']]['period'] = $r['period'];

                $total['psum'] += $ret[$r['id']]['psum'];
                $total['fsum'] += $ret[$r['id']]['fsum'];
            }
        }
        $total['nds'] = round($total['fsum']*(18/118), 2);
        $total['fsum_str'] = floor($total['fsum']) . ' руб. ' . floor(100*($total['fsum'] - floor($total['fsum']))) . ' коп.';
        $total['nds_str'] = floor($total['nds']) . ' руб. ' . floor(100*($total['nds'] - floor($total['nds']))) . ' коп.';
        return array($ret, $total);
    }

	function stats_report_rates(){
		global $db,$design;

		$selected = array(
			'client'=>array(
				'search'=>null,
				'client_search'=>array(
					'restrict'=>null,
					'field'=>null
				)
			),
			'stats'=>array(),
			'rates'=>array(
				'voip'=>array(
					'public'=>array('rur'=>array(),'usd'=>array()),
					'special'=>array('rur'=>array(),'usd'=>array()),
					'archive'=>array('rur'=>array(),'usd'=>array()),
				),
				'vpn'=>array(
					'public'=>array('rur'=>array(),'usd'=>array()),
					'special'=>array('rur'=>array(),'usd'=>array()),
					'archive'=>array('rur'=>array(),'usd'=>array()),
				),
				'collocation'=>array(
					'public'=>array('rur'=>array(),'usd'=>array()),
					'special'=>array('rur'=>array(),'usd'=>array()),
					'archive'=>array('rur'=>array(),'usd'=>array()),
				),
				'internet'=>array(
					'public'=>array('rur'=>array(),'usd'=>array()),
					'special'=>array('rur'=>array(),'usd'=>array()),
					'archive'=>array('rur'=>array(),'usd'=>array()),
				)
			),
			'with_nds'=>false,
			'group_bill'=>false
		);

		if(isset($_POST['with_nds']))
			$with_nds = true;
		else
			$with_nds = false;
		$selected['with_nds'] = $with_nds;

		if(isset($_POST['group_bill']))
			$selected['group_bill'] = true;
		else
			$selected['group_bill'] = false;

		if(isset($_POST['rates_voip'])){
			foreach($_POST['rates_voip'] as $section=>$ar){
				if(!isset($selected['rates']['voip'][$section]))
					$selected['rates']['voip'][$section] = array('rur'=>array(),'usd'=>array());
				foreach($ar as $curr=>$v){
					$selected['rates']['voip'][$section][$curr] = $v;
				}
			}
		}
		if(isset($_POST['statuses'])){
			$selected['stats'] = $_POST['statuses'];
		}
		if(isset($_POST['fclient']) && isset($_POST['fclient']['search'])){
			$selected['client']['search'] = addcslashes($_POST['fclient']['search'], "\\'");
			if(isset($_POST['client_search'])){
				if(isset($_POST['client_search']['restrict']))
					$selected['client']['client_search']['restrict'] = 1;
			}
			if(isset($_POST['client_search']) && isset($_POST['client_search']['field']) && $_POST['client_search']['field'] == 'company')
				$selected['client']['client_search']['field'] = 'company';
			else
				$selected['client']['client_search']['field'] = 'client';
		}
		$design->assign('client',$selected['client']);
		foreach(array('internet','vpn','collocation') as $k){
			if(!isset($_POST['rates_'.$k]))
				continue;
			foreach($_POST['rates_'.$k] as $section=>$ar){
				if(!isset($selected['rates'][$k][$section]))
					$selected['rates'][$k][$section] = array('rur'=>array(),'usd'=>array());
				foreach($ar as $curr=>$v){
					$selected['rates'][$k][$section][$curr] = $v;
				}
			}
		}
		if(isset($_POST['date_from_y'])){
			$date_from = param_load_date('date_from_', array('mday'=>0,'mon'=>0,'year'=>0), true);
			$date_to = param_load_date('date_to_', array('mday'=>0,'mon'=>0,'year'=>0), true);
		}else{
			$date_from = date('Y-m-01');
			$date_to = date('Y-m-d');
		}

		$statuses = array();
		foreach(ClientCS::$statuses as $status=>$v){
			$statuses[$status] = (isset(ClientCS::$statuses[$status]) ? ClientCS::$statuses[$status]['name'] : $status);
		}

		$sql = array('rates'=>array(),'main'=>array());
		$rates = array(
			'voip'=>array(),
			'internet'=>array(
				'I'=>array(),
				'V'=>array(),
				'C'=>array()
			)
		);
		// <editor-fold defaultstate="collapsed" desc="sql rates voip">
		$sql['rates']['voip'] = "
			select
				*
			from
				`tarifs_voip`
		";
		// </editor-fold>

		// <editor-fold defaultstate="collapsed" desc="sql rates internet">
		$sql['rates']['internet'] = "
			select
				*
			from
				`tarifs_internet` `ti`
		";
		// </editor-fold>

		// <editor-fold defaultstate="collapsed" desc="prepare data">
		$stats = array();
		if(count($selected['stats'])){
			foreach($selected['stats'] as $s){
				$stats[] = "'".addcslashes($s, "\\'")."'";
			}
		}
		$srates = array('voip'=>array(),'int'=>array());
		if(count($selected['rates'])){
			foreach($selected['rates'] as $section=>&$ar){
				if($section=='voip'){
					foreach($selected['rates']['voip'] as $subsec=>&$sar){
						foreach($selected['rates']['voip'][$subsec] as $cur=>&$ids){
							$srates['voip'] = array_merge($srates['voip'],$ids);
						}
					}
				}else{
					foreach($selected['rates'][$section] as $subsec=>&$sar){
						foreach($selected['rates'][$section][$subsec] as $cur=>&$ids){
							$srates['int'] = array_merge($srates['int'],$ids);
						}
					}
				}
			}
		}
		foreach($srates as $section=>&$ar){
			foreach($srates[$section] as $key=>&$val){
				$srates[$section][$key] = (int)$srates[$section][$key];
			}
		}

		if(count($stats))
			$cl_stats = " and `cl`.`status` in (".implode("','",$stats).")";
		else
			$cl_stats = "";
		if(count($srates['voip']))
			$voip_rates = " and `noa`.`rate_id` in (".implode(',',$srates['voip']).")";
		else
			$voip_rates = '';
		if(count($srates['int']))
			$int_rates = " and `noa`.`rate_id` in (".implode(',',$srates['int']).")";
		else
			$int_rates = "";
		if(isset($selected['client']) && $selected['client']['search']){
			$cs = "'".(($selected['client']['client_search']['restrict'])?'':'%').addcslashes($selected['client']['search'], "\\'").(($selected['client']['client_search']['restrict'])?'':'%')."'";
			if($selected['client']['client_search']['field'] == 'company'){
				$cs = " and `cl`.`company` like ".$cs;
			}else{
				$cs = " and`cl`.`client` like ".$cs;
			}
		}else
			$cs = "";
		// </editor-fold>

		// <editor-fold defaultstate="collapsed" desc="sql main section">
		$sql['main']['voip'] = "
		select
			`cl`.`client`,
			`cl`.`id` `client_id`,
			`cl`.`status` `client_status`,
			`nb`.`bill_no`,
			`nb`.`bill_date`,
			`noa`.`pk` `over_pk`,
			`noa`.`quantity`,
			`noa`.`quantity_by_rate`,
			`noa`.`msk_length`,
			`noa`.`rate_currency`,
			`noa`.`total_price` `overprice`,
			`noa`.`rate_id`,
			`tv`.`name` `rate_name`,
			`noad`.`key` `ad_key`,
			`noad`.`value` `ad_val`
		from
			`newbills` `nb`
		inner join
			`newbills_overprice_aggregate` `noa`
		on
			`noa`.`index` = 'voip'
		and
			`noa`.`bill_no` = `nb`.`bill_no`".$voip_rates."
		inner join
			`clients` `cl`
		on
			`cl`.`id` = `nb`.`client_id`".$cl_stats.$cs."
		left join
			`tarifs_voip` `tv`
		on
			`tv`.`id` = `noa`.`rate_id`
		left join
			`newbills_overprice_additions` `noad`
		on
			`noad`.`overprice_pk` = `noa`.`pk`
		where
			`nb`.`bill_date` between '".$date_from."' and '".$date_to."'
		";
		$cv = count($srates['voip']);

		$sql['main']['int'] = "
		select
			`cl`.`client`,
			`cl`.`id` `client_id`,
			`cl`.`status`,
			`nb`.`bill_no`,
			`nb`.`bill_date`,
			`noa`.`pk` `overpk`,
			`noa`.`quantity`,
			`noa`.`rate_currency`,
			`noa`.`total_price`,
			`noa`.`bill_price`,
			`noa`.`rate_id`,
			`ti`.`name` `rate_name`,
			`ti`.`type` `rate_type`,
			`noad`.`key` `ad_key`,
			`noad`.`value` `ad_val`
		from
			`newbills` `nb`
		inner join
			`newbills_overprice_aggregate` `noa`
		on
			`noa`.`index` = 'internet'
		and
			`noa`.`bill_no` = `nb`.`bill_no`".$int_rates."
		inner join
			`clients` `cl`
		on
			`cl`.`id` = `nb`.`client_id`".$cl_stats.$cs."
		left join
			`tarifs_internet` `ti`
		on
			`ti`.`id` = `noa`.`rate_id`
		left join
			`newbills_overprice_additions` `noad`
		on
			`noad`.`overprice_pk` = `noa`.`pk`
		where
			`nb`.`bill_date` between '".$date_from."' and '".$date_to."'
		";
		$ci = count($srates['int']);
		// </editor-fold>

		$db->Query($sql['rates']['voip']);
		while($row=$db->NextRecord(MYSQL_ASSOC)){
			if(!isset($rates['voip'][$row['status']]))
				$rates['voip'][$row['status']] = array();
			if(!isset($rates['voip'][$row['status']][$row['currency']]))
				$rates['voip'][$row['status']][$row['currency']] = array();
			$rates['voip'][$row['status']][$row['currency']][] = $row;
		}
		$db->Query($sql['rates']['internet']);
		while($row=$db->NextRecord(MYSQL_ASSOC)){
			if(!isset($rates['internet'][$row['type']][$row['status']]))
				$rates['internet'][$row['type']][$row['status']] = array();
			if(!isset($rates['internet'][$row['type']][$row['status']][$row['currency']]))
				$rates['internet'][$row['type']][$row['status']][$row['currency']] = array();
			$rates['internet'][$row['type']][$row['status']][$row['currency']][] = $row;
		}

		$v_r = array();
		if($cv){
			$db->Query($sql['main']['voip']);
			while($row=$db->NextRecord(MYSQL_ASSOC)){
				if($selected['group_bill'])
					$row['rate_id'] = 0;
				if(!isset($v_r[$row['rate_id']]))
					$v_r[$row['rate_id']] = array(
						'name'=>$row['rate_name'],
						'clients'=>array()
					);
				if(!isset($v_r[$row['rate_id']]['clients'][$row['client_id']]))
					$v_r[$row['rate_id']]['clients'][$row['client_id']] = array(
						'client'=>$row['client'],
						'status'=>(isset(ClientCS::$statuses[$row['client_status']]) ? ClientCS::$statuses[$row['client_status']]['name'] : $row['client_status']),
						'bills'=>array(),
					);
				if(!isset($v_r[$row['rate_id']]['clients'][$row['client_id']]['bills'][$row['bill_no']])){
					$v_r[$row['rate_id']]['clients'][$row['client_id']]['bills'][$row['bill_no']] = array(
						'date'=>$row['bill_date'],
						'overt'=>array(
							'cur'=>$row['rate_currency'],
							'sum'=>0,
							'groups'=>array(
								'0:96'=>array('qty'=>$row['msk_length'],'rti'=>0,'pry'=>0),
								'0:0'=>array('qty'=>0,'rti'=>0,'pry'=>0),
								'0:1'=>array('qty'=>0,'rti'=>0,'pry'=>0),
								'1:0'=>array('qty'=>0,'rti'=>0,'pry'=>0),
								'2:0'=>array('qty'=>0,'rti'=>0,'pry'=>0),
								'1:98'=>array('qty'=>0,'rti'=>0,'pry'=>0),
								'2:97'=>array('qty'=>0,'rti'=>0,'pry'=>0)
							)
						),
						'overs'=>array()
					);
				}

				if(!isset($v_r[$row['rate_id']]['clients'][$row['client_id']]['bills'][$row['bill_no']]['overs'][$row['over_pk']]))
					$v_r[$row['rate_id']]['clients'][$row['client_id']]['bills'][$row['bill_no']]['overs'][$row['over_pk']] = array(
						'quantity'=>$row['quantity'],
						'quantity_by_rate'=>$row['quantity_by_rate'],
						'overprice'=>$row['overprice'],
						'currency'=>$row['rate_currency']
					);
				$v_r[$row['rate_id']]['clients'][$row['client_id']]['bills'][$row['bill_no']]['overs'][$row['over_pk']][$row['ad_key']] = $row['ad_val'];
			}

			foreach($v_r as $rate_id=>&$rate_data){
				foreach($rate_data['clients'] as $client_id=>&$client_data){
					foreach($client_data['bills'] as $bill_no=>$bill_data){
						foreach($bill_data['overs'] as $opk=>&$over_data){
							if($over_data['dgroup']<>0){
								if(!in_array($over_data['dsubgroup'],array(96,97,98))){
									$over_data['dsubgroup'] = 0;
								}
							}
							$v_r[$rate_id]['clients'][$client_id]['bills'][$bill_no]['overt']['sum'] += $over_data['overprice'];
							$v_r[$rate_id]['clients'][$client_id]['bills'][$bill_no]['overt']['groups'][$over_data['dgroup'].':'.$over_data['dsubgroup']]['qty'] += $over_data['quantity_by_rate'];
							$v_r[$rate_id]['clients'][$client_id]['bills'][$bill_no]['overt']['groups'][$over_data['dgroup'].':'.$over_data['dsubgroup']]['rti'] += $over_data['quantity'];
							$v_r[$rate_id]['clients'][$client_id]['bills'][$bill_no]['overt']['groups'][$over_data['dgroup'].':'.$over_data['dsubgroup']]['pry'] += $over_data['overprice'];
							unset($v_r[$rate_id]['clients'][$client_id]['bills'][$bill_no]['overs'][$opk]);
						}
					}
				}
			}
		}

		$i_r = array();
		$c_r = array();
		$vpn_r = array();
		if($ci){
			$db->Query($sql['main']['int']);
			while($row = $db->NextRecord(MYSQL_ASSOC)){
				if($row['rate_type'] == 'I')
					$mid =& $i_r;
				elseif($row['rate_type'] == 'C')
					$mid =& $c_r;
				else
					$mid =& $vpn_r;
				if($selected['group_bill'])
					$row['rate_id'] = 0;
				if(!isset($mid[$row['rate_id']])){
					$mid[$row['rate_id']] = array(
						'name'=>$row['rate_name'],
						'clients'=>array()
					);
				}
				$j =& $mid[$row['rate_id']]['clients'];
				if(!isset($j[$row['client_id']])){
					$j[$row['client_id']] = array(
						'client'=>$row['client'],
						'status'=>(isset(ClientCS::$statuses[$row['status']]) ? ClientCS::$statuses[$row['status']]['name'] : $row['status']),
						'bills'=>array()
					);
				}
				$j =& $j[$row['client_id']]['bills'];
				if(!isset($j[$row['bill_no']])){
					$j[$row['bill_no']] = array(
						'price'=>$row['bill_price'],
						'date'=>$row['bill_date'],
						'currency'=>$row['rate_currency'],
						'overs'=>array()
					);
				}
				$j =& $j[$row['bill_no']]['overs'];
				if(!isset($j[$row['overpk']])){
					$j[$row['overpk']] = array(
						'quantity'=>$row['quantity'],
						'price'=>$row['total_price'],
						'ads'=>array()
					);
				}
				$j =& $j[$row['overpk']]['ads'];
				$j[$row['ad_key']] = $row['ad_val'];
			}
		}

		$design->assign_by_ref('v_r',$v_r);
		$design->assign_by_ref('i_r',$i_r);
		$design->assign_by_ref('c_r',$c_r);
		$design->assign_by_ref('vpn_r',$vpn_r);
		$design->assign_by_ref('selected',$selected);
		$design->assign_by_ref('rates',$rates);
		$design->assign('statuses',$statuses);
		$design->AddMain('stats/report_rates.html');
	}


function stat_test($itemNum, $dateFrom, $dateTo)
{
    global $db;
    $vv = $db->AllRecords("select l.bill_no, cast(s.date_start as date) as ds,  client
            from tt_troubles t , tt_stages s, tt_stages s2, newbill_lines l, newbills b, g_goods g
            where s.date_start between '".$dateFrom."' and '".$dateTo."'  and s.trouble_id = t.id and s.state_id = 18 and t.bill_no = l.bill_no and
            item_id =g.id and  g.num_id = ".$itemNum." and b.bill_no = l.bill_no and client ='WiMaxComstar'
            and s2.stage_id =cur_stage_id and s2.state_id != 21  group by b.bill_no");

    $d = array();
    foreach($vv as $v)
    {
        if(!isset($d[$v["ds"]]))
        {
            $d[$v["ds"]] = array("income" => 0, "outlay" => 0);
        }

        $d[$v["ds"]]["outlay"]++;
    }
    return $d;
}

function make_calend($date, $data)
{
    $o = "";
    $d = strtotime(date("Y-m", $date)."-01 00:00:00");

    $wd = date("w", $d);
    if($wd == 0) $wd = 7;

    $sd_offset = $wd-1;

    $de = strtotime("+1 month -1 day", $d);

    $wde = date("w", $de);
    if($wde == 0) $wde = 7;
    $ed_offset = 7-$wde;



    $days = (round(($de-$d)/86400)+1)+$sd_offset+$ed_offset;


    $cStart = $sd_offset > 0 ? strtotime("- ".$sd_offset." days", $d) : 0;
    $o .=  "<table valign=top border=1 style='border-collapse:collapse;'>";
    $o .= "<tr><td>ПН</td><td>ВТ</td><td>СР</td><td>ЧТ</td><td>ПТ</td><td>СБ</td><td>ВС</td></tr>";
    $o .= "<tr>";
    for($i = 0; $i < $days; $i++)
    {
        if($i != 0 && $i != $days && $i % 7 == 0)
            $o .=  "</tr><tr>";


        $_d = strtotime("+ ".$i." days", $cStart);

        $_dd = date("Y-m-d", $_d);
        $o .= "<td valign=top onclick=\"ll('".$_dd."')\">";

        $o .= date("d M",$_d);

        $o.="<table border=0 width=100%>";
        foreach($data as $pId => $pData)
        {
            $o .= "<tr><td style='font: normal 6pt sans-serif;'>".$pId."</td><td>&nbsp;";
            if(isset($pData["data"][$_dd]))
            {
                $v = $pData["data"][$_dd];
                $o .= ($v["income"] ? "<b style='color:red;'>+".$v["income"]."</b>" : "");
                $o .= "</td><td>&nbsp;";
                $o .= $v["outlay"] ? "<b style='color:green;'>-".$v["outlay"]."<b>" : "";
            }else{
                $o .= "</td><td>&nbsp;";
            }
            $o .= "</td></tr>";
        }
        $o .= "</table>";

        $o .= "</td>";
    }
    $o .= "</tr>";
    $o .= "</table>";

    return $o;
}
function make_sum(&$dd)
{
    $sum = array("outlay" => 0, "income" => 0);
    foreach($dd["data"] as $d)
    {
        $sum["outlay"] += $d["outlay"];
        $sum["income"] += $d["income"];
    }
    $dd["sum"] = $sum;

}
function stats_report_wimax($fixclient, $genReport = false){
    global $db,$design;

    if($genReport)
    {
	$dateFrom = new DatePickerValues('dateNoRequest', '-1 day');
	$d1 = $d2 = $dateFrom->getDay();
	$date = $d1 == $d2 ? 'за '.$d1 : 'с '.$d1.' по '.$d2;
	$dateFrom->format = 'Y-m-d';
	$d1 = $d2 = $dateFrom->getDay();
    }else{
	$dateFrom = new DatePickerValues('date_from', 'today');
	$dateTo = new DatePickerValues('date_to', 'today');
	$d1 = $dateFrom->getDay();
        $d2 = $dateTo->getDay();
	$date = $d1 == $d2 ? 'за '.$d1 : 'с '.$d1.' по '.$d2;
	$dateFrom->format = 'Y-m-d';$dateTo->format = 'Y-m-d';
	$d1 = $dateFrom->getDay();
        $d2 = $dateTo->getDay();
    }
    
    $design->assign("date", $date);


    $r = $db->AllRecords($sql=
            "SELECT
            i.req_no,
            i.bill_no,
            (
             select group_concat(item)
             from newbill_lines nl
             where nl.bill_no = i.bill_no
            ) as param,
            tr.date_creation, i.fio, i.phone, st.name as state_name,
            (
             select group_concat( concat(date_edit, ' ', st.name,' ',comment) SEPARATOR ' // ')
             from tt_troubles tr2
             left join tt_stages ts2 on (tr2.id = ts2.trouble_id)
             left join tt_states st2 on st2.id = ts2.state_id
             where tr2.bill_no = i.bill_no
            ) as comment,
            i.comment2
            FROM `newbills_add_info` i, newbills b
            left join tt_troubles tr using (bill_no)
            left join tt_stages ts on (tr.cur_stage_id = ts.stage_id)
            left  join tt_states st on st.id = ts.state_id
#where order_given = 'WiMAX COMSTAR'
            where i.bill_no = b.bill_no and client_id = 9322
            and (
                    (ts.state_id in(2,20,21) and date_edit between '".$d1." 00:00:00' and '".$d2." 23:59:59')
                    or ts.state_id not in(2,20,21)
                )");

    //printdbg($sql);

    $design->assign("d", $r);
    $design->assign("showSelects", !$genReport);
    if($genReport)
    {
        return array( $date, $design->fetch("stats/wimax.html", null, null, false));
    }else{
        $design->AddMain("stats/wimax.html");
    }
}

function stats_courier_sms($fixclient, $genReport = false){
    global $db,$design;

    if($genReport)
    {
	$dateFrom = new DatePickerValues('dateNoRequest', '-1 day');
	$d1 = $d2 = $dateFrom->getDay();
	$date = $d1 == $d2 ? 'за '.$d1 : 'с '.$d1.' по '.$d2;
	$dateFrom->format = 'Y-m-d';
	$d1 = $d2 = $dateFrom->getDay();
    }else{
	$dateFrom = new DatePickerValues('date_from', 'today');
	$dateTo = new DatePickerValues('date_to', 'today');
	$d1 = $dateFrom->getDay();
        $d2 = $dateTo->getDay();
	$date = $d1 == $d2 ? 'за '.$d1 : 'с '.$d1.' по '.$d2;
	$dateFrom->format = 'Y-m-d';$dateTo->format = 'Y-m-d';
	$d1 = $dateFrom->getDay();
        $d2 = $dateTo->getDay();
    }

    $design->assign("date", $date);


    $r = $db->AllRecords(
            "SELECT s.sms_sender as phone, c.name, sum(1) as count, group_concat(bill_no) bills FROM `newbill_sms` s
            left join courier c on s.sms_sender = c.phone
            where sms_send between '".$d1." 00:00:00' and '".$d2." 23:59:59' group by sms_sender");

    foreach($r as &$l)
        $l["bills"] = explode(",", $l["bills"]);

    $design->assign("d", $r);
    $design->assign("showSelects", !$genReport);
    if($genReport)
    {
        return array( $date, $design->fetch("stats/courier_sms.html", null, null, false));
    }else{
        $design->AddMain("stats/courier_sms.html");
    }
}

function stats_support_efficiency($fixclient)
{
    global $db,$design;

    $m = array();
    $total = array(
        "monitoring"   => 0, 
        "trouble"      => 0, 
        "consultation" => 0,
        "task"         => 0
        );

    $date = "";

    $usages = array(
            "" => "Без услуги",
            "usage_extra" => "Доп услуги",
            "usage_ip_ports" => "Интернет",
            "usage_voip" => "Телефония",
            "usage_virtpbx" => "Виртуальная АТС",
            "usage_welltime" => "Welltime"
            );
    $usage = array_keys($usages);

	$date_from = new DatePickerValues('date_from', 'first');
	$date_to = new DatePickerValues('date_to', 'last');
	$dateFrom = $date_from->getDay();$dateTo = $date_to->getDay();
	$date = $dateFrom == $dateTo ? 'за '.$dateFrom : 'с '.$dateFrom.' по '.$dateTo;
	$date_from->format = 'Y-m-d';$date_to->format = 'Y-m-d';
	$dateFrom = $date_from->getDay();$dateTo = $date_to->getDay();

    $onCompleted_users = array();
    $onCompleted_data = array();
    $onCompleted_total = array();
    $onCompleted_rating = array();
    
    $onCompleted_users2 = $onCompleted_data2 = $onCompleted_total2 = $onCompleted_rating2 = array();
    
    if(get_param_raw("make_report", "") == "OK")
    {
        $usage = get_param_raw("usage", $usage);

        $r = $db->AllRecords(
                $q = "
                    select *, 
                        count(1) as c, 
                        sum(rating) rating, 
                        sum(rating_count) as rating_count 
                    from (
                        SELECT 
                            uu.name,
                            user_author, 
                            trouble_subtype,
                            (select sum(rating) from tt_stages  where trouble_id =tt.id) as rating,
                            (select sum(if(rating=0,0,1)) from tt_stages where trouble_id =tt.id) as rating_count
                        FROM `tt_troubles` tt ,user_users uu 
                    where 
                        usergroup ='support' 
                        and uu.user = tt.user_author 
                        and date_creation between '".$dateFrom." 00:00:00' and '".$dateTo." 23:59:59' 
                        and trouble_type in ('trouble', 'task', 'support_welltime')
                        and service in ('".implode("','", $usage)."')
                        order by uu.name
                      ) a 
                    group by user_author, trouble_subtype
                ");

        $count = 0;
        foreach($r as $l)
        {
            if($l["trouble_subtype"] == "") continue;

            if(!isset($m[$l["user_author"]]))
                $m[$l["user_author"]] = array(
                    "name" => $l["name"], 
                    "count" => $count++, 
                    "data" => array()
                );

            $m[$l["user_author"]]["data"][$l["trouble_subtype"]] = array(
                    "count" => $l["c"],
                    "rating_avg" => ($l["rating_count"] > 0 ? $l["rating"] / $l["rating_count"] : 0),
                    "rating_count" => $l["rating_count"]
                    );

            if(isset($total[$l["trouble_subtype"]]))
                $total[$l["trouble_subtype"]] += $l["c"];
        }

        list($onCompleted_data, $onCompleted_users, $onCompleted_total, $onCompleted_rating) = $this->stats_support_efficiency__basisOnCompleted($dateFrom, $dateTo, $usage);
        list($onCompleted_data2, $onCompleted_users2, $onCompleted_total2, $onCompleted_rating2) = $this->stats_support_efficiency__basisOnStartDate($dateFrom, $dateTo, $usage);
    }

    $design->assign('usages', $usages);
    $design->assign('usages_selected', $usage);

    $design->assign(array(
                    "on_completed_data"=>$onCompleted_data,
                    "on_completed_users"=>$onCompleted_users,
                    "on_completed_total"=>$onCompleted_total,
                    "on_completed_rating"=>$onCompleted_rating,
                    "on_completed_data2"=>$onCompleted_data2,
                    "on_completed_users2"=>$onCompleted_users2,
                    "on_completed_total2"=>$onCompleted_total2,
                    "on_completed_rating2"=>$onCompleted_rating2
                    ));
    
    $design->assign("date", $date);
    $design->assign("d", $m);
    $design->assign("total", $total);
    $design->AddMain("stats/support_efficiency.html");
}
function stats_support_efficiency__basisOnStartDate(&$dateFrom, &$dateTo, &$usage)
{
    global $db;

    $rs = $db->AllRecords($q = "
            SELECT
                trouble_subtype as type,
                ts.trouble_id,
                ts.state_id,
                user_main,
                user_edit,
                tt.user_author,
                rating,
                user_rating
            FROM
                tt_stages ts
            LEFT JOIN `tt_troubles` tt ON tt.id = ts.trouble_id
            LEFT JOIN `user_users` u ON u.user = tt.user_author
            WHERE
                usergroup IN ('support','manager') AND 
                date_creation between '".$dateFrom." 00:00:00' and '".$dateTo." 23:59:59' AND 
                trouble_type in ('trouble', 'task', 'support_welltime') AND 
                service in ('".implode("','", $usage)."')
            ORDER BY tt.id, ts.stage_id
            ");
    $tmp = array();
    $rating = array();
    $counter = array(
                    "7" => array(), // completed
                    "2" => array()  // closed
    );

    $total = array(
                    "7" => array(), // completed
                    "2" => array()  // closed
    );

    $users = array();

    $troubleId = 0;
    foreach ($rs as $r)
    {
        if ($r["state_id"] == 7 && strlen($r["user_rating"])) {
            if (!isset($tmp[$r["trouble_id"]]))
                $tmp[$r["trouble_id"]] = array('type'=>$r["type"],'user_rating'=>'','7'=>0,'2'=>0,'1u'=>array());

            if ($r["rating"] > 0) {
                if (strlen($tmp[$r["trouble_id"]]['user_rating']) && $tmp[$r["trouble_id"]]['user_rating']!=$r["user_rating"])
                    $tmp[$r["trouble_id"]]['1u'][]=$tmp[$r["trouble_id"]]['user_rating'];

                $tmp[$r["trouble_id"]]['type']=$r["type"];
                $tmp[$r["trouble_id"]]['user_rating']=$r["user_rating"];
                $tmp[$r["trouble_id"]]['7']=$r["rating"];
            }
        }
        if ($r["state_id"] == 2 && strlen($r["user_rating"])) {
            if (isset($tmp[$r["trouble_id"]]) && $r["rating"] > 0 && strlen($r["user_rating"])) {
                $tmp[$r["trouble_id"]]['2']=$r["rating"];
            }
        }
        // new trouble, reset
        if ($r["trouble_id"] != $troubleId)
        {
            $troubleId = $r["trouble_id"];
            $state = $r["state_id"];
            $user = $r["user_main"];

            continue; //this first stage
        }

        $user = $r["user_author"];

        if ($state != $r["state_id"])
        {
            if($r["state_id"] == 7 || $r["state_id"] == 2)
            {
                if(!isset($counter[$r["state_id"]][$r["type"]]))
                    $counter[$r["state_id"]][$r["type"]] = array();

                if(!isset($counter[$r["state_id"]][$r["type"]][$user]))
                    $counter[$r["state_id"]][$r["type"]][$user] = 0;

                $counter[$r["state_id"]][$r["type"]][$user]++;


                if (!isset($total[$r["state_id"]][$r["type"]]))
                    $total[$r["state_id"]][$r["type"]] = 0;

                $total[$r["state_id"]][$r["type"]]++;


                $users[$user] = $user;
            }

            $state = $r["state_id"];
        }
    }
    foreach ($tmp as $k=>$rat) {
        if (strlen($rat['user_rating'])) {
            if (!isset($rating[$rat['user_rating']]))
                $rating[$rat['user_rating']] = array();
            if (!isset($rating[$rat['user_rating']][$rat['type']]))
                $rating[$rat['user_rating']][$rat['type']] = array('7'=>0,'2'=>0);

            $rating[$rat['user_rating']][$rat['type']]['7']+=$rat['7'];
            $rating[$rat['user_rating']][$rat['type']]['2']+=$rat['2'];

            if (!isset($users[$rat['user_rating']])) $users[$rat['user_rating']] = $rat['user_rating'];
        }
    }

    foreach($db->AllRecords("select user, name from user_users where user in ('".implode("','", $users)."')") as $u)
    {
        $users[$u["user"]] = $u["name"];
    }

    return array($counter, $users, $total, $rating);
}

function stats_support_efficiency__basisOnCompleted(&$dateFrom, &$dateTo, &$usage)
{
    global $db;

    $rs = $db->AllRecords($q = "
            SELECT
                trouble_subtype as type,
                ts.trouble_id,
                ts.state_id,
                user_main,
                user_edit,
                tt.user_author,
                rating,
                user_rating
            FROM
                tt_stages ts
            LEFT JOIN `tt_troubles` tt ON tt.id = ts.trouble_id
            LEFT JOIN `user_users` u ON u.user = tt.user_author
            WHERE
                ts.state_id IN(2,7) AND 
                usergroup IN ('support','manager') AND 
                date_edit between '".$dateFrom." 00:00:00' and '".$dateTo." 23:59:59' AND 
                trouble_type in ('trouble', 'task', 'support_welltime') AND 
                service in ('".implode("','", $usage)."') 
            ORDER BY tt.id, ts.stage_id
            ");
    $tmp = array();
    $rating = array();
    $counter = array(
        "7" => array(), // completed
        "2" => array(),  // closed
        "2w7" => array()  //closed without completed
        );

    $total = array(
        "7" => array(), // completed
        "2" => array(),  // closed
        "2w7" => array()  //closed without completed
    );

    $users = array();

    $troubleId = 0;
    foreach ($rs as $r)
    {
        if (!isset($tmp[$r["trouble_id"]]))
            $tmp[$r["trouble_id"]] = array('type'=>$r["type"], 'user_author'=>$r["user_author"], 'user_7'=>'', 'user_2'=>'','user_rating'=>'','7'=>0,'2'=>0,'1u'=>array());
        
        if ($r["state_id"] == 7) {
            $tmp[$r["trouble_id"]]['user_7'] = $r["user_edit"];

            if ($r["rating"] > 0 && strlen($r["user_rating"])) {
                if (strlen($tmp[$r["trouble_id"]]['user_rating']) && $tmp[$r["trouble_id"]]['user_rating']!=$r["user_rating"]) 
                    $tmp[$r["trouble_id"]]['1u'][]=$tmp[$r["trouble_id"]]['user_rating'];

                $tmp[$r["trouble_id"]]['user_rating']=$r["user_rating"];
                $tmp[$r["trouble_id"]]['7']=$r["rating"];
            }
        }
        
        if ($r["state_id"] == 2) {
            $tmp[$r["trouble_id"]]['user_2'] = $r["user_edit"];
            if ($r["rating"] > 0 && strlen($r["user_rating"])) {
                $tmp[$r["trouble_id"]]['2']=$r["rating"];
            }
        }
    }

    foreach ($tmp as $trouble_id=>$t) {
        
        //counter calculation
        
        if (strlen($t['user_7'])) {
            if (!isset($counter['7'][$t['type']])) $counter['7'][$t['type']] = array();
            if (!isset($total['7'][$t['type']])) $total['7'][$t['type']] = 0;
            
            if (!isset($counter['7'][$t['type']][$t['user_7']])) 
                $counter['7'][$t['type']][$t['user_7']] = 0;
            
            $counter['7'][$t['type']][$t['user_7']]++;
            $total['7'][$t["type"]]++;
            
            if (!isset($users[$t['user_7']])) 
                $users[$t['user_7']] = $t['user_7'];
        }
        
        
        if (strlen($t['user_2'])) {
            if (strlen($t['user_7'])) {
                // closed and completed
                if (!isset($counter['2'][$t['type']])) $counter['2'][$t['type']] = array();
                if (!isset($total['2'][$t['type']])) $total['2'][$t['type']] = 0;
                
                if (!isset($counter['2'][$t['type']][$t['user_7']]))
                    $counter['2'][$t['type']][$t['user_7']] = 0;
                
                $counter['2'][$t['type']][$t['user_7']]++;
                $total['2'][$t["type"]]++;
                
                if (!isset($users[$t['user_7']])) 
                    $users[$t['user_7']] = $t['user_7'];
            } else {
                // closed not completed
                if (!isset($counter['2w7'][$t['type']])) $counter['2w7'][$t['type']] = array();
                if (!isset($total['2w7'][$t['type']])) $total['2w7'][$t['type']] = 0;
                
                if (!isset($counter['2w7'][$t['type']][$t['user_2']]))
                    $counter['2w7'][$t['type']][$t['user_2']] = 0;
                
                $counter['2w7'][$t['type']][$t['user_2']]++;
                $total['2w7'][$t["type"]]++;
                
                if (!isset($users[$t['user_2']])) 
                    $users[$t['user_2']] = $t['user_2'];
            }
        
        }

        //rating calculation
        if (strlen($t['user_rating'])) {
            if (!isset($rating[$t['user_rating']])) 
                $rating[$t['user_rating']] = array();
            if (!isset($rating[$t['user_rating']][$t['type']])) 
                $rating[$t['user_rating']][$t['type']] = array('7'=>0,'2'=>0);

            $rating[$t['user_rating']][$t['type']]['7']+=$t['7'];
            $rating[$t['user_rating']][$t['type']]['2']+=$t['2'];
            
            if (!isset($users[$t['user_rating']])) 
                $users[$t['user_rating']] = $t['user_rating'];
        }
    }

    foreach($db->AllRecords("select user, name from user_users where user in ('".implode("','", $users)."')") as $u)
    {
        $users[$u["user"]] = $u["name"];
    }

    return array($counter, $users, $total, $rating);
}

function stats_report_netbynet($fixclient, $genReport = false, $viewLink = true){
    $this->stats_report_plusopers($fixclient, 'nbn', $genReport, $viewLink);
}

function stats_report_onlime($fixclient, $genReport = false, $viewLink = true){
    $this->stats_report_plusopers($fixclient, 'onlime', $genReport, $viewLink);
}

function stats_report_onlime2($fixclient, $genReport = false, $viewLink = true){
    $this->stats_report_plusopers($fixclient, 'onlime2', $genReport, $viewLink);
}

function stats_report_onlime_all($fixclient, $genReport = false, $viewLink = true){
    $this->stats_report_plusopers($fixclient, 'onlime_all', $genReport, $viewLink);
}

function stats_report_plusopers($fixclient, $client, $genReport = false, $viewLink = true){
    global $db,$design;

    $viewLink = $viewLink && !defined("no_link");

    $design->assign("viewLink", $viewLink);

    $names = array(
            "nbn"     => "NetByNet",
            "onlime"  => "OnLime",
            "onlime2" => "OnLime2",
            "onlime_all" => "OnLime 1+2"
            );
    $reports = array(
            "nbn"     => "netbynet",
            "onlime2" => "onlime2",
            "onlime"  => "onlime",
            "onlime_all" => "onlime_all"
            );

    $report = "report_".$reports[$client];
    $design->assign("client_name", $names[$client]);
    $design->assign("report",  $report);

    if(in_array(get_param_raw("do", ""), array("add", "edit")))
    {
        $error = "";
        $info = "";
        $a = array("bill_no" => get_param_raw("bill_no", ""));

        if(get_param_raw("info_form", ""))
        {
        	$db->QueryUpdate("params", "param", array(
        		"param" => "onlime_info",
        		"value" => get_param_raw("onlime_info","")
        	)
        	);
        }

        if(get_param_raw("form", ""))
        {
        	list($fields, $error, $a) = $this->report_plusopers__getFields_and_check($client, $a);

            if(!$error)
            {

                $i = $db->GetRow("select * from newbills_add_info where bill_no = '".mysql_escape_string($a["bill_no"])."'");

                $rNBN = new requestPlusOper();
                $info = $rNBN->create($client, $a);

                $isNewDateDeliv = false;
                if($i && $i["comment1"])
                {
                    @list(, $i["date_deliv"]) = explode(": ", $i["comment1"]);
                    $isNewDateDeliv = $i["date_deliv"] != $a["date_deliv"];
                }

                if(($a["comment"] || $isNewDateDeliv) && $a["bill_no"])
                {
                    $tId = $db->GetValue("select id from tt_troubles where bill_no = '".$a["bill_no"]."'");
                    $curStageId = $db->GetValue("select cur_stage_id from tt_troubles where bill_no = '".$a["bill_no"]."'");
                    $stateId = $db->GetValue("select state_id from tt_stages where stage_id = '".$curStageId."'");

                    if($isNewDateDeliv)
                        $a["comment"] .= "\nДоставка на: ".$a["date_deliv"];

                    $tsid = $db->QueryInsert('tt_stages',array(
                                'trouble_id'=>$tId,
                                'state_id'=>$stateId,
                                'user_main'=>$client,
                                'date_start'=>array('now()'),
                                'date_finish_desired'=>array('now()'),
                                'comment' => trim($a["comment"])
                                )
                            );
                }

                /*
                header("Location: ./?".($viewLink ? "module=stats&action=".$report : "")."");
                exit();
                */
                $a = array("bill_no" => "", "qty" => 1);

            }
        }else{
            $billNo = get_param_raw("bill_no", "");
            $a = $this->report_plusopers__Load($client, $billNo);

            if(!$billNo) //on add
            {
            	$a["qty"] = 1;
            }
        }

        $qtys = array();
        for($i =0; $i<=10 ; $i++)
        	$qtys[$i]=$i;

        $design->assign("a", $a);
        $design->assign("error", $error);
        $design->assign("info", $info);

        $design->assign("qtys", $qtys);
        $design->assign("once", 4);

        $design->assign("deliv_type", array("moskow" => "По Москве","mkad" => "За МКАД"));
        $design->assign("onlime_info", $db->GetValue("select value from params where param='onlime_info'"));

        $design->AddMain("stats/".$reports[$client]."_add.html");
        return;
    }

    if($genReport)
    {
	$dateFrom = new DatePickerValues('dateNoRequest', '-1 day');
	$d1 = $d2 = $dateFrom->getDay();
	$date = $d1 == $d2 ? 'за '.$d1 : 'с '.$d1.' по '.$d2;
	$dateFrom->format = 'Y-m-d';
	$d1 = $d2 = $dateFrom->getDay();
    }else{
	$dateFrom = new DatePickerValues('date_from', 'first');
	$dateTo = new DatePickerValues('date_to', 'last');
	$d1 = $dateFrom->getDay();
        $d2 = $dateTo->getDay();
	$date = $d1 == $d2 ? 'за '.$d1 : 'с '.$d1.' по '.$d2;
	$dateFrom->format = 'Y-m-d';$dateTo->format = 'Y-m-d';
	$d1 = $dateFrom->getDay();
        $d2 = $dateTo->getDay();

        $filterPromoAll = array("all"=> "Все заявки", "promo" => "По акции", "no_promo" => "Не по акции");
    	$filterPromo = get_param_raw("filter_promo", "all");

        $design->assign("filter_promo_all", $filterPromoAll);
        $design->assign("filter_promo", $filterPromo);
    }
    
    $design->assign("date", $date);

    if($client == "onlime_all")
    {
        list($r1, $closeList1, $deliveryList1) = $this->report_plusopers__getCount("onlime", $d1, $d2, $filterPromo);
        list($r2, $closeList2, $deliveryList2) = $this->report_plusopers__getCount("onlime2", $d1, $d2, $filterPromo);

        foreach($r1 as $k => $v) $r2[$k]+= $v;

        $r = $r2;

    }else{
        list($r, $closeList, $deliveryList) = $this->report_plusopers__getCount($client, $d1, $d2, $filterPromo);
    }


    $design->assign("s", $r);

    $list = array();

    if(($listType = get_param_raw("list", "")) != "")
    {
        if($client == "onlime_all")
        {
            $list1 = $this->report_plusopers__getList("onlime", $listType, $d1, $d2, $deliveryList1, $closeList1, $filterPromo);
            $list2 = $this->report_plusopers__getList("onlime2", $listType, $d1, $d2, $deliveryList2, $closeList2, $filterPromo);

            $list = array_merge($list1, $list2);
        }else{
            $list = $this->report_plusopers__getList($client, $listType, $d1, $d2, $deliveryList, $closeList, $filterPromo);
        }
    }

    $total = array("count_3" => 0, "count_9" => 0, "count_11" => 0, "count_12" => 0);

    foreach($list as $l)
    {
        $total["count_3"] += $l["count_3"];
        $total["count_9"] += $l["count_9"];
        $total["count_11"] += $l["count_11"];
        $total["count_12"] += $l["count_12"];
    }

    $design->assign("list", $list);
    $design->assign("total", $total);

    unset($_GET["list"]);
    $url = "";
    foreach($_GET as $k => $v)
    {
        $url .= ($url ? "&" :"").$k."=".$v;
    }

    $design->assign("url", $url);

    $design->assign("showSelects", !$genReport);



    if(get_param_raw("export", "") == "excel")
    {
        foreach($list as  &$l)
        {
            $l["count_3"] = (int)$l["count_3"];
            $l["count_9"] = (int)$l["count_9"];
            $l["count_11"] = (int)$l["count_11"];
            $l["count_12"] = (int)$l["count_12"];
            $design->assign("i_stages", $l["stages"]);
            $design->assign("last", 1000);
            $html = $design->fetch("stats/onlime_stage.tpl");
            $html = str_replace(array("\r","\n", "<br>", "    ", "   ", "   ", "  "), array("","", "\n", " ", " ", " ", " "), $html);
            $l["stages_text"] = strip_tags($html);
        }
        unset($l);

        $list[] = $total+array("date_creation" => "Итого:");

        $this->GenerateExcel("OnLime__".str_replace(" ", "_", $sTypes[$listType]["title"])."__".$d1."__".$d2,
                array(
                    "Оператор" => "fio_oper",
                    "Номер счета OnLime" => "req_no",
                    "Номер счета Маркомнет Сервис" => "bill_no",
                    "Дата создания заказа" => "date_creation",
                    "Кол-во Onlime-Telecard" => "count_3",
                    "Кол-во HD-ресивер OnLime" => "count_9",
                    "Кол-во HD-ресивер с диском" => "count_11",
                    "NetGear Беспроводной роутер, JNR3210-1NNRUS" => "count_12",
                    "Серийные номера" => "serials",
                    "Номер купона" => "coupon",
                    "ФИО клиента" => "fio",
                    "Телефон клиента" => "phone",
                    "Адрес" => "address",
                    "Дата доставки желаемая" => "date_deliv",
                    "Дата доставки фактическа" => "date_delivered",
                    "Этапы" => "stages_text"
                    ),
                $list);

    }

    if($genReport)
    {
        return array( $date, $design->fetch("stats/".$reports[$client].".html", null, null, false));
    }else{

        $design->AddMain("stats/".$reports[$client].".html");
    }
}


private function GenerateExcel($title, $head, $list)
{
    require_once 'Spreadsheet/Excel/Writer.php';

    $workbook = new Spreadsheet_Excel_Writer();
    $workbook->setVersion(8);
    $workbook->send($title);
    $sheet =& $workbook->addWorksheet();
    $sheet->setInputEncoding('koi8-r');

    $fHeader =& $workbook->addFormat();
    $fHeader->setHAlign('center');
    $fHeader->setSize(10);
    $fHeader->setBorder(1);
    $fHeader->setBold();


    $fData =& $workbook->addFormat();
    $fData->setSize(8);
    $fData->setBorder(1);
	$fData->setVAlign('top');

	$fDataText =$fData;
	$fDataText->setVAlign('vjustify');


	foreach(array(10, 12, 21, 11, 29, 35, 33, 14, 14, 88) as $idx => $width)
	    $sheet->setColumn($idx, $idx, $width);

    $idx = 0;
    foreach($head as $title => $field)
    {
        $sheet->write(2, $idx++, $title, $fHeader);
    }

    foreach($list as $rowIdx => $l)
    {
        $colIdx = 0;
        foreach($head as $title => $field)
        {
            $sheet->write(3+$rowIdx, $colIdx++, strip_tags($l[$field]), $idx == 9 ? $fDataText : $fData);
        }
        $c = count(explode("\n", $l[$field]));
        $sheet->setRow(3+$rowIdx, ($c < 2 ? 2 : $c)*12);
    }

    $workbook->close();
    exit();

}


private function report_plusopers__addressToStr($l)
{
	if(strpos($l["address"], "^") !== false)
	{
		list($street, $home, $bild, $porch, $floor, $flat, $intercom) = explode(" ^ ", $l["address"]." ^  ^  ^  ^  ^  ^  ^  ^ ");
		$a = $street;
		if($home) $a .= ", д.".$home;
		if($bild) $a .= " стр.".$bild;
		if($porch) $a .= ", подъезд ".$porch;
		if($floor) $a .= ", этаж ".$floor;
		if($flat)  $a .= ", кв.".$flat;
		if($intercom) $a .= " (домофон: ".$intercom.")";

		return $a;
	}else{
		return $l["address"];
	}
}

private function report_plusopers__phoneToStr($l)
{
	if(strpos($l["phone"], "^") !== false)
	{
		list($home, $mob, $work) = explode(" ^ ", $l["phone"]." ^  ^  ^ ");
		$p = array();

		if($home) $p[] = "Домашний: ".$home;
		if($mob) $p[] = "Сотовый: ".$mob;
		if($work) $p[] = "Рабочий: ".$work;

		return implode("<br/>", $p);
	}else{
		return $l["phone"];
	}
}

private function report_plusopers__getFields_and_check($client, $a)
{
	$fields = array();
	$error = "";

	$fields["fio"] = "ФИО";

	if($client == "nbn")
	{
		$fields["req"] = "Номер заявки";
		$fields["address"] = "Адресс";
		$fields["phone"] = "Контактный телефон";
	}


	if($client == "onlime" || $client == "onlime2") // need check
	{
		$fields["fio_oper"] = "ФИО оператора";
		$fields["date_deliv"] = "Желаемая дата и время заказа";
	}

	foreach($fields as $k => $v)
	{
		$a[$k] = trim(get_param_raw($k, ""));
		if(empty($a[$k]) && !$error)
		{
			$error = "Поле \"".$v."\" не заполненно";
		}
	}

	if(isset($fields["date_deliv"]) && !$error && !strtotime($a["date_deliv"]))
	{
		$error = "Неверная дата доставки заказа!";
    }


    $a["qty"] = get_param_raw("qty", 1);

	if(!$error && $a["qty"] == 0)
	{
		$error = "Позиции заказа не выбранны";
	}


	if($client == "onlime" || $client == "onlime2")
	{
		$address="";
		$phone = "";

		foreach(array("address_street", "address_house", "address_building", "address_porch",
						"address_floor", "address_flat", "address_interkom") as $f)
		{
			$a[$f] = get_param_raw($f, "");
			$address .= $a[$f]." ^ ";
		}

		foreach(array("phone_home", "phone_mobil", "phone_work") as $f)
		{
			$a[$f] = get_param_raw($f, "");
			$phone .= $a[$f]." ^ ";
		}

		$a["address"] = $address;
		$a["phone"] = $phone;
	}


	$a["deliv_type"] = get_param_raw("deliv_type", "moskow");
	$a["comment"] = trim(get_param_raw("comment", ""));

	return array($fields, $error, $a);
}

private function report_plusopers__Load($client, $billNo)
{
	global $db;
	$a = array();

	if($billNo && $a = $db->GetRow("select * from newbills_add_info where bill_no = '".mysql_escape_string($billNo)."'"))
	{
		$a["req"] = $a["req_no"];
		$a["comment"] = $db->GetValue("select comment from newbills where bill_no = '".mysql_escape_string($billNo)."'");

		if($a["comment1"])
		{
			@list(, $a["date_deliv"]) = explode(": ", $a["comment1"]);
		}

		if($a["comment2"])
		{
			@list(, $a["fio_oper"]) = explode(": ", $a["comment2"]);
		}

		if($client == "nbn")
		{
			$a["qty"] = $db->GetValue("select amount from newbill_lines where bill_no = '".$billNo."' and item_id = '4e8cc21b-d476-11e0-9255-d485644c7711'");
			$a["deliv_type"] = $db->GetValue("select amount from newbill_lines where bill_no = '".$billNo."' and item_id = 'a449a3f7-d918-11e0-bdf8-00155d21fe06'") ? "moskow" : "mkad";
		}else{//onlime
			$a["qty"] = $db->GetValue("select amount from newbill_lines where bill_no = '".$billNo."' and
				item_id in ('ea05defe-4e36-11e1-8572-00155d881200', 'f75a5b2f-382f-11e0-9c3c-d485644c7711','6d2dfd2a-211e-11e3-95df-00155d881200')");
			$a["deliv_type"] = $db->GetValue("select amount from newbill_lines where bill_no = '".$billNo."' and item_id = '81d52242-4d6c-11e1-8572-00155d881200'") ? "moskow" : "mkad";

			$address = explode(" ^ ", $a["address"]);
			foreach(array("address_street", "address_house", "address_building", "address_porch",
									"address_floor", "address_flat", "address_interkom") as $idx => $f)
				$a[$f] = @$address[$idx];

			$phone = explode(" ^ ", $a["phone"]);
			foreach(array("phone_home", "phone_mobil", "phone_work") as $idx => $f)
				$a[$f] = @$phone[$idx];
		}
	}

	return $a;
}

private function report_plusopers__getCount($client, $d1, $d2, $filterPromo)
{
    global $db;


    $deliveryList = $db->AllRecords($sql = "
                select
                    t.id as trouble_id, req_no,fio,phone,t.bill_no,date_creation
                from
                    (
                    select
                        s.trouble_id, max(s.stage_id) as max_stage_id
                    from
                        tt_stages s, tt_doers d ,(select
                                    t.id as trouble_id
                                from
                                    tt_troubles t, tt_stages s, tt_doers d
                                where
                                        date_start between '".$d1." 00:00:00' and '".$d2." 23:59:59'
                                    and t.id = s.trouble_id
                                    and t.client='".$client."'
                                    and d.stage_id = s.stage_id
                                group by
                                    t.id) ts
                    where
                            s.stage_id = d.stage_id
                        and s.trouble_id = ts.trouble_id
                    group by
                        s.trouble_id
                    ) as m, tt_stages s, tt_stages s2, tt_troubles t, newbills_add_info a
                where
                        m.max_stage_id = s.stage_id
                    and s.date_start between '".$d1." 00:00:00' and '".$d2." 23:59:59'
                    and t.id = m.trouble_id
                    and t.bill_no = a.bill_no
                    and s2.stage_id = t.cur_stage_id 
                    and s2.state_id in (2,20)
                ");

    $addWhere = "";
    $addJoin = "";

    if($filterPromo == "promo")
    {
        $addWhere = "and coupon != ''";
        $addJoin = "left join onlime_order oo on (oo.bill_no = b.bill_no)";
    }elseif($filterPromo == "no_promo")
    {
        $addWhere = "and (coupon = '' or coupon is null)";
        $addJoin = "left join onlime_order oo on (oo.bill_no = b.bill_no)";
    }

if($client != "nbn")
{
	$closeList = $db->AllRecords("
		select t.id as trouble_id, req_no, fio, phone, address, t.bill_no, date_creation,

			(select date_start
			from tt_stages s, tt_doers d
			where s.stage_id = d.stage_id and s.trouble_id = t.id
				order by s.stage_id desc limit 1) as date_delivered,

				(select sum(amount) from newbill_lines nl
                        where item_id in ('ea05defe-4e36-11e1-8572-00155d881200', 'f75a5b2f-382f-11e0-9c3c-d485644c7711', '6d2dfd2a-211e-11e3-95df-00155d881200')
                        and nl.bill_no = t.bill_no) as count_3,

				(select sum(amount) from newbill_lines nl
                        where item_id in ('4acdb33c-0319-11e2-9c41-00155d881200', '14723f35-d423-11e3-9fe5-00155d881200')
                        and nl.bill_no = t.bill_no) as count_9,

				(select sum(amount) from newbill_lines nl
                        where item_id in ('72904487-32f6-11e2-9369-00155d881200', '2c6d3955-d423-11e3-9fe5-00155d881200')
                        and nl.bill_no = t.bill_no) as count_11,

				(select sum(amount) from newbill_lines nl
                        where item_id in ('e1a5bf94-0764-11e4-8c79-00155d881200')
                        and nl.bill_no = t.bill_no) as count_12,

        (select group_concat(serial SEPARATOR ', ') from g_serials s where s.bill_no = t.bill_no) as serials,
        (select concat(coupon) from onlime_order oo where oo.bill_no = t.bill_no) as coupon,

                a.comment1 as date_deliv,
                a.comment2 as fio_oper

		from tt_stages s, tt_troubles t, newbills_add_info a, newbills b
        ".$addJoin."
		where
				s.trouble_id = t.id
			and s.date_start between '".$d1." 00:00:00' and '".$d2." 23:59:59'
			and t.client='".$client."'
			and s.state_id in (2,20)
			and t.bill_no = a.bill_no
            and b.bill_no = t.bill_no
            and is_rollback = 0
            ".$addWhere."
            group by s.trouble_id
	        ");

}else{
    $closeList = array();
}


    $r = $db->GetRow($q = "
                SELECT
                    sum(is_rollback) as rollback,
                    sum(if(is_rollback =0 and state_id in (2,20),1,0)) as close,
                    sum(if(is_rollback = 0 and state_id = 21,1,0)) as reject,
                    sum(if(is_rollback = 0 and state_id not in (2, 20, 21) ,1,0)) as work,
                    count(1)                                   as count
                FROM
                    `tt_troubles` t, tt_stages s, newbills b
                    ".$addJoin."
                where
                        t.client = '".$client."'
                    and b.bill_no = t.bill_no
                    and s.stage_id = t.cur_stage_id
                    and date_creation between '".$d1." 00:00:00' and '".$d2." 23:59:59'
                    ".$addWhere)+array("delivery" => count($deliveryList));

    if($client != "nbn")
    {
      //$closeCount = 0;
      //foreach($closeList as $l) $closeCount += $l["count_cards"];
      $r["close"] = count($closeList);//$closeCount;
    }
    return array($r, $closeList, $deliveryList);
}

private function report_plusopers__getList($client, $listType, $d1, $d2, $deliveryList, $closeList, $filterPromo)
{
    global $design, $db;


    $addWhere = "";
    $addJoin = "";

    if($filterPromo == "promo")
    {
        $addWhere = "and coupon != ''";
        $addJoin = "left join onlime_order oo on (oo.bill_no = b.bill_no)";
    }elseif($filterPromo == "no_promo")
    {
        $addWhere = "and (coupon = '' or coupon is null)";
        $addJoin = "left join onlime_order oo on (oo.bill_no = b.bill_no)";
    }

        $sTypes = array(
                "work"   => array("sql" => "is_rollback =0 and state_id not in (2,20,21)",
                                  "title" => ($client == "nbn" ? "в работе" : "В Обработке")),
                "close"  => array("sql" => "is_rollback =0 and state_id in (2,20)",
                                  "title" => ($client == "nbn" ? "закрытые" : "Доставлен")),
                "reject" => array("sql" => "is_rollback =0 and state_id = 21",
                                  "title" => ($client == "nbn" ? "в отказе" : "Отказ")),
                "delivery" => array(                            "title" => "доставка")
                );

        if($client != "nbn")  // onlime
        {
            $sTypes["rollback"] = array("sql" => "is_rollback =1", "title" => "Возврат");
        }

        if(isset($sTypes[$listType]))
        {
            $design->assign("listType", $listType);
        	if(($client == "onlime" || $client == "onlime2") && $listType == "close")
        	{
        		$list = $closeList;
        	}elseif($listType == "delivery")
            {
                $list = $deliveryList;
            }else{
                $list = $db->AllRecords($q = "
                        SELECT
                            t.id as trouble_id, t.bill_no, t.problem,
                            req_no, fio, phone, address, date_creation
                            ".($client != "nbn" ?
                    ", 
                    
                    
				(select sum(amount) from newbill_lines nl
                        where item_id in ('ea05defe-4e36-11e1-8572-00155d881200', 'f75a5b2f-382f-11e0-9c3c-d485644c7711', '6d2dfd2a-211e-11e3-95df-00155d881200')
                        and nl.bill_no = t.bill_no) as count_3,

				(select sum(amount) from newbill_lines nl
                        where item_id in ('4acdb33c-0319-11e2-9c41-00155d881200', '14723f35-d423-11e3-9fe5-00155d881200')
                        and nl.bill_no = t.bill_no) as count_9,

				(select sum(amount) from newbill_lines nl
                        where item_id in ('72904487-32f6-11e2-9369-00155d881200', '2c6d3955-d423-11e3-9fe5-00155d881200')
                        and nl.bill_no = t.bill_no) as count_11,

				(select sum(amount) from newbill_lines nl
                        where item_id in ('e1a5bf94-0764-11e4-8c79-00155d881200')
                        and nl.bill_no = t.bill_no) as count_12,

        (select group_concat(serial separator ', ') from g_serials s where s.bill_no = t.bill_no) as serials,
        (select concat(coupon) from onlime_order oo where oo.bill_no = t.bill_no) as coupon,

                            (select date_start from tt_stages s, tt_doers d where s.stage_id = d.stage_id and s.trouble_id = t.id order by s.stage_id desc limit 1) as date_delivered,
                            i.comment1 as date_deliv,
                            i.comment2 as fio_oper
                            " : "")."
                        FROM
                            `tt_troubles` t, tt_stages s, newbills_add_info i, newbills b
                            ".$addJoin."
                        WHERE
                                t.client = '".$client."'
                            AND s.stage_id = t.cur_stage_id
                            AND date_creation between '".$d1." 00:00:00' AND '".$d2." 23:59:59'
                            AND i.bill_no = t.bill_no
                            AND t.bill_no = b.bill_no
                            AND ".$sTypes[$listType]["sql"]."
                            ".$addWhere."
                        ORDER BY
                            date_creation");

            }

            foreach($list as &$l)
            {
            	if($client == "onlime" || $client == "onlime2")
            	{
	                if(isset($l["date_deliv"]) && $l["date_deliv"])
	                {
	                    @list(, $l["date_deliv"]) = explode(": ", $l["date_deliv"]);
	                }

	                if(isset($l["fio_oper"]) && $l["fio_oper"])
	                {
	                	@list(, $l["fio_oper"]) = explode(": ", $l["fio_oper"]);
	                }

	                $l["address"] = $this->report_plusopers__addressTOStr($l);
	                $l["phone"] = $this->report_plusopers__phoneToStr($l);
            	}


				$l['stages'] = $db->AllRecords('
					SELECT
						S.*,
						IF(S.date_edit=0,NULL,date_edit) as date_edit,
						tts.name as state_name
					FROM
						tt_stages as S
					INNER JOIN
						tt_states tts
					ON
						tts.id = state_id
					WHERE
						trouble_id='.$l['trouble_id'].'
					ORDER BY
						stage_id ASC
				');
                foreach($l["stages"] as &$s)
                {
				$query = "
					SELECT
						`td`.`doer_id`,
						`cr`.`name`,
						`cr`.`depart`
					FROM
						`tt_doers` `td`
					LEFT JOIN
						`courier` `cr`
					ON
						`cr`.`id` = `td`.`doer_id`
					WHERE
						`td`.`stage_id` = ".$s['stage_id']."
					ORDER BY
						`cr`.`depart`,
						`cr`.`name`
				";
				$s['doers']=$db->AllRecords($query,null,MYSQL_ASSOC);
			}
            }
            $design->assign("listName", $sTypes[$listType]["title"]);
        }
        return $list;
}

  public function stats_report_phone_sales()
  {
    global $db, $design;

    $curr_phones = $db->AllRecords('
        select 
          u.region, 
          count(*) as count_num, 
          sum(no_of_lines) as count_lines 
        from 
          voip_numbers v, usage_voip u 
        where 
          usage_id = u.id 
        group by 
          u.region', 'region');
    $curr_vpbx = $db->AllRecordsAssoc(
	'SELECT 
		c.region, COUNT(*) as count_vpbx
	FROM 
		usage_virtpbx as u
	LEFT JOIN 
		clients as c ON c.client = u.client 
	WHERE 
		CAST(NOW() as DATE)  BETWEEN u.actual_from AND u.actual_to  
	GROUP BY
		c.region
	', 'region', 'count_vpbx'
    );
    $region_clients_count = $db->AllRecordsAssoc("
	SELECT 
		COUNT(id) as clients,
		region
	FROM 
		clients
	WHERE 
		status IN ('testing', 'conecting', 'work') AND
		region > 0 
	GROUP BY 
		region
	ORDER BY
		region DESC
    ", 'region', 'clients');
    $month_list = array('Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь');
    $regions = $db->AllRecords("select id, short_name, name from regions order by id desc");
    $reports = array();
    for($mm = 0; $mm < 4; $mm++)
    {
      $date = date("Y-m-01");

      $client_ids = array();
      $region_sums = $db->AllRecordsAssoc($q="
		SELECT 
			b.region,
			ROUND(
				SUM(
					IF(a.currency='RUR', a.sum, 
						IF (a.inv_rur > 0, a.inv_rur, 
							(SELECT 
								rate 
							FROM 
								bill_currency_rate 
							WHERE 
								date = a.bill_date)
							*a.sum)
					)
				)) as sum
		FROM 
			newbills as a
		LEFT JOIN
			clients as b ON a.client_id = b.id
		WHERE 
			a.bill_date >= date_add('".$date."',interval -".$mm." month) AND 
			a.bill_date < date_add('".$date."',interval -".$mm."+1 month) AND 
			b.region > 0 AND 
			b.status IN ('testing', 'conecting', 'work') AND 
			b.type IN ('org', 'priv') AND 
			sum > 0 
		GROUP BY
			b.region
		ORDER BY 
			b.region DESC
      ", 'region', 'sum');
      $region_sums['all'] = 0;
      foreach ($region_sums as $k => $v)
      {
		$region_sums['all'] += $v;
      }
      
      $res = $db->AllRecords("
          select 
            u.region, 
            u.E164 as phone, 
            u.no_of_lines,
            c.id as client_id, 
            ifnull(c.created >= date_add('".$date."',interval -".$mm."-1 month), 0) as is_new, 
            s.name as sale_channel,
            s.courier_id as courier_id
          from usage_voip u
          left join clients c on c.client=u.client
          left join sale_channels s on s.id=c.sale_channel
          where 
              u.actual_from>=date_add('".$date."',interval -".$mm." month) 
            and u.actual_from<date_add('".$date."',interval -".$mm."+1 month)
          group by 
            u.region, u.E164, c.id, c.created, s.name  ");
            
	$res_vpbx = $db->AllRecords("
          select 
            c.region, 
            u.id, 
            c.id as client_id, 
            ifnull(c.created >= date_add('".$date."',interval -".$mm."-1 month), 0) as is_new, 
            s.name as sale_channel,
            s.courier_id as courier_id
          from usage_virtpbx u
          left join clients c on c.client=u.client
          left join sale_channels s on s.id=c.sale_channel
          where 
              u.actual_from>=date_add('".$date."',interval -".$mm." month) 
            and u.actual_from<date_add('".$date."',interval -".$mm."+1 month)
          group by 
            c.region, c.id, c.created, s.name  ");

      $sale_nums = array('all'=>array('new'=>0,'old'=>0,'all'=>0));
      $sale_vpbx = array('all'=>array('new'=>0,'old'=>0,'all'=>0));
      $sale_nonums = array('all'=>array('new'=>0,'old'=>0,'all'=>0));
      $sale_lines = array('all'=>array('new'=>0,'old'=>0,'all'=>0));
      $sale_clients = array('all'=>array('new'=>0,'old'=>0,'all'=>0));
      $sale_channels = array('all' => array('vpbx' => array('new'=>0,'old'=>0,'all'=>0), 'nums' => array('new'=>0,'old'=>0,'all'=>0), 'lines' => array('new'=>0,'old'=>0,'all'=>0), 'visits' => 0), "managers" => array());
      $clients = array();
      $clients_vpbx = array();
      $vpbx_clients = array('all'=>array('new'=>0,'old'=>0,'all'=>0));
      foreach($res as $r)
      {
          $client_ids[$r['client_id']] = 0;
        if (strlen($r['phone']) > 4) //номера
        {
          if (!isset($sale_nums[$r['region']])) 
              $sale_nums[$r['region']] = array('new'=>0,'old'=>0,'all'=>0);

          if ($r['is_new'] > 0){
            $sale_nums[$r['region']]['new'] += 1;
            $sale_nums['all']['new'] += 1;
          }else{
            $sale_nums[$r['region']]['old'] += 1;
            $sale_nums['all']['old'] += 1;
          }
          $sale_nums[$r['region']]['all'] += 1;
          $sale_nums['all']['all'] += 1;

        }else{ //линия без номера

          if (!isset($sale_nonums[$r['region']])) 
              $sale_nonums[$r['region']] = array('new'=>0,'old'=>0,'all'=>0);

          if ($r['is_new'] > 0){
            $sale_nonums[$r['region']]['new'] += 1;
            $sale_nonums['all']['new'] += 1;
          }else{
            $sale_nonums[$r['region']]['old'] += 1;
            $sale_nonums['all']['old'] += 1;
          }
          $sale_nonums[$r['region']]['all'] += 1;
          $sale_nonums['all']['all'] += 1;
        }

        //линии
        if (!isset($sale_lines[$r['region']]))
          $sale_lines[$r['region']] = array('new'=>0,'old'=>0,'all'=>0);

        $sale_lines[$r['region']][$r['is_new'] ? 'new' : 'old'] += $r["no_of_lines"];
        $sale_lines['all'][$r['is_new'] ? 'new' : 'old'] += $r["no_of_lines"];

        $sale_lines[$r['region']]['all'] += $r["no_of_lines"];
        $sale_lines['all']['all'] += $r["no_of_lines"];


        if (!isset($clients[$r['client_id']]))
        {
          $clients[$r['client_id']] = $r['client_id'];

          if (!isset($sale_clients[$r['region']])) 
              $sale_clients[$r['region']] = array('new'=>0,'old'=>0,'all'=>0);

          if ($r['is_new'] > 0){
            $sale_clients[$r['region']]['new'] += 1;
            $sale_clients['all']['new'] += 1;
          }else{
            $sale_clients[$r['region']]['old'] += 1;
            $sale_clients['all']['old'] += 1;
          }
          $sale_clients[$r['region']]['all'] += 1;
          $sale_clients['all']['all'] += 1;
        }

        if (!isset($sale_channels['managers'][$r['sale_channel']])) 
            $sale_channels['managers'][$r['sale_channel']] = array('vpbx' => array('new'=>0,'old'=>0,'all'=>0),'nums' => array('new'=>0,'old'=>0,'all'=>0), 'lines' => array('new'=>0,'old'=>0,'all'=>0), 'clients' => array(), 'visits' => 0, 'courier_id' => $r['courier_id']);
        $sale_channels['managers'][$r['sale_channel']]['nums']['all'] += 1;
        $sale_channels['managers'][$r['sale_channel']]['lines']['all'] += $r['no_of_lines'];
        $sale_channels['all']['lines']['all'] += $r['no_of_lines'];
        $sale_channels['managers'][$r['sale_channel']]['clients'][]=$r['client_id'];
        $sale_channels['all']['nums']['all'] += 1;
        if ($r['is_new']){
          $sale_channels['managers'][$r['sale_channel']]['nums']['new'] += 1;
          $sale_channels['managers'][$r['sale_channel']]['lines']['new'] += $r['no_of_lines'];
          $sale_channels['all']['nums']['new'] += 1;
          $sale_channels['all']['lines']['new'] += $r['no_of_lines'];
        } else {
          $sale_channels['managers'][$r['sale_channel']]['nums']['old'] += 1;
          $sale_channels['managers'][$r['sale_channel']]['lines']['old'] += $r['no_of_lines'];
          $sale_channels['all']['nums']['old'] += 1;
          $sale_channels['all']['lines']['old'] += $r['no_of_lines'];
        }
      }
      foreach($res_vpbx as $r)
      {
          $client_ids[$r['client_id']] = 0;
        
          if (!isset($sale_vpbx[$r['region']])) 
              $sale_vpbx[$r['region']] = array('new'=>0,'old'=>0,'all'=>0);

          if ($r['is_new'] > 0){
            $sale_vpbx[$r['region']]['new'] += 1;
            $sale_vpbx['all']['new'] += 1;
          }else{
            $sale_vpbx[$r['region']]['old'] += 1;
            $sale_vpbx['all']['old'] += 1;
          }
          $sale_vpbx[$r['region']]['all'] += 1;
          $sale_vpbx['all']['all'] += 1;

        if (!isset($clients_vpbx[$r['client_id']]))
        {
          $clients_vpbx[$r['client_id']] = $r['client_id'];

          if (!isset($vpbx_clients[$r['region']])) 
              $vpbx_clients[$r['region']] = array('new'=>0,'old'=>0,'all'=>0);

          if ($r['is_new'] > 0){
            $vpbx_clients[$r['region']]['new'] += 1;
            $vpbx_clients['all']['new'] += 1;
          }else{
            $vpbx_clients[$r['region']]['old'] += 1;
            $vpbx_clients['all']['old'] += 1;
          }
          $vpbx_clients[$r['region']]['all'] += 1;
          $vpbx_clients['all']['all'] += 1;
        }

        

        if (!isset($sale_channels['managers'][$r['sale_channel']])) 
            $sale_channels['managers'][$r['sale_channel']] = array('vpbx' => array('new'=>0,'old'=>0,'all'=>0),'nums' => array('new'=>0,'old'=>0,'all'=>0), 'lines' => array('new'=>0,'old'=>0,'all'=>0), 'clients' => array(), 'visits' => 0, 'courier_id' => $r['courier_id']);
        $sale_channels['managers'][$r['sale_channel']]['vpbx']['all'] += 1;
        $sale_channels['managers'][$r['sale_channel']]['clients'][]=$r['client_id'];
        $sale_channels['all']['vpbx']['all'] += 1;
        if ($r['is_new']){
          $sale_channels['managers'][$r['sale_channel']]['vpbx']['new'] += 1;
          $sale_channels['all']['vpbx']['new'] += 1;
        } else {
          $sale_channels['managers'][$r['sale_channel']]['vpbx']['old'] += 1;
          $sale_channels['all']['vpbx']['old'] += 1;
        }
      }
/*
      //Выезды
      $res = $db->AllRecords("
              select
                count(*) as cnt, 
                c.id as client_id
              from 
                tt_stages ts
              left join tt_troubles tt on tt.id=ts.trouble_id
              left join clients c on c.client=tt.client
              where
                ts.state_id=4 and
                ts.date_edit>=date_add('$date',interval -$mm month) and
                ts.date_edit<date_add('$date',interval -$mm+1 month) and
                c.id in (" . implode(',', array_keys($client_ids)) . ")
              group by c.id
              ");
      foreach ($res as $r) {
        foreach ($sale_channels['managers'] as $manager=>&$val) {
            if (in_array($r['client_id'], $val['clients'])) {
                $val['visits'] += $r['cnt'];
                $sale_channels['all']['visits'] += $r['cnt'];
            }
        }
      }
*/
      //Выезды
      foreach($sale_channels["managers"] as $manager => &$d)
      {
        if ($d['courier_id'] > 0) {
            $res = $db->GetValue("
                select count(*) as cnt from (
                SELECT 
                    distinct `s`.`date_start` `date`, 
                    `s`.`state_id` `cur_state`
                from 
                (
                    select 
                        max(s2.stage_id) as stage_id 
                    from 
                        tt_stages s 
                    inner join tt_troubles t on (t.id = s.trouble_id) 
                    inner join tt_stages s2 on (t.id = s2.trouble_id) 
                    inner JOIN `tt_doers` `td` ON `td`.`stage_id` = `s2`.`stage_id` and `td`.`doer_id` = ".$d['courier_id']." 
                    where 
                        s.`date_start`>=date_add('$date',interval -$mm month) and
                        s.`date_start`<date_add('$date',interval -$mm+1 month) and
                        s.state_id in (2,20) 
                    group by t.id 
                )a, tt_stages s 
                LEFT JOIN `tt_troubles` `tt` ON `tt`.`id` = s.trouble_id 
                LEFT JOIN `tt_stages` `cts` ON cts.stage_id = tt.cur_stage_id 
                LEFT JOIN `clients` `cl` ON `cl`.`client` = `tt`.`client` 
                INNER JOIN `tt_doers` `td` ON `td`.`stage_id` = `s`.`stage_id` 
                INNER JOIN `courier` `cr` ON `cr`.`id` = `td`.`doer_id` 
                where 
                   s.stage_id = a.stage_id and `s`.`state_id`=4
               )q
            ");
            $sale_channels['all']['visits'] += $res;
            $d['visits'] = $res;
        }
      }
      foreach($sale_channels["managers"] as $mamager => &$d)
      {
        if ($sale_channels["all"]["nums"]['all'] > 0)
        {
		$d["nums_perc"]['new'] = round( $d["nums"]['new'] / $sale_channels["all"]["nums"]['all'] * 100);
		$d["nums_perc"]['old'] = round( $d["nums"]['old'] / $sale_channels["all"]["nums"]['all'] * 100);
        } else {
		$d["nums_perc"]['new'] = 0;
		$d["nums_perc"]['old'] = 0;
        }
        if ($sale_channels["all"]["lines"]['all'] > 0)
        {
		$d["lines_perc"]['new'] = round( $d["lines"]['new'] / $sale_channels["all"]["lines"]['all'] * 100);
		$d["lines_perc"]['old'] = round( $d["lines"]['old'] / $sale_channels["all"]["lines"]['all'] * 100);
        } else {
		$d["lines_perc"]['new'] = 0;
		$d["lines_perc"]['old'] = 0;
        }
        if ($sale_channels["all"]["vpbx"]['all'] > 0) 
        {
		$d["vpbx_perc"]['new'] = round( $d["vpbx"]['new'] / $sale_channels["all"]["vpbx"]['all'] * 100);
		$d["vpbx_perc"]['old'] = round( $d["vpbx"]['old'] / $sale_channels["all"]["vpbx"]['all'] * 100);
        } else {
		$d["vpbx_perc"]['new'] = 0;
		$d["vpbx_perc"]['old'] = 0;
        }
        $d["visits_perc"] = ($sale_channels["all"]["visits"] > 0) ? round( $d["visits"] / $sale_channels["all"]["visits"] * 100) : 0;
      }


      $del_nums = array('all'=>0);
      $del_nonums = array('all'=>0);
      $del_lines = array('all'=>0);
      $del_vpbx = array('all'=>0);
      $res = $db->AllRecords("
          select 
            u.region, 
            u.E164 as phone, 
            u.no_of_lines,
            c.id as client_id
          from usage_voip u
          left join clients c on c.client=u.client
          where 
                u.actual_to>=date_add('".$date."',interval -".$mm." month) 
            and u.actual_to<date_add('".$date."',interval -".$mm."+1 month)
          group by u.region, u.E164, c.id  ");

      $res_vpbx = $db->AllRecords("
          select 
            c.region, 
            u.id, 
            c.id as client_id
          from usage_virtpbx u
          left join clients c on c.client=u.client
          where 
                u.actual_to>=date_add('".$date."',interval -".$mm." month) 
            and u.actual_to<date_add('".$date."',interval -".$mm."+1 month) 
          group by c.region, u.id, c.id  ");
      
      foreach($res as $r)
      {
        if (strlen($r['phone']) > 4)
        {
          if (!isset($del_nums[$r['region']])) 
            $del_nums[$r['region']] = 0;

          $del_nums[$r['region']] += 1;
          $del_nums['all'] += 1;
        }else{
          if (!isset($del_nonums[$r['region']])) 
            $del_nonums[$r['region']] = 0;

          $del_nonums[$r['region']] += 1;
          $del_nonums['all'] += 1;
        }

        if (!isset($del_lines[$r['region']])) 
          $del_lines[$r['region']] = 0;

        $del_lines[$r['region']] += $r['no_of_lines'];
        $del_lines['all'] += $r['no_of_lines'];
      }
      foreach($res_vpbx as $r)
      {
          if (!isset($del_vpbx[$r['region']])) 
            $del_vpbx[$r['region']] = 0;

          $del_vpbx[$r['region']] += 1;
          $del_vpbx['all'] += 1;
      }


      $m = date("m") - $mm;
      if ($m < 1) $m = 12;
      $date = $month_list[$m-1].' '.date("Y");

      $reports[] = array(
        'date' => $date,
        'region_sums' => $region_sums,
        'sale_nums'=>$sale_nums,
        'sale_nonums'=>$sale_nonums,
        'sale_lines'=>$sale_lines,
        'sale_clients'=>$sale_clients,
        'sale_channels'=>$sale_channels,
        'del_nums'=>$del_nums,
        'del_nonums'=>$del_nonums,
        'del_lines'=>$del_lines,
        'del_vpbx' => $del_vpbx,
        'sale_vpbx' => $sale_vpbx,
        'vpbx_clients' => $vpbx_clients
      );
    }

    $design->assign("regions", $regions);
    $design->assign("region_clients_count", $region_clients_count);
    $design->assign('reports',$reports);
    $design->assign('curr_phones',$curr_phones);
    $design->assign('curr_vpbx',$curr_vpbx);
    $design->AddMain('stats/report_phone_sales.tpl');
  }
}


class requestPlusOper
{
    function create($client, $d)
    {
        global $db;
        include_once INCLUDE_PATH."1c_integration.php";

        $bm = new \_1c\billMaker($db);

        $zeroDescr = "00000000-0000-0000-0000-000000000000";

        $count = 0;

        //if($count++ > 0) break;

        $metro = "";
        if(preg_match_all("/м\.(.*)$/six",$d['address'], $o ))
            $metro = $o[1][0];

        if($metro == "-")
            $metro = "";


        $ai = array (
                'ФИО' => $d['fio'],
                'Адрес' => $d['address'],
                'НомерЗаявки' => isset($d["req"]) ? $d["req"] : "",
                'ЛицевойСчет' => '',
                'НомерПодключения' => '',
                'Комментарий1' => (isset($d["date_deliv"]) ? "Доставка на: ".$d["date_deliv"] : ""),
                'Комментарий2' => (isset($d["fio_oper"]) ? "Оператор: ".$d["fio_oper"] : ""),
                'ПаспортСерия' => '',
                'ПаспортНомер' => '',
                'ПаспортКемВыдан' => '',
                'ПаспортКогдаВыдан' => '',
                'ПаспортКодПодразделения' => '',
                'ПаспортДатаРождения' => '',
                'ПаспортГород' => '',
                'ПаспортУлица' => '',
                'ПаспортДом' => '',
                'ПаспортКорпус' => '',
                'ПаспортСтроение' => '',
                'ПаспортКвартира' => '',
                'Email' => '',
                'ПроисхождениеЗаказа' => '',
                'КонтактныйТелефон' => $d["phone"],
                'Метро' => $metro,
                'Логистика' => '',
                'ВладелецЛинии' => '',
                );
        $aii = array();
        foreach($ai as $k => $v)
            $aii[\_1c\tr($k)] = \_1c\tr($v);

        $res = array(
                "client_tid" => $client,//WiMaxComstar",
                "order_number" => ($d["bill_no"] ? $d["bill_no"] : false),//"201109/0094",//false,
                "items_list" => array(),
                "order_comment" => $d["comment"],
                "is_rollback" => false,
                "add_info" => $aii,
                "store_id" => "8e5c7b22-8385-11df-9af5-001517456eb1"
                );

        //
        if($client == "nbn")
        {
        	if($d["qty"])
        	{
	            $res["items_list"][] =
	                    array(
	                        "id" => "4e8cc21b-d476-11e0-9255-d485644c7711".":".$zeroDescr,
	                        "quantity" => $d["qty"],
	                        "code_1c" => 0,
	                        "price" => 1); // 13340
        	}


        	$res["items_list"][] =
				array(
				"id" => ($d["deliv_type"] == "moskow" ?
					"a449a3f7-d918-11e0-bdf8-00155d21fe06" :
					"1ccf5f37-03be-11e1-aefc-00155d881200").":".$zeroDescr,
				"quantity" => 1,
				"code_1c" => 0,
				"price" => 1);  // 13363 | 13430

        }else{
            $res["items_list"][] =
                    array(
                        "id" => "f75a5b2f-382f-11e0-9c3c-d485644c7711".":".$zeroDescr,
                        "quantity" => $d["qty"],
                        "code_1c" => 0,
                        "price" => 1); // 13623

            $res["items_list"][] =
            array(
            				"id" => ($d["deliv_type"] == "moskow" ?
            					"81d52242-4d6c-11e1-8572-00155d881200" :
            					"81d52245-4d6c-11e1-8572-00155d881200").":".$zeroDescr,
            				"quantity" => 1,
            				"code_1c" => 0,
            				"price" => 1);  // 13619 | 13621

        }


        try{
            $null = null;
            $ret = $bm->saveOrder($res, $null, false);
        }catch(Exception $e){
            print_r($res);
            var_dump($e);
            exit();
        }

        $c1error = '';
        $cl = new stdClass();
        $cl->order = $ret;
        $cl->isRollback = false;

        $bill_no = $ret->{\_1c\tr('Номер')};

        $sh = new \_1c\SoapHandler();
        $sh->statSaveOrder($cl, $bill_no, $c1error);

        return $bill_no;
    }
}
