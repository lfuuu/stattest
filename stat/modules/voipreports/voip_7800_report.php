<?php
class m_voipreports_voip_7800_report
{
    public function invoke($method, $arguments)
    {
        if (is_callable(array($this, $method))) return call_user_func_array(array($this, $method), $arguments);
    }

    function voipreports_voip_7800_report() {
        global $design,$db, $pg_db;

        set_time_limit(0);
        session_write_close();

        $region = get_param_integer('region', '0');

        $date_from_y = get_param_raw('date_from_y', date('Y'));
        $date_from_m = get_param_raw('date_from_m', date('m'));
        $date_from_d = get_param_raw('date_from_d', date('d'));
        $date_to_y = get_param_raw('date_to_y', date('Y'));
        $date_to_m = get_param_raw('date_to_m', date('m'));
        $date_to_d = get_param_raw('date_to_d', date('d'));
        $trunk = get_param_integer('trunk', '0');
        $serviceTrunk = get_param_integer('serviceTrunk', '0');
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

        $trunks = $pg_db->AllRecords("select id, name from auth.trunk group by id, name",'id');
        $serviceTrunks = $db->AllRecords("select id, description as name from usage_trunk where actual_from < now() and actual_to > now() group by id, name",'id');

        if(isset($_GET['get'])){
            $date_from = $date_from_y.'-'.$date_from_m.'-'.$date_from_d.' 00:00:00';
            $date_to = $date_to_y.'-'.$date_to_m.'-'.$date_to_d.' 23:59:59';

            $where  = " and (connect_time between '".$date_from."' and '".$date_to."') ";
            $where .= ' and orig = true ';
            $where .= " and dst_number::varchar like '7800%'";

            if ($trunk > 0) {
                $where .= " and trunk_id=" . $trunk;
            }

            if ($serviceTrunk > 0) {
                $where .= " and trunk_service_id=" . $serviceTrunk;
            }

            if ($groupp == 1) {
                $god = " group by date_trunc('day',connect_time), trunk_id, trunk_service_id ";
                $sod = " ,date_trunc('day',connect_time) as date";
                $ob = " order by date, trunk_id, trunk_service_id";
            } elseif ($groupp == 2) {
                $god = " group by date_trunc('month',connect_time), trunk_id, trunk_service_id ";
                $sod = " ,date_trunc('month',connect_time) as date";
                $ob = " order by date, trunk_id, trunk_service_id";
            } elseif ($groupp == 3) {
                $god = " group by dst_number, trunk_id, trunk_service_id ";
                $sod = " ,dst_number as date";
                $ob = " order by dst_number, trunk_id, trunk_service_id ";
            }else{
                $god = ' group by trunk_id, trunk_service_id ';
                $sod = '';
                $ob = " order by trunk_id, trunk_service_id ";
            }

            $query = "
                select
                    count(*) as count,
                    sum(billed_time) / 60.0 as len_op,
                    -sum(cost) as amount_op,
                    trunk_id,
                    trunk_service_id
                    ".$sod."
                from calls_raw.calls_raw
                where
                    " . ($region ? "server_id = {$region} and" : '') . "
                    billed_time>0
                    ".$where.$god.$ob;

            $report = $pg_db->AllRecords($query);

            $design->assign('report',$report);
        }

        $design->assign('date_from_yy',$date_from_y);
        $design->assign('date_from_mm',$date_from_m);
        $design->assign('date_from_dd',$date_from_d);
        $design->assign('date_to_yy',$date_to_y);
        $design->assign('date_to_mm',$date_to_m);
        $design->assign('date_to_dd',$date_to_d);
        $design->assign('trunk',$trunk);
        $design->assign('trunks', $trunks);
        $design->assign('serviceTrunk',$serviceTrunk);
        $design->assign('serviceTrunks', $serviceTrunks);
        $design->assign('groupp',$groupp);
        $design->assign('region',$region);
        $design->assign('regions',$regions);
        $design->AddMain('voipreports/voip_7800_report.html');
    }
}
