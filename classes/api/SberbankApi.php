<?php

namespace app\classes\api;


use app\classes\HttpClient;
use app\exceptions\api\SberbankApiException;
use app\models\ClientAccount;
use app\models\Currency;

class SberbankApi
{
    const ERROR_NO_ERROR = 0;           // все ОК
    const ERROR_ALREADY_REGISTERED = 1; // Заказ с таким номером уже обработан
    const ERROR_PAYED = 2;              // Заказ оплачен

    private $_user = '';
    private $_password = '';
    private $_lkHost = '';

//    private $_host = "3dsec.sberbank.ru";
    private $_host = "securepayments.sberbank.ru";

    /**
     * SberbankApi constructor.
     */
    public function __construct($organizationId)
    {
        if (!isset(\Yii::$app->params['SberbankApi'])) {
            throw new \Exception('SberbankApi not configured');
        }

        if (!$organizationId || !isset(\Yii::$app->params['SberbankApi'][$organizationId])) {
            throw new \Exception("Внесение платежа на ЛС невозможно. (с{$organizationId})");
        }

        if (!isset(\Yii::$app->params['LK_PATH']) || !\Yii::$app->params['LK_PATH']) {
            throw new \Exception('LK path not set');
        }

        $this->_lkHost = \Yii::$app->params['LK_PATH'];

        $config = \Yii::$app->params['SberbankApi'][$organizationId];

        $this->_user = isset($config['user']) ? $config['user'] : '';
        $this->_password = isset($config['password']) ? $config['password'] : '';
    }

    /**
     * Регистрация заказа
     *
     * Пример ответа: {
     *      "orderId": "70906e55-7114-41d6-8332-4609dc6590f4",
     *      "formUrl": "https://server/application_context/merchants/test/payment_ru.html?mdOrder=70906e55-7114-41d6-8332-4609dc6590f4"
     * }
     *
     * @param ClientAccount $account
     * @param string $orderNumber
     * @param float $sum
     * @param bool $isPayPage
     * @param string $returnUrl
     * @param string $failUrl
     * @return array
     * @internal param int $clientId
     * @internal param string $currency
     * @internal param array $options
     */
    public function register(ClientAccount $account, $orderNumber, $sum, $isPayPage = false, $returnUrl = null, $failUrl = null)
    {
        $options = [
            'orderNumber' => $orderNumber,
            'amount' => $sum * 100, // in cents
            'currency' => Currency::getCodeById($account->currency),
            'returnUrl' => $returnUrl ?: ($isPayPage ? $this->_lkHost . 'pay/sbcard' : $this->_lkHost . 'app?#accounts/add_pay/sbcard'),
            'failUrl' => $failUrl ?: ($isPayPage ? $this->_lkHost . 'pay/failed' : $this->_lkHost . 'app?#accounts/add_pay/failed'),
            'description' => 'Prepayment order No #' . $orderNumber,
            'pageView' => 'DESKTOP',
            'clientId' => $account->id,
            'expirationDate' => (new \DateTime())->modify("+1 month")->format(DATE_ATOM),
            'language' => $this->_getLanguageFromAccount($account->id),
        ];

        return $this->_exec('register', $options);
    }

    /**
     * Получение информации о заказе
     *
     * Пример ответа: {
     *      "expiration":     "201512",
     *      "cardholderName": "tr",
     *      "depositAmount":  789789,
     *      "currency":       "810",
     *      "approvalCode":   "123456",
     *      "authCode":       2,
     *      "clientId":       "777",
     *      "bindingId":      "07a90a5d-cc60-4d1b-a9e6-ffd15974a74f",
     *      "ErrorCode":      "0",
     *      "ErrorMessage":   "",
     *      "OrderStatus":    2,
     *      "OrderNumber":    "23asdafaf",
     *      "Pan":            "411111**1111",
     *      "Amount":         789789
     * }
     *
     * @param string $orderId
     * @param int $accountId
     * @return mixed
     * @internal param array $options
     */
    public function getOrderStatus($orderId, $accountId = null)
    {
        $data = [
            'orderId' => $orderId
        ];

        $data['language'] = $this->_getLanguageFromAccount($accountId);

        return $this->_exec('getOrderStatus', $data);
    }

    /**
     * Получение расширеной информации о заказе
     *
     * Пример ответа: {
     *      "errorCode":            "0",
     *      "errorMessage":         "",
     *      "orderNumber":          "0784sse49d0s134567890",
     *      "orderStatus":          6,
     *      "actionCode":           -2007,
     *      "actionCodeDescription":"",
     *      "amount":               33000,
     *      "currency":             "810",
     *      "date":                 1383819429914,
     *      "orderDescription":     "",
     *      "merchantOrderParams":[
     *          {"name":"email","value":"yap"}
     *      ],
     *      "attributes":[
     *          {"name":"mdOrder","value":"b9054496-c65a-4975-9418-1051d101f1b9"}
     *      ],
     *      "cardAuthInfo":{
     *          "expiration":       "201912",
     *          "cardholderName":   "Ivan",
     *          "secureAuthInfo":{
     *              "eci":  6,
     *              "threeDSInfo":{
     *                  "xid":  "MDAwMDAwMDEzODM4MTk0MzAzMjM="
     *              }
     *          },
     *          "pan":  "411111**1111"
     *      },
     *      "terminalId":   "333333"
     *  }
     *
     * @param string  $orderId
     * @param string  $orderNumber
     * @param integer $accountId
     * @return mixed
     * @throws \Exception
     */
    public function getOrderStatusExtended($orderId = "", $orderNumber = "", $accountId = null)
    {
        $data = [];

        if ($orderId) {
            $data['orderId'] = $orderId;
        } else if ($orderNumber) {
            $data['orderNumber'] = $orderNumber;
        }

        if (!$data) {
            throw new \Exception('Order not set');
        }

        $data['language'] = $this->_getLanguageFromAccount($accountId);

        return $this->_exec('getOrderStatusExtended', $data);
    }

    /**
     * Отмена оплаты заказа
     *
     * Пример ответа: {
     *      "errorCode":"0",
     *      "errorMessage":""
     * }
     *
     * @param string $orderId
     * @param int $accountId
     * @return mixed
     */
    public function reverse($orderId, $accountId = null)
    {
        $data = [
            'orderId' => $orderId
        ];

        $data['language'] = $this->_getLanguageFromAccount($accountId);

        return $this->_exec('reverse', $data);
    }

    /**
     * Возврат средств оплаты заказа
     *
     * Пример ответа: {
     *      "errorCode":0
     * }
     *
     * @param string $orderId
     * @param float $amount
     * @param integer $accountId
     * @return mixed
     */
    public function refund($orderId, $amount, $accountId = null)
    {
        $data = [
            'orderId' => $orderId,
            'amount' => $amount * 100 // in cents
        ];

        $data['language'] = $this->_getLanguageFromAccount($accountId);

        return $this->_exec('reverse', $data);
    }

    /**
     * Получение языка по ЛС
     *
     * @param integer $accountId
     * @return string
     */
    private function _getLanguageFromAccount($accountId = null)
    {
        $defaultLang = 'ru';

        if (!$accountId) {
            return $defaultLang;
        }

        try {
            $account = ClientAccount::findOne([
                'id' => $accountId
            ]);

            if (!$account) {
                return $defaultLang;
            }

            $accountLang = $account->contragent->lang_code;

            if (!$accountLang) {
                return $defaultLang;
            }

            list($lang, ) = explode("-", $accountLang);
        } catch(\Exception $e) {
            $lang = $defaultLang;
        }

        return $lang;
    }


    /**
     * Исполнительный механизм
     *
     * @param string $fn
     * @param array $data
     * @param bool $isPostJSON
     * @return mixed
     * @throws \Exception
     */
    private function _exec($fn, $data, $isPostJSON = false)
    {
        $accessData = [
            'userName' => $this->_user,
            'password' => $this->_password
        ];

        $data += $accessData;

        $url = 'https://' . $this->_host . '/payment/rest/' . $fn . '.do';

        $answer = (new HttpClient())
                    ->createRequest()
                    ->setMethod($isPostJSON ? 'post' : 'get')
                    ->setData($data)
                    ->setUrl($url)
                    ->addOptions([CURLOPT_SSLVERSION => 1])
                    ->getResponseDataWithCheck();

        if (isset($answer['errorCode']) && $answer['errorCode']) {
            throw new SberbankApiException($answer['errorMessage'], $answer['errorCode']);
        }

        return $answer;
    }
}