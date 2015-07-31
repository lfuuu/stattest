<?php
class m_voipreports_calls_report
{

    public function invoke($method, $arguments)
    {
        if (is_callable(array($this, $method))) return call_user_func_array(array($this, $method), $arguments);
    }

    function voipreports_calls_report()
    {
        global $design, $pg_db;
        set_time_limit(0);

        $f_instance_id = (int)get_param_protected('f_instance_id', '0');
        $f_operator_id = (int)get_param_protected('f_operator_id', '0');
        $date_from = get_param_protected('date_from', date('Y-m-d'));
        $date_to = get_param_protected('date_to', date('Y-m-d'));
        $f_country_id = get_param_protected('f_country_id', '0');
        $f_region_id = get_param_protected('f_region_id', '0');
        $f_dest_group = get_param_protected('f_dest_group', '');
        $f_direction_out = get_param_protected('f_direction_out', 't');
        $f_mob = get_param_protected('f_mob', '0');
        $f_server_id = get_param_protected('f_server_id', '0');
        $f_prefix_op = get_param_protected('f_prefix_op', '');
        $f_without_prefix_op = get_param_protected('f_without_prefix_op', '0');
        $limit = 100;
        $offset = get_param_integer('offset', 0);


        if ($f_operator_id == 'all') $f_operator_id = '0';

        $report = array();


        if (isset($_GET['makeFile'])) {

            $where = " and r.connect_time >= '{$date_from}'";
            $where .= " and r.connect_time <= '{$date_to} 23:59:59'";
            $where .= $f_direction_out == 'f' ?  " and r.orig=true " : " and r.orig=false ";

            if ($f_operator_id != '0')
                $where .= " and r.operator_id='{$f_operator_id}' ";

            if ($f_server_id != '0')
                $where .= " and r.server_id='{$f_server_id}' ";

            $pg_db->Query("
                SELECT r.connect_time, r.src_number, r.dst_number, r.billed_time
                FROM calls_raw.calls_raw r
                WHERE billed_time > 0 $where
            ");
            
            header('Content-type: application/csv');
            header('Content-Disposition: attachment; filename="'.iconv("utf-8", "windows-1251", "Отчет по звонкам").'.csv"');

            ob_start();
            while($row = $pg_db->NextRecord(PGSQL_NUM)){
                echo implode(';', $row)."\n";
            }
            echo iconv('utf-8', 'windows-1251', ob_get_clean());
            exit();
        }

        if (isset($_GET['make'])) {

            $where = " and r.connect_time >= '{$date_from}'";
            $where .= " and r.connect_time <= '{$date_to} 23:59:59'";
            $where .= $f_direction_out == 'f' ?  " and r.orig=true " : " and r.orig=false ";

            if ($f_operator_id != '0')
                $where .= " and r.operator_id='{$f_operator_id}' ";

            if ($f_server_id != '0')
                $where .= " and r.server_id='{$f_server_id}' ";


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
            if ($f_prefix_op)
                $where .= " and r.prefix = '{$f_prefix_op}'";
            if ($f_without_prefix_op > 0)
                $where .= " and r.prefix is null ";

            $report = $pg_db->AllRecords("
                        select
                              r.id,
                              r.connect_time,
                              r.operator_id,
                              r.mob as mob,
                              r.src_number,
                              r.dst_number,
                              r.orig,
                              r.billed_time,
                              r.cost,
                              r.operator_id,
                              r.destination_id,
                              g.name as destination,
                              r.server_id,
                              r.prefix
                        from calls_raw.calls_raw r
                        left join voip_destinations d on d.ndef=r.geo_id
                        left join geo.geo g on g.id=d.geo_id
                        where
                            " . ($f_instance_id > 0 ? "server_id = {$f_instance_id} and " : '') . "
                            billed_time>0 {$where}
                        order by connect_time
                        limit 100 offset {$offset}
                                     ");

        }

        $operators = array();
        foreach (VoipOperator::find('all', array('order' => 'region desc, short_name')) as $op)
        {
            if (!isset($operators[$op->id])) {
                $operators[$op->id] = $op->short_name;
            }
        }

        $design->assign('report', $report);
        $design->assign('previous_offset', $offset > 0 ? ($offset - $limit > 0 ? $offset - $limit : 0) : 'none');
        $design->assign('next_offset', count($report) >= $limit ? $offset + $limit : 'none');
        $design->assign('limit', $limit);
        $design->assign('item_from', count($report) ? $offset + 1 : 0);
        $design->assign('item_to', count($report) ? $offset + $limit : 0);
        $design->assign('f_instance_id', $f_instance_id);
        $design->assign('f_operator_id', $f_operator_id);
        $design->assign('date_from', $date_from);
        $design->assign('date_to', $date_to);
        $design->assign('f_country_id', $f_country_id);
        $design->assign('f_region_id', $f_region_id);
        $design->assign('f_direction_out', $f_direction_out);
        $design->assign('f_mob', $f_mob);
        $design->assign('f_prefix_op', $f_prefix_op);
        $design->assign('f_without_prefix_op', $f_without_prefix_op);
        $design->assign('f_dest_group', $f_dest_group);
        $design->assign('operators', $operators);
        $design->assign('geo_countries', $pg_db->AllRecords("SELECT id, name FROM geo.country ORDER BY name"));
        $design->assign('geo_regions', $pg_db->AllRecords("SELECT id, name FROM geo.region ORDER BY name"));
        $design->assign('regions', Region::getListAssoc());
        $design->assign(
            'pricelists',
            $pg_db->AllRecords("    select p.id, p.name, o.short_name as operator from voip.pricelist p
                                    left join voip.operator o on p.operator_id=o.id and (o.region=p.region or o.region=0) ", 'id')
        );
        $design->AddMain('voipreports/calls_report_show.html');
    }
}