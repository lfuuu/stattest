<?php
namespace app\commands;

use app\classes\notification\Notification;
use Yii;
use yii\console\Controller;

class LkController extends Controller
{
    /**
     * Проверяет необходимости оповещения клиентов
     */
    public function actionCheckNotification()
    {
        (new Notification)->checkForNotification();
    }

}
