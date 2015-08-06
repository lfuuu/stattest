<?php
/*
4268 0337 0354 5624
*/
use app\classes\Event;

class YandexProcessor
{
    private $shopPassword = "DnI1N7mjQ19GMOPy1k6X";
    private $shopId = "15339";

    private $allField = array(
            "shopSumBankPaycash", "requestDatetime", "merchant_order_id", "customerNumber", "sumCurrency", 
            "cdd_pan_mask", "shopSumAmount", "shopSumCurrencyPaycash", "ErrorTemplate", "orderSumAmount", 
            "shn", "shopId", "action", "shopArticleId", "orderSumCurrencyPaycash", "skr_sum", "orderSumBankPaycash", 
            "external_id", "invoiceId", "paymentType", "cdd_rrn", "orderCreatedDatetime", "paymentPayerCode", 
            "rebillingOn", "depositNumber", "BuyButton", "yandexPaymentId", "skr_env", "SuccessTemplate", 
            "cps_region_id", "md5", "cps-source", "requestid", "cdd_auth_code", "scid", "paymentDatetime");

    private $data = array();

    public function proccessRequest()
    {
        $message = "";
        $code = 0;
        $response = "checkOrderResponse";

        try{
            $this->loadData();

            $this->checkData();
            $this->checkSign();
            $this->checkOrder();

            if ($this->data["action"] == "paymentAviso")
            {
                $response = "paymentAvisoResponse";
                $this->paymentAviso();
            }
        }catch(Exception $e)
        {
            $code = $e->getCode();
            $message = $e->getMessage();
        }

        echo '<?xml version="1.0" encoding="UTF-8"?>'.
        '<'.$response.' performedDatetime="'.date("Y-m-d").'T'.date("H:i:s").'.000+04:00" code="'.$code.'" invoiceId="'.$this->data["invoiceId"].'" shopId="'.$this->shopId.'"'.($message ? ' message="'.$message.'"' : '').'/>';
    }

    private function loadData()
    {
        //global $_p;

        foreach($this->allField as $field)
        {
            if (isset($_POST[$field]))
                $this->data[$field] = $_POST[$field];
        }
    }

    private function checkData()
    {
        $requiredFields = array("action", "orderSumAmount", "orderSumCurrencyPaycash", "orderSumBankPaycash", "shopId", "invoiceId", "customerNumber", "md5");

        $isError = false;

        foreach($requiredFields as $field)
        {
            if (!isset($this->data[$field]))
            {
                $isError = true;
                break;
            }
        }

        if (!$isError && !in_array($this->data["action"], array("checkOrder", "paymentAviso")))
            $isError = true;

        if (!$isError && $this->data["shopId"] != $this->shopId)
            $isError = true;

        if ($isError)
            throw new Exception("Ошибка разбора запроса", 200);
    }

    private function checkSign()
    {
        $fs = array();
        $signFields = array("action", "orderSumAmount", "orderSumCurrencyPaycash", "orderSumBankPaycash", "shopId", "invoiceId", "customerNumber");
        foreach($signFields as $fields)
        {
            $fs[] = $this->data[$fields];
        }

        $md5_1 = strtolower(md5(implode(";", $fs).";".$this->shopPassword));
        $md5_2 = strtolower($this->data["md5"]);

        if ($md5_1 != $md5_2)
            throw new Exception("Ошибка авторизации", 1);
    }

    private function checkOrder()
    {
        $this->data["orderSumAmount"] = (float)@floatval($this->data["orderSumAmount"]);

        if($this->data["orderSumAmount"] > 15000 || $this->data["orderSumAmount"] < 10)
            throw new Exception("Отказ в приеме перевода (bad amount)", 100);


        if(!$this->data["customerNumber"] || !preg_match("/^\d{1,13}$/", $this->data["customerNumber"]))
            throw new Exception("Отказ в приеме перевода (bad customer number)", 100);

        $c = $this->getClientByCustomerNumber($this->data["customerNumber"]);
        if(!$c)
            throw new Exception("Отказ в приеме перевода (customer not found)", 100);

    }

    private function paymentAviso()
    {
        //already added
        if(Payment::find_by_payment_no($this->data["invoiceId"]))
        {
            return true;
        }


        $paymentDate = new ActiveRecord\DateTime($this->data["paymentDatetime"]);
        $paymentDateFull = $paymentDate->format("Y-m-d H:i:s");
        $paymentDate = $paymentDate->format("Y-m-d");

        $client = $this->getClientByCustomerNumber($this->data["customerNumber"]);


        $objNow = new ActiveRecord\DateTime();
        $now = $objNow->format("db");

        $b = NewBill::getLastUnpayedBill($client->id);

        if (!$b)
            $b = NewBill::createBillOnPay($client->id, $this->data["orderSumAmount"]);

        $payment = new \app\models\Payment();
        $payment->client_id = $client->id;
        $payment->bill_no = $b ? $b->bill_no : "";
        $payment->bill_vis_no = $b ? $b->bill_no : "";
        $payment->payment_no = $this->data["invoiceId"];
        $payment->oper_date = $now;
        $payment->payment_date = $paymentDate;
        $payment->add_date = $now;
        $payment->type='ecash';
        $payment->ecash_operator='yandex';
        $payment->sum = $this->data["orderSumAmount"];
        $payment->currency = "RUB";
        $payment->payment_rate = 1;
        $payment->original_sum = $this->data["orderSumAmount"];
        $payment->original_currency = "RUB";
        $payment->comment = "Yandex pay# ".$this->data["invoiceId"]." at ".$paymentDateFull;
        $payment->save();

        Event::go("yandex_payment", array("client_id" => $client->id, "payment_id" => $payment->id)); // for start update balance

        return true;
    }

    public function getClientByCustomerNumber($customerNumber)
    {
        $c = \app\models\ClientAccount::findOne($customerNumber);

        if (!$c)
        {
            $usage = self::getActualUsageVoip($customerNumber);

            if (!$usage)
                $usage = self::getActualUsageVoip("7".$customerNumber);

            if (!$usage)
            {
                $_customerNumber = $customerNumber;
                $_customerNumber[0] = "7";

                $usage = self::getActualUsageVoip($_customerNumber);
            }

            if ($usage)
            {
                $c = \app\models\ClientAccount::findOne(['client' => $usage->client]);
            }
        }

        if ($c && in_array($c->status, array("income", "work", "connecting", "testing", "debt", "negotiations")))
        {
            return $c;
        } else {
            return null;
        }
    }

    private function getActualUsageVoip($number)
    {
        return UsageVoip::first(array("conditions" => array("E164 = ? and cast(now() as date) between actual_from and actual_to", $number)));
    }
}

