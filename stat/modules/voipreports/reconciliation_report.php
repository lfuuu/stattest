<?php
class m_voipreports_reconciliation_report
{

    public function invoke($method, $arguments)
    {
        if (is_callable(array($this, $method))) return call_user_func_array(array($this, $method), $arguments);
    }

    function voipreports_reconciliation_report()
    {
        global $pg_db, $design;

        $f_instance_id = (int)get_param_protected('f_instance_id', '99');
        $f_operator_id = (int)get_param_protected('f_operator_id', '0');
        $date_from = get_param_protected('date_from', date('Y-m-d'));
        $date_to = get_param_protected('date_to', date('Y-m-d'));
        $f_include_initiate = get_param_protected('f_include_initiate', 't');
        $exclude_local = get_param_integer('exclude_local', 0);

        $totals = array('count'=>0, 'minutes'=>0, 'amount'=>0, 'nds'=>0, 'amount_with_nds' => 0);

        if ($f_instance_id && $f_operator_id) {
            $where = "r.direction_out and len > 0 and r.operator_id = '{$f_operator_id}' and r.time >= '{$date_from}' and r.time <= '{$date_to} 23:59:59' ";

            if ($exclude_local > 0) {
                $where .= ' and r.dest >= 0 ';
            }

            $report = $pg_db->AllRecords("
                                                select
                                                    g.id,
                                                    r.phone_num::varchar like '7800%' as is7800,
                                                    g.name as destination,
                                                    count(*) as count,
                                                    sum(len_op) / 60 as minutes,
                                                    price_op / 10000.0 as price,
                                                    sum(amount_op) / 100.0 as amount,
                                                    case i.region_id @> ARRAY[g.region]::varchar[]
                                                    when true then
                                                        p.initiate_zona_cost
                                                    else
                                                        p.initiate_mgmn_cost
                                                    end as initiate_price
                                                from calls.calls_{$f_instance_id} r
                                                left join geo.geo g on g.id=r.geo_id
                                                left join voip.pricelist p on p.id=r.pricelist_op_id
                                                left join billing.instance_settings i on i.id = p.region
                                                where {$where}
                                                group by g.id, g.name, r.price_op, g.region, p.initiate_zona_cost, p.initiate_mgmn_cost, i.region_id, p.initiate_mgmn_cost, r.phone_num::varchar like '7800%'
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

        $operators = array();
        foreach (VoipOperator::find('all', array('order' => 'id, region desc')) as $op)
        {
            if (!isset($operators[$op->id])) {
                $operators[$op->id] = $op->short_name;
            }
        }

        $design->assign('report', $report);
        $design->assign('totals', $totals);
        $design->assign('f_instance_id', $f_instance_id);
        $design->assign('f_operator_id', $f_operator_id);
        $design->assign('f_include_initiate', $f_include_initiate);
        $design->assign('exclude_local', $exclude_local);
        $design->assign('date_from', $date_from);
        $design->assign('date_to', $date_to);
        $design->assign('operators', $operators);
        $design->assign('regions', Region::getListAssoc());
        $design->AddMain('voipreports/reconciliation_report.html');
    }
}