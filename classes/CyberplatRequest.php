<?php


class CyberPlatRequest
{
    // request fields
    private $data = array();

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
        CyberplatActionCheck::check($this->data);

        throw new Answer_OK("Абонент найден");
    }

    private function payment()
    {
        CyberplatActionCheck::payment($this->data);
    }

    private function status()
    {
        CyberplatActionCheck::status($this->data);
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
            $data[$f] = get_param_protected($f);
        }

        return $data;

    }

    private function log($d)
    {
        $pFile = fopen(LOG_DIR."cyberplat.log", "a+");
        fwrite($pFile, "\n".date("r").": ".var_export($d, true));
        fclose($pFile);
    }

    private function echoError($e)
    {
        $str = "<?xml version=\"1.0\" encoding=\"koi8-r\"?>\n".
            "<response>\n".
            "<code>".$e->getCode()."</code>\n".
            "<message>".iconv("utf-8", "koi8-r", $e->getMessage())."</message>\n".
            "</response>\n";

            $this->log($str);

        echo $this->sign($str);
    }

    private function echoOK($e)
    {
        $str = "<?xml version=\"1.0\" encoding=\"koi8-r\"?>\n".
            "<response>\n".
            "<code>0</code>\n".$e->getDataStr().
            "<message>".iconv("utf-8", "koi8-r", $e->getMessage())."</message>\n".
            "</response>\n";
            $this->log($str);
        echo $this->sign($str);
    }

    private function sign($str)
    {
        //return $str;

        $private_key = file_get_contents(PATH_TO_ROOT."store/keys/mcn_telecom__private.key");
        $passhare = file_get_contents(PATH_TO_ROOT."store/keys/mcn_telecom__passhare.key");

        $pk = openssl_pkey_get_private($private_key, trim($passhare));

        $sign = "";
        $res = openssl_sign($str, $sign, $pk);
        $sign = unpack("H*", $sign);
        $str = str_replace("</response>", "<sign>".$sign[1]."</sign></response>", $str);

        return $str;
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
    public function check(&$data)
    {
        CyberplatFieldCheck::type($data);
        CyberplatFieldCheck::amount($data);
        CyberplatFieldCheck::number($data);
    }

    public function payment(&$data)
    {
        CyberplatFieldCheck::type($data);
        CyberplatFieldCheck::amount($data);
        CyberplatFieldCheck::receipt($data);
        CyberplatFieldCheck::is_added_receipt($data);

        $paymentDate = CyberplatFieldCheck::date($data);

        $client = CyberplatFieldCheck::number($data);

        $objNow = new ActiveRecord\DateTime();
        $now = $objNow->format("db");

        $b = NewBill::getLastUnpayedBill($client->id);

        $payment = new Payment();
        $payment->client_id = $client->id;
        $payment->bill_no = $b ? $b->bill_no : "";
        $payment->bill_vis_no = $b ? $b->bill_no : "";
        $payment->payment_no = $data["receipt"];
        $payment->oper_date = $now;
        $payment->payment_date = $paymentDate;
        $payment->add_date = $now;
        $payment->payment_rate = 1;
        $payment->type='neprov';
        $payment->sum_rub = $data["amount"];
        $payment->currency = "RUR";
        $payment->comment = "Cyberplat pay# ".$data["receipt"]." at ".str_replace("T", " ", $data["date"]);
        $payment->save();

        $answer =  new Answer_OK_payment();
        $answer->setData(array(
            "authcode" => $payment->id, 
            "date" => $objNow->format("Y-m-d\TH:i:s"))
        );

        throw $answer;
    }

    public function status(&$data)
    {
        CyberplatFieldCheck::receipt($data);

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
}

class CyberplatFieldCheck
{
    public function type(&$data)
    {
        if(!$data["type"] || $data["type"] != 1)
            throw new Answer_ERR_TYPE();
    }

    public function amount(&$data)
    {
        if(!$data["amount"])
            throw new Answer_ERR_BAD_AMOUNT();

        $data["amount"] = (float)@floatval($data["amount"]);

        if($data["amount"] > 15000 || $data["amount"] < 10)
            throw new Answer_ERR_BAD_AMOUNT();
    }

    public function number(&$data)
    {
        if(!$data["number"] || !preg_match("/^\d{1,6}$/", $data["number"]))
            throw new Answer_ERR_CLIENT_NOT_FOUND();

        $c = ClientCard::find_by_id($data["number"]);
        if($c)
        {
            // Абонент найден
            return $c;
        }else
            throw new Answer_ERR_CLIENT_NOT_FOUND();
    }

    public function receipt(&$data)
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

    public function date(&$data)
    {
        //echo $data["date"];

        if(!preg_match("/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}$/", $data["date"]))
            throw new Answer_ERR_DATE();

        $paymentDate = new ActiveRecord\DateTime($data["date"]);
        $paymentDate = $paymentDate->format("Y-m-d");

        return $paymentDate;
    }
}


