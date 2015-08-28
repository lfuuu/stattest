<?php
namespace app\classes;

use Yii;
use Exception;

class JSONQuery
{
    public static function exec($url, $data, $isPostJSON = true)
    {
        Yii::info('Json request ' . $url . ': ' . json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        $defaults = array(
            CURLOPT_POST => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_URL => $url,
            CURLOPT_FRESH_CONNECT => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FORBID_REUSE => 1,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => FALSE
        );

        if ($isPostJSON)
        {
            $defaults[CURLOPT_POSTFIELDS] = json_encode($data);
        } else {
            $defaults[CURLOPT_URL] .= "?".http_build_query($data);
        }

        //Event::go("json", [$url, $data], true);

        $ch = curl_init();
        curl_setopt_array($ch, $defaults);
        if( ! $result = curl_exec($ch))
        {
            throw new Exception(curl_error($ch));
        }

        $info = curl_getinfo($ch);
        curl_close($ch);


        if ($info["http_code"] !== 200)
        {
            throw new Exception("Bad responce: http code: ".$info["http_code"], $info["http_code"]);
        }

        $response = $result;
        $result = @json_decode($result, true);


        if ($result === null) {
            Yii::info('Json response raw: ' . $response);
            throw new Exception("Json decoding error", -1);
        }

        Yii::info('Json response: ' . json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        return $result;
    }

}
