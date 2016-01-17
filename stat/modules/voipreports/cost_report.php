<?php
class m_voipreports_cost_report
{

    public function invoke($method, $arguments)
    {
        if (is_callable(array($this, $method))) return call_user_func_array(array($this, $method), $arguments);
    }

    function voipreports_cost_report()
    {
        global $design, $db, $pg_db;

        set_time_limit(0);
        session_write_close();

        $f_instance_id = (int)get_param_protected('f_instance_id', '99');
        $f_trunk_id = get_param_integer('f_trunk_id', '0');
        $f_service_trunk_id = get_param_integer('f_service_trunk_id', '0');
        $date_from = get_param_protected('date_from', date('Y-m-d'));
        $date_to = get_param_protected('date_to', date('Y-m-d'));
        $f_prefix_type = get_param_protected('f_prefix_type', 'op');
        $f_country_id = get_param_protected('f_country_id', '0');
        $f_region_id = get_param_protected('f_region_id', '0');
        $f_dest_group = get_param_protected('f_dest_group', '');
        $f_mob = get_param_protected('f_mob', '0');
        $f_prefix = get_param_protected('f_prefix', '');
        $f_volume = get_param_protected('f_volume', '');

        $report = array();
        $totals = array('trunks' => array());
        $reportTrunks = array();
        if (isset($_GET['make']) || isset($_GET['export'])) {

            $where = " and r.connect_time >= '{$date_from}'";
            $where .= " and r.connect_time <= '{$date_to} 23:59:59'";

            if ($f_trunk_id > 0) {
                $where .= " and r.trunk_id=" . $f_trunk_id;
            }

            if ($f_service_trunk_id > 0) {
                $where .= " and r.trunk_service_id=" . $f_service_trunk_id;
            }

            if ($f_prefix != '')
                $where .= " and r.prefix::varchar like '" . intval($f_prefix) . "%' ";
            if ($f_dest_group != '') {
                if ($f_dest_group == '-1') {
                    $where .= " and r.destination_id < 0 ";
                } else {
                    $where .= " and r.destination_id='{$f_dest_group}' ";
                }
            }
            if ($f_country_id != '0')
                $where .= " and g.country='{$f_country_id}' ";
            if ($f_region_id != '0')
                $where .= " and g.region='{$f_region_id}' ";
            if ($f_mob == 't')
                $where .= " and r.mob=true ";
            if ($f_mob == 'f')
                $where .= " and r.mob=false ";
            if ($f_region_id != '0')
                $where .= " and g.region='{$f_region_id}' ";

            $networkGroups = $pg_db->AllRecords('select id, name from voip.network_type', 'id');

            $preReport = $pg_db->AllRecords("
                        select
                              r.prefix as prefix,
                              r.trunk_id,
                              r.mob as mob,
                              r.destination_id,
                              count(*) as count,
                              sum(r.billed_time) as duration,
                              sum(r.cost) as cost,
                              g.name as destination
                        from calls_raw.calls_raw r
                        left join voip_destinations d on d.ndef=r.prefix
                        left join geo.geo g on g.id=d.geo_id
                        where not orig and not our and server_id = {$f_instance_id} and billed_time>0 {$where} 
                        group by r.prefix, r.mob, r.trunk_id, g.name, r.destination_id
                        order by destination, r.prefix
                                     ");

            foreach ($preReport as $r) {
                if ($r['prefix'] == '') {
                    $r['prefix'] = 'unknown';
                    $r['destination'] = 'unknown';
                    $r['mob'] = 'f';
                } elseif ($r['dest'] < 0 && isset($networkGroups[$r['prefix']])) {
                    $r['destination'] = $networkGroups[$r['prefix']]['name'];
                }

                $k = $r['prefix'];

                if (!isset($report[$k])) {
                    $r['trunks'] = array();
                    $report[$k] = array(
                        'prefix' => $r['prefix'],
                        'destination' => $r['destination'],
                        'mob' => $r['mob'],
                        'trunks' => array(),
                        'count' => 0,
                    );
                }
                $report[$k]['count'] += $r['count'];

                if (!isset($reportTrunks[$r['trunk_id']])) {
                    $reportTrunks[$r['trunk_id']] = \app\models\billing\Trunk::findOne($r['trunk_id']);
                }
                if (!isset($report[$k]['trunks'][$r['trunk_id']])) {
                    $report[$k]['trunks'][$r['trunk_id']] = array(
                        'duration' => 0,
                        'cost' => 0,
                        'count' => 0,
                    );
                }
                $report[$k]['trunks'][$r['trunk_id']]['duration'] += $r['duration'];
                $report[$k]['trunks'][$r['trunk_id']]['cost'] += $r['cost'];
                $report[$k]['trunks'][$r['trunk_id']]['count'] += $r['count'];
            }
        }

        foreach ($report as $k => $r) {
            foreach($r['trunks'] as $k_op => $op) {
                if (!isset($totals['trunks'][$k_op])) {
                    $totals['trunks'][$k_op] = array(
                        'duration' => 0,
                        'cost' => 0,
                        'count' => 0,
                    );
                }
                $totals['trunks'][$k_op]['duration'] += $op['duration'];
                $totals['trunks'][$k_op]['cost'] += $op['cost'];
                $totals['trunks'][$k_op]['count'] += $op['count'];

                $duration = $report[$k]['trunks'][$k_op]['duration'] / 60;
                $report[$k]['trunks'][$k_op]['duration'] = number_format(  $duration, 2, ',', '');
                $report[$k]['trunks'][$k_op]['price'] =
                                            number_format(  $report[$k]['trunks'][$k_op]['cost'] / $duration , 2, ',', '');
            }
        }

        foreach($totals['trunks'] as $k_op => $op) {
            $totals['trunks'][$k_op]['duration'] =
                number_format(  $totals['trunks'][$k_op]['duration'] / 60, 2, ',', '');
            $totals['trunks'][$k_op]['cost'] =
                number_format(  $totals['trunks'][$k_op]['cost'], 2, ',', '');
        }

        if (!isset($_GET['export'])) {
            $trunks = $pg_db->AllRecords("select id, name from auth.trunk group by id, name",'id');
            $serviceTrunks = $db->AllRecords("select id, description as name from usage_trunk where actual_from < now() and actual_to > now() group by id, name",'id');

            $design->assign('report', $report);
            $design->assign('totals', $totals);
            $design->assign('reportTrunks', $reportTrunks);
            $design->assign('f_instance_id', $f_instance_id);
            $design->assign('f_trunk_id', $f_trunk_id);
            $design->assign('f_service_trunk_id', $f_service_trunk_id);
            $design->assign('date_from', $date_from);
            $design->assign('date_to', $date_to);
            $design->assign('f_prefix_type', $f_prefix_type);
            $design->assign('f_prefix', $f_prefix);
            $design->assign('f_country_id', $f_country_id);
            $design->assign('f_region_id', $f_region_id);
            $design->assign('f_mob', $f_mob);
            $design->assign('f_dest_group', $f_dest_group);
            $design->assign('f_volume', $f_volume);
            $design->assign('trunks', $trunks);
            $design->assign('serviceTrunks', $serviceTrunks);
            $design->assign('geo_countries', $pg_db->AllRecords("SELECT id, name FROM geo.country ORDER BY name"));
            $design->assign('geo_regions', $pg_db->AllRecords("SELECT id, name FROM geo.region ORDER BY name"));
            $design->assign('regions', Region::getListAssoc());
            $design->AddMain('voipreports/cost_report_show.html');
        } else {
            header('Content-type: application/csv');
            header('Content-Disposition: attachment; filename="price.csv"');

            ob_start();

            echo ';;';
            foreach ($reportTrunks as $k => $trunk) {
                echo '"' . $trunk->name . ' (' . $k . ')";;;';
            }
            echo "\n";
            echo '"Префикс номера";"Назначение";';
            foreach ($reportTrunks as $k => $trunk) {
                echo '"кол.";"мин";"руб/мин";';
            }
            echo "\n";
            foreach ($report as $r) {
                echo '"' . $r['prefix'] . '";';
                echo '"' . $r['destination'] . ($r['mob']=='t'?' (mob)':'') . '";';
                foreach ($reportTrunks as $k => $trunk) {
                    echo '"' . $r['trunks'][$k]['count'] . '";';
                    echo '"' . $r['trunks'][$k]['duration'] . '";';
                    echo '"' . $r['trunks'][$k]['price'] . '";';
                }
                echo "\n";
            }

            echo iconv('utf-8', 'windows-1251', ob_get_clean());
            exit;
        }
    }

}
