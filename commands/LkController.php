<?php
namespace app\commands;

use app\classes\api\ApiCore;
use app\classes\notification\Notification;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\models\ClientSuper;
use app\models\Param;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

class LkController extends Controller
{
    /**
     * Проверяет необходимости оповещения клиентов
     */
    public function actionCheckNotification()
    {
        $switchOnParam = Param::findOne(Param::NOTIFICATIONS_SWITCH_ON_DATE);

        if ($switchOnParam) {
            $now = new \DateTime('now', new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT));

            $switchOnDate = new \DateTime($switchOnParam->value, new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT));

            if ($now < $switchOnDate) {
                echo PHP_EOL . '[lk/check-notification][+] Оповещения отключены ' . $switchOnDate->format(DateTimeZoneHelper::DATETIME_FORMAT);
                Yii::info('[lk/check-notification][+] Оповещения отключены ' . $switchOnDate->format(DateTimeZoneHelper::DATETIME_FORMAT));
                return ExitCode::OK;
            }

            echo PHP_EOL . '[lk/check-notification][-] Оповещения включены ' . $now->format(DateTimeZoneHelper::DATETIME_FORMAT);
            Yii::info('[lk/check-notification][-] Оповещения включены ' . $now->format(DateTimeZoneHelper::DATETIME_FORMAT));
            if (!$switchOnParam->delete()) {
                throw new ModelValidationException($switchOnParam);
            }
        }

        $now = new \DateTime('now', new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT));
        Param::setParam(
            Param::NOTIFICATIONS_SCRIPT_ON,
            $now->format(DateTimeZoneHelper::DATETIME_FORMAT),
            $isRawValue = true
        );

        (new Notification)->checkForNotification();

        Param::setParam(
            Param::NOTIFICATIONS_SCRIPT_ON, 
            Param::IS_OFF,
            $isRawValue = true
        );
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

        return ExitCode::OK;
    }
}
