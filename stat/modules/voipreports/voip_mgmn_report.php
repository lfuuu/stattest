<?php
class m_voipreports_voip_mgmn_report
{
    public function invoke($method, $arguments)
    {
        if (is_callable(array($this, $method))) return call_user_func_array(array($this, $method), $arguments);
    }

    function voipreports_voip_mgmn_report() {
        global $design,$db, $pg_db;
        $region = get_param_integer('region', '0');

        $date_from_y = get_param_raw('date_from_y', date('Y'));
        $date_from_m = get_param_raw('date_from_m', date('m'));
        $date_from_d = get_param_raw('date_from_d', date('d'));
        $date_to_y = get_param_raw('date_to_y', date('Y'));
        $date_to_m = get_param_raw('date_to_m', date('m'));
        $date_to_d = get_param_raw('date_to_d', date('d'));
        $operator = get_param_raw('operator', 'all');
        $groupp = get_param_raw('groupp',0);
        $details = get_param_integer('details', 0);

        if(!is_numeric($date_from_y))
            $date_from_y = date('Y');
        if(!is_numeric($date_from_m))
            $date_from_m = date('m');
        if(!is_numeric($date_from_d))
            $date_from_d = date('d');
        if(!is_numeric($date_to_y))
            $date_to_y = date('Y');
        if(!is_numeric($date_to_m))
            $date_to_m = date('m');
        if(!is_numeric($date_to_d))
            $date_to_d = date('d');

        $regions = $db->AllRecords('select * from regions','id');

        $operators = $pg_db->AllRecords("select id, max(short_name) as name from voip.operator group by id",'id');

        if(isset($_GET['get'])){
            $date_from = $date_from_y.'-'.$date_from_m.'-'.$date_from_d.' 00:00:00';
            $date_to = $date_to_y.'-'.$date_to_m.'-'.$date_to_d.' 23:59:59.999999';

            $where  = " and (connect_time between '".$date_from."' and '".$date_to."') ";
            $where .= ' and not orig and destination_id >= 0 ';

            if ($operator>0) {
                $where .= " and operator_id=".$operator;
            }

            if ($groupp == 1) {
                $god = " group by date_trunc('day',connect_time),operator_id, dest2 ";
                $sod = " ,date_trunc('day',connect_time) as date";
                $ob = " order by date, operator_id";
            } elseif ($groupp == 2) {
                $god = " group by date_trunc('month',connect_time), operator_id, dest2 ";
                $sod = " ,date_trunc('month',connect_time) as date";
                $ob = " order by date, operator_id";
            }else{
                $god = ' group by operator_id, dest2 ';
                $sod = '';
                $ob = " order by operator_id ";
            }

            $query = "
                select
                    count(*) as count,
                    sum(billed_time) / 60.0 as len_op,
                    sum(cost) as amount_op,
                    operator_id as operator_id,
                    case dst_number::varchar like '7800%' when true then
                        7800
                    else
                        case destination_id when 0 then
                            case mob when false then
                                1001
                            else
                                1002
                            end
                        else
                            100+destination_id
                        end
                    end as dest2
                    ".$sod."
                from calls_raw.calls_raw
                where
                    " . ($region ? "server_id = {$region} and" : '') . "
                    billed_time>0
                    ".$where.$god.$ob;

            $report = array();
            foreach($pg_db->AllRecords($query) as $r) {
                $k = $r['operator_id'];
                if (isset($r['date'])) {
                    $k .= '_' . $r['date'];
                }
                if (!isset($report[$k])) {
                    $report[$k] = array('operator_id' => $r['operator_id']);
                    if (isset($r['date'])) {
                        $report[$k]['date'] = $r['date'];
                    }
                }
                if (!isset($report[$k][$r['dest2']])) {
                    $report[$k][$r['dest2']] = $r;
                }
                else {
                    $report[$k][$r['dest2']]['count'] += $r['count'];
                    $report[$k][$r['dest2']]['len_op'] += $r['len_op'];
                    $report[$k][$r['dest2']]['amount_op'] += $r['amount_op'];
                }

                if (!isset($report[$k]['8000'])) {
                    $report[$k]['8000'] = $r;
                }
                else {
                    $report[$k]['8000']['count'] += $r['count'];
                    $report[$k]['8000']['len_op'] += $r['len_op'];
                    $report[$k]['8000']['amount_op'] += $r['amount_op'];
                }
            }

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
        $design->assign('groupp',$groupp);
        $design->assign('details',$details);
        $design->assign('region',$region);
        $design->assign('regions',$regions);
        $design->AddMain('voipreports/voip_mgmn_report.html');
    }
}
