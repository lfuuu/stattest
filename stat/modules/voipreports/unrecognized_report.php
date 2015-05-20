<?php

class m_voipreports_unrecognized_report
{

    public function invoke($method, $arguments)
    {
        if (is_callable(array($this, $method))) return call_user_func_array(array($this, $method), $arguments);
    }

    function voipreports_unrecognized()
    {
        global $db,$pg_db,$design;

        $def=getdate();
        $def['mday']=1; $from=param_load_date('from_',$def);
        $def['mday']=31; $to=param_load_date('to_',$def);

        $def['mday']=1; $cur_from=param_load_date('cur_from_',$def);
        $def['mday']=31; $cur_to=param_load_date('cur_to_',$def);
        $def['mon']--; if ($def['mon']==0) {$def['mon']=12; $def['year']--; }
        $def['mday']=1; $prev_from=param_load_date('prev_from_',$def);
        $def['mday']=31; $prev_to=param_load_date('prev_to_',$def);
        $def['mday']=31; $prev_to=param_load_date('prev_to_',$def);
        $phone = get_param_protected('phone','none');
        $haslen = get_param_protected('haslen','0');
        $region = get_param_integer('region', 99);

        $direction = get_param_raw('direction', 'both');
        if(!in_array($direction,array('both','in','out')))
            $direction = 'both';

        $stats = array();
        $geo = array();
        if ($phone != 'none')
        {
            $filter = " connect_time between '".date("Y-m-d", $from)." 00:00:00' and '".date("Y-m-d", $to)." 23:59:59.999999' ";
            $filter .= " and service_id is null and server_id=$region ";
            if($direction<>'both')
                $filter .= " and orig=".(($direction=='in')?'false':'true');

            if ($haslen == 1)
                $filter .= ' and billed_time>0 ';
            if ($phone != '')
                $filter .= ' and (src_number='.$pg_db->escape($phone).' or dst_number='.$pg_db->escape($phone).') ';

            $stats = $pg_db->AllRecords("select id, src_number, dst_number, billed_time, orig, connect_time, geo_id, mob
	                    from calls_raw.calls_raw
	                    where $filter
	                    order by connect_time
	                    limit 10000
	                    ");
            foreach($stats as $k=>$r)
            {

                if ($r["billed_time"]>=24*60*60) $d=floor($r["billed_time"]/(24*60*60)); else $d=0;
                $length=($d?($d.'d '):'').gmdate("<b>H:i</b>:s",$r["billed_time"]);

                $stats[$k]['connect_time'] = substr($r["connect_time"], 0, 19);
                $stats[$k]['length'] = $length;

                if (!isset($geo[$r['geo_id']]))
                    $geo[$r['geo_id']] = $pg_db->GetValue('select name from geo.geo where id='.((int)$r['geo_id']));
                $stats[$k]['geo'] = $geo[$r['geo_id']];
                if ($r['mob'] == 't') $stats[$k]['geo'] .= ' (mob)';
            }
            $design->assign('phone',$phone);
            $design->assign('haslen',$haslen);
        }

        $design->assign('stats',$stats);
        $design->assign('direction',$direction);
        $design->assign('region',$region);
        $design->assign('regions',$db->AllRecords('select * from regions'));

        $design->AddMain('voipreports/unrecognized_report_form.html');
        $design->AddMain('voipreports/unrecognized_report.html');

    }
}