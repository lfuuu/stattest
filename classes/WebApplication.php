<?php
namespace app\classes;

use welltime\graylog\GelfMessage;
use Yii;


class WebApplication extends \yii\web\Application
{
    public function init()
    {
        parent::init();

        register_shutdown_function(function(){
            $messageData = '';
            $request = Yii::$app->request;
            list($route, $params) = $request->resolve();

            $messageData .= "PATH:\n";
            $messageData .= json_encode($request->getPathInfo(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $messageData .= "\n\n";


            if ($params) {
                $messageData .= "PARAMS:\n";
                $messageData .= json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                $messageData .= "\n\n";
            }

            if ($request->getBodyParams()) {
                $messageData .= "BODY:\n";
                $messageData .= json_encode($request->getBodyParams(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
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
                    ->setShortMessage($request->getMethod() . ' ' . $requestUri)
                    ->setFullMessage($messageData)
                    ->setAdditional('route', $route)
                    ->setAdditional('duration' , microtime(true) - YII_BEGIN_TIME),
                'request'
            );
        });
    }

}