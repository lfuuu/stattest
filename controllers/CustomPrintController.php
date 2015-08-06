<?php

namespace app\controllers;

use app\classes\BaseController;
use app\classes\documents\GetBill;
use app\models\ClientAccount;
use app\models\Bill;
use app\models\Trouble;

class CustomPrintController extends BaseController
{

    public function actionPrintClient($id)
    {
        $clientAccount = ClientAccount::findOne($id);

        $this->layout = 'empty';
        return $this->render('client', [
            'account' => $clientAccount,
        ]);
    }

    public function actionPrintShopOrder($id)
    {
        $bill = Bill::findOne(['bill_no' => $id]);
        $billReport =
            (new GetBill)
                ->setBill($bill)
                ->prepare();

        $trouble = Trouble::findOne(['bill_no' => $bill->bill_no]);

        $this->layout = 'empty';
        return $this->render('shop-order', [
            'document' => $billReport,
            'trouble' => $trouble,
        ]);
    }

}