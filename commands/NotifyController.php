<?php

namespace app\commands;

use app\classes\Assert;
use app\models\EventQueue;
use app\models\User;
use yii\console\Controller;

class NotifyController extends Controller
{

    public function actionIndex($text, array $userNames)
    {
        Assert::isNotEmpty($text);

        foreach ($userNames as $userName) {
            if (!User::find()->where(['user' => $userName])->exists()) {
                echo PHP_EOL . 'Пользователь ' . $userName . ' не найден';
                continue;
            }

            echo PHP_EOL . "+" . $text . ': ' . $userName;

            EventQueue::go(EventQueue::TROUBLE_NOTIFIER_EVENT, [
                'user' => $userName,
                'trouble_id' => 1000,
                'text' => $text,
            ]);
        }

    }

}
