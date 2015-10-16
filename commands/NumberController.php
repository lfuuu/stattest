<?php
namespace app\commands;

use Yii;
use DateTime;
use yii\console\Controller;
use app\models\Number;
use app\models\UsageVoip;


class NumberController extends Controller
{
    public function actionReleaseFromHold()
    {
        $numbers =
            Number::find()
                ->andWhere(['status' => Number::STATUS_HOLD])
                ->andWhere('hold_from < NOW() - INTERVAL 6 MONTH')
                ->all(); /** @var Number[] $numbers */

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
            ["or", 
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

        foreach($usages as $usage) {

            Number::dao()->actualizeStatusByE164($usage->E164);
            echo $today->format("Y-m-d").": ".$usage->E164."\n";
        }
    }
}
