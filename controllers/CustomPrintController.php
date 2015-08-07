<?php

namespace app\controllers;

use app\classes\BaseController;
use app\classes\Assert;
use app\classes\documents\DocumentReportFactory;
use app\models\ClientAccount;
use app\models\Bill;
use app\models\Trouble;
use app\models\UsageVoip;
use app\models\UsageIpPorts;

class CustomPrintController extends BaseController
{

    public function actionPrintClient($id)
    {
        $clientAccount = ClientAccount::findOne($id);

        $services = [];

        $services['voip'] =
            UsageVoip::find()
                ->where(['client' => $clientAccount->client])
                ->orderBy(['status' => SORT_DESC, 'actual_to' => SORT_DESC, 'actual_from' => SORT_ASC])
                ->all();

        $services['ipport'] =
            UsageIpPorts::find()
                ->where(['client' => $clientAccount->client])
                ->orderBy(['status' => SORT_DESC, 'actual_to' => SORT_DESC, 'actual_from' => SORT_ASC])
                ->all();

        $this->layout = 'minimal';
        return $this->render('client', [
            'account' => $clientAccount,
            'services' => $services,
        ]);
    }

    public function actionPrintBill($bill_no)
    {
        $bill = Bill::findOne(['bill_no' => $bill_no]);
        Assert::isObject($bill);
        $report = DocumentReportFactory::me()->getReport($bill, $docType = 'bill', $sendEmail = 0);

        $trouble = Trouble::findOne(['bill_no' => $bill->bill_no]);

        $this->layout = 'minimal';
        return $this->render('bill', [
            'document' => $report,
            'trouble' => $trouble,
        ]);
    }

}