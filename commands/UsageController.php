<?php
namespace app\commands;

use app\models\Emails;
use app\models\UsageExtra;
use app\models\UsageIpPorts;
use app\models\UsageSms;
use app\models\UsageVirtpbx;
use app\models\UsageVoip;
use app\models\UsageWelltime;
use Yii;
use DateTime;
use app\models\ClientAccount;
use yii\console\Controller;
use yii\db\ActiveQuery;
use app\forms\usage\UsageVoipEditForm;


class UsageController extends Controller
{
    public function actionCloseExpired()
    {
        /*
        $now = new DateTime();

        $usageQueries = [
            Emails::find(),
            UsageExtra::find(),
            UsageIpPorts::find(),
            UsageSms::find(),
            UsageVirtpbx::find(),
            UsageVoip::find(),
            UsageWelltime::find(),
        ];

        foreach ($usageQueries as $usageQuery) {
            $usages =
                $usageQuery
                    ->andWhere(['status' => 'working'])
                    ->andWhere('actual_to < :date', [':date' => $now->format('Y-m-d')])
                    ->all();
            foreach ($usages as $usage) {
                $usage->status = 'archived';
                $usage->save();
            }
        }
        */
    }

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
            return 1;
        }
    }

    public function actionVoipTestClean()
    {
        $info = [];

        $now     =  new DateTime("now");

        echo "\nstart ".$now->format("Y-m-d H:i:s")."\n";

        $blockDate = (new DateTime("now"))->modify("-10 day");
        $offDate   = (new DateTime("now"))->modify("-40 day");

        echo $now->format("Y-m-d").": block: ".$blockDate->format("Y-m-d")."\n";
        echo $now->format("Y-m-d").": off: ".$offDate->format("Y-m-d")."\n";

        $infoBlock =$this->cleanUsages($blockDate, "set block");
        $infoOff = $this->cleanUsages($offDate, "set off");

        if ($infoBlock)
            $info = array_merge($info, $infoBlock);

        if ($infoOff)
            $info = array_merge($info, $infoOff);

        if ($info)
        {
            if (defined("ADMIN_EMAIL") && ADMIN_EMAIL)
            {
                mail(ADMIN_EMAIL, "voip clean processor", implode("\n", $info));
            }

            echo implode("\n", $info);
        }

    }

    private function cleanUsages($date, $action)
    {
        $now = new DateTime("now");

        $usages = UsageVoip::find()->actual()->andWhere(["actual_from" => $date->format("Y-m-d")])->all();

        $info = [];

        foreach ($usages as $usage)
        {
            $tarif = $usage->currentTariff;
            $account = $usage->clientAccount;

            if (!$tarif || $tarif->status == "test") // тестовый тариф, или без тарифа вообще
            {
                if ($usage->actual_to != $now->format("Y-m-d")) // не выключенные сегодня
                {
                    if ($action == "set block")
                    {
                        if (!$account->is_blocked)
                        {
                            $info[] = $now->format("Y-m-d").": ".$usage->E164.", from: ".$usage->actual_from.": set block ".$tarif->status;

                            $account->is_blocked = 1;
                            $account->save();
                        }
                    }

                    if ($action == "set off")
                    {
                        $info[] = $now->format("Y-m-d").": ".$usage->E164.", from: ".$usage->actual_from.": set off";

                        $model = new UsageVoipEditForm();
                        $model->initModel($usage->clientAccount, $usage);
                        $model->disconnecting_date = $now->format("Y-m-d");
                        $model->edit();
                    }
                }
            }
        }

        return $info;
    }
}
