<?php


class OnlimeRequest
{
    const STATUS_DELIVERY = 1;
    const STATUS_NOT_DELIVERY = 2;
    const STATUS_REJECT = 3;

    public function post($order_id, $delivery_id, $status, $status_text)
    {
        $data = "";
        $_data = "";

        if(!$delivery_id)
            $delivery_id = "rej".time()."_".rand(10000,99999);

        foreach(array("order_id", "delivery_id", "status", "status_text") as $f)
        {
            $_data .= ($data ? "&" : "").$f."=".($$f);
            $data .= ($data ? "&" : "").$f."=".urlencode($$f);
        }

        self::_log($data);
        echo "\n".$data."\n";
        self::_post($data);
    }

    private function _post($data)
    {
        $url = "http://www.onlime.ru/shop_xml_reply.php";
        //$url = "http://teststat.mcn.ru/test.php";
        //echo file_get_contents($url."?".$data);

        //return;
        $ch = curl_init(); // инициализируем сессию curl
        curl_setopt($ch, CURLOPT_URL,$url); // указываем URL, куда отправлять POST-запрос
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);// разрешаем перенаправление
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); // указываем, что результат запроса следует передать в переменную, а не вывести на экран
        curl_setopt($ch, CURLOPT_TIMEOUT, 3); // таймаут соединения
        curl_setopt($ch, CURLOPT_POST, 1); // указываем, что данные надо передать именно методом POST
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data); // добавляем данные POST-запроса
        $result = curl_exec($ch); // выполняем запрос
        curl_close($ch); // завершаем сессию
        echo $result;
    }

    private function _log($str)
    {
        $pFile = fopen(LOG_DIR."onlime.log", "a+");
        fwrite($pFile, "\n".date("r").": ".$str);
        fclose($pFile);
    }
}

