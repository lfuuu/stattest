<?php

namespace app\classes\api;

use app\classes\HttpClient;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\models\Currency;
use app\models\Payment;
use app\models\PaypalPayment;
use app\models\Bill;
use Yii;
use yii\base\InvalidParamException;

class PayPal
{
    /**
     * Данные API
     * @var array
     */
    private $_credentials = [];

    private $_defaultCredentials = [];

    private $_allConfig = [];

    protected $_requestParams = [];

    private $_minimums = [
        Currency::HUF => 1000,
        Currency::EUR => 10,
        Currency::USD => 10,
    ];

    private $_successStatuses = ["Success", "SuccessWithWarning"];

    /**
     * Указываем, куда будет отправляться запрос
     * Реальные условия - https://api-3t.paypal.com/nvp
     * Песочница - https://api-3t.sandbox.paypal.com/nvp
     * @var string
     */
    //protected $_endPoint = 'https://api-3t.sandbox.paypal.com/nvp';
    protected $_endPoint = 'https://api-3t.paypal.com/nvp';

    /**
     * Версия API
     * @var string
     */
    protected $_version = '93';

    /**
     * PayPal constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        if (!isset(Yii::$app->params['PayPal']) || !isset(Yii::$app->params['PayPal']['default'])) {
            throw new \Exception('PayPal not configured');
        }

        $default = Yii::$app->params['PayPal']['default'];

        if (!isset($default['user']) || !isset($default['password']) || !isset($default['signature'])) {
            throw new \Exception('PayPal default organization not configured');
        }

        $this->_defaultCredentials = [
            'USER' => $default['user'],
            'PWD' => $default['password'],
            'SIGNATURE' => $default['signature'],
        ];

        $this->_allConfig = Yii::$app->params['PayPal'];

        unset($this->_allConfig['default']);
    }

    /**
     * Устанавливаем данные для доступа
     *
     * @param $accountId
     * @throws \Exception
     */
    private function _loadConfigByAccountId($accountId)
    {
        $account = ClientAccount::findOne(['id' => $accountId]);

        if (!$account) {
            throw new \Exception("data_error");
        }

        $organizationId = $account->contract->organization_id;

        if (isset($this->_allConfig[$organizationId])) {
            $orgConfig = $this->_allConfig[$organizationId];
            $this->_credentials = [
                'USER' => $orgConfig['user'],
                'PWD' => $orgConfig['password'],
                'SIGNATURE' => $orgConfig['signature'],
            ];

            return;
        }

        $this->_credentials = $this->_defaultCredentials;
    }

    /**
     * Проверяем сумму платежа
     *
     * @param string $currency
     * @param float $sum
     * @throws InvalidParamException
     */
    private function _checkSum($currency, $sum)
    {
        if (!isset($this->_minimums[$currency]) || ($sum < $this->_minimums[$currency])) {
            throw new InvalidParamException("data_error");
        }
    }

    /**
     * Устанавливаем host для формирования обратных ссылок
     *
     * @param string $host
     */
    public function setHost($host)
    {
        $this->_requestParams = [
            'RETURNURL' => 'https://' . $host . '/lk/app?#accounts/add_pay/paypal?',
            'CANCELURL' => 'https://' . $host . '/lk/app?#accounts/add_pay/failed',
        ];
    }

    /**
     * Формируем массив с параметрами платежа
     *
     * @param float $sum
     * @param string $currency
     * @return array
     */
    private function _getOrderParams($sum, $currency)
    {
        return [
            'PAYMENTREQUEST_0_PAYMENTACTION' => 'SALE',
            'PAYMENTREQUEST_0_AMT' => $sum,
            'PAYMENTREQUEST_0_CURRENCYCODE' => $currency
        ];
    }

    /**
     * Получение токена платежа
     *
     * @param integer $accountId
     * @param float $sum
     * @param string $currency
     * @param string $lang
     * @return string
     * @throws ModelValidationException
     */
    public function getPaymentToken($accountId, $sum, $currency, $lang)
    {
        $this->_loadConfigByAccountId($accountId);
        $this->_checkSum($currency, $sum);

        $descr = Yii::t(
            'biller',
            'Replenishment of the account {account} for the amount of {sum} {currency}',
            [
                'account' => $accountId,
                'sum' => $sum,
                'currency' => Yii::t('biller', $currency, [], $lang)
            ],
            $lang
        );

        $response = $this->request('SetExpressCheckout',
            $this->_requestParams +
            $this->_getOrderParams($sum, $currency) +
            ['PAYMENTREQUEST_0_DESC' => $descr]
        );

        Yii::info("Paypal token request: account: " . $accountId . ", sum: " . $sum . " " . $currency . ":: (" . $lang . ") " . print_r($response + $this->_requestParams, true));

        if (!$this->_isSuccessResponse($response)) {
            throw new \LogicException("get token error");
        }

        $pay = new PaypalPayment();
        $pay->token = $response["TOKEN"];
        $pay->sum = $sum;
        $pay->currency = $currency;
        $pay->client_id = $accountId;
        $pay->data1 = json_encode($response);
        $pay->data3 = json_encode($this->_requestParams);
        if (!$pay->save()) {
            throw new ModelValidationException($pay);
        }

        return $response["TOKEN"];
    }

    /**
     * Применение платежа
     *
     * @param string $token
     * @param string $payerId
     * @return string
     * @throws ModelValidationException
     * @throws \Exception
     */
    public function paymentApply($token, $payerId)
    {
        $pay = PaypalPayment::findOne(["token" => $token]);

        if (!$pay) {
            throw new \Exception("data_error");
        }

        $this->_loadConfigByAccountId($pay->client_id);

        $pay->payer_id = $payerId;

        $response = $this->request('GetExpressCheckoutDetails', ["TOKEN" => $token]);

        $pay->data2 = json_encode($response);
        if (!$pay->save()) {
            throw new ModelValidationException($pay);
        }


        Yii::info("Paypal detail request: token: " . $token . ", payerId: " . $payerId . ":: " . print_r($response, true));


        if (!$this->_isSuccessResponse($response)) {
            return "data_error";
        }

        $response = $this->request('DoExpressCheckoutPayment',
            ["TOKEN" => $token, "PAYERID" => $payerId] +
            $this->_getOrderParams($pay->sum, $pay->currency)
        );

        $pay->data3 = json_encode($response);
        if (!$pay->save()) {
            throw new ModelValidationException($pay);
        }

        Yii::info("Paypal checkout request: token: " . $token . ", payerId: " . $payerId . ":: " . print_r($response, true));

        if (!$this->_isSuccessResponse($response)) {
            return "data_error";
        }

        $paymentId = $response["PAYMENTINFO_0_TRANSACTIONID"];
        $pay->payment_id = $paymentId;
        if (!$pay->save()) {
            throw new ModelValidationException($pay);
        }

        $now = (new \DateTime())->format(DateTimeZoneHelper::DATETIME_FORMAT);

        $b = Bill::dao()->getPrepayedBillOnSum($pay->client_id, $pay->sum, $pay->currency);

        if (Payment::find()->where(['payment_no' => $paymentId])->one()) {
            return 'ok';
        }

        $payment = new Payment();
        $payment->client_id = $pay->client_id;
        $payment->bill_no = $b ? $b->bill_no : "";
        $payment->bill_vis_no = $b ? $b->bill_no : "";
        $payment->payment_no = $paymentId;
        $payment->oper_date = $now;

        $payment->payment_date = str_replace(["T", "Z"], [" ", ""], $response["PAYMENTINFO_0_ORDERTIME"]);

        $payment->add_date = $now;
        $payment->type = 'ecash';
        $payment->ecash_operator = 'paypal';
        $payment->sum = $response["PAYMENTINFO_0_AMT"];
        $payment->currency = $response["PAYMENTINFO_0_CURRENCYCODE"];
        $payment->payment_rate = 1;
        $payment->original_sum = $response["PAYMENTINFO_0_AMT"];
        $payment->original_currency = $response["PAYMENTINFO_0_CURRENCYCODE"];
        $payment->comment = "PayPal pay# " . $paymentId . " at " . $now;
        if (!$payment->save()) {
            throw new ModelValidationException($payment);
        }

        return "ok";
    }


    /**
     * Формируем запрос
     *
     * @param string $method Данные о вызываемом методе перевода
     * @param array $params Дополнительные параметры
     * @return array|bool
     */
    public function request($method, $params = [])
    {
        if (empty($method)) {
            throw new InvalidParamException("Не указан метод");
        }

        $requestParams = [
                'METHOD' => $method,
                'VERSION' => $this->_version
            ] + $this->_credentials + $params;

        $response = (new HttpClient())
            ->createRequest()
            ->addOptions([CURLOPT_SSLVERSION => 1])
            ->setUrl($this->_endPoint)
            ->setMethod('post')
            ->setData($requestParams)
            ->send();

        return $response->getData();

    }

    /**
     * Успешный ли ответ
     *
     * @param array $response
     * @return bool
     */
    private function _isSuccessResponse($response)
    {
        return $response &&
            is_array($response) &&
            isset($response["ACK"]) &&
            in_array($response["ACK"], $this->_successStatuses);
    }
}

