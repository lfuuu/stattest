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
            $this->log(array("unexcepted errror", $e));
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
            case 'cancel': $this->cancel(); break;
            case 'status': $this->status(); break;
            default: 
                throw new Answer_ERR_ACTION();
        }
    }

    private function check()
    {
        if(!$this->data["type"] || $this->data["type"] != 1)
            throw new Answer_ERR_TYPE();

        if(!$this->data["amount"] || $this->data["amount"] <= 0)
            throw new Answer_ERR_BAD_AMOUNT();
        
        $c = ClientCard::find_by_id($this->data["number"]);
        if($c)
        {
            throw new Answer_OK("Абонент найден");
        }else
            throw new Answer_ERR_CLIENT_NOT_FOUND();;
    }

    private function payment()
    {
        throw new CyberplatError("Внутренная ошибка");
    }

    private function cancel()
    {
        throw new CyberplatError("Внутренная ошибка");
    }

    private function status()
    {
        throw new CyberplatError("Внутренная ошибка");
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
        $str = "<?xml version=\"1.0\" encoding=\"koi8-r\"?>\n";
            "<response>\n".
            "<code>".$e->getCode()."</code>\n".
            "<message>".iconv("utf-8", "koi8-r", $e->getMessage())."</message>\n".
            "</response>\n";

        echo $this->sign($str);
    }

    private function echoOK($e)
    {
        $str = "<?xml version=\"1.0\" encoding=\"koi8-r\"?>\n".
            "<response>\n".
            "<code>0</code>\n".
            "<message>".iconv("utf-8", "koi8-r", $e->getMessage())."</message>\n".
            "</response>\n";
        echo $this->sign($str);
    }

    private function sign($str)
    {
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

class Answer_OK extends CyberplatOK
{
    //
}


