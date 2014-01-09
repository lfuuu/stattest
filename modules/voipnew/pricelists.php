<?php
class m_voipnew_pricelists
{
    public function invoke($method, $arguments)
    {
        if (is_callable(array($this, $method))) return call_user_func_array(array($this, $method), $arguments);
    }


    public function voipnew_client_pricelists()
    {
        global $db, $pg_db, $design;

        $res = $pg_db->AllRecords(" select p.*, o.short_name as operator, c.code as currency from voip.pricelist p
                                    left join public.currency c on c.id=p.currency_id
                                    left join voip.operator o on o.id=p.operator_id and o.region=p.region
                                    where p.operator_id = 999 and p.type = 'client'
                                    order by p.region desc, p.operator_id, p.name");

        $design->assign('pricelists', $res);
        $design->assign('regions', $db->AllRecords('select id, name from regions', 'id'));
        $design->AddMain('voipnew/client_pricelists.html');
    }

    public function voipnew_operator_pricelists()
    {
        global $db, $pg_db, $design;

        $res = $pg_db->AllRecords(" select p.*, o.short_name as operator, c.code as currency from voip.pricelist p
                                    left join public.currency c on c.id=p.currency_id
                                    left join voip.operator o on o.id=p.operator_id and o.region=p.region
                                    where p.operator_id != 999 and p.type = 'operator'
                                    order by p.region desc, p.operator_id, p.name");

        $design->assign('pricelists', $res);
        $design->assign('regions', $db->AllRecords('select id, name from regions', 'id'));
        $design->AddMain('voipnew/operator_pricelists.html');
    }

    public function voipnew_client_pricelist_edit()
    {
        global $design;

        $id = get_param_protected('pricelist_id');

        if ($id) {
            $pricelist = Pricelist::find($id);
        } else {
            $pricelist = new Pricelist();
            $pricelist->type= 'client';
        }

        if ($pricelist->type == 'operator' && $pricelist->region) {
            $operator = VoipOperator::getByIdAndInstanceId($pricelist->operator_id, $pricelist->region);
        } else {
            $operator = null;
        }

        $design->assign('pricelist', $pricelist);
        $design->assign('regions', Region::getListAssoc());
        $design->assign('operator', $operator);
        $design->AddMain('voipnew/pricelist_edit.html');
    }

    public function voipnew_operator_pricelist_edit()
    {
        global $design;

        $id = get_param_protected('pricelist_id');

        if ($id) {
            $pricelist = Pricelist::find($id);
        } else {
            $pricelist = new Pricelist();
            $pricelist->type = 'operator';
        }

        if ($pricelist->type == 'operator' && $pricelist->region) {
            $operator = VoipOperator::getByIdAndInstanceId($pricelist->operator_id, $pricelist->region);
        } else {
            $operator = null;
        }

        $design->assign('type', $pricelist->type);
        $design->assign('pricelist', $pricelist);
        $design->assign('regions', Region::getListAssoc());
        $design->assign('operator', $operator);
        $design->assign('operators', VoipOperator::find('all'));
        $design->AddMain('voipnew/pricelist_edit.html');
    }

    public function voipnew_pricelist_save()
    {
        $pricelist_id = get_param_protected('pricelist_id');
        $instance_id = get_param_protected('instance_id');
        $instance_id_operator_id = get_param_protected('instance_id_operator_id');
        $type = get_param_protected('type');
        $name = get_param_protected('name');
        $initiateMgmnCost = get_param_protected('initiate_mgmn_cost');
        $initiateZonaCost = get_param_protected('initiate_zona_cost');
        $tarifficationByMinutes = get_param_protected('tariffication_by_minutes') > 0 ? 't' : 'f';
        $tarifficationFullFirstMinute = get_param_protected('tariffication_full_first_minute') ? 't' : 'f';

        if ($pricelist_id) {
            $pricelist = Pricelist::find($pricelist_id);
        } else {
            $pricelist = new Pricelist();
            $pricelist->type = $type;
        }

        $pricelist->name = $name;

        if ($pricelist->type == 'operator') {
            if ($pricelist->is_new_record()) {
                list($instance_id, $operator_id) = explode('_', $instance_id_operator_id);
                $pricelist->region = $instance_id;
                $pricelist->operator_id = $operator_id;
            }

            $pricelist->initiate_mgmn_cost = $initiateMgmnCost ? $initiateMgmnCost : 0;
            $pricelist->initiate_zona_cost = $initiateZonaCost ? $initiateZonaCost : 0;
            $pricelist->tariffication_by_minutes = $tarifficationByMinutes;
            $pricelist->tariffication_full_first_minute = $tarifficationFullFirstMinute;
        } elseif ($pricelist->type == 'client') {
            if ($pricelist->is_new_record()) {
                $pricelist->region = $instance_id;
                $pricelist->operator_id = 999;
            }

            $pricelist->initiate_mgmn_cost = 0;
            $pricelist->initiate_zona_cost = 0;
            $pricelist->tariffication_by_minutes = 'f';
            $pricelist->tariffication_full_first_minute = 'f';
        }
        $pricelist->save();

        if ($pricelist->type == 'operator') {
            header('location: index.php?module=voipnew&action=operator_pricelist_edit&pricelist_id=' . $pricelist->id);
        } elseif ($pricelist->type == 'client') {
            header('location: index.php?module=voipnew&action=client_pricelist_edit&pricelist_id=' . $pricelist->id);
        }
        exit;
    }

}