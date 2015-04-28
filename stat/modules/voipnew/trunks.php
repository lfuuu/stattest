<?php
class m_voipnew_trunks
{
    public function invoke($method, $arguments)
    {
        if (is_callable(array($this, $method))) return call_user_func_array(array($this, $method), $arguments);
    }


    public function voipnew_trunks()
    {
        global $db, $design;
        $now = new DateTime();
        $now = $now->format('Y-m-d H:i:s');


        $res = $db->AllRecords("    select u.*
                                    from usage_trunk u
                                    where activation_dt <= '{$now}' and expire_dt >= '{$now}'
                                    order by u.connection_point_id desc, u.trunk_name          ");

        $design->assign('trunks', $res);
        $design->assign('regions', $db->AllRecords('select id, name from regions', 'id'));
        $design->AddMain('voipnew/trunks.html');
    }

}