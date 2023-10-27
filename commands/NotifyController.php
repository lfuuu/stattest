<?php

namespace app\commands;

use app\classes\Assert;
use app\classes\dto\ChangeClientStructureRegistratorDto;
use app\dao\ClientSuperDao;
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

    public function actionClientStructureChanged()
    {
        $sleepTime = 15;
        $workTime = 300; // перезагрузка каждые 5-8 минут
        $maxCountShift = 3;

        $countShift = 0;

        $registr = ChangeClientStructureRegistratorDto::me();

        // Контроль времени работы. выключаем с 55 до 00 секунд.
        $time = (new \DateTimeImmutable());
        $stopTimeFrom = $time->modify('+' . $workTime . ' second');
        $stopTimeFrom = $stopTimeFrom->modify('-' . ((int)$stopTimeFrom->format('s') + 5) . 'second');
        $stopTimeTo = $stopTimeFrom->modify('+5 second');


        do {
            if ($data = $registr->checkDataForSend()) {
                echo PHP_EOL . date("r") . ': ChangeClientStructure: ' . preg_replace('/\s+/', " ", print_r($data, true));

                EventQueue::go(EventQueue::SYNC_CLIENT_CHANGED, $data); // old notification scheme

                $superIds = ClientSuperDao::me()->getSuperIds($data['clientIds'] ?? null, null, $data['contractIds'] ?? null, $data['contragentIds'] ?? null, $data['accountIds'] ?? null);

                foreach ($superIds as $superId) {
                    echo PHP_EOL . 'SuperId: ' . $superId . PHP_EOL;

                    EventQueue::go(ChangeClientStructureRegistratorDto::EVENT, $superId);
                }
            }

            sleep($sleepTime);

            $time = (new \DateTimeImmutable());

            $isExit = $stopTimeFrom < $time && $time < $stopTimeTo;

            if (!$isExit && $stopTimeTo < $time) {
                if ($countShift++ >= $maxCountShift) {
                    $isExit = true;
                } else {
                    $stopTimeFrom = $time->modify('+1 minute');
                    $stopTimeFrom = $stopTimeFrom->modify('-' . ((int)$stopTimeFrom->format('s') + 5) . 'second');
                    $stopTimeTo = $stopTimeFrom->modify('+5 second');
                }
            }
        } while (!$isExit);
    }

}
