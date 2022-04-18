<?php

namespace app\classes;

use welltime\graylog\GelfMessage;
use Yii;


class WebApplication extends \yii\web\Application
{
    public function init()
    {
        parent::init();

        $isLogAAA = isset($this->params['isLogAAA']) ? $this->params['isLogAAA'] : false;

        if ($isLogAAA) {
            $messageData = '';
            $request = Yii::$app->request;
            list($route, $params) = $request->resolve();

            if ($params) {
                $messageData .= "PARAMS:\n";
                $messageData .= json_encode($params,
                    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                $messageData .= "\n\n";
            }

            if ($request->getBodyParams()) {
                $messageData .= "BODY:\n";
                $messageData .= json_encode($request->getBodyParams(),
                    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                $messageData .= "\n\n";
            }

            if (isset($_SERVER['REQUEST_URI_ORIG'])) {
                $requestUri = $_SERVER['REQUEST_URI_ORIG'];
            } else {
                $requestUri = $_SERVER['REQUEST_URI'];
            }


            Yii::info(
                GelfMessage::create()
                    ->setTimestamp(YII_BEGIN_TIME)
                    ->setShortMessage('AAA START ' . $request->getMethod() . ' ' . $requestUri)
                    ->setFullMessage($messageData)
                    ->setAdditional('route', $route)
                    ->setAdditional('duration', microtime(true) - YII_BEGIN_TIME),
                'request'
            );
        }


        register_shutdown_function(function ($isLogAAA) {
            $messageData = '';
            $request = Yii::$app->request;
            list($route, $params) = $request->resolve();

            if ($params) {
                $messageData .= "PARAMS:\n";
                $messageData .= json_encode($params,
                    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                $messageData .= "\n\n";
            }

            if ($request->getBodyParams()) {
                $messageData .= "BODY:\n";
                $messageData .= json_encode($request->getBodyParams(),
                    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                $messageData .= "\n\n";
            }

            if (isset($_SERVER['REQUEST_URI_ORIG'])) {
                $requestUri = $_SERVER['REQUEST_URI_ORIG'];
            } else {
                $requestUri = $_SERVER['REQUEST_URI'];
            }

            if ($isLogAAA) {
                $response = Yii::$app->response;
                Yii::info(
                    GelfMessage::create()
                        ->setTimestamp(microtime(true))
                        ->setShortMessage('AAA END ' . $request->getMethod() . ' ' . $requestUri)
                        ->setFullMessage(substr($response->content, 0, 1024))
                        ->setAdditional('route', $route)
                        ->setAdditional('duration', microtime(true) - YII_BEGIN_TIME),
                    'request'
                );
            }

            Yii::info(
                GelfMessage::create()
                    ->setTimestamp(YII_BEGIN_TIME)
                    ->setShortMessage($request->getMethod() . ' ' . $requestUri)
                    ->setFullMessage($messageData)
                    ->setAdditional('route', $route)
                    ->setAdditional('duration', microtime(true) - YII_BEGIN_TIME),
                'request'
            );
        }, $isLogAAA);
    }

    private function _getProductCountry()
    {
        return ($_SERVER['COUNTRY'] ?? 'RU');
    }

    public function isEu()
    {
        return $this->_getProductCountry() == 'EU';
    }

    public function isRus()
    {
        return $this->_getProductCountry() == 'RU';
    }
}
