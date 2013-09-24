<?php
class m_voipnew_operator_report
{

    public function invoke($method, $arguments)
    {
        if (is_callable(array($this, $method))) return call_user_func_array(array($this, $method), $arguments);
    }

    function voipnew_operator_report_show()
    {
        global $design, $pg_db, $db;
        if (isset($_GET['id'])) $report_id = intval($_GET['id']); else $report_id = 0;

        set_time_limit(0);

        $f_country_id = get_param_protected('f_country_id', '0');
        $f_region_id = get_param_protected('f_region_id', '0');
        $f_dest_group = get_param_protected('f_dest_group', '-1');
        $f_mob = get_param_protected('f_mob', '0');
        $f_prefix = get_param_raw('f_prefix', '');
        $f_volume = get_param_raw('f_volume', '');

        if (isset($_GET['calc'])) {
            $pg_db->AllRecords("update voip.routing_report set generated=null where id='{$report_id}'");
            $pg_db->AllRecords("select * from voip.select_routing_report({$report_id}) limit 1");
        }

        $rep = $pg_db->GetRow("SELECT * FROM voip.routing_report WHERE id=$report_id");
        $volume = $pg_db->GetRow("SELECT * FROM voip.volume_calc_task WHERE id=" . intval($rep['volume_calc_task_id']));
        if (isset($volume['id']))
            $volume_task_id = $volume['id'];
        else
            $volume_task_id = 0;

        $rep['pricelists'] = explode(',', substr($rep['pricelists'], 1, strlen($rep['pricelists']) - 2));

        $totals = array('all' => array('volume' => '', 'amount' => '', 'amount_op' => ''));

        $report = array();
        if (isset($_GET['make']) || isset($_GET['calc']) || isset($_GET['export'])) {

            $where = '';
            if ($f_prefix != '')
                $where .= " and r.prefix like '" . intval($f_prefix) . "%' ";
            if ($f_dest_group != '-1')
                $where .= " and g.dest='{$f_dest_group}' ";
            if ($f_country_id != '0')
                $where .= " and g.country='{$f_country_id}' ";
            if ($f_region_id != '0')
                $where .= " and g.region='{$f_region_id}' ";
//            if ($f_volume != '')
//                $where .= " and (v.seconds_op>=" . ($f_volume * 60) . ")  ";
            if ($f_mob == 't')
                $where .= " and d.mob=true ";
            if ($f_mob == 'f')
                $where .= " and d.mob=false ";

            $pricelistToOperator = $pg_db->AllRecords("
                                        select id, operator_id
                                        from voip.pricelist
                                  ", 'id');

            $res_volumes = $pg_db->AllRecords("
                                        select prefix, operator_id, seconds_op/60.0 as volume, amount_op/100.0 as amount_op
                                        from voip.volume_calc_data
                                        where task_id={$volume_task_id}
                                  ");
            $volumes = array();
            foreach ($res_volumes as $r) {
                if (!isset($volumes[$r['operator_id']])) {
                    $volumes[$r['operator_id']] = array();
                }
                $volumes[$r['operator_id']][$r['prefix']] = $r;
            }

            $report = $pg_db->AllRecords("
                                        select r.prefix, r.pricelists, r.prices, r.locked,
                                              g.name as destination, d.mob
                                        from voip.select_routing_report({$report_id}) r
                                                LEFT JOIN voip_destinations d ON r.prefix=d.defcode
                                  LEFT JOIN geo.geo g ON g.id=d.geo_id
                                                LEFT JOIN voip_dest_groups dgr ON dgr.id=g.dest
                                                where true {$where}
                                                order by g.name, r.prefix
                                         ");

            foreach ($report as $k => $r) {

                $r['volume'] = isset($volumes['0'][$r['prefix']]) ? $volumes['0'][$r['prefix']]['volume'] : '';
                $r['amount_op'] = isset($volumes['0'][$r['prefix']]) ? $volumes['0'][$r['prefix']]['amount_op'] : '';

                if ($f_volume != '' && $r['volume'] < $f_volume) {
                    unset($report[$k]);
                    continue;
                }

                $r_prices = explode(',', substr($r['prices'], 1, strlen($r['prices']) - 2));
                $r_pricelists = explode(',', substr($r['pricelists'], 1, strlen($r['pricelists']) - 2));

                $r_parts = array();
                $i = 0;
                foreach ($r_pricelists as $pl) {
                    if ($i == 0) {
                        $operator_id = isset($pricelistToOperator[$pl]) ? $pricelistToOperator[$pl]['operator_id'] : '';


                        $amount_op =

                        $r_volume = isset($volumes[$operator_id][$r['prefix']]) ? $volumes[$operator_id][$r['prefix']]['volume'] : '';
                        $r_amount = isset($volumes[$operator_id][$r['prefix']]) ? $volumes[$operator_id][$r['prefix']]['amount_op'] : '';;
                        $r_parts[$pl] = array(
                            'price' => $r_prices[$i],
                            'volume' => $r_volume ? round($r_volume) : '',
                            'amount' => $r_amount ? round($r_amount) : '');
                        if (!isset($totals[$pl])) $totals[$pl] = array('volume' => '', 'amount' => '', 'amount_op' => '');

                        if ($r_volume) {
                            $totals[$pl]['volume'] += $r_volume;
                            $totals['all']['volume'] += $r_volume;
                        }
                        if ($r_amount) {
                            $totals[$pl]['amount'] += $r_amount;
                            $totals['all']['amount'] += $r_amount;
                        }
                    } else {
                        $r_parts[$pl] = array('price' => $r_prices[$i]);
                    }
                    $i++;
                }


                if ($r['amount_op']) {
                    $report[$k]['amount_op'] = round($r['amount_op']);
                    $totals['all']['amount_op'] += $r['amount_op'];
                }
                $report[$k]['parts'] = $r_parts;
                $report[$k]['pricelists'] = $r_pricelists;
                $report[$k]['prices'] = $r_prices;
            }
        } else {
            if (!isset($_GET['volume']))
                $f_volume = '0';
        }

        foreach ($totals as $k => $v) {
            if ($v['volume'])
                $totals[$k]['volume'] = round($v['volume']);
            if ($v['amount'])
                $totals[$k]['amount'] = round($v['amount']);
            if ($v['amount_op'])
                $totals[$k]['amount_op'] = round($v['amount_op']);
        }

        $countries = $pg_db->AllRecords("SELECT id, name FROM geo.country ORDER BY name");
        $regions = $pg_db->AllRecords("SELECT id, name FROM geo.region ORDER BY name");
        $pricelists = $pg_db->AllRecords("select p.id, p.name, o.short_name as operator, 0 as volume, 0 as amount from voip.pricelist p
                                                  left join voip.operator o on p.operator_id=o.id and o.region=p.region ", 'id');

        if (!isset($_GET['export'])) {
            $design->assign('rep', $rep);
            $design->assign('volume', $volume);
            $design->assign('totals', $totals);
            $design->assign('report', $report);
            $design->assign('report_id', $report_id);
            $design->assign('f_prefix', $f_prefix);
            $design->assign('f_volume', $f_volume);
            $design->assign('f_country_id', $f_country_id);
            $design->assign('f_region_id', $f_region_id);
            $design->assign('f_mob', $f_mob);
            $design->assign('f_dest_group', $f_dest_group);
            $design->assign('geo_countries', $countries);
            $design->assign('geo_regions', $regions);
            $design->assign('regions', $db->AllRecords('select id, name from regions', 'id'));
            $design->assign('pricelists', $pricelists);
            $design->AddMain('voipnew/operator_report_show.html');
        } else {
            header('Content-type: application/csv');
            header('Content-Disposition: attachment; filename="routing.csv"');

            ob_start();

            echo '"Префикс";"Направление";"Лучшая цена";';
            foreach ($rep['pricelists'] as $pl) {
                echo '"' . $pricelists[$pl]['operator'] . '";';
            }
            echo '"Порядок"' . "\n";
            foreach ($report as $r) {
                echo '"' . $r['prefix'] . '";';
                echo '"' . $r['destination'] . '";';
                echo '"' . str_replace('.', ',', $r['prices'][0]) . '";';
                foreach ($rep['pricelists'] as $pl) {
                    echo '"' . str_replace('.', ',', $r['parts'][$pl]['price']) . '";';
                }
                echo '"';
                foreach ($r['pricelists'] as $i => $pl) {
                    if ($i > 0) echo ' -> ';
                    echo $pricelists[$pl]['operator'];
                }
                echo '"' . "\n";
            }

            echo iconv('koi8-r', 'windows-1251', ob_get_clean());
            exit;
        }
    }


    public function voipnew_operator_report_list($p)
    {
        global $design, $pg_db, $db;

        $design->assign('regions', $db->AllRecords('select id, name from regions', 'id'));

        $reports = $pg_db->AllRecords("select p.id, p.region, p.pricelists from voip.routing_report p order by p.region desc");
        foreach ($reports as $k => $v) {
            $reports[$k]['pricelists'] = explode(',', substr($v['pricelists'], 1, strlen($v['pricelists']) - 2));
        }
        $design->assign('reports', $reports);

        $pricelists = $pg_db->AllRecords('
            select p.id as pricelist_id, p.operator_id, o.short_name as operator from voip.pricelist p
                        left join voip.operator o on p.operator_id=o.id and o.region=p.region ', 'pricelist_id');
        $design->assign('pricelists', $pricelists);
        $design->AddMain('voipnew/operator_report_list.html');

    }

}
