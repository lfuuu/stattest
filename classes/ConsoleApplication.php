<?php
namespace app\classes;

use welltime\graylog\GelfMessage;
use Yii;


class ConsoleApplication extends \yii\console\Application
{
    public function init()
    {
        parent::init();

        register_shutdown_function(function(){
            $messageData = '';
            $request = Yii::$app->request;
            list($route, $params) = $request->resolve();

            if ($params) {
                $messageData .= "PARAMS:\n";
                $messageData .= json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                $messageData .= "\n\n";
            }

            Yii::info(
                GelfMessage::create()
                    ->setTimestamp(YII_BEGIN_TIME)
                    ->setShortMessage('CONSOLE ' . implode(' ', $_SERVER['argv']))
                    ->setFullMessage($messageData)
                    ->setAdditional('route', $route)
                    ->setAdditional('username', $_SERVER['USERNAME'])
                    ->setAdditional('duration' , microtime(true) - YII_BEGIN_TIME),
                'request'
            );
        });
    }

}