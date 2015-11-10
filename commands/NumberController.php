<?php
namespace app\commands;

use Yii;
use DateTime;
use yii\console\Controller;
use app\models\Number;
use app\models\UsageVoip;
use app\models\Region;


class NumberController extends Controller
{
    public function actionReleaseFromHold()
    {
        $numbers =
            Number::find()
                ->andWhere(['status' => Number::NUMBER_STATUS_HOLD])
                ->andWhere('hold_to < NOW()')
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

    public function actionPreloadDetailReport()
    {
        echo "\n".date("r").": start";
        if (date("N") > 5) {
            echo "\n".date("r").": non working day";
        } else {
            foreach(Region::find()->all() as $region) {
                echo "\n".date("r").": region ".$region->id;
                Number::dao()->getCallsWithoutUsages($region->id);
            }
        }
        echo "\n".date("r").": end";
    }

}
