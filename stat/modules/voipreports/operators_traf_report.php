<?php

class m_voipreports_operators_traf
{

    public function invoke($method, $arguments)
    {
        if (is_callable(array($this, $method))) return call_user_func_array(array($this, $method), $arguments);
    }

    function voipreports_operators_traf(){
        global $design,$db, $pg_db;
        $region = get_param_integer('region', '0');

        $date_from_y = get_param_raw('date_from_y', date('Y'));
        $date_from_m = get_param_raw('date_from_m', date('m'));
        $date_from_d = get_param_raw('date_from_d', date('d'));
        $date_to_y = get_param_raw('date_to_y', date('Y'));
        $date_to_m = get_param_raw('date_to_m', date('m'));
        $date_to_d = get_param_raw('date_to_d', date('d'));
        $trunk = get_param_integer('trunk', '0');
        $serviceTrunk = get_param_integer('serviceTrunk', '0');
        $destination = get_param_raw('destination', 'all');
        $direction = get_param_raw('direction', 'both');
        $groupp = get_param_raw('groupp',0);

        if(!is_numeric($date_from_y))
            $date_from_y = date('Y');
        if(!is_numeric($date_from_m))
            $date_from_m = date('m');
//        if(!is_numeric($date_from_d))
//            $date_from_d = date('d');
        if(!is_numeric($date_to_y))
            $date_to_y = date('Y');
        if(!is_numeric($date_to_m))
            $date_to_m = date('m');
        if(!is_numeric($date_to_d))
            $date_to_d = date('d');
        if(!in_array($destination,array('all',10,11,101,102,103)))
            $destination = 'all';
        if(!in_array($direction,array('both','in','out')))
            $direction = 'both';

        $regions = $db->AllRecords('select * from regions','id');
        $trunks = $pg_db->AllRecords("select id, name from auth.trunk group by id, name",'id');
        $serviceTrunks = $db->AllRecords("select id, description as name from usage_trunk where actual_from < now() and actual_to > now() group by id, name",'id');

        if(isset($_GET['get'])){
            $date_from = $date_from_y.'-'.$date_from_m.'-'.$date_from_d.' 00:00:00';
            $date_to = $date_to_y.'-'.$date_to_m.'-'.$date_to_d.' 23:59:59.999999';

            $wm = " (connect_time between '".$date_from."' and '".$date_to."') ";

            $wo = '';

            if ($trunk > 0) {
                $wo .= " and trunk_id=" . $trunk;
            }

            if ($serviceTrunk > 0) {
                $wo .= " and trunk_service_id=" . $serviceTrunk;
            }

            if($destination != 'all'){
                if ($destination == 10){
                    $wde = " and destination_id<0 and mob=false ";
                }elseif ($destination == 11){
                    $wde = " and destination_id<0 and mob=true ";
                }else{
                    $wde = " and destination_id=".($destination-100);
                }
            }else
                $wde = '';

            if($direction<>'both')
                $wdi = " and orig=".(($direction=='in')?'true':'false');
            else
                $wdi = '';

            if($groupp){
                $god = " group by ";
                if ($groupp==1)        $god .= " date_trunc('day',connect_time), ";
                else $god .= " date_trunc('month',connect_time), ";
                $god .= "    trunk_id, trunk_service_id,
                            dest2";
                if ($groupp==1)        $sod = " ,date_trunc('day',connect_time) as date";
                else    $sod = " ,date_trunc('month',connect_time) as date";
                $ob = " order by date, trunk_id, trunk_service_id, dest2";
            }else{
                $god = ' group by trunk_id, trunk_service_id, dest2';
                $sod = '';
                $ob = " order by trunk_id, trunk_service_id, dest2";
            }

            $query = "
                select
                    sum(billed_time) as length,
                    sum(cost) as price,
                    trunk_id,
                    trunk_service_id,
                    case orig when false then
                        case dst_number::varchar like '7800%' when true then
                            100
                        else
                            case destination_id when 0 then
                                case mob when true then
                                    11
                                else
                                    10
                                end
                            when -1 then
                                9
                            else
                                100+destination_id
                            end
                        end
                    else 900 end as dest2
                    ".$sod."
                from
                    calls_raw.calls_raw
                where
                    " . ($region ? "server_id = {$region} and" : '') . "
                    billed_time > 0 and
                    ".$wm.$wo.$wde.$wdi.$god.$ob;

            $report = array();
            $report_dest = array();
            $report_oper = array();
            $operators = [];

            $pg_db->Query($query);
            while($row=$pg_db->NextRecord(MYSQL_ASSOC)){
                $tTrunk = isset($trunks[$row['trunk_id']]) ? $trunks[$row['trunk_id']] : ['id' => '', 'name' => ''];
                $tServiceTrunk = isset($serviceTrunks[$row['trunk_service_id']]) ? $serviceTrunks[$row['trunk_service_id']] : ['id' => '', 'name' => ''];

                $key = $row['trunk_id'] . '_' . $row['trunk_service_id'];
                $operators[$key] = $tTrunk['name'] . ' (' . $tTrunk['id'] . ') / ' . $tServiceTrunk['name'] . ' (' . $tServiceTrunk['id'] . ')';

                if(!isset($report[$key]))
                    $report[$key] = array();
                $r =& $report[$key];

                if(!isset($report_oper[$key]))
                    $report_oper[$key] = array();

                if($groupp){
                    if(!isset($r[$row['date']]))
                        $r[$row['date']] = array();
                    $r =& $r[$row['date']];
                }else{
                    if(!isset($r[0]))
                        $r[0] = array();
                    $r =& $r[0];
                }

                if(!isset($report_oper[$key][$row['dest2']])){
                    $report_oper[$key][$row['dest2']] = array(
                        'clean'=>0,
                        'human'=>0,
                        'price'=>0,
                    );
                }
                if(!isset($report_dest[$row['dest2']])){
                    $report_dest[$row['dest2']] = array(
                        'clean'=>0,
                        'human'=>'',
                        'price'=>0,
                    );
                }
                $report_dest[$row['dest2']]['clean'] += $row['length'];
                $report_dest[$row['dest2']]['price'] += $row['price'];

                $report_dest[$row['dest2']]['human'] = round($report_dest[$row['dest2']]['clean']/3600,2);//$h.'ч '.$m.'м '.$s.'с';

                $row['length'] = (int)$row['length'];

                $report_oper[$key][$row['dest2']]['clean'] += $row['length'];
                $report_oper[$key][$row['dest2']]['price'] += $row['price'];
                if($row['dest2']==900){
                    $r[900] = array(
                        'clean'=>$row['length'],
                        'human'=> round($row['length']/3600,2), //$h.'ч '.$m.'м '.$s.'с',
                        'price'=>$row['price'],
                    );
                }else{
                    $r[$row['dest2']] = array(
                        'clean'=>$row['length'],
                        'human'=>round($row['length']/3600,2), //$h.'ч '.$m.'м '.$s.'с',
                        'price'=>$row['price'],
                    );
                    if(!isset($r['sum'])){
                        $r['sum'] = array(
                            'clean'=>0,
                            'human'=>'',
                            'price'=>0,
                        );
                    }
                    if(!isset($report_dest['sum'])){
                        $report_dest['sum'] = array(
                            'clean'=>0,
                            'human'=>'',
                            'price'=>0,
                        );
                    }
                    $r['sum']['clean'] += $row['length'];
                    $r['sum']['price'] += $row['price'];
                    $r['sum']['human'] = round($r['sum']['clean']/3600,2);//$h.'ч '.$m.'м '.$s.'с';

                    $report_dest['sum']['clean'] += $row['length'];
                    $report_dest['sum']['price'] += $row['price'];
                    $report_dest['sum']['human'] = round($report_dest['sum']['clean']/3600,2); //$h.'ч '.$m.'м '.$s.'с';
                }
                ksort($r);
            }
            ksort($report_dest);
            foreach($report_oper as $op=>&$repo){
                if(!isset($report_oper[$op]['sum']))
                    $report_oper[$op]['sum'] = array('clean'=>0,'human'=>'','price'=>0);
                foreach($repo as $dk=>&$dd){
                    if($dk=='sum')
                        continue;
                    if($dk<>900){
                        $report_oper[$op]['sum']['clean'] += $dd['clean'];
                        $report_oper[$op]['sum']['price'] += $dd['price'];
                    }
                    $report_oper[$op][$dk]['human'] = round($report_oper[$op][$dk]['clean']/3600,2);//$h.'ч '.$m.'м '.$s.'с';
                }
                $report_oper[$op]['sum']['human'] = round($report_oper[$op]['sum']['clean']/3600,2);//$h.'ч '.$m.'м '.$s.'с';
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
        $design->assign('operators',$operators);
        $design->assign('trunk',$trunk);
        $design->assign('trunks', $trunks);
        $design->assign('serviceTrunk',$serviceTrunk);
        $design->assign('serviceTrunks', $serviceTrunks);
        $design->assign('destination',$destination);
        $design->assign('direction',$direction);
        $design->assign('groupp',$groupp);
        $design->assign('region',$region);
        $design->assign('regions',$regions);
        $design->AddMain('voipreports/operators_traf.html');
    }


}