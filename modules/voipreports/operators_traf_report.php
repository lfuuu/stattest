<?php

class m_voipreports_operators_traf
{

    public function invoke($method, $arguments)
    {
        if (is_callable(array($this, $method))) return call_user_func_array(array($this, $method), $arguments);
    }

    function voipreports_operators_traf(){
        global $design,$db, $pg_db;
        $region = get_param_integer('region', '99');

        $date_from_y = get_param_raw('date_from_y', date('Y'));
        $date_from_m = get_param_raw('date_from_m', date('m'));
        $date_from_d = get_param_raw('date_from_d', date('d'));
        $date_to_y = get_param_raw('date_to_y', date('Y'));
        $date_to_m = get_param_raw('date_to_m', date('m'));
        $date_to_d = get_param_raw('date_to_d', date('d'));
        $operator = get_param_raw('operator', 'all');
        $destination = get_param_raw('destination', 'all');
        $direction = get_param_raw('direction', 'both');
        $groupp = get_param_raw('groupp',0);

        if(!is_numeric($date_from_y))
            $date_from_y = date('Y');
        if(!is_numeric($date_from_m))
            $date_from_m = date('m');
//		if(!is_numeric($date_from_d))
//			$date_from_d = date('d');
        if(!is_numeric($date_to_y))
            $date_to_y = date('Y');
        if(!is_numeric($date_to_m))
            $date_to_m = date('m');
        if(!is_numeric($date_to_d))
            $date_to_d = date('d');
        if(!in_array($operator,array(0,1,2,3,4,9)))
            $operator = 0;
        if(!in_array($destination,array('all',10,11,101,102,103)))
            $destination = 'all';
        if(!in_array($direction,array('both','in','out')))
            $direction = 'both';

        $regions = $db->AllRecords('select * from regions','id');

        $operators = $pg_db->AllRecords("select id::varchar||'_'||case region when 0 then ".$region." else region end as idregion, * from voip.operator  order by id, region",'idregion');
        foreach($operators as $k=>$v){
            $operators[$k]['fullname'] = $v['name'];
            if (isset($regions[$v['region']]))
                $operators[$k]['fullname'] .= ' - '.$regions[$v['region']]['name'];
        }

        if(isset($_GET['get'])){
            $date_from = $date_from_y.'-'.$date_from_m.'-'.$date_from_d.' 00:00:00';
            $date_to = $date_to_y.'-'.$date_to_m.'-'.$date_to_d.' 23:59:59';

            $wm = " (time between '".$date_from."' and '".$date_to."') ";

            if($operator>0)
                $wo = " and operator_id=".$operator;
            else
                $wo = '';

            if($destination != 'all'){
                if ($destination == 10){
                    $dest = -$db->GetValue('select code from regions where id='.intval($region));
                    $wde = " and dest=".$dest." and mob=false ";
                }elseif ($destination == 11){
                    $dest = -$db->GetValue('select code from regions where id='.intval($region));
                    $wde = " and dest=".$dest." and mob=true ";
                }else{
                    $wde = " and dest=".($destination-100);
                }
            }else
                $wde = '';

            if($direction<>'both')
                $wdi = " and direction_out=".(($direction=='in')?'false':'true');
            else
                $wdi = '';

            if($groupp){
                $god = " group by ";
                if ($groupp==1)		$god .= " day, ";
                else $god .= " month, ";
                $god .= "	operator_id,
							dest2";
                if ($groupp==1)		$sod = " ,day as date";
                else	$sod = " ,month as date";
                $ob = " order by date, operator_id, dest2";
            }else{
                $god = ' group by operator_id, dest2';
                $sod = '';
                $ob = " order by operator_id, dest2";
            }

            $query = "
				select
					sum(len) as length,
					cast(sum(amount_op)/100.0 as NUMERIC(10,2)) as price,
					cast(sum(amount)/100.0 as NUMERIC(10,2)) as price_mcn,
					operator_id as operator_id,
					case direction_out when true then
						case phone_num::varchar like '7800%' when true then
							100
						else
							case dest when 0 then
								case mob when true then
									11
								else
									10
								end
							when -1 then
								9
							else
								100+dest
							end
						end
					else 900 end as dest2
					".$sod."
				from
					calls.calls_".intval($region)."
				where len>0 and
					".$wm.$wo.$wde.$wdi.$god.$ob;

            $pg_db->Query($query);
            $report = array();
            $report_dest = array();
            $report_oper = array();
            while($row=$pg_db->NextRecord(MYSQL_ASSOC)){
                if(!isset($report[$row['operator_id']]))
                    $report[$row['operator_id']] = array();
                $r =& $report[$row['operator_id']];

                if(!isset($report_oper[$row['operator_id']]))
                    $report_oper[$row['operator_id']] = array();

                if($groupp){
                    if(!isset($r[$row['date']]))
                        $r[$row['date']] = array();
                    $r =& $r[$row['date']];
                }else{
                    if(!isset($r[0]))
                        $r[0] = array();
                    $r =& $r[0];
                }

                if(!isset($report_oper[$row['operator_id']][$row['dest2']])){
                    $report_oper[$row['operator_id']][$row['dest2']] = array(
                        'clean'=>0,
                        'human'=>0,
                        'price'=>0,
                        'price_mcn'=>0
                    );
                }
                if(!isset($report_dest[$row['dest2']])){
                    $report_dest[$row['dest2']] = array(
                        'clean'=>0,
                        'human'=>'',
                        'price'=>0,
                        'price_mcn'=>0
                    );
                }
                $report_dest[$row['dest2']]['clean'] += $row['length'];
                $report_dest[$row['dest2']]['price'] += $row['price'];
                $report_dest[$row['dest2']]['price_mcn'] += $row['price_mcn'];

                //$h = (int)($report_dest[$row['dest2']]['clean']/3600);
                //$m = (int)(($report_dest[$row['dest2']]['clean']-($h*3600))/60);
                //$s = $report_dest[$row['dest2']]['clean'] - ($m*60+$h*3600);
                $report_dest[$row['dest2']]['human'] = round($report_dest[$row['dest2']]['clean']/3600,2);//$h.'Þ '.$m.'Í '.$s.'Ó';

                $row['length'] = (int)$row['length'];
                //$h = (int)($row['length']/3600);
                //$m = (int)(($row['length']-($h*3600))/60);
                //$s = $row['length'] - ($m*60+$h*3600);
                $report_oper[$row['operator_id']][$row['dest2']]['clean'] += $row['length'];
                $report_oper[$row['operator_id']][$row['dest2']]['price'] += $row['price'];
                $report_oper[$row['operator_id']][$row['dest2']]['price_mcn'] += $row['price_mcn'];
                if($row['dest2']==900){
                    $r[900] = array(
                        'clean'=>$row['length'],
                        'human'=> round($row['length']/3600,2), //$h.'Þ '.$m.'Í '.$s.'Ó',
                        'price'=>$row['price'],
                        'price_mcn'=>$row['price_mcn']
                    );
                }else{
                    $r[$row['dest2']] = array(
                        'clean'=>$row['length'],
                        'human'=>round($row['length']/3600,2), //$h.'Þ '.$m.'Í '.$s.'Ó',
                        'price'=>$row['price'],
                        'price_mcn'=>$row['price_mcn']
                    );
                    if(!isset($r['sum'])){
                        $r['sum'] = array(
                            'clean'=>0,
                            'human'=>'',
                            'price'=>0,
                            'price_mcn'=>0
                        );
                    }
                    if(!isset($report_dest['sum'])){
                        $report_dest['sum'] = array(
                            'clean'=>0,
                            'human'=>'',
                            'price'=>0,
                            'price_mcn'=>0
                        );
                    }
                    $r['sum']['clean'] += $row['length'];
                    $r['sum']['price'] += $row['price'];
                    $r['sum']['price_mcn'] += $row['price_mcn'];
                    //$h = (int)($r['sum']['clean']/3600);
                    //$m = (int)(($r['sum']['clean']-($h*3600))/60);
                    //$s = $r['sum']['clean'] - ($m*60+$h*3600);
                    $r['sum']['human'] = round($r['sum']['clean']/3600,2);//$h.'Þ '.$m.'Í '.$s.'Ó';

                    $report_dest['sum']['clean'] += $row['length'];
                    $report_dest['sum']['price'] += $row['price'];
                    $report_dest['sum']['price_mcn'] += $row['price_mcn'];
                    //$h = (int)($report_dest['sum']['clean']/3600);
                    //$m = (int)(($report_dest['sum']['clean']-($h*3600))/60);
                    //$s = $report_dest['sum']['clean'] - ($m*60+$h*3600);
                    $report_dest['sum']['human'] = round($report_dest['sum']['clean']/3600,2); //$h.'Þ '.$m.'Í '.$s.'Ó';
                }
                ksort($r);
            }
            ksort($report_dest);
            foreach($report_oper as $op=>&$repo){
                if(!isset($report_oper[$op]['sum']))
                    $report_oper[$op]['sum'] = array('clean'=>0,'human'=>'','price'=>0,'price_mcn'=>0);
                foreach($repo as $dk=>&$dd){
                    if($dk=='sum')
                        continue;
                    if($dk<>900){
                        $report_oper[$op]['sum']['clean'] += $dd['clean'];
                        $report_oper[$op]['sum']['price'] += $dd['price'];
                        $report_oper[$op]['sum']['price_mcn'] += $dd['price_mcn'];
                    }
                    //$h = (int)($report_oper[$op][$dk]['clean']/3600);
                    //$m = (int)(($report_oper[$op][$dk]['clean']-($h*3600))/60);
                    //$s = $report_oper[$op][$dk]['clean'] - ($m*60+$h*3600);
                    $report_oper[$op][$dk]['human'] = round($report_oper[$op][$dk]['clean']/3600,2);//$h.'Þ '.$m.'Í '.$s.'Ó';
                }
                //$h = (int)($report_oper[$op]['sum']['clean']/3600);
                //$m = (int)(($report_oper[$op]['sum']['clean']-($h*3600))/60);
                //$s = $report_oper[$op]['sum']['clean'] - ($m*60+$h*3600);
                $report_oper[$op]['sum']['human'] = round($report_oper[$op]['sum']['clean']/3600,2);//$h.'Þ '.$m.'Í '.$s.'Ó';
            }
            $design->assign('report_oper',$report_oper);
            $design->assign('report_dest',$report_dest);


            $design->assign('report',$report);
        }

        $design->assign('date_from_yy',$date_from_y);
        $design->assign('date_from_mm',$date_from_m);
        $design->assign('date_from_dd',$date_from_d);
        $design->assign('date_to_yy',$date_to_y);
        $design->assign('date_to_mm',$date_to_m);
        $design->assign('date_to_dd',$date_to_d);
        $design->assign('operator',$operator);
        $design->assign('operators', $operators);
        $design->assign('destination',$destination);
        $design->assign('direction',$direction);
        $design->assign('groupp',$groupp);
        $design->assign('region',$region);
        $design->assign('regions',$regions);
        $design->AddMain('voipreports/operators_traf.html');
    }


}