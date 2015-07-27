<?
use app\models\Bill;
use app\models\ClientAccount;
use app\classes\documents\DocumentReportFactory;
use app\classes\documents\DocumentReport;

	//для просмотра клиентами того, что было отправлено через модуль mail

    header('Content-Type: text/html; charset=utf-8');
	define("PATH_TO_ROOT",'../stat/');
	include PATH_TO_ROOT."conf_yii.php";
	$o = MailJob::GetObjectP();
	$db->Query('update mail_object set view_count=view_count+1, view_ts = IF(view_ts=0,NOW(),view_ts) where object_id='.$o['object_id']);

	if (in_array($o["object_type"], array("bill", "assignment", "order", "notice", "invoice","akt", "lading", "new_director_info", "upd", "sogl_mcm_telekom", "notice_mcm_telekom"))) {
        if($o["object_type"] == "assignment" && $o["source"] == 2)
            $o["source"] = 4;
		$R = array();

		$R['bill'] = $o['object_param'];
		$R['obj'] = $o["object_type"];
		$R['source'] = $o["source"];

        if ($R['obj'] == 'bill') {
            $bill = Bill::findOne(['bill_no' => $R['bill']]);

            $report = DocumentReportFactory::me()->getReport($bill, DocumentReport::BILL_DOC_TYPE, $sendEmail = 1);
            echo $report->render();
        } else if ($R['obj'] == "sogl_mcm_telekom" || $R["obj"] == "notice_mcm_telekom") {

            $bill = Bill::findOne(['client_id' => $R['bill']]);

            $report = DocumentReportFactory::me()->getReport($bill, $R['obj']);
            echo $report->renderAsPDF();
        } else {
            $design->assign('emailed',1);
            $_GET = $R;
            \app\classes\StatModule::newaccounts()->newaccounts_bill_print('');
            $design->Process();
        }
	}
?>
