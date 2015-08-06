<?php
use app\classes\Event;

use \app\models\ClientAccount;

class CyberPlatProcessor
{
    // request fields
    private $data = array();
    private $actionChecker = null;
    private $crypter = null;

    public function proccessRequest()
    {
        try{
            $this->do_action(get_param_raw("action"));
        }catch(CyberplatError $e)
        {
            $this->echoError($e);
            exit();
        }catch(CyberplatOK $e)
        {
            $this->echoOK($e);
            exit();
        }catch(Exception $e)
        {
            // error code 10 && mail error
            $this->log("-------------------------");
            $this->log("unexcepted error");
            $this->log($e->getMessage());

            echo "<pre>".$e->getMessage()."</pre>";

            $ee = new CyberplatError("Внутренная ошибка");
            $this->echoError($ee);
            exit();
        }
    }

    private function do_action($action)
    {

        if($action)
        {
            $this->data = $this->_load();
            $this->log($this->data);

            $this->actionChecker = new CyberplatActionCheck();
            $this->actionChecker->assertSign();
        }



        switch($action)
        {
            case 'check': $this->check(); break;
            case 'payment': $this->payment(); break;
            case 'status': $this->status(); break;
            case 'cancel': $this->cancel(); break;
            default: 
                throw new Answer_ERR_ACTION();
        }
    }

    private function check()
    {
        $this->actionChecker->check($this->data);

        throw new Answer_OK("Абонент найден");
    }

    private function payment()
    {
        $this->actionChecker->payment($this->data);
    }

    private function status()
    {
        $this->actionChecker->status($this->data);
    }

    private function cancel()
    {
        throw new Answer_ERR_cancal();
    }

    private function _load()
    {
        $data = array();
        foreach(array("number", "amount", "type", "sign", "receipt", "date", "mes", "additional") as $f)
        {
            $data[$f] = get_param_raw($f);
        }

        return $data;

    }

    private function log($d)
    {
        if($pFile = fopen(LOG_DIR."cyberplat.log", "a+"))
        {
            fwrite($pFile, "\n".date("r").": ".var_export($d, true));
            fclose($pFile);
        }
    }

    private function echoError($e)
    {
        header("Content-Type:text/html; charset=windows-1251");
        $str = "<?xml version=\"1.0\" encoding=\"windows-1251\"?>".
            "<response>".
            "<code>".$e->getCode()."</code>".
            "<message>".iconv("utf-8", "windows-1251", $e->getMessage())."</message>".
            "</response>";

            $this->log($str);

        echo CyberplatCrypt::sign($str);
    }

    private function echoOK($e)
    {
        header("Content-Type:text/html; charset=windows-1251");
        $str = "<?xml version=\"1.0\" encoding=\"windows-1251\"?>".
            "<response>".
            "<code>0</code>\n".$e->getDataStr().
            "<message>".iconv("utf-8", "windows-1251", $e->getMessage())."</message>".
            "</response>";
            $this->log($str);

        echo CyberplatCrypt::sign($str);
    }

}



class CyberplatError extends Exception
{
    public $code = 10;
    public $message = "";
}

abstract class CyberplatOK extends Exception
{
    public $code = 0;
    public $message = "";
    public $data = array();

    public function setData($data)
    {
        $this->data = $data;
    }

    public function getDataStr()
    {
        $str = "";
        foreach($this->data as $key => $value)
        {
            $str .= "<".$key.">".$value."</".$key.">\n";
        }
        return $str;
    }
}

class Answer_OK extends CyberplatOK
{
    //
}

class Answer_OK_payment extends CyberplatOK
{
    public $message = "Платеж принят";
    
}

class Answer_OK_status extends CyberplatOK
{
    public $message = "Платеж найден";
    
}

class Answer_ERR extends CyberplatError
{
    //
}

class Answer_ERR_SIGN extends CyberplatError
{
    public $code = -4;
    public $message = "Ошибка проверки АСП";
}

class Answer_ERR_TYPE extends CyberplatError
{
    public $code = -2;
    public $message = "Неверное значение платежа";
}

class Answer_ERR_ACTION extends CyberplatError
{
    public $code = 1;
    public $message = "Неизвестный тип запроса";
}

class Answer_ERR_CLIENT_NOT_FOUND extends CyberplatError
{
    public $code = 2;
    public $message = "Лицевой счет не найден";
}

class Answer_ERR_BAD_AMOUNT extends CyberplatError
{
    public $code = 3;
    public $message = "Неверная сумма платежа";
}

class Answer_ERR_RECEIPT extends CyberplatError
{
    public $code = 4;
    public $message = "Неверное значение платежа";
}

class Answer_ERR_DATE extends CyberplatError
{
    public $code = 5;
    public $message = "Неверное значение даты";
}

class Answer_ERR_status extends CyberplatError
{
    public $code = 6;
    public $message = "Успешный платеж с таким номером не найден";
}

class Answer_ERR_cancal extends CyberplatError
{
    public $code = 9;
    public $message = "Платеж не может быть отменен";
}



class CyberplatActionCheck
{
    public function __construct()
    {
        $this->fieldChecker = new CyberplatFieldCheck();
    }

    public function check(&$data)
    {
        $this->fieldChecker->assertType($data);
        $this->fieldChecker->assertAmount($data);
        $this->fieldChecker->assertNumber($data);
    }

    public function payment(&$data)
    {
        $this->fieldChecker->assertType($data);
        $this->fieldChecker->assertAmount($data);
        $this->fieldChecker->assertReceipt($data);

        $already = false;

        try{
            $this->fieldChecker->is_added_receipt($data);
        }catch(CyberplatError $e)
        {
            $already = true;
        }


        $paymentDate = $this->fieldChecker->assertDate($data);

        $client = $this->fieldChecker->assertNumber($data);


        if(!$already)
        {
            $objNow = new ActiveRecord\DateTime();
            $now = $objNow->format("db");

            $b = NewBill::getLastUnpayedBill($client->id);

            if (!$b)
                $b = NewBill::createBillOnPay($client->id, $data["amount"]);

            $payment = new \app\models\Payment();
            $payment->client_id = $client->id;
            $payment->bill_no = $b ? $b->bill_no : "";
            $payment->bill_vis_no = $b ? $b->bill_no : "";
            $payment->payment_no = $data["receipt"];
            $payment->oper_date = $now;
            $payment->payment_date = $paymentDate;
            $payment->add_date = $now;
            $payment->type = 'ecash';
            $payment->ecash_operator = 'cyberplat';
            $payment->sum = $data["amount"];
            $payment->currency = "RUB";
            $payment->payment_rate = 1;
            $payment->original_sum = $data["amount"];
            $payment->original_currency = "RUB";
            $payment->comment = "Cyberplat pay# " . $data["receipt"] . " at " . str_replace("T", " ", $data["date"]);
            $payment->save();

            Event::go("cyberplat_payment", array("client_id" => $client->id, "payment_id" => $payment->id)); // for start update balance


            $answer =  new Answer_OK_payment();
            $answer->setData(array(
                        "authcode" => $payment->id, 
                        "date" => $objNow->format("Y-m-d\TH:i:s"))
                    );

            throw $answer;
        }else{
            $payment = Payment::find('first', array(
                "conditions" => array("client_id" => $client->id, "payment_no" => $data["receipt"])
                ));

            $answer =  new Answer_OK_payment();
            $answer->setData(array(
                        "authcode" => $payment->id, 
                        "date" => $payment->add_date->format("Y-m-d\TH:i:s"))
                    );

            throw $answer;
        }
    }

    public function status(&$data)
    {
        $this->fieldChecker->assertReceipt($data);

        $pay = Payment::find_by_payment_no($data["receipt"]);

        if($pay)
        {
            $answer = new Answer_OK_payment();
            $answer->setData(array(
                "authcode" => $pay->id,
                "date" => $pay->add_date->format("Y-m-d\TH:i:s")
                )
            );

            throw $answer;
        }else{
            throw new Answer_ERR_status();
        }
        
    }

    public function assertSign()
    {
        $queryStr = $_SERVER["QUERY_STRING"];

        if(preg_match("/(action=.*)&sign=(.*)/", $queryStr, $o))
        {
            if(CyberplatCrypt::checkSign($o[1], $o[2]))
            {
                return true;
            }
        }

        throw new Answer_ERR_SIGN();
    }
}

class CyberplatCrypt
{
    static $my_private = "";
    static $my_public = "";
    static $my_passhare = "";
    static $cyberplat_public = "";

    private static function init()
    {
        self::$my_private = file_get_contents(STORE_PATH."keys/mcn_telecom__private.key");
        self::$my_public = file_get_contents(STORE_PATH."keys/mcn_telecom__public.key");
        self::$my_passhare = file_get_contents(STORE_PATH."keys/mcn_telecom__passhare.key");
        self::$cyberplat_public = file_get_contents(STORE_PATH."keys/cyberplat_public.key");
    }

    public static function checkSign($msg, $signHex)
    {
        self::init();
        $msg = trim($msg);
        if(!($sign = @pack("H*", $signHex)))
            return false;

        $publicKey = openssl_get_publickey(self::$cyberplat_public);

        return openssl_verify($msg, $sign, $publicKey);
    }

    public static function sign(&$str)
    {
        //return $str;

        $pk = openssl_pkey_get_private(self::$my_private, trim(self::$my_passhare));

        $sign = "";
        $res = openssl_sign($str, $sign, $pk);
        $sign = unpack("H*", $sign);
        $str = str_replace("</response>", "<sign>".$sign[1]."</sign></response>", $str);

        return $str;
    }
}

class CyberplatFieldCheck
{
    public function assertType(&$data)
    {
        if(!$data["type"] || $data["type"] != 1)
            throw new Answer_ERR_TYPE();
    }

    public function assertAmount(&$data)
    {
        if(!$data["amount"])
            throw new Answer_ERR_BAD_AMOUNT();

        $data["amount"] = (float)@floatval($data["amount"]);

        if($data["amount"] > 15000 || $data["amount"] < 10)
            throw new Answer_ERR_BAD_AMOUNT();
    }

    public function assertNumber(&$data)
    {
        if(!$data["number"] || !preg_match("/^\d{1,6}$/", $data["number"]))
            throw new Answer_ERR_CLIENT_NOT_FOUND();

        $c = ClientAccount::findOne([is_numeric($data["number"]) ? 'id' : 'client' => ($data["number"])]);
        if ($c && in_array($c->status, array("work", "connecting", "testing", "debt")))
        {
            // Абонент найден
            return $c;
        }else
            throw new Answer_ERR_CLIENT_NOT_FOUND();
    }

    public function assertReceipt(&$data)
    {
        $r = $data["receipt"];

        if(!$r || !preg_match("/^\d{3,15}$/", $r))
            throw new Answer_ERR_RECEIPT();
    }

    public function is_added_receipt(&$data)
    {
        if(Payment::find_by_payment_no($data["receipt"]))
        {
            throw new Answer_ERR("Платеж уже внесен");
        }
    }

    public function assertDate(&$data)
    {
        //echo $data["date"];

        if(!preg_match("/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}$/", $data["date"]))
            throw new Answer_ERR_DATE();

        $paymentDate = new ActiveRecord\DateTime($data["date"]);
        $paymentDate = $paymentDate->format("Y-m-d");

        return $paymentDate;
    }
}


