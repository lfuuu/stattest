<?php
use app\models\Bill;
use app\classes\documents\DocumentReportFactory;
use app\classes\documents\DocumentReport;

    define("PATH_TO_ROOT",'../stat/');
    include PATH_TO_ROOT."conf_yii.php";
    if (!($R=udata_decode_arr(get_param_raw('bill')))) return;
    if(!isset($R["object"]) || $R["object"] != "receipt-2-RUB")
    {
        if (!$R['client'] || !$R['bill']) return;
        if (!$db->QuerySelectRow('newbills',array('bill_no'=>$R['bill'],'client_id'=>$R['client']))) return;
        $db->Query('update newbill_send set state="viewed" where bill_no="'.$R['bill'].'"');
    }
    $_GET=$R;

    if (isset($R['is_pdf']) && $R['is_pdf'] == 1)
    {
        header('Content-Type: application/pdf');
    }else {
        header('Content-Type: text/html; charset=utf-8');
    }

    if (
        isset($R['doc_type'])
        || (
            isset($R['object'])
            && strpos($R['object'], 'bill') === 0
        )
    ) {
        $bill = Bill::findOne(['bill_no' => $R['bill']]);

        $report = DocumentReportFactory::me()->getReport($bill, (!isset($R['doc_type']) ? DocumentReport::BILL_DOC_TYPE : $R['doc_type']), get_param_raw('emailed', 1));
        if (isset($R['is_pdf']) && $R['is_pdf'] == 1)
            $report->renderAsPDF();
        else
            echo $report->render();
    }
    else {
        if(isset($_REQUEST['dbg']))
            $design->assign('dbg',true);
        else
            $design->assign('dbg',false);

        $design->assign('emailed',$v=get_param_raw('emailed',1));

        \app\classes\StatModule::newaccounts()->newaccounts_bill_print('');

        $design->Process();
    }

?>
