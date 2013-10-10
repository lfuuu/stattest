<?php
class m_voipnew_analyze_pricelist_report
{

    public function invoke($method, $arguments)
    {
        if (is_callable(array($this, $method))) return call_user_func_array(array($this, $method), $arguments);
    }

    function load_params($report_id)
    {
        global $pg_db;
        $params = $pg_db->AllRecords("select * from voip.analyze_pricelist_report_params WHERE report_id={$report_id} ORDER BY position, param");
        $params2 = array();
        $param2 = null;
        $last_position = 0;
        foreach ($params as $p) {
            if ($p['position'] != $last_position) {
                if ($param2 != null) $params2[] = $param2;
                $param2 = array('pricelist_id' => $p['pricelist_id']);
                $param2['position'] = $p['position'];
                $last_position = $p['position'];
            }
            if ($p['param'] == 'd1') {
                $m = explode('-', $p['date']);
                $param2['date1'] = $m[2] . '.' . $m[1] . '.' . $m[0];
            }
            if ($p['param'] == 'd2') {
                $m = explode('-', $p['date']);
                $param2['date2'] = $m[2] . '.' . $m[1] . '.' . $m[0];
            }

        }
        if ($param2 != null) $params2[] = $param2;
        return $params2;
    }

    function voipnew_analyze_pricelist_report_show()
    {
        global $design, $db, $pg_db;
        if (isset($_GET['id'])) $report_id = intval($_GET['id']); else $report_id = 0;

        set_time_limit(0);

        $f_country_id = get_param_protected('f_country_id', '0');
        $f_region_id = get_param_protected('f_region_id', '0');
        $f_mob = get_param_protected('f_mob', '0');
        $f_dest_group = get_param_protected('f_dest_group', '-1');
        $f_onlydiff = get_param_raw('f_onlydiff', '');
        $f_showdefs = get_param_raw('f_showdefs', '');
        $f_short = get_param_raw('f_short', '');

        $rep = $pg_db->GetRow("select * from voip.analyze_pricelist_report where id={$report_id}");
        $volume = $pg_db->GetRow("SELECT * FROM voip.volume_calc_task t WHERE id=" . intval($rep['volume_calc_task_id']));
        if (isset($volume['id']))
            $volume_task_id = $volume['id'];
        else
            $volume_task_id = 0;

        $params = array();
        $ext_params = array();
        $report = array();
        if (isset($_GET['make']) || isset($_GET['export'])) {
            $where = '';
            if ($f_dest_group != '-1')
                $where .= " and g.dest='{$f_dest_group}'";
            if ($f_country_id != '0')
                $where .= " and g.country='{$f_country_id}'";
            if ($f_region_id != '0')
                $where .= " and g.region='{$f_region_id}'";
            if ($f_mob == 't')
                $where .= " and d.mob=true ";
            if ($f_mob == 'f')
                $where .= " and d.mob=false ";

            //$ext_params = array( array('name' => 'ext1', 'formula'=>'[f1] + [f2]') );

            $ext_params = $pg_db->GetValue("select ext_params from voip.analyze_pricelist_report where id={$report_id}");
            if ($ext_params == '') $ext_params = array();
            else $ext_params = unserialize($ext_params);

            $params = $this->load_params($report_id);
            //$params = $pg_db->AllRecords("select * from voip.analyze_pricelist_report_params where report_id={$report_id} order by report_id, position");
            $data = $pg_db->AllRecords("
                                        select d.defcode, r.*, v.seconds/60 as volume, dgr.shortname as dgroup,
                                              g.name as destination, g.zone, d.mob
                                        from voip.analyze_pricelist_report_data r
                                                LEFT JOIN voip_destinations d ON r.ndef=d.ndef
                                  LEFT JOIN geo.geo g ON g.id=d.geo_id
                                                LEFT JOIN voip_dest_groups dgr ON dgr.id=g.dest
                                                LEFT JOIN voip.volume_calc_data v on v.task_id={$volume_task_id} and v.operator_id=0 and v.prefix=d.defcode
                                                where r.report_id={$report_id} {$where}
                                                order by r.report_id, r.position, r.param, g.name, d.defcode
                                         ");

            $report = array();
            foreach ($data as $r) {
                if (!isset($report[$r['defcode']])) {
                    $report[$r['defcode']] = array('defcode' => $r['defcode'], 'zone' => $r['zone'], 'volume' => $r['volume'], 'mob' => $r['mob'], 'dgroup' => $r['dgroup'], 'destination' => $r['destination'], 'parts' => array(), 'ext' => array());
                }

                if (!isset($report[$r['defcode']]['parts'][$r['position']]))
                    $report[$r['defcode']]['parts'][$r['position']] = array('d1' => array(), 'd2' => array());

                $m = explode('-', $r['date_from']);
                if (count($m) == 3) $r['date_from'] = $m[2] . '.' . $m[1] . '.' . $m[0];

                $report[$r['defcode']]['parts'][$r['position']][$r['param']] = $r;
            }
            foreach ($report as $k => $r) {
                foreach ($ext_params as $fp) {
                    $f = $fp['formula'];
                    $n = 1;
                    for ($n = 1; $n <= count($params); $n = $n + 1) {
                        if (isset($r['parts'][$n]['d1']['price'])) {
                            $f = str_replace('[f' . $n . ']', $r['parts'][$n]['d1']['price'], $f);
                        }
                        if (isset($r['parts'][$n]['d2']['price'])) {
                            $f = str_replace('[f' . $n . 'n]', $r['parts'][$n]['d2']['price'], $f);
                        }
                    }
                    $f = str_replace('[volume]', $r['volume'], $f);
                    if (@eval('$f = ' . $f . ';') !== NULL) $f = '';
                    $report[$k]['ext'][] = $f; //eval('return('.$f.');');    
                }
            }

        }

        $m = explode('-', $rep['volumes_date_from']);
        if (count($m) == 3) $rep['volumes_date_from'] = $m[2] . '.' . $m[1] . '.' . $m[0];
        $m = explode('-', $rep['volumes_date_to']);
        if (count($m) == 3) $rep['volumes_date_to'] = $m[2] . '.' . $m[1] . '.' . $m[0];

        $countries = $pg_db->AllRecords("SELECT id, name FROM geo.country ORDER BY name");
        $geo_regions = $pg_db->AllRecords("SELECT id, name FROM geo.region ORDER BY name");
        $pricelists = $pg_db->AllRecords("select * from voip.pricelist", 'id');

        if (isset($_GET['export'])) {
            foreach ($report as &$r) {
                $r['destination'] = iconv('koi8-r', 'utf-8', $r['destination']);
            }
            foreach ($pricelists as &$r) {
                $r['name'] = iconv('koi8-r', 'utf-8', $r['name']);
            }
        }

        if ($f_short != '') {
            $dest = '';
            $destination = '';
            $ismob = '';
            $price = '';

            $resgroups = array();
            $resgroup = array();
            foreach ($report as $r) {
                $r_price = '';
                foreach ($r['parts'] as $part) {
                    if (isset($part['d1']['price'])) $r_price .= $part['d1']['price'];
                    if (isset($part['d2']['price'])) $r_price .= $part['d2']['price'];
                }

                if ($dest != $r['dgroup'] ||
                    $destination != $r['destination'] ||
                    $ismob != $r['mob'] ||
                    $price != $r_price
                ) {
                    $dest = $r['dgroup'];
                    $destination = $r['destination'];
                    $ismob = $r['mob'];
                    $price = '';
                    foreach ($r['parts'] as $part) {
                        if (isset($part['d1']['price'])) $price .= $part['d1']['price'];
                        if (isset($part['d2']['price'])) $price .= $part['d2']['price'];
                    }

                    if (count($resgroup) > 0) {
                        $resgroups[] = $resgroup;
                    }
                    $resgroup = $r;
                    $resgroup['defs'] = array();
                    $resgroup['defcode'] = '';

                } else {
                    $resgroup['volume'] = $resgroup['volume'] + $r['volume'];
                }


                $resgroup['defs'][] = $r['defcode'];
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
                        $resgroups[$k]['defcode'] = $resgroups[$k]['defcode'] . $char;
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
                    $resgroup['defcode'] = $resgroup['defcode'] . ' </b>' . '(' . $defs . ')<b>';
                }
                $res[] = $resgroup;
            }
            $report = $res;
        }


        $design->assign('rep', $rep);
        $design->assign('volume', $volume);
        $design->assign('params', $params);
        $design->assign('ext_params', $ext_params);
        $design->assign('report', $report);
        $design->assign('report_id', $report_id);
        $design->assign('f_country_id', $f_country_id);
        $design->assign('f_region_id', $f_region_id);
        $design->assign('f_mob', $f_mob);
        $design->assign('f_dest_group', $f_dest_group);
        $design->assign('f_onlydiff', $f_onlydiff);
        $design->assign('f_showdefs', $f_showdefs);
        $design->assign('f_short', $f_short);
        $design->assign('countries', $countries);
        $design->assign('geo_regions', $geo_regions);
        $design->assign('regions', $db->AllRecords('select id, name from regions', 'id'));
        $design->assign('pricelists', $pricelists);

        if (!isset($_GET['export'])) {

            $design->AddMain('voipnew/analyze_pricelist_report_show_old.html');
        } else {

            $ctype = "application/vnd.ms-excel; charset=utf-8";
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header('Content-Disposition: attachment; filename="price.xls"');
            header("Content-Type: $ctype");
            header("Content-Transfer-Encoding: binary");

            //header("Content-Type: text/html; charset=utf-8");      

            $design->ProcessEx('voipnew/analyze_pricelist_report_export_old.html');
            exit;
        }
    }

    function voipnew_analyze_pricelist_report_edit()
    {
        global $design, $pg_db;
        set_time_limit(0);

        if (isset($_POST['data'])) {
            $report_id = intval($_POST['report_id']);
            $data = json_decode($_POST['data'], true);
            $selprices = $data['selprices'];
            $calcfields = $data['calcfields'];
            $calcfields = serialize($calcfields);
            $sp2 = array();
            foreach ($selprices as $sp) {
                if ($sp['date1'] == null && $sp['date2'] == null) continue;
                if ($sp['date1'] == null && $sp['date2'] != null) {
                    $sp['date1'] = $sp['date2'];
                    $sp['date2'] = null;
                }

                if ($sp['date1'] != NULL) $sp['date1'] = substr($sp['date1'], 0, 10);
                if ($sp['date2'] != NULL) $sp['date2'] = substr($sp['date2'], 0, 10);

                $sp2[] = $sp;
            }

            if ($report_id == 0) {
                $report_id = $pg_db->GetNextId('voip.analyze_pricelist_report');
                $pg_db->Query("INSERT INTO voip.analyze_pricelist_report(id, generated,ext_params)values('{$report_id}',null, '" . $calcfields . "')");
            } else {
                $pg_db->Query("UPDATE voip.analyze_pricelist_report SET ext_params='" . $calcfields . "' WHERE id = '{$report_id}'");
            }
            $pg_db->Query("DELETE FROM voip.analyze_pricelist_report_params WHERE report_id='{$report_id}'");
            $pos = 1;
            foreach ($sp2 as $sp) {
                if ($sp['date1'] != null) {
                    $md = explode('.', $sp['date1']);
                    $sp['date1'] = $md[2] . '-' . $md[1] . '-' . $md[0];
                    $pg_db->QueryInsert('voip.analyze_pricelist_report_params', array('report_id' => $report_id, 'position' => $pos, 'pricelist_id' => $sp['pricelist_id'], 'param' => 'd1', 'date' => $sp['date1']));
                }
                if ($sp['date2'] != null) {
                    $md = explode('.', $sp['date2']);
                    $sp['date2'] = $md[2] . '-' . $md[1] . '-' . $md[0];
                    $pg_db->QueryInsert('voip.analyze_pricelist_report_params', array('report_id' => $report_id, 'position' => $pos, 'pricelist_id' => $sp['pricelist_id'], 'param' => 'd2', 'date' => $sp['date2']));
                }
                $pos = $pos + 1;
            }
            $pg_db->Query("select * from voip.prepare_analyze_pricelist_report('{$report_id}')");

            header('location: index.php?module=voipnew&action=analyze_pricelist_report_edit&id=' . $report_id);
            exit;
        }

        if (isset($_GET['id'])) $report_id = intval($_GET['id']); else $report_id = 0;
        $rep = $pg_db->GetRow("select * from voip.analyze_pricelist_report where id={$report_id}");
        $calcfields = $rep['ext_params'];
        if ($calcfields == '') $calcfields = array();
        else $calcfields = unserialize($calcfields);

        $pricelists = $pg_db->AllRecords('select p.id as pricelist_id, p.name as pricelist, p.operator_id, o.name as operator, 
                                o.group_id, g.name as group, p.currency_id, c.name as currency from voip.pricelist p
                        left join voip.operator o on p.operator_id=o.id
                        left join voip.operator_group g on o.group_id=g.id
                        left join public.currency c on p.currency_id=c.id
                        order by o.name, p.name', 'pricelist_id');

        $selprices[] = array('pricelist_id' => 13);
        $design->assign('pricelists', $pricelists);
        $selprices = array();
        if ($report_id > 0) {
            $selprices = $this->load_params($report_id);
        }

        $m = explode('-', $rep['volumes_date_from']);
        if (count($m) == 3) $rep['volumes_date_from'] = $m[2] . '.' . $m[1] . '.' . $m[0];
        $m = explode('-', $rep['volumes_date_to']);
        if (count($m) == 3) $rep['volumes_date_to'] = $m[2] . '.' . $m[1] . '.' . $m[0];
        $design->assign('rep', $rep);
        $design->assign('data', json_encode(array('selprices' => $selprices, 'calcfields' => $calcfields)));
        $design->assign('report_id', $report_id);
        $design->AddMain('voipnew/analyze_pricelist_report_edit.html');
    }


    function voipnew_analyze_pricelist_report_delete()
    {
        global $design, $pg_db;
        if (isset($_GET['id'])) $report_id = intval($_GET['id']); else $report_id = 0;
        if ($report_id > 0) {
            $pg_db->Query("DELETE FROM voip.analyze_pricelist_report_defs WHERE report_id='{$report_id}'");
            $pg_db->Query("DELETE FROM voip.analyze_pricelist_report_data WHERE report_id='{$report_id}'");
            $pg_db->Query("DELETE FROM voip.analyze_pricelist_report_params WHERE report_id='{$report_id}'");
            $pg_db->Query("DELETE FROM voip.analyze_pricelist_report WHERE id='{$report_id}'");
        }
        header('location: index.php?module=voipnew&action=analyze_pricelist_report_list');
        exit;
    }

    function voipnew_analyze_pricelist_report_list($p)
    {
        global $design, $pg_db;
        $reports = $pg_db->AllRecords("select * from voip.analyze_pricelist_report order by generated desc");
        foreach ($reports as $k => $v) {
            $params = $this->load_params($v['id']);
            $reports[$k]['params'] = $params;
        }
        $design->assign('reports', $reports);

        $pricelists = $pg_db->AllRecords('select p.id as pricelist_id, p.name as pricelist, p.operator_id, o.name as operator,
                                o.group_id, g.name as group, p.currency_id, c.name as currency from voip.pricelist p
                        left join voip.operator o on p.operator_id=o.id
                        left join voip.operator_group g on o.group_id=g.id
                        left join public.currency c on p.currency_id=c.id
                        order by o.name, p.name', 'pricelist_id');
        $design->assign('pricelists', $pricelists);
        $design->AddMain('voipnew/analyze_pricelist_report_list.html');

    }


    function analyze_report_show()
    {
        global $design, $db, $pg_db;
        if (isset($_GET['id'])) $report_id = intval($_GET['id']); else $report_id = 0;

        $f_country_id = get_param_protected('f_country_id', '0');
        $f_region_id = get_param_protected('f_region_id', '0');
        $f_mob = get_param_protected('f_mob', '0');
        $f_dest_group = get_param_protected('f_dest_group', '-1');
        $f_short = get_param_raw('f_short', '');

        $recalc = isset($_GET['calc']) ? 'true' : 'false';

        $rep = PricelistReport::find($report_id);

        $volume = $pg_db->GetRow("SELECT * FROM voip.volume_calc_task WHERE id=" . intval($rep->volume_calc_task_id));
        if (isset($volume['id']))
            $volume_task_id = $volume['id'];
        else
            $volume_task_id = 0;

        $report = array();
        if (isset($_GET['make']) || isset($_GET['calc']) || isset($_GET['export'])) {
            $where = '';
            if ($f_dest_group != '-1')
                $where .= " and g.dest='{$f_dest_group}'";
            if ($f_country_id != '0')
                $where .= " and g.country='{$f_country_id}'";
            if ($f_region_id != '0')
                $where .= " and g.region='{$f_region_id}'";
            if ($f_mob == 't')
                $where .= " and d.mob=true ";
            if ($f_mob == 'f')
                $where .= " and d.mob=false ";

            $report = $pg_db->AllRecords("
                                        select r.prefix, r.prices, r.locked, r.orders, v.seconds/60 as volume,
                                                  g.name as destination, d.mob, g.zone, dgr.shortname as dgroup
                                        from voip.select_pricelist_report({$report_id}, {$recalc}) r
                                                LEFT JOIN voip_destinations d ON r.prefix=d.defcode
                                                LEFT JOIN geo.geo g ON g.id=d.geo_id
                                                LEFT JOIN voip_dest_groups dgr ON dgr.id=g.dest
                                                LEFT JOIN voip.volume_calc_data v on v.task_id={$volume_task_id} and v.operator_id=0 and v.prefix=d.defcode
                                                where true {$where}
                                                order by g.name, r.prefix
                                         ");

            foreach ($report as $k => $r) {
                $orders = explode(',', substr($r['orders'], 1, strlen($r['orders']) - 2));
                $prices = explode(',', substr($r['prices'], 1, strlen($r['prices']) - 2));
                $report[$k]['prices'] = $prices;
                $report[$k]['orders'] = $orders;
                $report[$k]['best_price'] = $prices[$orders[0]];
            }

        }

        if (isset($_GET['export'])) {
            foreach ($report as &$r) {
                $r['destination'] = iconv('koi8-r', 'utf-8', $r['destination']);
            }
        }

        if ($f_short != '') {
            $dest = '';
            $destination = '';
            $ismob = '';
            $price = '';

            $resgroups = array();
            $resgroup = array();
            foreach ($report as $r) {
                $r_price = implode('', $r['prices']);

                if ($dest != $r['dgroup'] ||
                    $destination != $r['destination'] ||
                    $ismob != $r['mob'] ||
                    $price != $r_price
                ) {
                    $dest = $r['dgroup'];
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
            $report = $res;
        }


        $design->assign('rep', $rep);
        $design->assign('volume', $volume);
        $design->assign('report', $report);
        $design->assign('report_id', $report_id);
        $design->assign('f_country_id', $f_country_id);
        $design->assign('f_region_id', $f_region_id);
        $design->assign('f_mob', $f_mob);
        $design->assign('f_dest_group', $f_dest_group);
        $design->assign('f_short', $f_short);
        $design->assign('countries', $pg_db->AllRecords("SELECT id, name FROM geo.country ORDER BY name"));
        $design->assign('geo_regions', $pg_db->AllRecords("SELECT id, name FROM geo.region ORDER BY name"));
        $design->assign('regions', $db->AllRecords('select id, name from regions', 'id'));

        $pricelists = $pg_db->AllRecords("select * from voip.pricelist", 'id');
        if (isset($_GET['export'])) {
            foreach ($pricelists as &$r) {
                $r['name'] = iconv('koi8-r', 'utf-8', $r['name']);
            }
        }
        $design->assign('pricelists', $pricelists);

        if (!isset($_GET['export'])) {

            $design->AddMain('voipnew/analyze_pricelist_report_show.html');
        } else {

            $ctype = "application/vnd.ms-excel; charset=utf-8";
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header('Content-Disposition: attachment; filename="price.xls"');
            header("Content-Type: $ctype");
            header("Content-Transfer-Encoding: binary");

            //header("Content-Type: text/html; charset=utf-8");

            $design->ProcessEx('voipnew/analyze_pricelist_report_export.html');
            exit;
        }
    }

}
