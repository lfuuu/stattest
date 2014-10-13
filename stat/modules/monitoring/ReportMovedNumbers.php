<?php
class ReportMovedNumbers 
{
    public static function getReport()
    {
        global $design;
        $options = array();
        $options['select'] = '  a.E164 as number, 
                                a.id as from_id, 
                                UNIX_TIMESTAMP(a.actual_from) as from_actual_from ,
                                UNIX_TIMESTAMP(a.actual_to) as from_actual_to, 
                                b.id as to_id,
                                UNIX_TIMESTAMP(b.actual_from) as to_actual_from,
                                UNIX_TIMESTAMP(b.actual_to) as to_actual_to,
                                a.client as from_client,
                                b.client as to_client,
                                b.is_moved,
                                b.is_moved_with_pbx';
        $options['from'] = 'usage_voip as a';
        $options['joins'] = 'LEFT JOIN usage_voip as b ON (b.E164 = a.E164)';
        $options['conditions'] = array(
                '   a.id <> b.id 
                AND CAST(NOW() as DATE) <= a.actual_to 
                AND a.actual_to < "2029-01-01" 
                AND a.actual_to = DATE_SUB(b.actual_from, INTERVAL 1 DAY)'
        );
        $data = UsageVoip::find('all', $options);
        $design->assign('data', $data);
        
        $options = array();
        $options['select'] = '  a.id as from_id, 
                                UNIX_TIMESTAMP(a.actual_from) as from_actual_from ,
                                UNIX_TIMESTAMP(a.actual_to) as from_actual_to, 
                                b.id as to_id,
                                UNIX_TIMESTAMP(b.actual_from) as to_actual_from,
                                UNIX_TIMESTAMP(b.actual_to) as to_actual_to,
                                a.client as from_client,
                                b.client as to_client,
                                b.is_moved';
        $options['from'] = 'usage_virtpbx as a';
        $options['joins'] = 'LEFT JOIN usage_virtpbx as b ON (a.actual_to = DATE_SUB(b.actual_from, INTERVAL 1 DAY))';
        $options['conditions'] = array(
                '   a.id <> b.id 
                AND a.actual_to < "2029-01-01" 
                AND CAST(NOW() as DATE) <= a.actual_to'
        );
        $data = UsageVoip::find('all', $options);
        $design->assign('vpbx_data', $data);
        $design->AddMain('monitoring/report_move_numbers.tpl');
    }
}
?>