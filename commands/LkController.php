<?php
namespace app\commands;

use app\classes\api\ApiCore;
use app\classes\notification\Notification;
use app\models\ClientAccount;
use app\models\ClientSuper;
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

    /**
     * Проверяем у кого включен ЛК
     */
    public function actionSetIsLkExists()
    {
        $clients = ClientAccount::find()
            ->select('super_id')
            ->distinct()
            ->where(['is_active' => 1])
            ->with('superClient');

        echo PHP_EOL . date("r");

        $count = 0;
        foreach ($clients->each() as $client) {
            /** @var ClientSuper $superClient */
            $superClient = $client->superClient;
            $isLkExists = (int)ApiCore::isLkExists($superClient->id);
            if ($isLkExists != $superClient->is_lk_exists) {
                echo PHP_EOL . "super_id: " . $superClient->id . " (" . ($isLkExists ? "+" : "-") . ")";
                $superClient->is_lk_exists = $isLkExists;
                $superClient->save();
            }
            $count++;
        }
        echo PHP_EOL . " count clients checked: " . $count;
        echo PHP_EOL;

        return Controller::EXIT_CODE_NORMAL;
    }
}
