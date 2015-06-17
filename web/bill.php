<?php
use app\models\Bill;
use app\classes\documents\DocumentReportFactory;

	define("PATH_TO_ROOT",'../stat/');
	include PATH_TO_ROOT."conf_yii.php";
	if (!($R=udata_decode_arr(get_param_raw('bill')))) return;
    if($R["object"] != "receipt-2-RUB")
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

    if (isset($R['doc_type'])) {
        $bill = Bill::findOne(['bill_no' => $R['bill']]);

        $sendEmail = Yii::$app->request->get('emailed') == 1;
        $report = DocumentReportFactory::me()->getReport($bill, $R['doc_type'], $sendEmail);
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
