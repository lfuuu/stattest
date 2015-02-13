<?php
namespace app\controllers;

use app\classes\Assert;
use app\forms\buh\PaymentAddForm;
use app\models\ClientAccount;
use app\models\Currency;
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

    public function actionAdd($clientAccountId, $currencyId = null)
    {
        $client = ClientAccount::findOne($clientAccountId);
        Assert::isObject($client);

        if ($currencyId === null) {
            $currencyId = $client->currency;
        }

        $currency = Currency::findOne($currencyId);
        Assert::isObject($currency);

        $model = new PaymentAddForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            $this->redirect('/?module=newaccounts&action=bill_list');
        }

        $model->client_id = $client->id;
        $model->currency = $client->currency;
        $model->original_currency = $currency->id;
        $model->payment_date = date('Y-m-d');
        $model->oper_date = date('Y-m-d');
        if ($model->currency == $model->original_currency) {
            $model->payment_rate = 1;
        } else {
            $model->payment_rate = 65;
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

        if (!$payment->delete()) {
            var_dump($payment->getErrors());
            die();
        };

        ClientAccount::dao()->updateBalance($payment->client_id);

        $this->redirect(Yii::$app->request->referrer);
    }
}