<?php

global $report_defs;


class m_voipnew_pricelist_report
{

    public function invoke($method, $arguments)
    {
        if (is_callable(array($this, $method))) return call_user_func_array(array($this, $method), $arguments);
    }

    function voipnew_pricelist_report_show()
    {
        global $design, $pg_db, $db;
        if (isset($_GET['id'])) $report_id = intval($_GET['id']); else $report_id = 0;

        set_time_limit(0);

        $f_country_id = get_param_protected('f_country_id', '0');
        $f_region_id = get_param_protected('f_region_id', '0');
        $f_mob = get_param_protected('f_mob', '0');
        $f_dest_group = get_param_protected('f_dest_group', '-1');
        $f_locks = get_param_raw('f_locks', '');
        $f_prefix = get_param_raw('f_prefix', '');

        $rep = $pg_db->GetRow("
          SELECT p.id, p.region_id, p.pricelist_id, r.pricelists
          from voip.pricelist_report p
          left join voip.routing_report r on r.id=p.routing_report_id
          WHERE p.id=$report_id");
        $rep['pricelists'] = explode(',', substr($rep['pricelists'], 1, strlen($rep['pricelists']) - 2));

        $report = array();
        if (isset($_GET['make']) || isset($_GET['export'])) {
            $where = '';
            if ($f_prefix != '')
                $where .= " and r.prefix like '" . intval($f_prefix) . "%' ";
            if ($f_dest_group != '-1')
                $where .= " and g.dest='{$f_dest_group}' ";
            if ($f_country_id != '0')
                $where .= " and g.country='{$f_country_id}' ";
            if ($f_region_id != '0')
                $where .= " and g.region='{$f_region_id}' ";
            if ($f_locks != '')
                $where .= " and (r.locked=true or r.locked_raw is not null)  ";
            if ($f_mob == 't')
                $where .= " and d.mob=true ";
            if ($f_mob == 'f')
                $where .= " and d.mob=false ";


            $report = $pg_db->AllRecords("
                                        select r.prefix, r.op_pricelists as pricelists, r.op_prices as prices,
                                              g.name as destination, d.mob
                                        from voip.prepare_pricelist_report({$report_id}) r
                                                LEFT JOIN voip_destinations d ON r.prefix=d.defcode
                                  LEFT JOIN geo.geo g ON g.id=d.geo_id
                                                LEFT JOIN voip_dest_groups dgr ON dgr.id=g.dest
                                                where true {$where}
                                                order by g.name, d.defcode
                                         ");
            foreach ($report as $k => $r) {
                $prices = explode(',', substr($r['prices'], 1, strlen($r['prices']) - 2));
                $pricelists = explode(',', substr($r['pricelists'], 1, strlen($r['pricelists']) - 2));

                $parts = array();
                $i = 0;
                foreach ($pricelists as $pl) {
                    $parts[$pl] = array('price' => $prices[$i], 'priority' => 0);
                    $i++;
                }

                $report[$k]['parts'] = $parts;
                $report[$k]['pricelists'] = $pricelists;
                $report[$k]['prices'] = $prices;
            }
        }

        $countries = $pg_db->AllRecords("SELECT id, name FROM geo.country ORDER BY name");
        $regions = $pg_db->AllRecords("SELECT id, name FROM geo.region ORDER BY name");
        $pricelists = $pg_db->AllRecords("select p.id, p.name, o.short_name as operator from voip.pricelist p
                                                  left join voip.operator o on p.operator_id=o.id and o.region=p.region ", 'id');

        $design->assign('rep', $rep);
        $design->assign('report', $report);
        $design->assign('report_id', $report_id);
        $design->assign('f_prefix', $f_prefix);
        $design->assign('f_country_id', $f_country_id);
        $design->assign('f_region_id', $f_region_id);
        $design->assign('f_mob', $f_mob);
        $design->assign('f_dest_group', $f_dest_group);
        $design->assign('f_locks', $f_locks);
        $design->assign('geo_countries', $countries);
        $design->assign('geo_regions', $regions);
        $design->assign('regions', $db->AllRecords('select id, name from regions', 'id'));
        $design->assign('pricelists', $pricelists);
        $design->AddMain('voipnew/pricelist_report_show.html');
    }

    function voipnew_pricelist_report_edit()
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
                $report_id = $pg_db->GetNextId('voip.pricelist_report');
                $pg_db->Query("INSERT INTO voip.pricelist_report(id, generated,ext_params)values('{$report_id}',null, '" . $calcfields . "')");
            } else {
                $pg_db->Query("UPDATE voip.pricelist_report SET ext_params='" . $calcfields . "' WHERE id = '{$report_id}'");
            }
            $pg_db->Query("DELETE FROM voip.pricelist_report_params WHERE report_id='{$report_id}'");
            $pos = 1;
            foreach ($sp2 as $sp) {
                if ($sp['date1'] != null) {
                    $md = explode('.', $sp['date1']);
                    $sp['date1'] = $md[2] . '-' . $md[1] . '-' . $md[0];
                    $pg_db->QueryInsert('voip.pricelist_report_params', array('report_id' => $report_id, 'position' => $pos, 'pricelist_id' => $sp['pricelist_id'], 'param' => 'd1', 'date' => $sp['date1']));
                }
                if ($sp['date2'] != null) {
                    $md = explode('.', $sp['date2']);
                    $sp['date2'] = $md[2] . '-' . $md[1] . '-' . $md[0];
                    $pg_db->QueryInsert('voip.pricelist_report_params', array('report_id' => $report_id, 'position' => $pos, 'pricelist_id' => $sp['pricelist_id'], 'param' => 'd2', 'date' => $sp['date2']));
                }
                $pos = $pos + 1;
            }
            $pg_db->Query("select * from voip.prepare_pricelist_report('{$report_id}')");

            header('location: index.php?module=voipnew&action=pricelist_report_edit&id=' . $report_id);
            exit;
        }

        if (isset($_GET['id'])) $report_id = intval($_GET['id']); else $report_id = 0;
        $rep = $pg_db->GetRow("select *, volumes < generated as old_volumes from voip.pricelist_report where id={$report_id}");
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
        $design->AddMain('voipnew/pricelist_report_edit.html');
    }

    function voipnew_pricelist_report_calc_volumes()
    {
        global $pg_db;
        set_time_limit(0);
        if (!isset($_GET['id']) ||
            !preg_match("/\d{1,5}/", $_GET['id']) ||
            !preg_match("/\d{2}\.\d{2}\.\d{4}/", $_GET['from']) ||
            !preg_match("/\d{2}\.\d{2}\.\d{4}/", $_GET['to'])
        ) {
            echo 'bad parameters';
            return;
        }
        $report_id = $_GET['id'];
        $from = explode('.', $_GET['from']);
        $from = $from[2] . '-' . $from[1] . '-' . $from[0];
        $to = explode('.', $_GET['to']);
        $to = $to[2] . '-' . $to[1] . '-' . $to[0];


        echo '<html><body><script type="text/javascript">function print(txt){ document.getElementById("txt").innerHTML=txt; }</script>';

        echo "calculating volumes...<br/><br/>\n";
        echo "processed calls: <b id='txt'></b><br/>\n";
        flush();

        /*
        report_defs_load($report_id);
            
        $pg_db->Query('BEGIN');
        $pg_db->Query('DECLARE curs CURSOR FOR select phone_num as p, "lengthResult" as l from raw.usage_nvoip_sess r where r.ts_full>=\'01.06.2011\' and r.ts_full<\'01.07.2011\' and r.flag=4');
        $n = 0;
        while(1){
            $result = pg_query($pg_db->_LinkId, "FETCH 1000 FROM curs");
            while ($data = @pg_fetch_array($result, NULL, PGSQL_ASSOC)){
                report_defs_cals($data['p'], $data['l']);    
            }
            $res = pg_affected_rows($result);
            pg_free_result($result);
            $n = $n + $res;
            echo "<script>print('{$n}')</script>";flush();
            if ($res < 1000) break;    
        }
        $pg_db->Query('END');
            
        echo "<script>print('saving')</script>";flush();
        $q = '';
        foreach($report_defs as $d){
            if ($d['l'] > 0) {
                if ($q != ''){
                    $q .= ",('{$report_id}','{$d['d']}','{$d['l']}')";
                }else{
                    $q = "insert into voip.pricelist_report_defs(report_id, ndef, len) values
                            ('{$report_id}','{$d['d']}','{$d['l']}')";
                }
            }
        }
        $pg_db->Query('DELETE FROM voip.pricelist_report_defs WHERE report_id={$report_id}');
        if ($q != '') $pg_db->Query($q);
        */
        session_write_close();

        $t = new timer();
        $t->start();
//        $s = "/usr/bin/perl ".__DIR__."/calc.pl $report_id $from $to";
        $s = __DIR__ . "/calc.pl $report_id $from $to";
        echo $s . "<br/>\n";
        $handle = popen($s, 'r');
        while (!feof($handle)) {
            $read = fread($handle, 2096);
            echo $read;
            flush();
            ob_flush();
        }
        pclose($handle);
        echo $t->end();
        //echo "<script>window.location='index.php?module=voipnew&action=pricelist_report_edit&id={$report_id}'</script>";flush();

        exit;
    }

    function voipnew_pricelist_report_delete()
    {
        global $design, $pg_db;
        if (isset($_GET['id'])) $report_id = intval($_GET['id']); else $report_id = 0;
        if ($report_id > 0) {
            $pg_db->Query("DELETE FROM voip.pricelist_report_defs WHERE report_id='{$report_id}'");
            $pg_db->Query("DELETE FROM voip.pricelist_report_data WHERE report_id='{$report_id}'");
            $pg_db->Query("DELETE FROM voip.pricelist_report_params WHERE report_id='{$report_id}'");
            $pg_db->Query("DELETE FROM voip.pricelist_report WHERE id='{$report_id}'");
        }
        header('location: index.php?module=voipnew&action=pricelist_report_list');
        exit;
    }

    function voipnew_pricelist_report_list($p)
    {
        global $design, $pg_db, $db;

        $design->assign('regions', $db->AllRecords('select id, name from regions', 'id'));

        $reports = $pg_db->AllRecords("
          select p.id, p.region_id, p.pricelist_id, r.pricelists
          from voip.pricelist_report p
          left join voip.routing_report r on r.id=p.routing_report_id
          order by p.region_id desc");
        foreach ($reports as $k => $v) {
            $reports[$k]['pricelists'] = explode(',', substr($v['pricelists'], 1, strlen($v['pricelists']) - 2));
        }
        $design->assign('reports', $reports);

        $pricelists = $pg_db->AllRecords('
            select p.id as pricelist_id, p.operator_id, o.short_name as operator, p.name as pricelist from voip.pricelist p
                        left join voip.operator o on p.operator_id=o.id and o.region=p.region ', 'pricelist_id');
        $design->assign('pricelists', $pricelists);
        $design->AddMain('voipnew/pricelist_report_list.html');

    }
}
