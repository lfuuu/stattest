<?php
namespace app\controllers;

use app\classes\Assert;
use app\classes\traits\AddClientAccountFilterTraits;
use app\forms\buh\PaymentAddForm;
use app\forms\buh\PaymentYandexTransfer;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\models\Bill;
use app\models\Payment;
use Yii;
use app\classes\BaseController;

class PaymentController extends BaseController
{
    use AddClientAccountFilterTraits;

    /**
     * @return array
     */
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
                'allow' => true,
                'actions' => ['yandex-transfer'],
                'roles' => ['newaccounts_payments.delete'],
            ],
            [
                'allow' => false,
            ],
        ];
        return $behaviors;
    }

    /**
     * @param int $clientAccountId
     * @param int $billId
     * @return string
     * @throws \yii\base\InvalidParamException
     */
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
        $model->payment_date = date(DateTimeZoneHelper::DATE_FORMAT);
        $model->oper_date = date(DateTimeZoneHelper::DATE_FORMAT);
        $model->payment_rate = 1;

        if ((int)$billId && ($bill = Bill::findOne($billId)) instanceof Bill) {
            $model->original_sum = $bill->sum;
            $model->bill_no = $bill->bill_no;
        }

        return $this->render('add', [
            'model' => $model,
            'client' => $client,
        ]);
    }

    /**
     * @param int $paymentId
     */
    public function actionDelete($paymentId)
    {
        $payment = Payment::findOne($paymentId);
        Assert::isObject($payment);

        $payment->delete();

        ClientAccount::dao()->updateBalance($payment->client_id);

        $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionYandexTransfer()
    {
        $account = $this->_getCurrentClientAccount();
        if (!$account) {
            return '';
        }

        $model = new PaymentYandexTransfer();
        $model->from_client_id = $account->id;

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->transfer()) {
            Yii::$app->session->addFlash('success', 'Платеж перенесен');
        }

        return $this->render(
            'yandex-transfer', [
                'model' => $model
            ]
        );
    }
}