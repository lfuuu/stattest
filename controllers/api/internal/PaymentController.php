<?php

namespace app\controllers\api\internal;

use app\classes\Assert;
use app\classes\validators\BillNoValidator;
use app\classes\validators\FormFieldValidator;
use app\classes\validators\JsonValidator;
use app\classes\validators\PaymentApiAccessCheckerValidator;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\Bill;
use app\models\ClientAccount;
use app\models\Currency;
use app\models\Payment;
use app\models\PaymentApiChannel;
use app\exceptions\web\NotImplementedHttpException;
use app\exceptions\api\internal\ExceptionValidationForm;
use app\classes\ApiInternalController;
use app\classes\DynamicModel;
use app\classes\validators\AccountIdValidator;
use app\models\PaymentApiInfo;

class PaymentController extends ApiInternalController
{
    const UNRECOGNIZED_PAYMENTS_ACCOUONT_ID = 115504;

    /**
     * @throws NotImplementedHttpException
     */
    public function actionIndex()
    {
        throw new NotImplementedHttpException;
    }

    /**
     * @SWG\Post(tags={"Payments"}, path="/internal/payment/add/", summary="Добавление платежа", operationId="Добавление платежа",
     *   @SWG\Parameter(name="access_token", type="string", description="Код доступа к каналу", in="formData", default="", required=true),
     *   @SWG\Parameter(name="channel", type="string", description="Канал платежа", in="formData", default="", required=true),
     *   @SWG\Parameter(name="account_id", type="integer", description="ID ЛС", in="formData", default=""),
     *   @SWG\Parameter(name="payment_no", type="string", description="ID платежа", in="formData", default="", required=true),
     *   @SWG\Parameter(name="sum", type="integer", description="Сумма платежа", in="formData", default="0", required=true),
     *   @SWG\Parameter(name="currency", type="string", description="Валюта платежа", in="formData", default="RUB", required=true),
     *   @SWG\Parameter(name="bill_no", type="string", description="Оплата счета", in="formData", default=""),
     *   @SWG\Parameter(name="info_json", type="string", description="Платежная информация (JSON)", in="formData", default=""),
     *   @SWG\Parameter(name="description", type="string", description="Описание платежа", in="formData", default=""),
     *
     *   @SWG\Response(response=200, description="данные о добавленном платеже",
     *     @SWG\Schema(type="object", required={"id","name","contragents"},
     *       @SWG\Property(property="id", type="integer", description="Идентификатор платежа")
     *     )
     *   ),
     *   @SWG\Response(response="default", description="Ошибки",
     *     @SWG\Schema(ref="#/definitions/error_result")
     *   )
     * )
     */
    public function actionAdd()
    {
        $requestData = $this->requestData;

        $model = DynamicModel::validateData(
            $requestData,
            [
                [['channel', 'access_token'], 'required'],
                ['access_token', PaymentApiAccessCheckerValidator::class],

                [['payment_no', 'sum', 'currency'], 'required'],
                ['currency', 'in', 'range' => Currency::enum()],
                [['bill_no', 'payment_no', 'currency', 'description'], FormFieldValidator::class],
                ['info_json', JsonValidator::class],
                [['sum'], 'number', 'min' => 0.1, 'max' => 15000],
                ['account_id', 'default', 'value' => self::UNRECOGNIZED_PAYMENTS_ACCOUONT_ID],
                ['account_id', AccountIdValidator::class],
                ['bill_no', BillNoValidator::class],
            ]
        );

        if ($model->hasErrors()) {
            throw new ExceptionValidationForm($model);
        }

        if ($model->payment_no) {
            $p = Payment::find()
                ->alias('p')
                ->where([
                    'p.type' => Payment::TYPE_API,
                    'p.ecash_operator' => $model->channel,
                    'p.payment_no' => $model->payment_no,
                ])
                ->exists();

            if ($p) {
                throw new \InvalidArgumentException('Payment No is exists');
            }
        }

        $account = ClientAccount::find()->where(['id' => $model->account_id])->one();
        Assert::isObject($account, 'Account not found');

        $bill = Bill::find()->where(['bill_no' => $model->bill_no, 'client_id' => $model->account_id])->one();

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if (!$bill) {
                $bill = Bill::dao()->getPrepayedBillOnSum($account->id, $model->sum, $model->currency);
            }

            $now = (new \DateTime('now', new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_MOSCOW)));

            $payment = new Payment();
            $payment->client_id = $account->id;
            $payment->payment_no = $model->payment_no;
            $payment->bill_no = $payment->bill_vis_no = $bill->bill_no;
            $payment->sum = $payment->original_sum = $model->sum;
            $payment->currency = $model->currency;

            $payment->payment_date = $payment->oper_date = $now->format(DateTimeZoneHelper::DATE_FORMAT);
            $payment->add_date = $now->format(DateTimeZoneHelper::DATETIME_FORMAT);

            $payment->type = Payment::TYPE_API;
            $payment->ecash_operator = $model->channel;

            $channels = PaymentApiChannel::getList();

            $infoJson =  json_decode($requestData['info_json'] ?? '{}', true);
            $payment->comment = $infoJson['comment'] ?? ucfirst($channels[$model->channel]) . " #" . $model->payment_no . ' (API)';

            if (!$payment->save()) {
                throw new ModelValidationException($payment);
            }

            unset(
                $requestData['channel'],
                $requestData['access_token'],
                $requestData['account_id'],
                $requestData['payment_no'],
                $requestData['sum'],
                $requestData['currency'],
                $requestData['bill_no']
            );

            $paymentInfo = new PaymentApiInfo();
            $paymentInfo->payment_id = $payment->id;
            $paymentInfo->channel = $payment->ecash_operator;
            $paymentInfo->payment_no = $payment->payment_no;
            $paymentInfo->info_json = $requestData['info_json'] ?? '';
            unset($requestData['info_json']);
            $paymentInfo->request = json_encode($requestData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            if (!$paymentInfo->save()) {
                throw new ModelValidationException($paymentInfo);
            }

            $transaction->commit();

            return ['payment_id' => $payment->id];
        } catch (\Exception $e) {
            \Yii::error($e);
            $transaction->rollBack();

            $msg = $e->getMessage();

            if (strpos($msg, 'SQLSTATE') !== false) {
                throw new \InvalidArgumentException('Payment No is exists.');
            }

            throw $e;
        }
    }
}