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

    function voipnew_network_config_show()
    {
        global $design;

        $networkConfig = VoipNetworkConfig::find($_GET['id']);
        $files =
            VoipNetworkFile::find(
                'all',
                array(
                    'conditions' => array('network_config_id' => $networkConfig->id),
                    'order' => 'startdate desc, created_at desc'
                )
            );

        $design->assign('currentDate', date('Y-m-d', time()));
        $design->assign('files', $files);
        $design->assign('networkConfig', $networkConfig);
        $design->AddMain('voipnew/network_config_show.html');
    }

    function voipnew_network_file_upload()
    {
        global $pg_db;

        if (!isset($_FILES['file'])) {
            trigger_error('Пожалуйста, загрузите файл для обработки');
            return;
        } elseif ($_FILES['file']['error']) {
            trigger_error('При загрузке файла произошла ошибка. Пожалуйста, попробуйте еще раз ' . $_FILES['file']['error']);
            return;
        }

        $networkConfig = VoipNetworkConfig::find($_POST['network_config_id']);
        $startDate = get_param_protected('startdate');

        $table = null;
        if ($networkConfig->operator_id == 15){
            $table = prices_parser::read_mgts_networks($_FILES['file']['tmp_name']);
            $table = VoipUtils::reducePrefixes($table, 'prefix', array('group'));
        }

        if ($table) {
            $networkFile = new VoipNetworkFile();
            $networkFile->network_config_id = $networkConfig->id;
            $networkFile->startdate = $startDate;
            $networkFile->created_at = date('Y-m-d H:i:s', time());
            $networkFile->rows = count($table);
            $networkFile->file_name = $_FILES['file']['name'];
            $networkFile->save();

            $query = '';
            foreach ($table as $row) {
                if ($query === '') {
                    $query = "insert into voip.network_file_data(network_file_id, prefix, network_type_id) values ";
                } else {
                    $query .= ',';
                }
                $query .= "('" . $networkFile->id . "','" . pg_escape_string($row['prefix']) . "','" . pg_escape_string($row['group']) . "')";
            }

            $pg_db->Query($query);

            if ($pg_db->mError) {
                echo $pg_db->mError;
                $networkFile->delete();
                return;
            }
        }

        header('Location: index.php?module=voipnew&action=network_config_show&id=' . $networkConfig->id);
    }

    function voipnew_network_file_show()
    {
        global $design, $pg_db;

        $f_prefix = get_param_protected('f_prefix', '');
        $f_network_type_id = get_param_protected('f_network_type_id', '');

        $networkFile = VoipNetworkFile::find($_GET['id']);
        $networkConfig = $networkFile->config;

        $where = " r.network_file_id = {$networkFile->id} ";
        if ($f_prefix) {
            $where .= " and r.prefix like '{$f_prefix}%' ";
        }
        if ($f_network_type_id) {
            $where .= " and r.network_type_id = '{$f_network_type_id}' ";
        }

        $query = "
                    select r.prefix, r.network_type_id
                    from voip.network_file_data r
                    where {$where}
                    order by r.prefix
                    limit 500";
        $prefixes = $pg_db->AllRecords($query);

        $design->assign('file', $networkFile);
        $design->assign('networkConfig', $networkConfig);
        $design->assign('prefixes', $prefixes);
        $design->assign('f_prefix', $f_prefix);
        $design->assign('f_network_type_id', $f_network_type_id);
        $design->assign('network_types', VoipNetworkType::getListAssoc());
        $design->AddMain('voipnew/network_file_show.html');
    }
}