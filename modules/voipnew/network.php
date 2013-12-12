<?php

class m_voipnew_network
{
    public function invoke($method, $arguments)
    {
        if (is_callable(array($this, $method))) return call_user_func_array(array($this, $method), $arguments);
    }

    function voipnew_network_list()
    {
        global $design;

        $networkConfigs = VoipNetworkConfig::find('all', array('order' => 'instance_id desc, operator_id asc, name asc'));

        $operators = array();
        foreach (VoipOperator::find('all', array('order' => 'region desc, short_name')) as $op)
        {
            if (!isset($operators[$op->id])) {
                $operators[$op->id] = $op->short_name;
            }
        }

        $networksByConfig = array();
        foreach (VoipNetwork::find('all') as $network) {
            if (!isset($networksByConfig[$network->network_config_id])) {
                $networksByConfig[$network->network_config_id] = array();
            }
            $networksByConfig[$network->network_config_id][] = $network;
        }

        $design->assign('networkTypes', VoipNetworkType::getListAssoc());
        $design->assign('networkConfigs', $networkConfigs);
        $design->assign('networksByConfig', $networksByConfig);
        $design->assign('operators', $operators);
        $design->assign('regions', Region::getListAssoc());
        $design->AddMain('voipnew/network_list.html');
    }
}