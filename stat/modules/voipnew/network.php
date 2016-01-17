<?php

class m_voipnew_network
{
    public function invoke($method, $arguments)
    {
        if (is_callable(array($this, $method))) return call_user_func_array(array($this, $method), $arguments);
    }

    function voipnew_network_file_upload()
    {
        global $pg_db;

        if (!isset($_FILES['file'])) {
            trigger_error2('Пожалуйста, загрузите файл для обработки');
            return;
        } elseif ($_FILES['file']['error']) {
            trigger_error2('При загрузке файла произошла ошибка. Пожалуйста, попробуйте еще раз ' . $_FILES['file']['error']);
            return;
        }

        $networkConfig = VoipNetworkConfig::find($_POST['network_config_id']);
        $startDate = get_param_protected('startdate');

        $table = null;
        if ($networkConfig->operator_id == 15){
            $table = prices_parser::read_mgts_networks($_FILES['file']['tmp_name']);
            $table = VoipUtils::reducePrefixes($table, 'prefix', array('network_type_id'));
        } elseif ($networkConfig->operator_id == 2){
            $table = prices_parser::read_beeline_networks($_FILES['file']['tmp_name']);
            $table = VoipUtils::reducePrefixes($table, 'prefix', array('network_type_id'));
        } elseif ($networkConfig->operator_id == 6){
            $table = prices_parser::read_mts_networks($_FILES['file']['tmp_name']);
            $table = VoipUtils::reducePrefixes($table, 'prefix', array('network_type_id'));
        }

        if ($table) {
            $networkFile = new VoipNetworkFile();
            $networkFile->network_config_id = $networkConfig->id;
            $networkFile->startdate = $startDate;
            $networkFile->created_at = date('Y-m-d H:i:s', time());
            $networkFile->rows = count($table);
            $networkFile->filename = $_FILES['file']['name'];
            $networkFile->save();

            $query = '';
            foreach ($table as $row) {
                if ($query === '') {
                    $query = "insert into voip.network_file_data(network_file_id, prefix, network_type_id) values ";
                } else {
                    $query .= ',';
                }
                $query .= "('" . $networkFile->id . "','" . pg_escape_string($row['prefix']) . "','" . pg_escape_string($row['network_type_id']) . "')";
            }

            $pg_db->Query($query);

            if ($pg_db->mError) {
                echo $pg_db->mError;
                $networkFile->delete();
                return;
            }
        }

        header('Location: /voip/network-config/files?networkConfigId=' . $networkConfig->id);
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

    public function voipnew_network_file_activatedeactivate()
    {
        $id = get_param_protected('id', 0);

        $file = VoipNetworkFile::find($id);
        if (!$file) {
            trigger_error2('network file #' . $id . ' not found');
        }

        set_time_limit(0);

        if (isset($_POST['activate'])) {
            $file->active = 't';
        } elseif (isset($_POST['deactivate'])) {
            $file->active = 'f';
        }
        $file->save();

        header('location: /voip/network-config/files?networkConfigId=' . $file->network_config_id);
        exit;
    }

    public function voipnew_network_file_change_start_date()
    {
        $id = get_param_protected('id', 0);
        $startDate = get_param_protected('startdate', 0);

        $file = VoipNetworkFile::find($id);
        if (!$file) {
            trigger_error2('network file #' . $id . ' not found');
        }

        $file->startdate = $startDate;
        $file->save();

        header('location: index.php?module=voipnew&action=network_file_show&id=' . $file->id);
        exit;
    }

    public function voipnew_network_price()
    {
        global $pg_db, $design;

        $pricelist_id = get_param_protected('pricelist', '');
        $f_date = get_param_raw('f_date', date('Y-m-d', time()));
        $f_date = pg_escape_string($f_date);



        if ($pricelist_id != '') {

            $query = "
                    select d.defcode, r.date_from, r.date_to, r.price,
                                g.name as destination, d.mob,
                                r.price as price
                    from select_defs_price('$pricelist_id', '$f_date') r
                                                LEFT JOIN voip_destinations d ON r.ndef=d.ndef
                                    LEFT JOIN geo.geo g ON g.id=d.geo_id
                    order by d.defcode";
            $res = $pg_db->AllRecords($query);

            $design->assign('defs', $res);
        }

        $design->assign('regions', Region::getListAssoc());
        $design->assign('pricelists', $pg_db->AllRecords("select r.id, r.name, r.region as instance_id from voip.pricelist r where r.type='network_prices'", 'id'));
        $design->assign('network_types', VoipNetworkType::getListAssoc());

        $design->assign('pricelist_id', $pricelist_id);
        $design->assign('f_date', $f_date);
        $design->AddMain('voipnew/network_price.html');
    }
}