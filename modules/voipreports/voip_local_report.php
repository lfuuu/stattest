<?php
class m_voipreports_voip_local_report
{
    public function invoke($method, $arguments)
    {
        if (is_callable(array($this, $method))) return call_user_func_array(array($this, $method), $arguments);
    }

    function voipreports_voip_local_report() {
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
            $date_to = $date_to_y.'-'.$date_to_m.'-'.$date_to_d.' 23:59:59';

            $where  = " and (time between '".$date_from."' and '".$date_to."') ";
            $where .= ' and ( dest < 0 or direction_out = false) ';

            $link = "index.php?module=voipreports&action=calls_report&make=";
            $link .= "&f_instance_id={$region}&f_operator_id={$operator}";
            $link .= "&date_from={$date_from_y}-{$date_from_m}-{$date_from_d}";
            $link .= "&date_to={$date_to_y}-{$date_to_m}-{$date_to_d}";


            if ($operator>0) {
                $where .= " and operator_id=".$operator;
            }

            if ($groupp == 1) {
                $god = " group by direction_out, day,operator_id, prefix_op, phone_num::varchar like '7_____' or phone_num::varchar like '7______' ";
                $sod = " ,day as date";
                $ob = " order by date, operator_id ";
            } elseif ($groupp == 2) {
                $god = " group by direction_out, month, operator_id, prefix_op, phone_num::varchar like '7_____' or phone_num::varchar like '7______' ";
                $sod = " ,month as date";
                $ob = " order by date, operator_id";
            }else{
                $god = " group by direction_out, operator_id, prefix_op, phone_num::varchar like '7_____' or phone_num::varchar like '7______' ";
                $sod = '';
                $ob = " order by operator_id ";
            }

            $networkGroups = $pg_db->AllRecords('select id, name from voip.network_type', 'id');

            $query = "
                select
                    phone_num::varchar like '7_____' or phone_num::varchar like '7______' as is_special,
                    count(*) as count,
                    sum(len) / 60.0 as len,
                    sum(len_op) / 60.0 as len_op,
                    sum(len_mcn) / 60.0 as len_mcn,
                    cast(sum(amount_op)/100.0 as NUMERIC(10,2)) as amount_op,
                    cast(sum(amount)/100.0 as NUMERIC(10,2)) as amount_mcn,
                    direction_out,
                    operator_id as operator_id,
                    prefix_op
                    ".$sod."
                from
                    " . ($region ? "calls.calls_{$region}" : "calls.calls") . "
                where len>0
                    ".$where.$god.$ob;

            $columns = array();

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

                if ($r['direction_out'] == 'f') {
                    $r['prefix_op'] = '9000';
                } else {
                    if ($r['is_special'] == 't') {
                        $r['prefix_op'] = 'special';
                    }
                }

                if (!isset($report[$k][$r['prefix_op']])) {
                    $report[$k][$r['prefix_op']] = $r;
                    if ($r['prefix_op'] == '9000') {
                        $report[$k][$r['prefix_op']]['link'] = $link . "&f_direction_out=f&f_operator_id={$r['operator_id']}";
                    } elseif ($r['prefix_op'] == '') {
                        $report[$k][$r['prefix_op']]['link'] = $link . "&f_direction_out=t&f_operator_id={$r['operator_id']}&f_without_prefix_op=1";
                    } else {
                        $report[$k][$r['prefix_op']]['link'] = $link . "&f_direction_out=t&f_operator_id={$r['operator_id']}&f_prefix_op={$r['prefix_op']}";
                    }
                }
                else {
                    $report[$k][$r['prefix_op']]['count'] += $r['count'];
                    $report[$k][$r['prefix_op']]['len'] += $r['len'];
                    $report[$k][$r['prefix_op']]['len_op'] += $r['len_op'];
                    $report[$k][$r['prefix_op']]['len_mcn'] += $r['len_mcn'];
                    $report[$k][$r['prefix_op']]['amount_op'] += $r['amount_op'];
                    $report[$k][$r['prefix_op']]['amount_mcn'] += $r['amount_mcn'];
                }

                if ($r['direction_out'] == 't') {
                    if (!isset($report[$k]['8000'])) {
                        $report[$k]['8000'] = $r;
                        $report[$k]['8000']['link'] = $link . "&f_direction_out=t&f_operator_id={$r['operator_id']}";
                    }
                    else {
                        $report[$k]['8000']['count'] += $r['count'];
                        $report[$k]['8000']['len'] += $r['len'];
                        $report[$k]['8000']['len_op'] += $r['len_op'];
                        $report[$k]['8000']['len_mcn'] += $r['len_mcn'];
                        $report[$k]['8000']['amount_op'] += $r['amount_op'];
                        $report[$k]['8000']['amount_mcn'] += $r['amount_mcn'];
                    }
                }

                if (!isset($columns[$r['prefix_op']])) {
                    $columns[$r['prefix_op']] = $r['prefix_op'];
                    if (isset($networkGroups[$r['prefix_op']])) {
                        $columns[$r['prefix_op']] = $networkGroups[$r['prefix_op']]['name'] . ' (' . $r['prefix_op'] . ')';
                    }
                    if ($columns[$r['prefix_op']] == '') {
                        $columns[$r['prefix_op']] = 'unknown';
                    } elseif($columns[$r['prefix_op']] == '8000') {
                        $columns[$r['prefix_op']] = 'Итого';
                    } elseif($columns[$r['prefix_op']] == 'special') {
                        $columns[$r['prefix_op']] = 'Спец. службы';
                    } elseif($columns[$r['prefix_op']] == '9000') {
                        $columns[$r['prefix_op']] = 'Входящие';
                    }
                }
            }

            $columns['8000'] = '<b>Итого</b>';

            $design->assign('report',$report);
            ksort($columns);
            $design->assign('columns',$columns);
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
        $design->AddMain('voipreports/voip_local_report.html');
    }
}
