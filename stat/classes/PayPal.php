<?php
use app\models\PaypalPayment;

class PayPal {
   /**
    * Данные API
    * @var array
    */
   protected $_credentials = array(
      'USER' => '',
      'PWD' => '',
      'SIGNATURE' => ''
  );

   protected $_requestParams = [];

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

   public function __construct()
   {
       if (
           !defined("paypal_user") || !paypal_user || 
           !defined("paypal_password") || !paypal_password ||
           !defined("paypal_signature") || !paypal_signature
       )
       {
           throw new Exception("Не заданы настройки PayPal");
       }

       $this -> _credentials["USER"] = paypal_user;
       $this -> _credentials["PWD"] = paypal_password;
       $this -> _credentials["SIGNATURE"] = paypal_signature;
   }

   public function setHost($host)
   {
       $this->makeRequestParam($host);
   }

   private function makeRequestParam($host)
   {
       $this -> _requestParams = array(
           'RETURNURL' => 'https://' . $host . '/lk/app?#accounts/add_pay/paypal?',
           'CANCELURL' => 'https://' . $host . '/lk/app?#accounts/add_pay/failed',
       );
   }

   private function _getOrderParams($sum, $currency)
   {
       return [
           'PAYMENTREQUEST_0_PAYMENTACTION' => 'SALE',
           'PAYMENTREQUEST_0_AMT' => $sum,
           'PAYMENTREQUEST_0_CURRENCYCODE' => $currency
           ];
   }

   public function getPaymentToken($accountId, $sum, $currency, $lang)
   {
       $descr = \Yii::t(
               'biller', 'paypal_payment_description',
               [
                   'account' => $accountId, 
                   'sum' => $sum,
                   'currency' => \Yii::t('biller', $currency, [], $lang)
               ], $lang);


       $response = $this -> request('SetExpressCheckout', 
           $this -> _requestParams + 
           $this -> _getOrderParams($sum, $currency) +
           ['PAYMENTREQUEST_0_DESC' => $descr]
       );

       Yii::info("Paypal token request: account: ".$accountId.", sum: ".$sum." ".$currency.":: (".$lang.") ".print_r($response + $this->_requestParams, true));

       if (
           $response && 
           is_array($response) && 
           isset($response["ACK"]) && 
           in_array($response["ACK"], ["Success", "SuccessWithWarning"])
       )
       {
           $pay = new PaypalPayment();
           $pay->token = $response["TOKEN"];
           $pay->sum = $sum;
           $pay->currency = $currency;
           $pay->client_id = $accountId;
           $pay->data1 = json_encode($response);
           $pay->data3 = json_encode($this->_requestParams);
           $pay->save();

           return $response["TOKEN"];
       } else {
           throw new Exception("get token error");
       }
   }

   public function paymentApply($token, $payerId)
   {
       $pay = PaypalPayment::findOne(["token" => $token]);

       if (!$pay)
           throw new Exception("data_error");

       $pay -> payer_id = $payerId;

       $response = $this -> request('GetExpressCheckoutDetails',["TOKEN" => $token]);

       $pay->data2 = json_encode($response);
       $pay->save();


       Yii::info("Paypal detail request: token: ".$token.", payerId: ".$payerId.":: ".print_r($response, true));


       if (
           $response && 
           is_array($response) && 
           isset($response["ACK"]) && 
           in_array($response["ACK"], ["Success", "SuccessWithWarning"])
       )
       {
           $response = $this -> request('DoExpressCheckoutPayment',
               ["TOKEN" => $token, "PAYERID" => $payerId] + 
               $this -> _getOrderParams($pay->sum, $pay->currency)
           );

           $pay->data3 = json_encode($response);
           $pay->save();

           Yii::info("Paypal checkout request: token: ".$token.", payerId: ".$payerId.":: ".print_r($response, true));

           if (
               $response && 
               is_array($response) && 
               isset($response["ACK"]) && 
               in_array($response["ACK"], ["Success", "SuccessWithWarning"])
           )
           {
               $paymentId = $response["PAYMENTINFO_0_TRANSACTIONID"];
               $pay->payment_id = $paymentId;
               $pay->save();

               $objNow = new ActiveRecord\DateTime();
               $now = $objNow->format("db");

               $b = NewBill::getLastUnpayedBill($pay->client_id);

               if (!$b)
                   $b = NewBill::createBillOnPay($pay->client_id, $pay->sum);

               $payment = new \app\models\Payment();
               $payment->client_id = $pay->client_id;
               $payment->bill_no = $b ? $b->bill_no : "";
               $payment->bill_vis_no = $b ? $b->bill_no : "";
               $payment->payment_no = $paymentId;
               $payment->oper_date = $now;

               $payment->payment_date = str_replace(["T", "Z"], [" ", ""], $response["PAYMENTINFO_0_ORDERTIME"]);

               $payment->add_date = $now;
               $payment->type='ecash';
               $payment->ecash_operator='paypal';
               $payment->sum = $response["PAYMENTINFO_0_AMT"];
               $payment->currency = $response["PAYMENTINFO_0_CURRENCYCODE"];
               $payment->payment_rate = 1;
               $payment->original_sum = $response["PAYMENTINFO_0_AMT"];
               $payment->original_currency = $response["PAYMENTINFO_0_CURRENCYCODE"];
               $payment->comment = "PayPal pay# ".$paymentId." at ".$now;
               $payment->save();

               return "ok";
           }
       }
       return "data_error";
   }



   /**
    * Сформировываем запрос
    *
    * @param string $method Данные о вызываемом методе перевода
    * @param array $params Дополнительные параметры
    * @return array / boolean Response array / boolean false on failure
    */
   public function request($method, $params = [])
   {
      $this -> _errors = array();
      if( empty($method) ) { // Проверяем, указан ли способ платежа
         throw new Exception("Не указан метод перевода средств");
      }

      // Параметры нашего запроса
      $requestParams = array(
         'METHOD' => $method,
         'VERSION' => $this -> _version
      ) + $this -> _credentials;

      // Сформировываем данные для NVP
      $request = http_build_query($requestParams + $params);

      // Настраиваем cURL
      $curlOptions = array (
         CURLOPT_URL => $this -> _endPoint,
         CURLOPT_VERBOSE => 1,
         CURLOPT_SSL_VERIFYPEER => true,
         CURLOPT_SSL_VERIFYHOST => 2,
         //CURLOPT_CAINFO => dirname(__FILE__) . '/cacert.pem', // Файл сертификата
         CURLOPT_RETURNTRANSFER => 1,
         CURLOPT_POST => 1,
         CURLOPT_POSTFIELDS => $request
     );

      $ch = curl_init();
      curl_setopt_array($ch,$curlOptions);

      // Отправляем наш запрос, $response будет содержать ответ от API
      $response = curl_exec($ch);

      // Проверяем, нету ли ошибок в инициализации cURL
      if (curl_errno($ch)) {
         $this -> _errors = curl_error($ch);
         curl_close($ch);
         return false;
      } else  {
         curl_close($ch);
         $responseArray = array();
         parse_str($response,$responseArray); // Разбиваем данные, полученные от NVP в массив
         return $responseArray;
      }
   }
}
