<?php

namespace app\classes;

use Yii;
use yii\httpclient\Client;

class HttpClient extends Client
{

    /**
     * @param [] $config
     * @return []
     */
    public function auth(array $config)
    {
        if (isset($config['method'])) {
            switch ($config['method']) {
                case 'basic': {
                    return [
                        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
                        CURLOPT_USERPWD =>
                            (isset($config['user']) ? $config['user'] : '') .
                            ':' .
                            (isset($config['passwd']) ? $config['passwd'] : ''),
                    ];
                }

                case 'bearer': {
                    return [
                        'Authorization' => 'Bearer ' . (isset($config['token']) ? $config['token'] : ''),
                    ];
                }
            }
        }
        return [];
    }

}