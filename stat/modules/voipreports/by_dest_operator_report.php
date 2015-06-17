<?php
class m_voipreports_by_dest_operator_report
{

    public function invoke($method, $arguments)
    {
        if (is_callable(array($this, $method))) return call_user_func_array(array($this, $method), $arguments);
    }

    function voipreports_by_dest_operator()
    {
        global $pg_db, $design;

        $f_instance_id = (int)get_param_protected('f_instance_id', '99');
        $f_operator_id = (int)get_param_protected('f_operator_id', '0');
        $date_from = get_param_protected('date_from', date('Y-m-d'));
        $date_to = get_param_protected('date_to', date('Y-m-d'));
        $f_country_id = get_param_protected('f_country_id', '0');
        $f_region_id = get_param_protected('f_region_id', '0');
        $f_dest_group = get_param_protected('f_dest_group', '-1');
        $f_mob = get_param_protected('f_mob', '0');
        $f_exclude_other_numbers = (int)get_param_protected('f_exclude_other_numbers', '0');

        if ($f_instance_id) {
            $where = "r.orig=false and billed_time > 0 and r.connect_time >= '{$date_from}' and r.connect_time <= '{$date_to} 23:59:59.999999' ";

            if ($f_operator_id)
                $where .= " and r.operator_id = '{$f_operator_id}' ";

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
            if ($f_exclude_other_numbers > 0)
                $where .= " and r.src_number::varchar not like '7495950%' and r.src_number::varchar not like '7495638%' ";


            $report = $pg_db->AllRecords("
                                                select
                                                    o.name as operator,
                                                    count(*) as count,
                                                    sum(billed_time) / 60 as minutes,
                                                    sum(cost) as amount
                                                from calls_raw.calls_raw r
                                                left join geo.operator o on o.id=r.geo_operator_id
                                                left join geo.geo g on g.id=r.geo_id
                                                where
                                                    server_id = {$f_instance_id}
                                                    and {$where}
                                                    and operator_id > 0
                                                group by o.name
                                                order by minutes desc

                                             ");
        } else {
            $report = array();
        }

        $operators = array();
        foreach (VoipOperator::find('all', array('order' => 'region desc, short_name')) as $op)
        {
            if (!isset($operators[$op->id])) {
                $operators[$op->id] = $op->short_name;
            }
        }

        $design->assign('report', $report);
        $design->assign('f_instance_id', $f_instance_id);
        $design->assign('f_operator_id', $f_operator_id);
        $design->assign('date_from', $date_from);
        $design->assign('date_to', $date_to);
        $design->assign('f_country_id', $f_country_id);
        $design->assign('f_region_id', $f_region_id);
        $design->assign('f_mob', $f_mob);
        $design->assign('f_dest_group', $f_dest_group);
        $design->assign('f_exclude_other_numbers', $f_exclude_other_numbers);
        $design->assign('operators', $operators);
        $design->assign('regions', Region::getListAssoc());
        $design->assign('geo_countries', $pg_db->AllRecords("SELECT id, name FROM geo.country ORDER BY name"));
        $design->assign('geo_regions', $pg_db->AllRecords("SELECT id, name FROM geo.region ORDER BY name"));
        $design->AddMain('voipreports/by_dest_operator_report.html');
    }
}