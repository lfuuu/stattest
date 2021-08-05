<?php

use app\classes\Encrypt;
use app\classes\Html2Pdf;
use app\models\Bill;
use app\classes\documents\DocumentReportFactory;
use app\classes\documents\DocumentReport;
use app\models\ClientAccount;
use app\models\Country;
use app\models\Invoice;
use app\modules\uu\models_light\InvoiceLight;
use yii\web\Response;

define("PATH_TO_ROOT", '../stat/');
include PATH_TO_ROOT . "conf_yii.php";

$billStr = get_param_raw('bill');
if (!($R = Encrypt::decodeToArray($billStr))) {
    return;
}

$bill = null;

if (!isset($R['tpl1']) && (!isset($R["object"]) || $R["object"] != "receipt-2-RUB")) {
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
    isset($R['invoice_id'])
    && ($invoice = Invoice::findOne(['id' => $R['invoice_id']]))
    && ($invoice->bill->clientAccountModel->contragent->country_id == Country::RUSSIA)
) {
    $_GET = [
        'module' => 'newaccounts',
        'action' => 'bill_print',
        'bill' => $invoice->bill_no,
        'object' => 'invoice2',
        'to_print' => 'true',
        'invoice_id' => $invoice->id,
        'is_pdf' => $isPdf
    ];

    global $design;
    $design->assign('emailed', true);
    \app\classes\StatModule::newaccounts()->newaccounts_bill_print('');

    $design->Process();
    exit;
}

if (isset($R['tpl1']) && $R['tpl1'] == 1) {

    if (!isset($R['invoice_id']) || !isset($R['client'])) {
        return;
    }

    $invoice = Invoice::findOne(['id' => $R['invoice_id']]);

    if (
        !$invoice
        || !($bill = $invoice->bill)
        || !($clientAccount = $bill->clientAccount)
        || $bill->client_id != $R['client']
    ) {
        return;
    }

    $clientAccount = $invoice->bill->clientAccount;
    $invoiceDocument = (new InvoiceLight($clientAccount));
    $invoiceDocument->setInvoice($invoice);
    $invoiceDocument->setBill($bill);
    $invoiceDocument->setLanguage(Country::findOne(['code' => $clientAccount->getUuCountryId() ?: Country::RUSSIA])->lang);

    $generator = new Html2Pdf();
    $generator->html = $invoiceDocument->render();
    $pdfContent = $generator->pdf;


    $attachmentName = $clientAccount->id . '-' . $invoice->number . '.pdf';

    Yii::$app->response->format = Response::FORMAT_RAW;
    Yii::$app->response->content = $pdfContent;
    Yii::$app->response->setDownloadHeaders($attachmentName, 'application/pdf', true);

    \Yii::$app->end();
}

// 'tpl1' => 2,
if (
    isset($R['doc_type'])
    || (
        isset($R['object'])
        && strpos($R['object'], 'bill') === 0
    )
) {
    if (isset($R['doc_type']) && $R['doc_type'] == DocumentReport::DOC_TYPE_CURRENT_STATEMENT) {
        $mainDocument = ClientAccount::findOne(['id' => $R['client']]);
    } else {
        $mainDocument = $bill ?: Bill::findOne(['bill_no' => $R['bill']]);
    }

    $report = DocumentReportFactory::me()->getReport(
        $mainDocument,
        (!isset($R['doc_type']) ? DocumentReport::DOC_TYPE_BILL : $R['doc_type']),
        $isEmailed
    );

    echo $isPdf ? $report->renderAsPDF() : $report->render();
} else {
    global $design;

    $design->assign('dbg', isset($_REQUEST['dbg']) && (bool)$_REQUEST['dbg']);
    $design->assign('emailed', $isEmailed);

    \app\classes\StatModule::newaccounts()->newaccounts_bill_print('');

    $design->Process();
}
