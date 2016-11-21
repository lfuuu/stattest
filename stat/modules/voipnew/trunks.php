<?php
use app\models\billing\Trunk;

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

        $res = $db->AllRecords($query = "
            SELECT
                u.*, c.id AS client_id,
                cr.number as contract_number,
	            cct.name as contract_type,
                cg.name as company
            FROM usage_trunk u
                 LEFT JOIN clients c on c.id=u.client_account_id
                 LEFT JOIN client_contract cr ON cr.id = c.contract_id
                 LEFT JOIN client_contragent cg ON cg.id = cr.contragent_id
                 LEFT JOIN client_contract_type cct ON cct.id = cr.contract_type_id
            WHERE u.activation_dt <= '{$now}' AND u.expire_dt >= '{$now}'
            ORDER BY u.connection_point_id DESC, c.id, u.trunk_id
        ");

        $design->assign('trunks', $res);
        $design->assign('regions', $db->AllRecords('select id, name from regions', 'id'));
        $design->assign('bill_trunks', Trunk::dao()->getListAll());
        $design->AddMain('voipnew/trunks.html');
    }

}