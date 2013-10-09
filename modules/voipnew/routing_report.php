<?php
class m_voipnew_routing_report
{

    public function invoke($method, $arguments)
    {
        if (is_callable(array($this, $method))) return call_user_func_array(array($this, $method), $arguments);
    }

    function voipnew_routing_report_show()
    {
        global $design, $pg_db, $db;
        if (isset($_GET['id'])) $report_id = intval($_GET['id']); else $report_id = 0;

        set_time_limit(0);

        $f_country_id = get_param_protected('f_country_id', '0');
        $f_region_id = get_param_protected('f_region_id', '0');
        $f_dest_group = get_param_protected('f_dest_group', '-1');
        $f_mob = get_param_protected('f_mob', '0');
        $f_locks = get_param_raw('f_locks', '');
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

        $lock_prefix = $pg_db->AllRecords("
              SELECT p.prefix, p.locked
              from voip.lock_prefix p
              WHERE p.region_id={$rep['region']}", 'prefix');
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
            if ($f_prefix != '')
                $where .= " and r.prefix like '" . intval($f_prefix) . "%' ";
            if ($f_mob == 't')
                $where .= " and d.mob=true ";
            if ($f_mob == 'f')
                $where .= " and d.mob=false ";
            if ($f_region_id != '0')
                $where .= " and g.region='{$f_region_id}' ";
            if ($f_locks != '')
                $where .= " and (r.locked=true)  ";
            if ($f_volume != '')
                $where .= " and (v.seconds_op>=" . ($f_volume * 60) . ")  ";

            $report = $pg_db->AllRecords("
                                            select r.prefix, r.pricelists, r.prices, r.priorities, r.locked, v.seconds_op/60 as volume,
                                                  g.name as destination, d.mob
                                            from voip.select_routing_report({$report_id}) r
                                            LEFT JOIN voip_destinations d ON r.prefix=d.defcode
                                            LEFT JOIN geo.geo g ON g.id=d.geo_id
                                            LEFT JOIN voip_dest_groups dgr ON dgr.id=g.dest
                                            LEFT JOIN voip.volume_calc_data v on v.task_id={$volume_task_id} and v.operator_id=0 and v.prefix=r.prefix
                                            where true {$where}
                                            order by g.name, r.prefix
                                     ");
            foreach ($report as $k => $r) {
                $prices = explode(',', substr($r['prices'], 1, strlen($r['prices']) - 2));
                $pricelists = explode(',', substr($r['pricelists'], 1, strlen($r['pricelists']) - 2));
                $priorities = explode(',', substr($r['priorities'], 1, strlen($r['priorities']) - 2));

                $parts = array();
                $i = 0;
                foreach ($pricelists as $pl) {
                    $parts[$pl] = array('price' => $prices[$i], 'priority' => $priorities[$i]);
                    $i++;
                }

                $report[$k]['locked_raw'] = (isset($lock_prefix[$r['prefix']]) ? $lock_prefix[$r['prefix']]['locked'] : '');
                $report[$k]['parts'] = $parts;
                $report[$k]['pricelists'] = $pricelists;
                $report[$k]['prices'] = $prices;
            }
        }

        $countries = $pg_db->AllRecords("SELECT id, name FROM geo.country ORDER BY name");
        $regions = $pg_db->AllRecords("SELECT id, name FROM geo.region ORDER BY name");
        $pricelists = $pg_db->AllRecords("select p.id, p.name, o.short_name as operator from voip.pricelist p
                                                      left join voip.operator o on p.operator_id=o.id and o.region=p.region ", 'id');


        if (!isset($_GET['export'])) {
            $design->assign('rep', $rep);
            $design->assign('volume', $volume);
            $design->assign('report', $report);
            $design->assign('report_id', $report_id);
            $design->assign('f_prefix', $f_prefix);
            $design->assign('f_country_id', $f_country_id);
            $design->assign('f_region_id', $f_region_id);
            $design->assign('f_mob', $f_mob);
            $design->assign('f_dest_group', $f_dest_group);
            $design->assign('f_locks', $f_locks);
            $design->assign('f_volume', $f_volume);
            $design->assign('geo_countries', $countries);
            $design->assign('geo_regions', $regions);
            $design->assign('regions', $db->AllRecords('select id, name from regions', 'id'));
            $design->assign('pricelists', $pricelists);
            $design->AddMain('voipnew/routing_report_show_old.html');
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


    public function voipnew_routing_report_list($p)
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
        $design->AddMain('voipnew/routing_report_list.html');

    }


    function routing_report_show()
    {
        global $design, $pg_db, $db;
        if (isset($_GET['id'])) $report_id = intval($_GET['id']); else $report_id = 0;

        set_time_limit(0);

        $f_country_id = get_param_protected('f_country_id', '0');
        $f_region_id = get_param_protected('f_region_id', '0');
        $f_dest_group = get_param_protected('f_dest_group', '-1');
        $f_mob = get_param_protected('f_mob', '0');
        $f_locks = get_param_raw('f_locks', '');
        $f_prefix = get_param_raw('f_prefix', '');
        $f_volume = get_param_raw('f_volume', '');

        $recalc = isset($_GET['calc']) ? 'true' : 'false';

        $rep = PricelistReport::find($report_id);
        $volume = $pg_db->GetRow("SELECT * FROM voip.volume_calc_task WHERE id=" . intval($rep->volume_calc_task_id));
        if (isset($volume['id']))
            $volume_task_id = $volume['id'];
        else
            $volume_task_id = 0;

        if ($rep->instance_id) {
            $lock_prefix = $pg_db->AllRecords("
                  SELECT p.prefix, p.locked
                  from voip.lock_prefix p
                  WHERE p.region_id={$rep->instance_id}", 'prefix');
        } else {
            $lock_prefix = array();
        }
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
            if ($f_prefix != '')
                $where .= " and r.prefix like '" . intval($f_prefix) . "%' ";
            if ($f_mob == 't')
                $where .= " and d.mob=true ";
            if ($f_mob == 'f')
                $where .= " and d.mob=false ";
            if ($f_region_id != '0')
                $where .= " and g.region='{$f_region_id}' ";
            if ($f_locks != '')
                $where .= " and (r.locked=true)  ";
            if ($f_volume != '')
                $where .= " and (v.seconds_op>=" . ($f_volume * 60) . ")  ";

            $report = $pg_db->AllRecords("
                                            select r.prefix, r.prices, r.locked, r.orders, r.routes, v.seconds_op/60 as volume,
                                                  g.name as destination, d.mob
                                            from voip.select_pricelist_report({$report_id}, {$recalc}) r
                                            LEFT JOIN voip_destinations d ON r.prefix=d.defcode
                                            LEFT JOIN geo.geo g ON g.id=d.geo_id
                                            LEFT JOIN voip_dest_groups dgr ON dgr.id=g.dest
                                            LEFT JOIN voip.volume_calc_data v on v.task_id={$volume_task_id} and v.operator_id=0 and v.prefix=r.prefix
                                            where true {$where}
                                            order by g.name, r.prefix
                                     ");

            foreach ($report as $k => $r) {
                $orders = explode(',', substr($r['orders'], 1, strlen($r['orders']) - 2));
                $prices = explode(',', substr($r['prices'], 1, strlen($r['prices']) - 2));
                $routes = explode(',', substr($r['routes'], 1, strlen($r['routes']) - 2));

                $report[$k]['locked_raw'] = (isset($lock_prefix[$r['prefix']]) ? $lock_prefix[$r['prefix']]['locked'] : '');
                $report[$k]['prices'] = $prices;
                $report[$k]['routes'] = $routes;
                $report[$k]['orders'] = $orders;
                $report[$k]['best_price'] = $prices[$orders[0]];
            }
        }

        $countries = $pg_db->AllRecords("SELECT id, name FROM geo.country ORDER BY name");
        $regions = $pg_db->AllRecords("SELECT id, name FROM geo.region ORDER BY name");
        $pricelists = $pg_db->AllRecords("select p.id, p.name, o.short_name as operator from voip.pricelist p
                                          left join voip.operator o on p.operator_id=o.id and (o.region=p.region or o.region=0) ", 'id');


        if (!isset($_GET['export'])) {
            $design->assign('rep', $rep);
            $design->assign('volume', $volume);
            $design->assign('report', $report);
            $design->assign('report_id', $report_id);
            $design->assign('f_prefix', $f_prefix);
            $design->assign('f_country_id', $f_country_id);
            $design->assign('f_region_id', $f_region_id);
            $design->assign('f_mob', $f_mob);
            $design->assign('f_dest_group', $f_dest_group);
            $design->assign('f_locks', $f_locks);
            $design->assign('f_volume', $f_volume);
            $design->assign('geo_countries', $countries);
            $design->assign('geo_regions', $regions);
            $design->assign('regions', $db->AllRecords('select id, name from regions', 'id'));
            $design->assign('pricelists', $pricelists);
            $design->AddMain('voipnew/routing_report_show.html');
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

}
