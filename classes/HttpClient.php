<?php

namespace app\classes;

use Yii;
use yii\httpclient\Client;
use yii\httpclient\Request;

class HttpClient extends Client
{

    /**
     * @param [] $config
     */
    public function auth(Request $request, array $config)
    {
        if (isset($config['method'])) {
            switch ($config['method']) {
                case 'basic': {
                    $request->setOptions([
                        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
                        CURLOPT_USERPWD =>
                            (isset($config['user']) ? $config['user'] : '') .
                            ':' .
                            (isset($config['passwd']) ? $config['passwd'] : ''),
                    ]);
                    break;
                }

                case 'bearer': {
                    $request->setHeaders([
                        'Authorization' => 'Bearer ' . (isset($config['token']) ? $config['token'] : ''),
                    ]);
                    break;
                }
            }
        }
    }

}