<?php
	define("PATH_TO_ROOT",'../');
	include PATH_TO_ROOT."conf.php";

    $p_region = intval($_GET['region']);
    $p_dest = intval($_GET['dest']);

    $filter = " and g.dest={$p_dest} ";

    $report_id = -1;
    $params = array();
    if ($p_region == 99 && $p_dest == 1){
        $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>17,'param'=>'d1', 'date'=>date('Y-m-d'));
        $params[] = array('report_id'=>$report_id,'position'=>2,'pricelist_id'=>18,'param'=>'d1', 'date'=>date('Y-m-d'));
        $params[] = array('report_id'=>$report_id,'position'=>3,'pricelist_id'=>19,'param'=>'d1', 'date'=>date('Y-m-d'));
    }elseif ($p_region == 99 && $p_dest == 2){
        $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>17,'param'=>'d1', 'date'=>date('Y-m-d'));
        $params[] = array('report_id'=>$report_id,'position'=>2,'pricelist_id'=>18,'param'=>'d1', 'date'=>date('Y-m-d'));
        $params[] = array('report_id'=>$report_id,'position'=>3,'pricelist_id'=>19,'param'=>'d1', 'date'=>date('Y-m-d'));
    }elseif ($p_region == 99 && $p_dest == 3){
            $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>17,'param'=>'d1', 'date'=>date('Y-m-d'));
            $params[] = array('report_id'=>$report_id,'position'=>2,'pricelist_id'=>18,'param'=>'d1', 'date'=>date('Y-m-d'));
            $params[] = array('report_id'=>$report_id,'position'=>3,'pricelist_id'=>19,'param'=>'d1', 'date'=>date('Y-m-d'));
    }elseif ($p_region == 99 && $p_dest == 4){
        $filter = " and g.dest=1 and d.mob=false ";
        $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>61,'param'=>'d1', 'date'=>date('Y-m-d'));
        $params[] = array('report_id'=>$report_id,'position'=>2,'pricelist_id'=>61,'param'=>'d1', 'date'=>date('Y-m-d'));
        $params[] = array('report_id'=>$report_id,'position'=>3,'pricelist_id'=>61,'param'=>'d1', 'date'=>date('Y-m-d'));
    }elseif ($p_region == 99 && $p_dest == 5){
        $filter = " and g.dest=1 and d.mob=true ";
        $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>39,'param'=>'d1', 'date'=>date('Y-m-d'));
        $params[] = array('report_id'=>$report_id,'position'=>2,'pricelist_id'=>42,'param'=>'d1', 'date'=>date('Y-m-d'));
        $params[] = array('report_id'=>$report_id,'position'=>3,'pricelist_id'=>43,'param'=>'d1', 'date'=>date('Y-m-d'));
        $params[] = array('report_id'=>$report_id,'position'=>4,'pricelist_id'=>45,'param'=>'d1', 'date'=>date('Y-m-d'));

    //Архивные тарифы для Москвы
    }elseif ($p_region == 990 && $p_dest == 1){
        $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>6,'param'=>'d1', 'date'=>date('Y-m-d'));
        $params[] = array('report_id'=>$report_id,'position'=>2,'pricelist_id'=>6,'param'=>'d1', 'date'=>date('Y-m-d'));
        $params[] = array('report_id'=>$report_id,'position'=>3,'pricelist_id'=>6,'param'=>'d1', 'date'=>date('Y-m-d'));
    }elseif ($p_region == 990 && $p_dest == 2){
        $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>6,'param'=>'d1', 'date'=>date('Y-m-d'));
        $params[] = array('report_id'=>$report_id,'position'=>2,'pricelist_id'=>6,'param'=>'d1', 'date'=>date('Y-m-d'));
        $params[] = array('report_id'=>$report_id,'position'=>3,'pricelist_id'=>6,'param'=>'d1', 'date'=>date('Y-m-d'));
    }elseif ($p_region == 990 && $p_dest == 3){
            $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>6,'param'=>'d1', 'date'=>date('Y-m-d'));
            $params[] = array('report_id'=>$report_id,'position'=>2,'pricelist_id'=>6,'param'=>'d1', 'date'=>date('Y-m-d'));
            $params[] = array('report_id'=>$report_id,'position'=>3,'pricelist_id'=>6,'param'=>'d1', 'date'=>date('Y-m-d'));

    //Тарифы для договора присоединения сетей
    }elseif ($p_region == 991 && in_array($p_dest, array(1,2,3))){
        $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>111,'param'=>'d1', 'date'=>date('Y-m-d'));
        $params[] = array('report_id'=>$report_id,'position'=>2,'pricelist_id'=>111,'param'=>'d1', 'date'=>date('Y-m-d'));
        $params[] = array('report_id'=>$report_id,'position'=>3,'pricelist_id'=>111,'param'=>'d1', 'date'=>date('Y-m-d'));

    }elseif ($p_region == 97 && $p_dest == 1){
        $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>27,'param'=>'d1', 'date'=>date('Y-m-d'));
//        $params[] = array('report_id'=>$report_id,'position'=>2,'pricelist_id'=>28,'param'=>'d1', 'date'=>date('Y-m-d'));
//        $params[] = array('report_id'=>$report_id,'position'=>3,'pricelist_id'=>29,'param'=>'d1', 'date'=>date('Y-m-d'));
    }elseif ($p_region == 97 && $p_dest == 2){
            $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>27,'param'=>'d1', 'date'=>date('Y-m-d'));
//            $params[] = array('report_id'=>$report_id,'position'=>2,'pricelist_id'=>28,'param'=>'d1', 'date'=>date('Y-m-d'));
//            $params[] = array('report_id'=>$report_id,'position'=>3,'pricelist_id'=>29,'param'=>'d1', 'date'=>date('Y-m-d'));
    }elseif ($p_region == 97 && $p_dest == 3){
            $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>27,'param'=>'d1', 'date'=>date('Y-m-d'));
//            $params[] = array('report_id'=>$report_id,'position'=>2,'pricelist_id'=>28,'param'=>'d1', 'date'=>date('Y-m-d'));
//            $params[] = array('report_id'=>$report_id,'position'=>3,'pricelist_id'=>29,'param'=>'d1', 'date'=>date('Y-m-d'));
    }elseif ($p_region == 97 && $p_dest == 4){
        $filter = " and g.dest=1 and d.mob=false ";
        $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>38,'param'=>'d1', 'date'=>date('Y-m-d'));
//        $params[] = array('report_id'=>$report_id,'position'=>2,'pricelist_id'=>38,'param'=>'d1', 'date'=>date('Y-m-d'));
//        $params[] = array('report_id'=>$report_id,'position'=>3,'pricelist_id'=>38,'param'=>'d1', 'date'=>date('Y-m-d'));
    }elseif ($p_region == 97 && $p_dest == 5){
        $filter = " and g.dest=1 and d.mob=true ";
        $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>38,'param'=>'d1', 'date'=>date('Y-m-d'));
//        $params[] = array('report_id'=>$report_id,'position'=>2,'pricelist_id'=>38,'param'=>'d1', 'date'=>date('Y-m-d'));
//        $params[] = array('report_id'=>$report_id,'position'=>3,'pricelist_id'=>38,'param'=>'d1', 'date'=>date('Y-m-d'));

    }elseif ($p_region == 98 && $p_dest == 1){
      $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>52,'param'=>'d1', 'date'=>date('Y-m-d'));
//      $params[] = array('report_id'=>$report_id,'position'=>2,'pricelist_id'=>53,'param'=>'d1', 'date'=>date('Y-m-d'));
//      $params[] = array('report_id'=>$report_id,'position'=>3,'pricelist_id'=>54,'param'=>'d1', 'date'=>date('Y-m-d'));
    }elseif ($p_region == 98 && $p_dest == 2){
      $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>52,'param'=>'d1', 'date'=>date('Y-m-d'));
//      $params[] = array('report_id'=>$report_id,'position'=>2,'pricelist_id'=>53,'param'=>'d1', 'date'=>date('Y-m-d'));
//      $params[] = array('report_id'=>$report_id,'position'=>3,'pricelist_id'=>54,'param'=>'d1', 'date'=>date('Y-m-d'));
    }elseif ($p_region == 98 && $p_dest == 3){
      $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>52,'param'=>'d1', 'date'=>date('Y-m-d'));
//      $params[] = array('report_id'=>$report_id,'position'=>2,'pricelist_id'=>53,'param'=>'d1', 'date'=>date('Y-m-d'));
//      $params[] = array('report_id'=>$report_id,'position'=>3,'pricelist_id'=>54,'param'=>'d1', 'date'=>date('Y-m-d'));
    }elseif ($p_region == 98 && $p_dest == 4){
      $filter = " and g.dest=1 and d.mob=false ";
      $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>50,'param'=>'d1', 'date'=>date('Y-m-d'));
//      $params[] = array('report_id'=>$report_id,'position'=>2,'pricelist_id'=>50,'param'=>'d1', 'date'=>date('Y-m-d'));
//      $params[] = array('report_id'=>$report_id,'position'=>3,'pricelist_id'=>50,'param'=>'d1', 'date'=>date('Y-m-d'));
    }elseif ($p_region == 98 && $p_dest == 5){
      $filter = " and g.dest=1 and d.mob=true ";
      $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>50,'param'=>'d1', 'date'=>date('Y-m-d'));
//      $params[] = array('report_id'=>$report_id,'position'=>2,'pricelist_id'=>50,'param'=>'d1', 'date'=>date('Y-m-d'));
//      $params[] = array('report_id'=>$report_id,'position'=>3,'pricelist_id'=>50,'param'=>'d1', 'date'=>date('Y-m-d'));

    }elseif ($p_region == 96 && $p_dest == 1){
      $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>56,'param'=>'d1', 'date'=>date('Y-m-d'));
    }elseif ($p_region == 96 && $p_dest == 2){
      $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>56,'param'=>'d1', 'date'=>date('Y-m-d'));
    }elseif ($p_region == 96 && $p_dest == 3){
      $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>56,'param'=>'d1', 'date'=>date('Y-m-d'));
    }elseif ($p_region == 96 && $p_dest == 4){
      $filter = " and g.dest=1 and d.mob=false ";
      $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>55,'param'=>'d1', 'date'=>date('Y-m-d'));
    }elseif ($p_region == 96 && $p_dest == 5){
      $filter = " and g.dest=1 and d.mob=true ";
      $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>55,'param'=>'d1', 'date'=>date('Y-m-d'));

    }elseif ($p_region == 95 && $p_dest == 1){
      $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>66,'param'=>'d1', 'date'=>date('Y-m-d'));
    }elseif ($p_region == 95 && $p_dest == 2){
      $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>66,'param'=>'d1', 'date'=>date('Y-m-d'));
    }elseif ($p_region == 95 && $p_dest == 3){
      $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>66,'param'=>'d1', 'date'=>date('Y-m-d'));
    }elseif ($p_region == 95 && $p_dest == 4){
      $filter = " and g.dest=1 and d.mob=false ";
      $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>65,'param'=>'d1', 'date'=>date('Y-m-d'));
    }elseif ($p_region == 95 && $p_dest == 5){
      $filter = " and g.dest=1 and d.mob=true ";
      $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>65,'param'=>'d1', 'date'=>date('Y-m-d'));

    }elseif ($p_region == 94 && $p_dest == 1){
      $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>70,'param'=>'d1', 'date'=>date('Y-m-d'));
    }elseif ($p_region == 94 && $p_dest == 2){
      $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>70,'param'=>'d1', 'date'=>date('Y-m-d'));
    }elseif ($p_region == 94 && $p_dest == 3){
      $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>70,'param'=>'d1', 'date'=>date('Y-m-d'));
    }elseif ($p_region == 94 && $p_dest == 4){
      $filter = " and g.dest=1 and d.mob=false ";
      $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>68,'param'=>'d1', 'date'=>date('Y-m-d'));
    }elseif ($p_region == 94 && $p_dest == 5){
      $filter = " and g.dest=1 and d.mob=true ";
      $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>68,'param'=>'d1', 'date'=>date('Y-m-d'));

    }elseif ($p_region == 93 && $p_dest == 1){
      $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>79,'param'=>'d1', 'date'=>date('Y-m-d'));
    }elseif ($p_region == 93 && $p_dest == 2){
      $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>79,'param'=>'d1', 'date'=>date('Y-m-d'));
    }elseif ($p_region == 93 && $p_dest == 3){
      $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>79,'param'=>'d1', 'date'=>date('Y-m-d'));
    }elseif ($p_region == 93 && $p_dest == 4){
      $filter = " and g.dest=1 and d.mob=false ";
      $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>78,'param'=>'d1', 'date'=>date('Y-m-d'));
    }elseif ($p_region == 93 && $p_dest == 5){
      $filter = " and g.dest=1 and d.mob=true ";
      $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>78,'param'=>'d1', 'date'=>date('Y-m-d'));

    }elseif ($p_region == 87 && $p_dest == 1){
      $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>73,'param'=>'d1', 'date'=>date('Y-m-d'));
    }elseif ($p_region == 87 && $p_dest == 2){
      $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>73,'param'=>'d1', 'date'=>date('Y-m-d'));
    }elseif ($p_region == 87 && $p_dest == 3){
      $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>73,'param'=>'d1', 'date'=>date('Y-m-d'));
    }elseif ($p_region == 87 && $p_dest == 4){
      $filter = " and g.dest=1 and d.mob=false ";
      $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>72,'param'=>'d1', 'date'=>date('Y-m-d'));
    }elseif ($p_region == 87 && $p_dest == 5){
      $filter = " and g.dest=1 and d.mob=true ";
      $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>72,'param'=>'d1', 'date'=>date('Y-m-d'));

    }elseif ($p_region == 89 && $p_dest == 1){
      $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>117,'param'=>'d1', 'date'=>date('Y-m-d'));
    }elseif ($p_region == 89 && $p_dest == 2){
      $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>117,'param'=>'d1', 'date'=>date('Y-m-d'));
    }elseif ($p_region == 89 && $p_dest == 3){
      $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>117,'param'=>'d1', 'date'=>date('Y-m-d'));
    }elseif ($p_region == 89 && $p_dest == 4){
      $filter = " and g.dest=1 and d.mob=false ";
      $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>116,'param'=>'d1', 'date'=>date('Y-m-d'));
    }elseif ($p_region == 89 && $p_dest == 5){
      $filter = " and g.dest=1 and d.mob=true ";
      $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>116,'param'=>'d1', 'date'=>date('Y-m-d'));

    }elseif ($p_region == 88 && $p_dest == 1){
      $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>82,'param'=>'d1', 'date'=>date('Y-m-d'));
    }elseif ($p_region == 88 && $p_dest == 2){
      $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>82,'param'=>'d1', 'date'=>date('Y-m-d'));
    }elseif ($p_region == 88 && $p_dest == 3){
      $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>82,'param'=>'d1', 'date'=>date('Y-m-d'));
    }elseif ($p_region == 88 && $p_dest == 4){
      $filter = " and g.dest=1 and d.mob=false ";
      $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>81,'param'=>'d1', 'date'=>date('Y-m-d'));
    }elseif ($p_region == 88 && $p_dest == 5){
      $filter = " and g.dest=1 and d.mob=true ";
      $params[] = array('report_id'=>$report_id,'position'=>1,'pricelist_id'=>81,'param'=>'d1', 'date'=>date('Y-m-d'));

    }else{
        die('error: incorrect parameters');
    }


    $pg_db->Query("DELETE FROM voip.analyze_pricelist_report WHERE id='{$report_id}'");
    $pg_db->Query("INSERT INTO voip.analyze_pricelist_report(id, generated,ext_params)values('{$report_id}',null, null)");
    foreach($params as $param){
        $pg_db->QueryInsert('voip.analyze_pricelist_report_params', $param, false);
    }

    $pg_db->Query("select * from voip.prepare_analyze_pricelist_report('{$report_id}')");
    $data =   $pg_db->AllRecords("          select d.defcode, r.*, dgr.shortname as dgroup,
                                                    g.name as destination, g.zone, d.mob
                                            from voip.analyze_pricelist_report_data r
                                                    LEFT JOIN voip_destinations d ON r.ndef=d.ndef
                      					                    LEFT JOIN geo.geo g ON g.id=d.geo_id
                                                    LEFT JOIN voip_dest_groups dgr ON dgr.id=g.dest
                                                    where r.report_id={$report_id} {$filter}
                                                    order by r.report_id, r.position, r.param, g.name, d.defcode
                                             ");
    foreach($data as $r){
        if (!isset($report[$r['defcode']])){
            $report[$r['defcode']] = array('defcode'=>$r['defcode'], 'zone'=>$r['zone'], 'mob'=>$r['mob'], 'dgroup'=>$r['dgroup'], 'destination'=>$r['destination'], 'parts'=>array(), );
        }

        if (!isset($report[$r['defcode']]['parts'][$r['position']]))
            $report[$r['defcode']]['parts'][$r['position']] = array('d1'=>array(), 'd2'=>array());

        $m = explode('-', $r['date_from']); if (count($m)==3) $r['date_from'] = $m[2].'.'.$m[1].'.'.$m[0];

        $report[$r['defcode']]['parts'][$r['position']][$r['param']] = $r;
    }

    $dest=''; $ismob=''; $price='';

    foreach($report as $k => $r){
        $price = '';
        foreach($r['parts'] as $part){
            if (isset($part['d1']['price'])) $price .= $part['d1']['price'];
            if (isset($part['d2']['price'])) $price .= $part['d2']['price'];
        }
        $report[$k]['price'] = $price;
    }

    function cmp($r1, $r2)
    {
        $res = strcmp( $r1['destination'], $r2['destination']);
        if ($res != 0) return $res;

        $res = strcmp($r1['price'], $r2['price']);
        if ($res != 0) return $res;

        $res = strcmp($r1['defcode'], $r2['defcode']);
        if ($res != 0) return $res;

        return 0;
    }

    usort($report, "cmp");

    $resgroups = array();
    $resgroup = array();
    foreach($report as $r){

        if ($dest != $r['dgroup'] ||
            $destination != $r['destination'] ||
            $ismob != $r['mob'] ||
            $price != $r['price'] )
        {
            $dest = $r['dgroup'];
            $destination = $r['destination'];
            $ismob = $r['mob'];
            $price = $r['price'];

            if (count($resgroup) > 0){
                $resgroups[] = $resgroup;
            }
            $resgroup = $r;
            $resgroup['defs'] = array();
            $resgroup['defcode'] = '';

        }


        $resgroup['defs'][] = $r['defcode'];
    }
    if (count($resgroup) > 0){
        $resgroups[] = $resgroup;
    }

    foreach ($resgroups as $k => $resgroup)
    {
        while(true){
            $can_trim = false;
            $first = true;
            $char = '';
            $defs = array();
            foreach($resgroups[$k]['defs'] as $d){
                if ($first == true){
                    $can_trim = true;
                    $first = false;
                    $char = substr($d, 0, 1);
                }else{
                    if ($char != substr($d, 0, 1)){
                        $can_trim = false;
                    }
                }
            }

            if ($can_trim == true){
                foreach($resgroups[$k]['defs'] as $d){
                    $dd = substr($d, 1);
                    if (strlen($dd) > 0)
                      $defs[] = $dd;
                    else if (strlen($dd) == 0)
                    {
                      $defs = array();
                      break;
                    }
                }
                $resgroups[$k]['defcode'] = $resgroups[$k]['defcode'] . $char;
                $resgroups[$k]['defs'] = $defs;
            }else{
                break;
            }
        }
    }


    $data = array();
    foreach ($resgroups as $resgroup)
    {
        $defs = '';
        foreach($resgroup['defs'] as $d){
            if ($defs == '') { $defs .= $d; } else { $defs .= ', '.$d; }
        }
        $data[] = array(    'code1'=>$resgroup['defcode'],
                            'code2'=>$defs,
                            'name'=>str_replace('"','""',$resgroup['destination']) . ($resgroup['mob']=='t' ? ' (п╪п╬п╠.)' : ''),
                            'zone'=>$resgroup['zone'],
                            'price1'=>str_replace('.',',',$resgroup['parts'][1]['d1']['price']),
                            'price2'=>str_replace('.',',',$resgroup['parts'][2]['d1']['price']),
                            'price3'=>str_replace('.',',',$resgroup['parts'][3]['d1']['price']),
                            'price4'=>str_replace('.',',',(isset($resgroup['parts'][4]['d1']['price'])?$resgroup['parts'][4]['d1']['price']:'') )
                    );
    }


    header("Content-type: text/plain; charset=UTF-8");
    header('Pragma: no-cache');
    header('Expires: 0');

    if ($p_region == 99 || $p_region == 990)
    {
      echo '"code1";"code2";"name";"zone";"price1";"price2";"price3";"price4"'."\n";
      foreach ($data as $r)
      {
          echo '"'.$r['code1'].'";"'.$r['code2'].'";"'.$r['name'].'";"'.$r['zone'].'";"'.$r['price1'].'";"'.$r['price2'].'";"'.$r['price3'].'";"'.$r['price4'].'"'."\n";
      }
    }else{
      echo '"code1";"code2";"name";"zone";"price1"'."\n";
      foreach ($data as $r)
      {
        echo '"'.$r['code1'].'";"'.$r['code2'].'";"'.$r['name'].'";"'.$r['zone'].'";"'.$r['price1'].'"'."\n";
      }
    }
