<?
use app\models\Bill;
use app\classes\documents\DocumentsFactory;
use app\classes\documents\DocumentReport;

	//для просмотра клиентами того, что было отправлено через модуль mail

    header('Content-Type: text/html; charset=utf-8');
	define("PATH_TO_ROOT",'../stat/');
	include PATH_TO_ROOT."conf_yii.php";
	$o = MailJob::GetObjectP();
	$db->Query('update mail_object set view_count=view_count+1, view_ts = IF(view_ts=0,NOW(),view_ts) where object_id='.$o['object_id']);

	if (in_array($o["object_type"], array("bill", "assignment", "order", "notice", "invoice","akt", "lading", "new_director_info", "upd"))) {
        if($o["object_type"] == "assignment" && $o["source"] == 2)
            $o["source"] = 4;
		$R = array();

		$R['bill'] = $o['object_param'];
		$R['obj'] = $o["object_type"];
		$R['source'] = $o["source"];

        if ($R['obj'] == 'bill') {
            $bill = Bill::findOne(['bill_no' => $R['bill']]);

            $report = DocumentsFactory::me()->getReport($bill, DocumentReport::BILL_DOC_TYPE, $sendEmail = 1);
            echo $report->render();
        }
        else {
            $design->assign('emailed',1);
            $_GET = $R;
            \app\classes\StatModule::newaccounts()->newaccounts_bill_print('');
            $design->Process();
        }
	}
?>
