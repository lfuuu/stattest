<?php
class m_voipnew_operators
{
    public function invoke($method, $arguments)
    {
        if (is_callable(array($this, $method))) return call_user_func_array(array($this, $method), $arguments);
    }


    public function voipnew_operators()
    {
        global $pg_db, $design;

        $res = $pg_db->AllRecords(" select o.region, o.id, o.short_name, o.term_in_cost, o.local_network_id, o.local_network_pricelist_id, o.pricelist_id, o.client_7800_pricelist_id
                                    from voip.operator o
                                    order by o.region desc, o.id ");



        $design->assign('operators', $res);
        $design->assign('pricelists', Pricelist::getListAssoc());
        $design->assign('regions', Region::getListAssoc());
        $design->AddMain('voipnew/operator_list.html');
    }
}