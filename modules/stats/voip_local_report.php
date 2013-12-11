<?php
class m_stats_voip_local_report
{
    public function invoke($method, $arguments)
    {
        if (is_callable(array($this, $method))) return call_user_func_array(array($this, $method), $arguments);
    }

    function stats_voip_local_report() {
        global $design,$db, $pg_db;
        $region = get_param_integer('region', '99');

        $date_from_y = get_param_raw('date_from_y', date('Y'));
        $date_from_m = get_param_raw('date_from_m', date('m'));
        $date_from_d = get_param_raw('date_from_d', date('d'));
        $date_to_y = get_param_raw('date_to_y', date('Y'));
        $date_to_m = get_param_raw('date_to_m', date('m'));
        $date_to_d = get_param_raw('date_to_d', date('d'));
        $operator = get_param_raw('operator', 'all');
        $groupp = get_param_raw('groupp',0);

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
            $where .= ' and direction_out and dest < 0';


            if ($operator>0) {
                $where .= " and operator_id=".$operator;
            }

            if ($groupp == 1) {
                $god = " group by day,operator_id, prefix_op ";
                $sod = " ,day as date";
                $ob = " order by date, operator_id";
            } elseif ($groupp == 2) {
                $god = " group by month, operator_id, prefix_op ";
                $sod = " ,month as date";
                $ob = " order by date, operator_id";
            }else{
                $god = ' group by operator_id, prefix_op ';
                $sod = '';
                $ob = " order by operator_id ";
            }

            $networkGroups = $pg_db->AllRecords('select code, name from voip.operator_network_groups', 'code');

            $query = "
				select
				    count(*) as count,
					sum(len) / 60.0 as len,
					sum(len_op) / 60.0 as len_op,
					sum(len_mcn) / 60.0 as len_mcn,
					cast(sum(amount_op)/100.0 as NUMERIC(10,2)) as amount_op,
					cast(sum(amount)/100.0 as NUMERIC(10,2)) as amount_mcn,
					operator_id as operator_id,
					prefix_op
					".$sod."
				from
					calls.calls_".intval($region)."
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
                    $report[$k] = array('operator_id' => $r['operator_id'],'date' => $r['date']);
                }
                if (!isset($report[$k][$r['prefix_op']])) {
                    $report[$k][$r['prefix_op']] = $r;
                }
                else {
                    $report[$k][$r['prefix_op']]['count'] += $r['count'];
                    $report[$k][$r['prefix_op']]['len_op'] += $r['len_op'];
                    $report[$k][$r['prefix_op']]['len_mcn'] += $r['len_mcn'];
                    $report[$k][$r['prefix_op']]['amount_op'] += $r['amount_op'];
                    $report[$k][$r['prefix_op']]['amount_mcn'] += $r['amount_mcn'];
                }
                if (!isset($columns[$r['prefix_op']])) {
                    $columns[$r['prefix_op']] = $r['prefix_op'];
                    if (isset($networkGroups[$r['prefix_op']])) {
                        $columns[$r['prefix_op']] = $networkGroups[$r['prefix_op']]['name'];
                    }
                    if ($columns[$r['prefix_op']] == '') {
                        $columns[$r['prefix_op']] = 'unknown';
                    }
                }
            }


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
        $design->assign('region',$region);
        $design->assign('regions',$regions);
        $design->AddMain('stats/voip_local_report.html');
    }
}
