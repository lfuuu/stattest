<?php

use app\models\Bill;
use app\classes\documents\DocumentReportFactory;
use app\classes\documents\DocumentReport;

define("PATH_TO_ROOT", '../stat/');
include PATH_TO_ROOT . "conf_yii.php";
if (!($R = \app\classes\Encrypt::decodeToArray(get_param_raw('bill')))) {
    return;
}

$bill = null;

if (!isset($R["object"]) || $R["object"] != "receipt-2-RUB") {
    if (!$R['client'] || !$R['bill']) {
        return;
    }

    $bill = Bill::findOne(['bill_no' => $R['bill'], 'client_id' => $R['client']]);
    if (!$bill) {
        return;
    }

    $bill->setViewed();
}

$_GET = $R;

$isPdf = isset($R['is_pdf']) && $R['is_pdf'] == 1;
$isEmailed = get_param_raw('emailed', 1);

header('Content-Type: ' . ($isPdf ? 'application/pdf' : 'text/html; charset=utf-8'));

if (
    isset($R['doc_type'])
    || (
        isset($R['object'])
        && strpos($R['object'], 'bill') === 0
    )
) {
    $bill = $bill ?: Bill::findOne(['bill_no' => $R['bill']]);

    $report = DocumentReportFactory::me()->getReport(
        $bill,
        (!isset($R['doc_type']) ? DocumentReport::DOC_TYPE_BILL : $R['doc_type']),
        $isEmailed
    );

    echo $isPdf ? $report->renderAsPDF() : $report->render();
} else {
    global $design;

    $design->assign('dbg', (bool)$_REQUEST['dbg']);
    $design->assign('emailed', $isEmailed);

    \app\classes\StatModule::newaccounts()->newaccounts_bill_print('');

    $design->Process();
}
