<?php
namespace app\controllers;

use Yii;
use app\classes\BaseController;
use app\classes\Assert;
use app\classes\documents\DocumentReportFactory;
use app\models\Bill;

class BillController extends BaseController
{

    /**
     * @param string $billNo
     * @param string $docType
     * @param int $isPdf
     * @return string
     * @throws \yii\base\Exception
     */
    public function actionPrint($billNo, $docType = 'bill', $isPdf = 0)
    {
        /** @var Bill $bill */
        $bill = Bill::findOne(['bill_no' => $billNo]);

        Assert::isObject($bill);

        $sendEmail = Yii::$app->request->get('emailed') == 1;
        $report = DocumentReportFactory::me()->getReport($bill, $docType, $sendEmail);

        return $isPdf ? $report->renderAsPDF() : $report->render();
    }

}