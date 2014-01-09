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

        $res = $pg_db->AllRecords(" select o.region, o.id, o.short_name, o.minimum_payment, o.term_in_cost, o.local_network_id, o.local_network_pricelist_id, o.pricelist_id, o.operator_7800_pricelist_id, o.client_7800_pricelist_id
                                    from voip.operator o
                                    order by o.region desc, o.id ");



        $design->assign('operators', $res);
        $design->assign('pricelists', Pricelist::getListAssoc());
        $design->assign('regions', Region::getListAssoc());
        $design->AddMain('voipnew/operator_list.html');
    }

    public function voipnew_operator_edit()
    {
        global $design;
        $instance_id = get_param_protected('instance_id');
        $operator_id = get_param_protected('operator_id', 0);

        $operator = VoipOperator::getByIdAndInstanceId($operator_id, $instance_id);
        if (!$operator) {
            $operator = new VoipOperator();
            $operator->region = $instance_id;
            $operator->id = $operator_id;
        }

        $design->assign('operator', $operator);
        $design->assign('pricelists', Pricelist::getListAssoc());
        $design->assign('regions', Region::getListAssoc());
        $design->AddMain('voipnew/operator_edit.html');
    }

    public function voipnew_operator_save()
    {
        $instance_id = get_param_protected('instance_id', 0);
        $operator_id = get_param_protected('operator_id', 0);
        $name = get_param_protected('name');
        $shortName = get_param_protected('short_name');
        $minimumPayment = get_param_protected('minimum_payment');
        $termInCost = get_param_protected('term_in_cost');
        $localNetworkPricelistId = get_param_protected('local_network_pricelist_id');
        $defaultPricelistId = get_param_protected('default_pricelist_id');
        $operator7800PricelistId = get_param_protected('operator_7800_pricelist_id');
        $client7800PricelistId = get_param_protected('client_7800_pricelist_id');

        $operator = VoipOperator::getByIdAndInstanceId($operator_id, $instance_id);
        if (!$operator) {
            $operator = new VoipOperator();
            $operator->region = $instance_id;
            $operator->id = $operator_id;
        }

        $operator->name = $name;
        $operator->short_name = $shortName;
        $operator->minimum_payment = $minimumPayment ? $minimumPayment : null;
        $operator->term_in_cost = $termInCost ? $termInCost : 0;
        $operator->default_pricelist_id = $defaultPricelistId ? $defaultPricelistId : null;
        $operator->local_network_pricelist_id = $localNetworkPricelistId ? $localNetworkPricelistId : null;
        $operator->operator_7800_pricelist_id = $operator7800PricelistId ? $operator7800PricelistId : null;
        $operator->client_7800_pricelist_id = $client7800PricelistId ? $client7800PricelistId : null;
        $operator->save();

        header('location: index.php?module=voipnew&action=operator_edit&instance_id=' . $instance_id . '&operator_id=' . $operator_id);
        exit;
    }
}