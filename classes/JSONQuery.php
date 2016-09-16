<?php
namespace app\classes;

use Exception;
use Yii;

class JSONQuery
{
    /**
     * @param string $url
     * @param [] $data
     * @param bool $isPostJSON
     * @return mixed
     * @throws Exception
     */
    public static function exec($url, $data, $isPostJSON = true)
    {
        Yii::info('Json request ' . $url . ': ' . json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        $defaults = [
            CURLOPT_POST => $isPostJSON ? 1 : 0,
            CURLOPT_HEADER => 0,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_URL => $url,
            CURLOPT_FRESH_CONNECT => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FORBID_REUSE => 1,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false
        ];

        if ($data) {
            if ($isPostJSON) {
                $defaults[CURLOPT_POSTFIELDS] = json_encode($data);
            } else {
                $defaults[CURLOPT_URL] .= '?' . http_build_query($data);
            }
        }

        //Event::go('json', [$url, $data], true);

        $debugInfo = '';
        $debugInfo .= sprintf('$url = %s', $url) . PHP_EOL;
        $debugInfo .= sprintf('$data = %s', print_r($data, true)) . PHP_EOL;
        $debugInfo .= sprintf('$isPostJSON = %d', $isPostJSON) . PHP_EOL;

        $ch = curl_init();
        curl_setopt_array($ch, $defaults);
        if (!$response = curl_exec($ch)) {
            $debugInfo .= sprintf('curl_error = %s', curl_error($ch)) . PHP_EOL;
            throw new Exception($debugInfo);
        }

        $info = curl_getinfo($ch);
        curl_close($ch);

        if ($info['http_code'] !== 200) {
            $debugInfo .= sprintf('$info = %s', $info) . PHP_EOL;
            throw new Exception($debugInfo, $info["http_code"]);
        }

        $responseArray = @json_decode($response, true);

        if ($responseArray === null) {
            Yii::info('Json response raw: ' . $response);
            $debugInfo .= sprintf('$response = %s', $response) . PHP_EOL;
            throw new Exception($debugInfo, -1);
        }

        $debugInfo .= sprintf('$responseArray = %s', $responseArray) . PHP_EOL;
        Yii::info('Json response: ' . $debugInfo);

        if (isset($responseArray["errors"]) && $responseArray["errors"]) {

            if (isset($responseArray["errors"]["message"], $responseArray["errors"]["code"])) {
                $msg = $responseArray["errors"]["message"];
                $code = $responseArray["errors"]["code"];
            } else {
                if (isset($responseArray['errors'][0], $responseArray['errors'][0]["message"])) {
                    $msg = $responseArray['errors'][0]["message"];
                    $code = $responseArray['errors'][0]["code"];
                } else {
                    $msg = "Текст ошибки не найден! <br>\n" . var_export($responseArray, true);
                    $code = 500;
                }
            }

            throw new Exception($msg . ' ' . $debugInfo, $code);
        }
        
        return $responseArray;
    }

}
