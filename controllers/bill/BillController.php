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
                    'set-invoice2-date-as-invoice1',
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
}