<?php

namespace app\controllers;

use Yii;
use app\models\Bill;
use app\classes\Assert;
use app\classes\BaseController;
use app\classes\documents\DocumentReportFactory;

class DocumentsController extends BaseController
{

    public function actionPrint($bill_no, $doc_type = 'bill', $is_pdf = 0)
    {
        $bill = Bill::findOne(['bill_no' => $bill_no]);

        Assert::isObject($bill);

        $sendEmail = Yii::$app->request->get('emailed') == 1;
        $report = DocumentReportFactory::me()->getReport($bill, $doc_type, $sendEmail);

        if ($is_pdf == 1)
            $report->renderAsPDF();
        else
            echo $report->render($inline_img = false);
    }

    public function actionGetMhtml($bill_no, $doc_type = 'bill')
    {
        $bill = Bill::findOne(['bill_no' => $bill_no]);

        Assert::isObject($bill);

        $report = DocumentReportFactory::me()->getReport($bill, $doc_type, $sendEmail = 0);
        $report->renderAsMhtml();
    }

}