<?php
namespace app\commands;

use app\helpers\DateTimeZoneHelper;
use app\models\BusinessProcessStatus;
use app\models\usages\UsageInterface;
use app\models\UsageVoip;
use Yii;
use DateTime;
use app\models\ClientAccount;
use yii\console\Controller;
use app\forms\usage\UsageVoipEditForm;


class UsageController extends Controller
{

    const ACTION_SET_BLOCK = 1;
    const ACTION_SET_OFF = 2;
    const ACTION_CLEAN_TRASH = 3;

    public function actionClientUpdateIsActive()
    {
        $partSize = 500;
        try {
            $count = $partSize;
            $offset = 0;
            while ($count >= $partSize) {
                $clientAccounts =
                    ClientAccount::find()
                        ->limit($partSize)->offset($offset)
                        ->orderBy('id')
                        ->all();

                foreach ($clientAccounts as $clientAccount) {
                    $offset++;
                    ClientAccount::dao()->updateIsActive($clientAccount);
                }

                $count = count($clientAccounts);
            }

        } catch (\Exception $e) {
            Yii::error($e);
            throw $e;
            return Controller::EXIT_CODE_ERROR;
        }

        return Controller::EXIT_CODE_NORMAL;
    }

    /**
     * Очистка услуг телефонии.
     * через 3 дня   - высвободить номер, если статус бизнес процесса - "Заказ услуг"
     * через 10 дней - заблокировать ЛС, если есть услуга в тесте
     * через 40 дней - высвободить номер, если есть услуга в тесте
     *
     * @return int
     */
    public function actionVoipTestClean()
    {
        $info = [];

        $now = new DateTime("now");

        echo "\nstart " . $now->format(DateTimeZoneHelper::DATETIME_FORMAT) . "\n";

        $cleanOrderOfServiceDate = (new DateTime("now"))->modify("-3 day");
        $offDate = (new DateTime("now"))->modify("-10 day");

        echo $now->format(DateTimeZoneHelper::DATE_FORMAT) . ": off:   " . $offDate->format(DateTimeZoneHelper::DATE_FORMAT) . "\n";
        echo $now->format(DateTimeZoneHelper::DATE_FORMAT) . ": clean: " . $cleanOrderOfServiceDate->format(DateTimeZoneHelper::DATE_FORMAT) . "\n";

        $infoOff = $this->cleanUsages($offDate, self::ACTION_SET_OFF);
        $infoClean = $this->cleanUsages($cleanOrderOfServiceDate, self::ACTION_CLEAN_TRASH);

        if ($infoOff) {
            $info = array_merge($info, $infoOff);
        }

        if ($infoClean) {
            $info = array_merge($info, $infoClean);
        }

        if ($info) {
            if (defined("ADMIN_EMAIL") && ADMIN_EMAIL) {
                mail(ADMIN_EMAIL, "voip clean processor", implode("\n", $info));
            }

            echo implode("\n", $info);
        }

        return Controller::EXIT_CODE_NORMAL;
    }

    private function cleanUsages(\DateTime $date, $action)
    {
        $now = new DateTime("now");
        $yesterday = clone $now;
        $yesterday->modify('-1 day');

        $usages = UsageVoip::find()->actual()->andWhere(["actual_from" => $date->format(DateTimeZoneHelper::DATE_FORMAT)])->all();

        $info = [];

        foreach ($usages as $usage) {
            $tariff = $usage->tariff;
            $account = $usage->clientAccount;

            if ($action == self::ACTION_CLEAN_TRASH) {
                if ($account->contract->business_process_status_id != BusinessProcessStatus::TELEKOM_MAINTENANCE_TRASH) {
                    continue;
                }

                $info[] = $now->format(DateTimeZoneHelper::DATE_FORMAT) . ": " . $usage->E164 . ", from: " . $usage->actual_from . ": clean trash";

                $model = new UsageVoipEditForm();
                $model->initModel($account, $usage);
                $model->disconnecting_date = $yesterday->format(DateTimeZoneHelper::DATE_FORMAT);
                $model->status = UsageInterface::STATUS_WORKING;
                $model->edit();
            } elseif ($action == self::ACTION_SET_OFF) {
                if (!$tariff || $tariff->isTest()) {// тестовый тариф, или без тарифа вообще
                    if ($usage->actual_to != $now->format(DateTimeZoneHelper::DATE_FORMAT)) {// не выключенные сегодня
                        $info[] = $now->format(DateTimeZoneHelper::DATE_FORMAT) . ": " . $usage->E164 . ", from: " . $usage->actual_from . ": set off";

                        $model = new UsageVoipEditForm();
                        $model->initModel($account, $usage);
                        $model->disconnecting_date = $now->format(DateTimeZoneHelper::DATE_FORMAT);
                        $model->status = UsageInterface::STATUS_WORKING;
                        $model->edit();
                    }
                }
            }
        }

        return $info;
    }

    /**
     * @inheritdoc
     * @return int
     */
    public function actionCheckVoipDayDisable()
    {
        $now = new DateTime('now');
        echo PHP_EOL . 'start ' . $now->format(DateTimeZoneHelper::DATETIME_FORMAT);

        $ress = Yii::$app->dbPgSlave->createCommand('
            SELECT cc.client_id
            FROM
                billing.clients c
            LEFT JOIN billing.counters cc ON c.id=cc.client_id
            LEFT JOIN billing.locks cl ON c.id=cl.client_id
            WHERE
                cl.is_overran
                AND NOT c.voip_disabled
                AND (
                    (voip_limit_day != 0 AND amount_day_sum < -voip_limit_day) OR
                    (voip_limit_mn_day != 0 AND amount_mn_day_sum < -voip_limit_mn_day) OR
                    (voip_limit_month != 0 AND amount_month_sum > voip_limit_month)
                )
        ')->queryAll();

        foreach ($ress as $res) {
            $client = ClientAccount::findOne($res['client_id']);

            if ($client->voip_disabled == 0) {
                echo PHP_EOL . '...' . $res['client_id'];
                $client->voip_disabled = 1;
                $client->save();
            }
        }

        return Controller::EXIT_CODE_NORMAL;
    }
}
