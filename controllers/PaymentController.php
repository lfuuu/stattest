<?php
namespace app\controllers;

use app\classes\Assert;
use app\forms\buh\PaymentAddForm;
use app\models\ClientAccount;
use app\models\Bill;
use app\models\Payment;
use Yii;
use app\classes\BaseController;

class PaymentController extends BaseController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['access']['rules'] = [
            [
                'allow' => true,
                'actions' => ['add'],
                'roles' => ['newaccounts_payments.edit'],
            ],
            [
                'allow' => true,
                'actions' => ['delete'],
                'roles' => ['newaccounts_payments.delete'],
            ],
            [
                'allow' => false,
            ],
        ];
        return $behaviors;
    }

    public function actionAdd($clientAccountId, $billId = 0)
    {
        $client = ClientAccount::findOne($clientAccountId);
        Assert::isObject($client);

        $model = new PaymentAddForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            $this->redirect('/?module=newaccounts&action=bill_list');
        }

        $model->client_id = $client->id;
        $model->currency = $client->currency;
        $model->original_currency = $client->currency;
        $model->payment_date = date('Y-m-d');
        $model->oper_date = date('Y-m-d');
        $model->payment_rate = 1;

        if ((int) $billId && ($bill = Bill::findOne($billId)) instanceof Bill) {
            $model->original_sum = $bill->sum;
            $model->bill_no = $bill->bill_no;
        }

        return $this->render('add', [
            'model' => $model,
            'client' => $client,
        ]);
    }

    public function actionDelete($paymentId)
    {
        $payment = Payment::findOne($paymentId);
        Assert::isObject($payment);

        $payment->delete();

        ClientAccount::dao()->updateBalance($payment->client_id);

        $this->redirect(Yii::$app->request->referrer);
    }
}