<?php
/**
 * Печать и просмотр закрывающих документов.
 * (для клиентов, которые оплачивают доставку).
 */

namespace app\controllers\report;

use app\classes\BaseController;
use Yii;
use app\models\Organization;
use app\models\Bill;
use app\models\Invoice;
use app\models\Country;
use app\classes\Encrypt;
use app\classes\documents\DocumentReportFactory;
use app\classes\documents\DocumentReport;
use app\modules\uu\models_light\InvoiceLight;

class ClosingStatementsPrintController extends BaseController
{

    const PRINT_PAGE_LANDSCAPE = '<style>@media print { @page { size: landscape; } }</style>';
    const PRINT_PAGE_PORTRAIT = '<style>@media print { @page { size: portrait; } }</style>';
    const PRINT_PAGE_BREAK = '<div style="page-break-after: always;"></div>';

    /**
     * Форма с параметрами для отчетов.
     *
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionIndex()
    {
        $this->view->title = 'Печать закрывающих документов';

        $organizationId = Yii::$app->request->get('organization_id');
        $isIncludeSignatureStamp = Yii::$app->request->get('include_signature_stamp');

        return $this->render('index', [
            'organizationId' => $organizationId,
            'organizations' => Organization::dao()->getList($isWithEmpty = false),
            'includeSignatureStamp' => $isIncludeSignatureStamp
        ]);
    }

    /**
     * Просмотр.
     *
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionReview()
    {
        $this->view->title = 'Просмотр PDF';

        $organizationId = Yii::$app->request->get('organization_id');
        $isIncludeSignatureStamp = Yii::$app->request->get('include_signature_stamp');

        $dateFrom = date("Y-m-01");
        $dateTo   = date("Y-m-t");

        $url = \Yii::$app->params['SITE_URL'] . 'bill.php?bill=';

        $pdfList = [];

        $invoices = Invoice::getPaymentDeliveryList($organizationId, $dateFrom, $dateTo);

        foreach ($invoices as $invoice) {

            $pdfItem = [
                'name' => $invoice->number,
                'client_name' => $invoice->bill->clientAccount->superClient->name,
            ];

            // Акт
            $pdfList[] = array_merge($pdfItem, [
                'doc_type' => 'Акт',
                'link' => Encrypt::encodePdfLink('act', $invoice, $isIncludeSignatureStamp),
            ]);

            // Счет
            $pdfList[] = array_merge($pdfItem, [
                'name' => $invoice->bill->bill_no,
                'doc_type' => 'Счет',
                'link' => Encrypt::encodePdfLink('bill', $invoice, $isIncludeSignatureStamp),
            ]);

            // Счет-фактура
            $pdfList[] = array_merge($pdfItem, [
                'doc_type' => 'Счет-фактура',
                'link' => Encrypt::encodePdfLink('invoice', $invoice, $isIncludeSignatureStamp),
            ]);

        }

        return $this->render('review', [
            'organizationId' => $organizationId,
            'pdfList' => $pdfList,
            'includeSignatureStamp' => $isIncludeSignatureStamp,
        ]);
    }

    /**
     * Печать.
     *
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionPrint()
    {
        if (!defined('PATH_TO_ROOT')) {
            define("PATH_TO_ROOT", Yii::$app->basePath . '/stat/');
        }
        require_once PATH_TO_ROOT . 'conf.php';
        global $design;

        $dateFrom = date("Y-m-01");
        $dateTo   = date("Y-m-t");

        $organizationId = Yii::$app->request->get('organization_id');
        $isLandscape = (bool) Yii::$app->request->get('is_landscape');
        $isIncludeSignatureStamp = Yii::$app->request->get('include_signature_stamp');

        $print = '';

        $invoices = Invoice::getPaymentDeliveryList($organizationId, $dateFrom, $dateTo);

        $design = new \MySmarty();

        foreach ($invoices as $invoice) {

            // ландщафтная ориентация
            if ($isLandscape) {
                // счет-фактура
                $clientAccount = $invoice->bill->clientAccount;
                $invoiceDocument = (new InvoiceLight($clientAccount));
                $invoiceDocument->setInvoice($invoice);
                $invoiceDocument->setBill($invoice->bill);
                $invoiceDocument->setLanguage(Country::findOne(['code' => Country::RUSSIA])->lang);
                $print .= $invoiceDocument->render(false, $isLandscape, $isIncludeSignatureStamp) . self::PRINT_PAGE_BREAK;

                // конверт
                $print .= Yii::$app->runAction(
                    'document/print-envelope',
                    ['clientId' => $clientAccount->id]
                ) . self::PRINT_PAGE_BREAK;
            }

            // портретная ориентация
            if (!$isLandscape) {
                //акт
                $_GET = [
                    'module' => 'newaccounts',
                    'action' => 'bill_print',
                    'bill' => $invoice->bill_no,
                    'to_print' => 'true',
                    'invoice_id' => $invoice->id,
                ];
                $_GET['object'] = 'akt-' . $invoice->type_id;

                $design->assign('emailed', true);
                $design->assign('include_signature_stamp', $isIncludeSignatureStamp);

                ob_start();
                \app\classes\StatModule::newaccounts()->newaccounts_bill_print('');
                $output = ob_get_contents();
                ob_end_clean();

                $print .= $output . self::PRINT_PAGE_BREAK;

                // счет
                $report = DocumentReportFactory::me()->getReport(
                    $invoice->bill,
                    DocumentReport::DOC_TYPE_BILL,
                    false
                );

                $print .= $report->render(false) . self::PRINT_PAGE_BREAK;
            }

        }

        // вывод на печать
        Yii::$app->response->content = ($isLandscape ? self::PRINT_PAGE_LANDSCAPE : self::PRINT_PAGE_PORTRAIT ) . $print;
        Yii::$app->response->send();
        Yii::$app->end(200);
    }
}
