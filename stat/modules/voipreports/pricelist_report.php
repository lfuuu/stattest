<?php

global $report_defs;


class m_voipreports_pricelist_report
{

    public function invoke($method, $arguments)
    {
        if (is_callable(array($this, $method))) return call_user_func_array(array($this, $method), $arguments);
    }

    function voipreports_pricelist_report_show()
    {
        set_time_limit(0);
        session_write_close();

        $report_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        $report = PricelistReport::find($report_id);

        if ($report->report_type_id == PricelistReport::TYPE_ROUTING) {
            $this->module->routing_report_show();
        } elseif ($report->report_type_id == PricelistReport::TYPE_OPERATOR) {
            $this->module->operator_report_show();
        } elseif ($report->report_type_id == PricelistReport::TYPE_ANALYZE) {
            $this->module->analyze_report_show();
        }
    }

    function voipreports_pricelist_report_edit()
    {
        global $design;

        set_time_limit(0);
        session_write_close();

        $report_id = isset($_GET['id']) ? intval($_GET['id']) : 0;


        if ($report_id > 0) {
            $report = PricelistReport::first($report_id);
        } else {
            $report = new PricelistReport();
            $report->report_type_id = isset($_GET['report_type_id']) ? intval($_GET['report_type_id']) : 0;
        }

        $design->assign('pricelists', Pricelist::find('all', array('order'=>'region desc')));

        $fieldsData = array(
            'pricelists' => array(),
            'dates' => array(),
        );

        foreach ($report->getFields() as $field) {
            $fieldsData['pricelists'][] = $field['pricelist_id'];
            $fieldsData['dates'][] = $field['date'] ? $field['date']->format('Y-m-d') : '';
        }

        $design->assign('rep', $report);
        $design->assign('data', json_encode($fieldsData));
        $design->assign('regions', Region::getListAssoc());

        $design->AddMain('voipreports/pricelist_report_edit.html');
    }

    function voipreports_pricelist_report_save()
    {
        $report_id = intval($_POST['report_id']);
        $pricelist_ids = $_POST['pricelist_ids'];
        $dates = $_POST['dates'];
        foreach ($dates as &$date) {
            if (!$date) {
                $date = 'NULL';
            }
        }

        if ($report_id > 0) {
            $report = PricelistReport::first($report_id);
        } else {
            $report = new PricelistReport();
            $report->report_type_id = isset($_POST['report_type_id']) ? intval($_POST['report_type_id']) : 0;
        }

        $report->instance_id = $_POST['instance_id'] ?: null;
        $report->pricelist_ids = $pricelist_ids;
        $report->dates = $dates;
        $report->generated_at = null;
        $report->use_rossvyaz_codes = isset($_POST['use_rossvyaz_codes']) && $_POST['use_rossvyaz_codes'] ? 't' : 'f';
        $report->name = $_POST['name'];
        $report->save();

        header('location: index.php?module=voipreports&action=pricelist_report_edit&id=' . $report->id);
        exit;
    }

    function voipreports_pricelist_report_delete()
    {
        if (isset($_GET['id'])) $report_id = intval($_GET['id']); else $report_id = 0;

        $report = PricelistReport::first($report_id);
        $report_type_id = $report->report_type_id;
        $report->delete();

        header('location: ?module=voipreports&action=pricelist_report_list&report_type_id='.$report_type_id);
        exit;
    }

    function voipreports_pricelist_report_list($type)
    {
        global $design;

        $f_instance_id = isset($_GET['f_instance_id']) ? intval($_GET['f_instance_id']) : 0;

        if (isset($_GET['report_type_id'])) {
            $type = $_GET['report_type_id'];
        }

        $conditions = array(
            'report_type_id' => $type,
        );

        if ($f_instance_id > 0) {
            $conditions['instance_id'] = $f_instance_id;
        }

        $reports = PricelistReport::find('all',
            array(
                'conditions' => $conditions,
                'order' => 'created_at desc',
            )
        );

        $design->assign('f_instance_id', $f_instance_id);
        $design->assign('report_type_id', $type);
        $design->assign('reports', $reports);
        $design->assign('regions', Region::getListAssoc());
        $design->assign('pricelists', Pricelist::getListAssoc());

        $design->AddMain('voipreports/pricelist_report_list.html');
    }

    function voipreports_pricelist_report_routing_list()
    {
        $this->voipreports_pricelist_report_list(PricelistReport::TYPE_ROUTING);
    }

    function voipreports_pricelist_report_operator_list()
    {
        $this->voipreports_pricelist_report_list(PricelistReport::TYPE_OPERATOR);
    }

    function voipreports_pricelist_report_analyze_list()
    {
        $this->voipreports_pricelist_report_list(PricelistReport::TYPE_ANALYZE);
    }

    public function voipreports_calc_volume()
    {
        global $db, $pg_db, $design;

        $report_type = get_param_protected('report_type');
        $report_id = get_param_integer('report_id');
        $region_id = get_param_integer('region_id', 0);
        if ($region_id <= 0) $region_id = null;
        $volume = $pg_db->GetRow("select * from voip.volume_calc_task where report_type='$report_type' and report_id='$report_id'");


        if ($volume && $volume['calc_running'] != 't' && isset($_POST['calc'])) {
            $pg_db->QueryUpdate(
                'voip.volume_calc_task', 'id',
                array(
                    'id' => $volume['id'],
                    'calc_running' => true,
                    'date_from' => $_POST['volume_date_from'],
                    'date_to' => $_POST['volume_date_to'],
                    'region_id' => $region_id,
                )
            );
            if ($pg_db->mError) die($pg_db->mError);
            set_time_limit(60 * 30);
            session_write_close();

            $pg_db->Query("select * from voip.calc_volumes({$volume['id']})");
            if ($pg_db->mError) die($pg_db->mError);
            header("location: index.php?module=voipreports&action=calc_volume&report_type=$report_type&report_id=$report_id");
            exit;
        }

        if (!$volume) {
            if ($report_type == 'routing') {
                if ($pg_db->GetRow("select * from voip.routing_report where id='$report_id'")) {
                    $volume_id =
                        $pg_db->QueryInsert(
                            'voip.volume_calc_task',
                            array(
                                'report_type' => $report_type,
                                'report_id' => $report_id,
                                'region_id' => $region_id,
                            )
                        );
                    if ($pg_db->mError) die($pg_db->mError);
                    $pg_db->QueryUpdate(
                        'voip.routing_report', 'id',
                        array(
                            'id' => $report_id,
                            'volume_calc_task_id' => $volume_id,
                        )
                    );
                    if ($pg_db->mError) die($pg_db->mError);
                }
            } elseif ($report_type == 'analyze_pricelist') {
                if ($pg_db->GetRow("select * from voip.analyze_pricelist_report where id='$report_id'")) {
                    $volume_id = $pg_db->QueryInsert('voip.volume_calc_task', array('report_type' => $report_type, 'report_id' => $report_id));
                    if ($pg_db->mError) die($pg_db->mError);
                    $pg_db->QueryUpdate('voip.analyze_pricelist_report', 'id', array('id' => $report_id, 'volume_calc_task_id' => $volume_id));
                    if ($pg_db->mError) die($pg_db->mError);
                }
            } elseif ($report_type == 'pricelist') {
                if ($rep = $pg_db->GetRow("select * from voip.pricelist_report where id='$report_id'")) {
                    $volume_id =
                        $pg_db->QueryInsert(
                            'voip.volume_calc_task',
                            array(
                                'report_type' => $report_type,
                                'report_id' => $report_id,
                                'region_id' => $rep['instance_id'],
                            )
                        );
                    if ($pg_db->mError) die($pg_db->mError);
                    $pg_db->QueryUpdate(
                        'voip.pricelist_report', 'id',
                        array(
                            'id' => $report_id,
                            'volume_calc_task_id' => $volume_id,
                        )
                    );
                    if ($pg_db->mError) die($pg_db->mError);
                }
            } else {
                die('unknown report type');
            }

            $volume = $pg_db->GetRow("select * from voip.volume_calc_task where report_type='$report_type' and report_id='$report_id'");
        }

        if (!$volume) die('volume calc task not found');

        $design->assign('volume', $volume);
        $design->assign('regions', $db->AllRecords("select id, name from regions order by id desc"));
        $design->AddMain('voipreports/calc_volume.html');

    }
}
