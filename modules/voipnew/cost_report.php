<?php
class m_voipnew_cost_report
{

    public function invoke($method, $arguments)
    {
        if (is_callable(array($this, $method))) return call_user_func_array(array($this, $method), $arguments);
    }

    function voipnew_cost_report()
    {
        global $design, $pg_db;
        set_time_limit(0);

        $f_instance_id = (int)get_param_protected('f_instance_id', '99');
        $f_operator_id = get_param_protected('f_operator_id', '0');
        $date_from = get_param_protected('date_from', date('Y-m-d'));
        $date_to = get_param_protected('date_to', date('Y-m-d'));
        $f_prefix_type = get_param_protected('f_prefix_type', 'op');
        $f_country_id = get_param_protected('f_country_id', '0');
        $f_region_id = get_param_protected('f_region_id', '0');
        $f_dest_group = get_param_protected('f_dest_group', '');
        $f_direction_out = get_param_protected('f_direction_out', 't');
        $f_mob = get_param_protected('f_mob', '0');
        $f_prefix = get_param_protected('f_prefix', '');
        $f_volume = get_param_protected('f_volume', '');

        if (!in_array($f_prefix_type, array('op', 'mcn'))) $f_prefix_type = 'op';
        $prefixField = 'prefix_' . $f_prefix_type;

        $report = array();
        $totals = array('count' => 0, 'len_mcn' => 0, 'amount_mcn' => 0, 'operators' => array());
        $reportOperators = array();
        if (isset($_GET['make']) || isset($_GET['export'])) {

            $where = " and r.time >= '{$date_from}'";
            $where .= " and r.time <= '{$date_to} 23:59:59'";
            $where .= $f_direction_out == 'f' ?  " and r.direction_out=false " : " and r.direction_out=true ";

            if ($f_operator_id != '0')
                $where .= " and r.operator_id='{$f_operator_id}' ";
            if ($f_prefix != '')
                $where .= " and r.{$prefixField}::varchar like '" . intval($f_prefix) . "%' ";
            if ($f_dest_group != '') {
                if ($f_dest_group == '-1') {
                    $where .= " and r.dest < 0 ";
                } else {
                    $where .= " and r.dest='{$f_dest_group}' ";
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

            $networkGroups = $pg_db->AllRecords('select code, name from voip.operator_network_groups', 'code');

            $preReport = $pg_db->AllRecords("
                        select
                              r.{$prefixField} as prefix,
                              r.operator_id,
                              r.mob as mob,
                              r.dest,
                              count(*) as count,
                              sum(r.len_mcn) as len_mcn,
                              sum(r.amount) as amount_mcn,
                              sum(r.len_op) as len_op,
                              sum(r.amount_op) amount_op,
                              g.name as destination
                        from calls.calls_{$f_instance_id} r
                        left join voip_destinations d on d.ndef=r.{$prefixField}
                        left join geo.geo g on g.id=d.geo_id
                        where len>0 {$where}
                        group by r.{$prefixField}, r.mob, r.operator_id, g.name, r.dest
                        order by destination, r.{$prefixField}
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
                    $r['operators'] = array();
                    $report[$k] = array(
                        'prefix' => $r['prefix'],
                        'destination' => $r['destination'],
                        'mob' => $r['mob'],
                        'operators' => array(),
                        'len_mcn' => 0,
                        'amount_mcn' => 0,
                        'count' => 0,
                    );
                }
                $report[$k]['len_mcn'] += $r['len_mcn'];
                $report[$k]['amount_mcn'] += $r['amount_mcn'];
                $report[$k]['count'] += $r['count'];

                if (!isset($reportOperators[$r['operator_id']])) {
                    $reportOperators[$r['operator_id']] = VoipOperator::getByIdAndInstanceId($r['operator_id'], $f_instance_id);
                }
                if (!isset($report[$k]['operators'][$r['operator_id']])) {
                    $report[$k]['operators'][$r['operator_id']] = array(
                        'len_op' => 0,
                        'amount_op' => 0,
                        'count' => 0,
                    );
                }
                $report[$k]['operators'][$r['operator_id']]['len_op'] += $r['len_op'];
                $report[$k]['operators'][$r['operator_id']]['amount_op'] += $r['amount_op'];
                $report[$k]['operators'][$r['operator_id']]['count'] += $r['count'];
            }
        }

        foreach ($report as $k => $r) {

            $totals['len_mcn'] += $r['len_mcn'];
            $totals['amount_mcn'] += $r['amount_mcn'];
            $totals['count'] += $r['count'];

            $report[$k]['len_mcn'] =        number_format(  $report[$k]['len_mcn'] / 60, 2, ',', '');
            $report[$k]['amount_mcn'] =     number_format(  $report[$k]['amount_mcn'] / 100, 2, ',', '');
            foreach($r['operators'] as $k_op => $op) {

                if (!isset($totals['operators'][$k_op])) {
                    $totals['operators'][$k_op] = array(
                        'len_op' => 0,
                        'amount_op' => 0,
                        'count' => 0,
                    );
                }
                $totals['operators'][$k_op]['len_op'] += $op['len_op'];
                $totals['operators'][$k_op]['amount_op'] += $op['amount_op'];
                $totals['operators'][$k_op]['count'] += $op['count'];

                $report[$k]['operators'][$k_op]['len_op'] =
                                            number_format(  $report[$k]['operators'][$k_op]['len_op'] / 60, 2, ',', '');
                $report[$k]['operators'][$k_op]['amount_op'] =
                                            number_format(  $report[$k]['operators'][$k_op]['amount_op'] / 100, 2, ',', '');
            }
        }

        $totals['len_mcn'] =        number_format(  $totals['len_mcn'] / 60, 2, ',', '');
        $totals['amount_mcn'] =     number_format(  $totals['amount_mcn'] / 100, 2, ',', '');
        foreach($totals['operators'] as $k_op => $op) {
            $totals['operators'][$k_op]['len_op'] =
                number_format(  $totals['operators'][$k_op]['len_op'] / 60, 2, ',', '');
            $totals['operators'][$k_op]['amount_op'] =
                number_format(  $totals['operators'][$k_op]['amount_op'] / 100, 2, ',', '');
        }

        $operators = array();
        foreach (VoipOperator::find('all', array('order' => 'region desc, short_name')) as $op)
        {
            if (!isset($operators[$op->id])) {
                $operators[$op->id] = $op->short_name;
            }
        }
        $countries = $pg_db->AllRecords("SELECT id, name FROM geo.country ORDER BY name");
        $pricelists = $pg_db->AllRecords("select p.id, p.name, o.short_name as operator from voip.pricelist p
                                          left join voip.operator o on p.operator_id=o.id and (o.region=p.region or o.region=0) ", 'id');

        if (!isset($_GET['export'])) {
            $design->assign('report', $report);
            $design->assign('totals', $totals);
            $design->assign('reportOperators', $reportOperators);
            $design->assign('f_instance_id', $f_instance_id);
            $design->assign('f_operator_id', $f_operator_id);
            $design->assign('date_from', $date_from);
            $design->assign('date_to', $date_to);
            $design->assign('f_prefix_type', $f_prefix_type);
            $design->assign('f_prefix', $f_prefix);
            $design->assign('f_country_id', $f_country_id);
            $design->assign('f_region_id', $f_region_id);
            $design->assign('f_direction_out', $f_direction_out);
            $design->assign('f_mob', $f_mob);
            $design->assign('f_dest_group', $f_dest_group);
            $design->assign('f_volume', $f_volume);
            $design->assign('operators', $operators);
            $design->assign('geo_countries', $countries);
            $design->assign('geo_regions', $pg_db->AllRecords("SELECT id, name FROM geo.region ORDER BY name"));
            $design->assign('regions', Region::getListAssoc());
            $design->assign('pricelists', $pricelists);
            $design->AddMain('voipnew/cost_report_show.html');
        } else {
            header('Content-type: application/csv');
            header('Content-Disposition: attachment; filename="cost.csv"');

            ob_start();

            echo ';;МСN Telecom;;;';
            foreach ($reportOperators as $k => $op) {
                echo '"' . $op->short_name . ' (' . $k . ')";;;';
            }
            echo "\n";
            echo '"Префикс номера";"Назначение";"Кол.";"Мин.";"Руб.";';
            foreach ($reportOperators as $k => $op) {
                echo '"Кол.";"Мин.";"Руб.";';
            }
            echo "\n";
            foreach ($report as $r) {
                echo '"' . $r['prefix'] . '";';
                echo '"' . $r['destination'] . ($r['mob']=='t'?' (mob)':'') . '";';
                echo '"' . $r['count'] . '";';
                echo '"' . $r['len_mcn'] . '";';
                echo '"' . $r['amount_mcn'] . '";';
                foreach ($reportOperators as $k => $op) {
                    echo '"' . $r['operators'][$k]['count'] . '";';
                    echo '"' . $r['operators'][$k]['len_op'] . '";';
                    echo '"' . $r['operators'][$k]['amount_op'] . '";';
                }
                echo "\n";
            }

            echo iconv('koi8-r', 'windows-1251', ob_get_clean());
            exit;
        }
    }

}
