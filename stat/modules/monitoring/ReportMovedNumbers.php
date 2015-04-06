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
                AND a.actual_to < "3000-01-01"
                AND a.actual_to = DATE_SUB(b.actual_from, INTERVAL 1 DAY)'
        );
        $options['order'] = 'from_actual_to, from_id, to_id';
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
                                b.is_moved,
                                b.moved_from';
        $options['from'] = 'usage_virtpbx as a';
        $options['joins'] = 
                    'LEFT JOIN usage_virtpbx as b ON (a.actual_to = DATE_SUB(b.actual_from, INTERVAL 1 DAY))'
                .   'LEFT JOIN clients as c ON (c.client = a.client)';
        $options['conditions'] = array(
                '   a.id <> b.id 
                AND a.actual_to < "3000-01-01"
                AND CAST(NOW() as DATE) <= a.actual_to 
                AND c.id = b.moved_from'
        );
        $options['order'] = 'from_actual_to, from_id, to_id';
        $data = UsageVoip::find('all', $options);
        $design->assign('vpbx_data', $data);
        
        $options = array();
        $options['select'] = '  a.id as from_id, 
                                UNIX_TIMESTAMP(a.actual_from) as from_actual_from ,
                                UNIX_TIMESTAMP(a.actual_to) as from_actual_to, 
                                b.id as to_id,
                                UNIX_TIMESTAMP(b.actual_from) as to_actual_from,
                                UNIX_TIMESTAMP(b.actual_to) as to_actual_to,
                                a.client as from_client,
                                b.client as to_client';
        $options['from'] = 'usage_virtpbx as a';
        $options['joins'] = '   LEFT JOIN usage_virtpbx as b ON (a.actual_to = DATE_SUB(b.actual_from, INTERVAL 1 DAY)) 
                                LEFT JOIN clients as cl ON (a.client = cl.client) 
                                LEFT JOIN usage_virtpbx as c ON (cl.id = c.moved_from)
        ';
        $options['conditions'] = array(
                '   a.id <> b.id 
                AND a.actual_to < "3000-01-01"
                AND CAST(NOW() as DATE) <= a.actual_to 
                AND b.is_moved = 0 
                AND (c.actual_from <> b.actual_from OR ISNULL(c.actual_from))'
        );
        $options['order'] = 'from_actual_to, from_id, to_id';
        $data = UsageVoip::find('all', $options);
        
        $sort_data = array();
        if (!empty($data))
        {
            foreach ($data as $v)
            {
                if (!isset($sort_data[$v->from_actual_to]))
                {
                    $sort_data[$v->from_actual_to] = array('from' => array(), 'to' => array(), 'date_to' => $v->to_actual_from);
                }
                if (!isset($sort_data[$v->from_actual_to]['from'][$v->from_id]))
                {
                    $sort_data[$v->from_actual_to]['from'][$v->from_id] = $v;
                }
                if (!isset($sort_data[$v->from_actual_to]['to'][$v->to_id]))
                {
                    $sort_data[$v->from_actual_to]['to'][$v->to_id] = $v;
                }
            }
        }

        $design->assign('possible_vpbx_data', $sort_data);
        $design->AddMain('monitoring/report_move_numbers.tpl');
    }
}
?>