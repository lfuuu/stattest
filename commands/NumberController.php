<?php
namespace app\commands;

use app\models\CounterInteropTrunk;
use Yii;
use DateTime;
use yii\console\Controller;
use app\models\Number;
use app\models\UsageVoip;
use app\models\Region;
use yii\helpers\ArrayHelper;


class NumberController extends Controller
{
    public function actionReleaseFromHold()
    {
        $numbers =
            Number::find()
                ->andWhere(['status' => Number::STATUS_NOTACTIVE_HOLD])
                ->andWhere('hold_to < NOW()')
                ->all();
        /** @var Number[] $numbers */

        foreach ($numbers as $number) {
            Number::dao()->stopHold($number);
            echo $number->number . " unholded\n";
        }
    }

    public function actionActualizeNumbersByUsages()
    {
        $today = new DateTime("now");
        $yesterday = (new DateTime("now"))->modify("-1 day");
        $usages = UsageVoip::find()->andWhere(
            [
                "or",
                [
                    "=",
                    "actual_from",
                    $today->format("Y-m-d")
                ],
                [
                    "=",
                    "actual_to",
                    $yesterday->format("Y-m-d")
                ]
            ])->all();

        foreach ($usages as $usage) {

            Number::dao()->actualizeStatusByE164($usage->E164);
            echo $today->format("Y-m-d") . ": " . $usage->E164 . "\n";
        }
    }

    public function actionPreloadDetailReport()
    {
        echo "\n" . date("r") . ": start";
        if (date("N") > 5) {
            echo "\n" . date("r") . ": non working day";
        } else {
            foreach (Region::find()->all() as $region) {
                echo "\n" . date("r") . ": region " . $region->id;
                Number::dao()->getCallsWithoutUsages($region->id);
            }
        }
        echo "\n" . date("r") . ": end";
    }

    public function actionUpdateInteropCounter()
    {
        $saved = CounterInteropTrunk::find()->indexBy('account_id')->asArray()->all();

        $loaded = ArrayHelper::index(\Yii::$app->dbPg->createCommand("
          select 
            ROUND(CAST(SUM(CASE WHEN cost > 0 THEN cost ELSE 0 END) as NUMERIC), 2) as income_sum, 
            ROUND(CAST(SUM(CASE WHEN cost < 0 THEN cost ELSE 0 END) as NUMERIC), 2) as outcome_sum, 
            account_id
          FROM \"calls_raw\".\"calls_raw_".date("Ym")."\" 
          WHERE 
                account_id IS NOT NULL 
            AND trunk_service_id IS NOT NULL 
          GROUP BY account_id")->queryAll(),
            'account_id');

        $savedAccounts = array_keys($saved);
        $loadAccounts = array_keys($loaded);

        $addAccounts = array_diff($loadAccounts, $savedAccounts);
        $delAccounts = array_diff($savedAccounts, $loadAccounts);

        $addRows = [];
        foreach($addAccounts as $accountId) {
            $row = $loaded[$accountId];
            $addRows[] = [$accountId, $row['income_sum'], $row['outcome_sum']];
        }

        $changedRows = [];
        foreach(array_intersect($savedAccounts, $loadAccounts) as $accountId) {
            $savedRow = $saved[$accountId];
            $loadedRow = $loaded[$accountId];

            if ($savedRow['income_sum'] != $loadedRow['income_sum'] || $savedRow['outcome_sum'] != $loadedRow['outcome_sum']) {

                $changedRow = [];

                if ($savedRow['income_sum'] != $loadedRow['income_sum']) {
                    $changedRow['income_sum'] = $loadedRow['income_sum'];
                }

                if ($savedRow['outcome_sum'] != $loadedRow['outcome_sum']) {
                    $changedRow['outcome_sum'] = $loadedRow['outcome_sum'];
                }

                $changedRows[$accountId] = $changedRow;
            }
        }

        CounterInteropTrunk::getDb()->transaction(function($db) use ($addRows, $delAccounts, $changedRows) {

            /** @var $db Connection */
            if ($addRows) {
                $db->createCommand()->batchInsert(CounterInteropTrunk::tableName(), ['account_id', 'income_sum', 'outcome_sum'], $addRows)->execute();
            }

            if ($delAccounts) {
                CounterInteropTrunk::deleteAll(['account_id' => $delAccounts]);
            }

            if ($changedRows) {
                foreach ($changedRows as $accountId => $row) {
                    CounterInteropTrunk::updateAll($row, ['account_id' => $accountId]);
                }
            }
        });
    }

}
