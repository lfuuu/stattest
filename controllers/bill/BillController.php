<?php
namespace app\controllers\bill;

use app\models\Bill;
use Yii;
use app\classes\BaseController;
use yii\base\Exception;


class BillController extends BaseController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['access']['rules'] = [
            [
                'allow' => true,
                'actions' => [
                    'set-invoice2-date-as-invoice1', 'set-invoice1-rate', 'set-invoice2-rate', 'set-invoice3-rate',
                    'set-bill-rate', 'set-invoice-sum-rub', 'set-bill-sum-rub',
                ],
                'roles' => ['newaccounts_bills.edit'],
            ],
            [
                'allow' => false,
            ],
        ];
        return $behaviors;
    }

    public function actionSetInvoice2DateAsInvoice1($billId, $value)
    {
        $bill = $this->getBillOr404($billId);
        $bill->inv2to1 = $value;
        $bill->save();

        return $this->redirect('?module=newaccounts&action=bill_view&bill=' . $bill->bill_no);
    }

    public function actionSetInvoice1Rate()
    {
        $billId = Yii::$app->request->getBodyParam('billId');
        $rate = Yii::$app->request->getBodyParam('rate');

        $bill = $this->getBillOr404($billId);
        $bill->setRateForInvoice1($rate);
        $bill->save();

        $this->redirect('?module=newaccounts&action=bill_view&bill=' . $bill->bill_no);
    }

    public function actionSetInvoice2Rate()
    {
        $billId = Yii::$app->request->getBodyParam('billId');
        $rate = Yii::$app->request->getBodyParam('rate');

        $bill = $this->getBillOr404($billId);
        $bill->setRateForInvoice2($rate);
        $bill->save();

        $this->redirect('?module=newaccounts&action=bill_view&bill=' . $bill->bill_no);
    }

    public function actionSetInvoice3Rate()
    {
        $billId = Yii::$app->request->getBodyParam('billId');
        $rate = Yii::$app->request->getBodyParam('rate');

        $bill = $this->getBillOr404($billId);
        $bill->setRateForInvoice3($rate);
        $bill->save();

        $this->redirect('?module=newaccounts&action=bill_view&bill=' . $bill->bill_no);
    }

    public function actionSetBillRate()
    {
        $billId = Yii::$app->request->getBodyParam('billId');
        $rate = Yii::$app->request->getBodyParam('rate');

        $bill = $this->getBillOr404($billId);
        $bill->setRateForBill($rate);
        $bill->save();

        $this->redirect('?module=newaccounts&action=bill_view&bill=' . $bill->bill_no);
    }

    public function actionSetInvoiceSumRub()
    {
        $billId = Yii::$app->request->getBodyParam('billId');
        $sumRub = Yii::$app->request->getBodyParam('sumRub');

        $bill = $this->getBillOr404($billId);
        $bill->setSumRubForInvoice($sumRub);
        $bill->save();

        $this->redirect('?module=newaccounts&action=bill_view&bill=' . $bill->bill_no);
    }


    public function actionSetBillSumRub()
    {
        $billId = Yii::$app->request->getBodyParam('billId');
        $sumRub = Yii::$app->request->getBodyParam('sumRub');

        $bill = $this->getBillOr404($billId);
        $bill->setSumRubForBill($sumRub);
        $bill->save();

        $this->redirect('?module=newaccounts&action=bill_view&bill=' . $bill->bill_no);
    }
}