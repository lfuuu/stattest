<?php
use app\models\Bill;
use app\models\ClientAccount;
use app\classes\documents\DocumentReportFactory;
use app\classes\documents\DocumentReport;

//для просмотра клиентами того, что было отправлено через модуль mail

header('Content-Type: text/html; charset=utf-8');
define("PATH_TO_ROOT", '../stat/');
include PATH_TO_ROOT . "conf_yii.php";
$o = MailJob::GetObjectP();


if (isset($o["object_type"]) && $o["object_type"] && in_array($o["object_type"], array(
        "bill",
        "assignment",
        "order",
        "notice",
        "invoice",
        "akt",
        "lading",
        "new_director_info",
        "upd",
        "notice_mcm_telekom",
        "sogl_mcm_telekom",
        "sogl_mcn_telekom"
    ))
) {
    $db->Query('update mail_object set view_count=view_count+1, view_ts = IF(view_ts=0,NOW(),view_ts) where object_id=' . $o['object_id']);

    if ($o["object_type"] == "assignment" && $o["source"] == 2) {
        $o["source"] = 4;
    }
    $R = array();

    $R['bill'] = $o['object_param'];
    $R['obj'] = $o["object_type"];
    $R['source'] = $o["source"];

    if ($R['obj'] === 'bill') {
        $bill = Bill::findOne(['bill_no' => $R['bill']]);

        $report = DocumentReportFactory::me()->getReport($bill, DocumentReport::DOC_TYPE_BILL, $sendEmail = 1);

        if (isset($o['is_pdf']) && $o['is_pdf']) {
            header('Content-Type: application/pdf');
            echo $report->renderAsPDF();
            exit();
        }

        echo  $report->render();
    } else {
        if (in_array($R['obj'], ['notice_mcm_telekom', 'sogl_mcm_telekom', 'sogl_mcn_telekom'])) {
            $bill = Bill::find()->where(['client_id' => $R['bill']])->orderBy(['bill_date' => SORT_DESC])->one();
            $report = DocumentReportFactory::me()->getReport($bill, $R['obj']);
            echo $report->renderAsPDF();
        } else {
            $design->assign('emailed', 1);
            $_GET = $R;
            \app\classes\StatModule::newaccounts()->newaccounts_bill_print('', ['is_pdf' => $o['is_pdf']]);
            $design->Process();
        }
    }
}

