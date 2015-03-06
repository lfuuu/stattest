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
}
