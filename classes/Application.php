<?php
namespace app\classes;

use welltime\graylog\GelfMessage;
use Yii;


class Application extends \yii\web\Application
{
    public function init()
    {
        parent::init();

        $this->on(self::EVENT_AFTER_REQUEST, function(){
            $request = $this->getRequest();

            $messageData = '';

            if ($request->getUserHost()) {
                $messageData .= "HOST: " . $request->getHostInfo() . "\n\n";
            }

            if ($request->getQueryParams()) {
                $messageData .= "GET:\n";
                $messageData .= json_encode($request->getQueryParams(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                $messageData .= "\n\n";
            }

            if ($request->getBodyParams()) {
                $messageData .= "BODY:\n";
                $messageData .= json_encode($request->getBodyParams(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                $messageData .= "\n\n";
            }

            Yii::info(
                GelfMessage::create()
                    ->setShortMessage($request->getMethod() . ' ' . $_SERVER['REQUEST_URI'])
                    ->setFullMessage($messageData),
                'request'
            );
        });
    }

}