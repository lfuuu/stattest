<?php
class m_voipreports_analyze_pricelist_report
{

    public function invoke($method, $arguments)
    {
        if (is_callable(array($this, $method))) return call_user_func_array(array($this, $method), $arguments);
    }

    function analyze_report_show()
    {
        global $design, $pg_db;
        if (isset($_GET['id'])) $report_id = intval($_GET['id']); else $report_id = 0;

        $f_country_id = get_param_protected('f_country_id', '0');
        $f_region_id = get_param_protected('f_region_id', '0');
        $f_mob = get_param_protected('f_mob', '0');
        $f_short = get_param_protected('f_short', '');

        $recalc = isset($_GET['calc']) ? 'true' : 'false';

        $rep = PricelistReport::find($report_id);

        $volume = $pg_db->GetRow("SELECT * FROM voip.volume_calc_task WHERE id=" . intval($rep->volume_calc_task_id));
        if (isset($volume['id']))
            $volume_task_id = $volume['id'];
        else
            $volume_task_id = 0;
        $volumes = array();
        $volumesByPricelist = array();
        $showOperator = false;

        $pricelists = Pricelist::getListAssoc();
        $regions = Region::getListAssoc();

        $report = array();
        if (isset($_GET['make']) || isset($_GET['calc']) || isset($_GET['export'])) {
            $where = '';
            if ($f_country_id != '0')
                $where .= " and g.country='{$f_country_id}'";
            if ($f_region_id != '0')
                $where .= " and g.region='{$f_region_id}'";
            if ($f_mob == 't')
                $where .= " and d.mob=true ";
            if ($f_mob == 'f')
                $where .= " and d.mob=false ";

            $showOperator = false;

            $sql = "
                    select r.prefix, r.prices, r.locked, r.orders, round(v.seconds_op/60.0) as volume,
                              g.name as destination,
                              " . ($showOperator ? 'pp.operator_id,' :'' ) . "
                              d.mob, g.zone
                    from voip.select_pricelist_report({$report_id}, {$recalc}) r
                            LEFT JOIN voip_destinations d ON r.prefix=d.defcode
                            LEFT JOIN geo.geo g ON g.id=d.geo_id
                            LEFT JOIN voip.volume_calc_data v on v.task_id={$volume_task_id} and v.instance_id=0 and v.pricelist_id=0 and v.prefix=d.defcode
                            " . ($showOperator ? 'left join geo.prefix pp on pp.prefix=r.prefix' :'' ) . "
                            where true {$where}
                            order by g.name, r.prefix
                     ";
            $report = $pg_db->AllRecords($sql);

            foreach ($report as $k => $r) {
                $orders = substr($r['orders'], 1, strlen($r['orders']) - 2);
                $orders = $orders != '' ? explode(',', $orders) : array();
                $prices = substr($r['prices'], 1, strlen($r['prices']) - 2);
                $prices = $prices != '' ? explode(',', $prices) : array();

                $report[$k]['prices'] = $prices;
                if (count($orders) > 0) {
                    $report[$k]['best_index'] = $orders[0];
                    $report[$k]['best_price'] = $prices[$orders[0]];
                } else {
                    $report[$k]['best_index'] = -1;
                    $report[$k]['best_price'] = '';
                }
            }


            $res_volumes = $pg_db->AllRecords("
                                        select prefix, instance_id, round(seconds_op/60.0) as volume
                                        from voip.volume_calc_data
                                        where task_id={$volume_task_id} and pricelist_id=0
                                  ");
            foreach($rep->getFields() as $field) {
                $volumes[$field['pricelist']->region] = array();
            }
            foreach ($res_volumes as $r) {
                if (!isset($volumes[$r['instance_id']])) {
                    continue;
                }
                $volumes[$r['instance_id']][$r['prefix']] = $r;
            }

            $res_volumes = $pg_db->AllRecords("
                                        select prefix, pricelist_id, round(seconds_op/60.0) as volume
                                        from voip.volume_calc_data
                                        where task_id={$volume_task_id} and instance_id=0 and pricelist_id!=0
                                  ");
            foreach ($res_volumes as $r) {
                if (!isset($volumesByPricelist[$r['pricelist_id']])) {
                    $volumesByPricelist[$r['pricelist_id']] = array();
                }
                $volumesByPricelist[$r['pricelist_id']][$r['prefix']] = $r;
            }

        }

        if ($f_short != '') {
            $report = $this->reduceCodes($report);
        }

        $geoOperators = [
            '5000585' => 'МТС',
            '5000610' => 'МегаФон',
            '5001095' => 'Билайн',
        ];

        $design->assign('rep', $rep);
        $design->assign('volume', $volume);
        $design->assign('volumes', $volumes);
        $design->assign('volumesByPricelist', $volumesByPricelist);
        $design->assign('report', $report);
        $design->assign('report_id', $report_id);
        $design->assign('showOperator', $showOperator);
        $design->assign('f_country_id', $f_country_id);
        $design->assign('f_region_id', $f_region_id);
        $design->assign('f_mob', $f_mob);
        $design->assign('f_short', $f_short);
        $design->assign('countries', $pg_db->AllRecords("SELECT id, name FROM geo.country ORDER BY name"));
        $design->assign('geo_regions', $pg_db->AllRecords("SELECT id, name FROM geo.region ORDER BY name"));

        $design->assign('pricelists', $pricelists);
        $design->assign('regions', $regions);
        $design->assign('geoOperators', $geoOperators);

        if (!isset($_GET['export'])) {

            $design->AddMain('voipreports/analyze_pricelist_report_show.html');
        } else {

            $ctype = "application/vnd.ms-excel; charset=utf-8";
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header('Content-Disposition: attachment; filename="price.xls"');
            header("Content-Type: $ctype");
            header("Content-Transfer-Encoding: binary");

//            header("Content-Type: text/html; charset=utf-8");

            $design->ProcessEx('voipreports/analyze_pricelist_report_export.html');
            exit;
        }
    }

    private function reduceCodes($report)
    {
        $destination = '';
        $ismob = '';
        $price = '';

        $resgroups = array();
        $resgroup = array();
        foreach ($report as $r) {
            $r_price = implode('', $r['prices']);

            if ($destination != $r['destination'] ||
                $ismob != $r['mob'] ||
                $price != $r_price
            ) {
                $destination = $r['destination'];
                $ismob = $r['mob'];
                $price = implode('', $r['prices']);

                if (count($resgroup) > 0) {
                    $resgroups[] = $resgroup;
                }
                $resgroup = $r;
                $resgroup['defs'] = array();
                $resgroup['prefix'] = '';

            } else {
                $resgroup['volume'] = $resgroup['volume'] + $r['volume'];
            }


            $resgroup['defs'][] = $r['prefix'];
        }
        if (count($resgroup) > 0) {
            $resgroups[] = $resgroup;
        }

        foreach ($resgroups as $k => $resgroup) {
            while (true) {
                $can_trim = false;
                $first = true;
                $char = '';
                $defs = array();
                foreach ($resgroups[$k]['defs'] as $d) {
                    if ($first == true) {
                        $can_trim = true;
                        $first = false;
                        $char = substr($d, 0, 1);
                    } else {
                        if ($char != substr($d, 0, 1)) {
                            $can_trim = false;
                        }
                    }
                }

                if ($can_trim == true) {
                    foreach ($resgroups[$k]['defs'] as $d) {
                        $dd = substr($d, 1);
                        if (strlen($dd) > 0)
                            $defs[] = $dd;
                        else if (strlen($dd) == 0) {
                            $defs = array();
                            break;
                        }
                    }
                    $resgroups[$k]['prefix'] = $resgroups[$k]['prefix'] . $char;
                    $resgroups[$k]['defs'] = $defs;
                } else {
                    break;
                }
            }
        }

        $res = array();
        foreach ($resgroups as $resgroup) {
            $defs = '';
            foreach ($resgroup['defs'] as $d) {
                if ($defs == '') {
                    $defs .= $d;
                } else {
                    $defs .= ', ' . $d;
                }
            }
            $resgroup['def2'] = '';

            if ($defs != '') {
                $resgroup['prefix'] = $resgroup['prefix'] . ' </b>' . '(' . $defs . ')<b>';
            }
            $res[] = $resgroup;
        }

        return $res;
    }
}
