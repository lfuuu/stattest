<?php

namespace app\modules\payments\controllers\api;

use app\classes\HttpClient;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\Bill;
use app\models\ClientAccount;
use app\models\Payment;
use app\modules\payments\models\PaymentStripe;
use kartik\base\Config;
use Yii;
use app\classes\DynamicModel;
use app\classes\ApiController;
use app\classes\validators\AccountIdValidator;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;

/**
 * Class StripeController
 */
class StripeController extends ApiController
{

    const STRIPE_URL = 'https://api.stripe.com/v1/charges';

    private $_moduleConfig = null;

    public function init()
    {
        parent::init();

        $this->_moduleConfig = Config::getModule('payments');
    }

    /**
     * Получение публичного ключа. Со статустом
     *
     * @return array
     * @throws InvalidConfigException
     */
    public function actionGetKeySt()
    {
        try {
            return [
                'status' => 'ok',
                'result' => $this->actionGetKey(),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ];
        }
    }

    /**
     * Получение публичного ключа
     *
     * @return array
     * @throws InvalidConfigException
     */
    public function actionGetKey()
    {
        $config = $this->_moduleConfig->params;

        if (
            !$config['Stripe']['publishable_key']
            || !$config['Stripe']['secret_key']
        ) {
            throw new InvalidConfigException('Stripe not configured');
        }

        return ['key' => $config['Stripe']['publishable_key']];
    }

    /**
     * Проведение платежа. Со статустом
     *
     * @return array
     * @throws InvalidConfigException
     */
    public function actionMakePaymentSt()
    {
        try {
            return [
                'status' => 'ok',
                'result' => $this->actionMakePayment(),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ];
        }
    }

    /**
     * Проведение платежа
     *
     * @return array
     */
    public function actionMakePayment()
    {
        $form = DynamicModel::validateData(
            Yii::$app->request->bodyParams,
            [
                [['account_id', 'token_data', 'currency', 'amount'], 'required'],
                ['account_id', AccountIdValidator::class],
                [['token_data', 'currency', 'description'], 'string'],
                ['amount', 'number', 'min' => 10, 'max' => 999999],
            ]
        );

        if ($form->hasErrors()) {
            $errors = $form->getFirstErrors();
            throw new \InvalidArgumentException(reset($errors));
        }

        $tokenData = json_decode($form->token_data, true);

        if (!$tokenData) {
            throw new \InvalidArgumentException('get token error');
        }

        if ($this->_processCharge($form->account_id, $tokenData, $form->amount, $form->currency)) {
            return ['status' => 'OK'];
        }

        throw new \BadMethodCallException('Unknown error');
    }

    /**
     * Запуск процесса списания с карты
     *
     * @param integer $accountId
     * @param array $tokenData
     * @param float $amount
     * @param string $currency
     * @return bool
     * @throws \Exception
     */
    private function _processCharge($accountId, $tokenData, $amount, $currency)
    {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            $bill = Bill::dao()->createBillOnSum($accountId, $amount, $currency);

            $nowStr = (new \DateTime('now'))->format(DateTimeZoneHelper::DATETIME_FORMAT);

            $payment = new Payment();
            $payment->client_id = $accountId;
            $payment->payment_no = $tokenData['id'];
            $payment->bill_no = $bill->bill_no;
            $payment->bill_vis_no = $bill->bill_no;
            $payment->payment_date = $nowStr;
            $payment->oper_date = $nowStr;
            $payment->payment_rate = 1;
            $payment->type = Payment::TYPE_ECASH;
            $payment->ecash_operator = Payment::ECASH_STRIPE;
            $payment->sum = $amount;
            $payment->currency = $bill->currency;
            $payment->original_sum = $amount;
            $payment->original_currency = $bill->currency;
            $payment->comment = 'Stripe payment #' . $tokenData['id'] . ' at ' . $nowStr;
            $payment->add_date = $nowStr;

            if (!$payment->save()) {
                throw new ModelValidationException($payment);
            }

            $paymentData = $this->_sendRequest(
                $accountId,
                $bill->clientAccountModel->contract->organization->name,
                $tokenData['id'],
                $amount,
                $currency,
                $bill->lines[0]->item
            );

            $paymentStripe = new PaymentStripe();
            $paymentStripe->account_id = $accountId;
            $paymentStripe->sum = $amount;
            $paymentStripe->currency = $bill->currency;
            $paymentStripe->payment_id = $payment->id;
            $paymentStripe->token_id = $paymentData['id'];
            $paymentStripe->token_data = json_encode($tokenData);

            if (!$paymentStripe->save()) {
                throw new ModelValidationException($paymentStripe);
            }

            ClientAccount::dao()->updateBalance($payment->client_id);

            $transaction->commit();
        } catch (\Exception $e) {
            Yii::error($e);
            $transaction->rollBack();

            throw $e;
        }

        return true;
    }

    /**
     * Отправляем запрос на списание с карты
     *
     * @param int $accountId
     * @param string $organizationName
     * @param string $tokenId
     * @param float $amount
     * @param string $currency
     * @param string $description
     * @return array
     */
    private function _sendRequest($accountId, $organizationName, $tokenId, $amount, $currency, $description)
    {
        $req = (new HttpClient())
            ->createRequest()
            ->setMethod('POST')
            ->setUrl(self::STRIPE_URL)
            ->auth([
                'method' => 'basic',
                'user' => $this->_moduleConfig->params['Stripe']['secret_key'],
                'passwd' => ''
            ])
            ->setData([
                'metadata' => [
                    'customer' => $accountId . '|' . $organizationName,
                ],
                'customer_description' => $organizationName,
                'amount' => round($amount * 100),
                'currency' => $currency,
                'description' => $description,
                'source' => $tokenId
            ])
            ->addOptions([CURLOPT_SSLVERSION => 1])
            ->setIsCheckOk(false);

        try {
            $response = $req->send();

            if (!$response->isOk) {
                $responseData = $response->getData();

                if (!isset($responseData['error']['message'])) {
                    throw new \LogicException('Error charge');
                }

                throw new \LogicException($responseData['error']['message']);
            }

            return $response->getData();
        } catch (\Exception $e) {
            Yii::error($e);
        }

        throw new \LogicException('Processing charge error');
    }
}
