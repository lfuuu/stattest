<?php


class JSONQuery
{
    public function exec($url, $data)
    {
        self::log($url, $data);


        $defaults = array( 
                CURLOPT_POST => 1, 
                CURLOPT_HEADER => 0, 
                CURLOPT_URL => $url, 
                CURLOPT_FRESH_CONNECT => 1, 
                CURLOPT_RETURNTRANSFER => 1, 
                CURLOPT_FORBID_REUSE => 1, 
                CURLOPT_TIMEOUT => 4, 
                CURLOPT_POSTFIELDS => json_encode($data),
                //CURLOPT_COOKIE => "mcn_uid=139031302648788611069664" /* !!!!!!! */
                ); 

        echo "\n".json_encode($data);

        $ch = curl_init(); 
        curl_setopt_array($ch, $defaults); 
        if( ! $result = curl_exec($ch)) 
        { 
            trigger_error(curl_error($ch)); 
        } 
        
        $info = curl_getinfo($ch);
        curl_close($ch); 


        // todo: переделать эту хрень на событийную модель
        if ($info["http_code"] !== 200)
        {
            throw new Exception("VPBX Sync Error: http code: ".$info["http_code"], $info["http_code"]);
        }

        print_r($result);
        $result = @json_decode($result, true);
        print_r($result);

        if (!$result)
        {
            throw new Exception("VPBX Sync Error: result false");
        }

        if (isset($result["errors"]))
        {
            throw new Exception($result["errors"][0]["message"], $result["errors"][0]["code"]);
        }

        return $result;
    }

    private function log($url, $data)
    {

        $f = fopen("/tmp/json_query_log", "a+");
        fwrite($f, "\n".date("d-m-Y H;i:s").": ".$url."\n".var_export($data, true));
        fclose($f);
    }
}
