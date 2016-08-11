<?php
namespace app\commands;

use app\models\BusinessProcessStatus;
use app\models\Emails;
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

        echo "\nstart " . $now->format("Y-m-d H:i:s") . "\n";

        $cleanOrderOfServiceDate = (new DateTime("now"))->modify("-3 day");
        $blockDate = (new DateTime("now"))->modify("-10 day");
        $offDate = (new DateTime("now"))->modify("-40 day");

        echo $now->format("Y-m-d") . ": block: " . $blockDate->format("Y-m-d") . "\n";
        echo $now->format("Y-m-d") . ": off:   " . $offDate->format("Y-m-d") . "\n";
        echo $now->format("Y-m-d") . ": clean: " . $cleanOrderOfServiceDate->format("Y-m-d") . "\n";

        $infoBlock = $this->cleanUsages($blockDate, self::ACTION_SET_BLOCK);
        $infoOff = $this->cleanUsages($offDate, self::ACTION_SET_OFF);
        $infoClean = $this->cleanUsages($cleanOrderOfServiceDate, self::ACTION_CLEAN_TRASH);

        if ($infoBlock) {
            $info = array_merge($info, $infoBlock);
        }

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

        $usages = UsageVoip::find()->actual()->andWhere(["actual_from" => $date->format("Y-m-d")])->all();

        $info = [];

        foreach ($usages as $usage) {
            $tarif = $usage->tariff;
            $account = $usage->clientAccount;

            if ($action == self::ACTION_CLEAN_TRASH) {
                if ($account->contract->business_process_status_id == BusinessProcessStatus::TELEKOM_MAINTENANCE_TRASH) {
                    $info[] = $now->format("Y-m-d") . ": " . $usage->E164 . ", from: " . $usage->actual_from . ": clean trash";

                    $model = new UsageVoipEditForm();
                    $model->initModel($account, $usage);
                    $model->disconnecting_date = $now->modify('-1 day')->format("Y-m-d");
                    $model->edit();
                }
            } elseif (!$tarif || $tarif->isTested()) {// тестовый тариф, или без тарифа вообще
                if ($usage->actual_to != $now->format("Y-m-d")) {// не выключенные сегодня
                    if ($action == self::ACTION_SET_BLOCK) {
                        if (!$account->is_blocked) {
                            $info[] = $now->format("Y-m-d") . ": " . $usage->E164 . ", from: " . $usage->actual_from . ": set block " . $tarif->status;

                            $account->is_blocked = 1;
                            $account->save();
                        }
                    }

                    if ($action == self::ACTION_SET_OFF) {
                        $info[] = $now->format("Y-m-d") . ": " . $usage->E164 . ", from: " . $usage->actual_from . ": set off";

                        $model = new UsageVoipEditForm();
                        $model->initModel($account, $usage);
                        $model->disconnecting_date = $now->format("Y-m-d");
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
        echo PHP_EOL . 'start ' . $now->format('Y-m-d H:i:s');

        $ress = Yii::$app->dbPg->createCommand('
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
