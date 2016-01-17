<?php

class m_voipreports_reconciliation_report
{

    public function invoke($method, $arguments)
    {
        if (is_callable(array($this, $method))) return call_user_func_array(array($this, $method), $arguments);
    }

    function voipreports_reconciliation_report()
    {
        global $db, $pg_db, $design;

        $f_instance_id = (int)get_param_protected('f_instance_id', '99');
        $f_trunk_id = get_param_integer('f_trunk_id', '0');
        $f_service_trunk_id = get_param_integer('f_service_trunk_id', '0');

        $date_from = get_param_protected('date_from', date('Y-m-d'));
        $date_to = get_param_protected('date_to', date('Y-m-d'));
        $f_include_initiate = get_param_protected('f_include_initiate', 't');
        $exclude_local = get_param_integer('exclude_local', 0);

        $totals = array('count'=>0, 'minutes'=>0, 'amount'=>0, 'nds'=>0, 'amount_with_nds' => 0);

        if ($f_instance_id && $f_trunk_id) {
            $where = "r.orig=false and billed_time > 0 and r.trunk_id = '{$f_trunk_id}' and r.connect_time >= '{$date_from}' and r.connect_time <= '{$date_to} 23:59:59.999999' ";

            if ($f_service_trunk_id) {
                $where .= " and r.trunk_service_id = '{$f_service_trunk_id}' ";
            }

            if ($exclude_local > 0) {
                $where .= ' and r.destination_id >= 0 ';
            }

            $report = $pg_db->AllRecords("
                                                select
                                                    g.id,
                                                    r.dst_number::varchar like '7800%' as is7800,
                                                    g.name as destination,
                                                    count(*) as count,
                                                    sum(billed_time) as seconds,
                                                    rate as price,
                                                    sum(cost) as amount,
                                                    case i.region_id @> ARRAY[g.region]::varchar[]
                                                    when true then
                                                        p.initiate_zona_cost
                                                    else
                                                        p.initiate_mgmn_cost
                                                    end as initiate_price
                                                from calls_raw.calls_raw r
                                                left join geo.geo g on g.id=r.geo_id
                                                left join voip.pricelist p on p.id=r.pricelist_id
                                                left join billing.instance_settings i on i.id = p.region
                                                where
                                                    server_id = {$f_instance_id}
                                                    and {$where}
                                                group by g.id, g.name, r.rate, g.region, p.initiate_zona_cost, p.initiate_mgmn_cost, i.region_id, p.initiate_mgmn_cost, r.dst_number::varchar like '7800%'
                                                order by is7800 desc, g.name, amount
                                             ");
            foreach($report as $k => $r) {
                if ($f_include_initiate == 't' && $r['initiate_price'] > 0 && $r['price'] > 0) {
                    $initiate_price = $r['initiate_price'];
                } else {
                    $initiate_price = 0;
                }

                if ($r['is7800'] == 't') {
                    $r['destination'] = '7800 ' . $r['destination'];
                }

                $r['price'] = $r['price'] + $initiate_price;
                $initiate_amount = $initiate_price * $r['minutes'];
                $r['amount'] = $r['amount'] + $initiate_amount;
                $r['nds'] = ($r['amount']) * 0.18;
                $r['amount_with_nds'] = $r['amount'] + $r['nds'];

                $totals['count'] += $r['count'];
                $totals['minutes'] += $r['minutes'];
                $totals['amount'] += $r['amount'];
                $totals['nds'] += $r['nds'];
                $totals['amount_with_nds'] += $r['amount_with_nds'];

                $r['price'] = number_format($r['price'], 2, ',', '');
                $r['amount'] = number_format($r['amount'], 2, ',', '');
                $r['nds'] = number_format($r['nds'], 2, ',', '');
                $r['amount_with_nds'] = number_format($r['amount_with_nds'], 2, ',', '');
                $report[$k] = $r;
            }

            $totals['amount'] = number_format($totals['amount'], 2, ',', '');
            $totals['nds'] = number_format($totals['nds'], 2, ',', '');
            $totals['amount_with_nds'] = number_format($totals['amount_with_nds'], 2, ',', '');

        } else {
            $report = array();
        }

        $trunks = $pg_db->AllRecords("select id, name from auth.trunk group by id, name",'id');
        $serviceTrunks = $db->AllRecords("select id, description as name from usage_trunk where actual_from < now() and actual_to > now() group by id, name",'id');

        $design->assign('report', $report);
        $design->assign('totals', $totals);
        $design->assign('f_instance_id', $f_instance_id);
        $design->assign('f_trunk_id', $f_trunk_id);
        $design->assign('f_service_trunk_id', $f_service_trunk_id);
        $design->assign('f_include_initiate', $f_include_initiate);
        $design->assign('exclude_local', $exclude_local);
        $design->assign('date_from', $date_from);
        $design->assign('date_to', $date_to);
        $design->assign('trunks', $trunks);
        $design->assign('serviceTrunks', $serviceTrunks);
        $design->assign('regions', Region::getListAssoc());
        $design->AddMain('voipreports/reconciliation_report.html');
    }
}