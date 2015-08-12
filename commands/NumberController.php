<?php
namespace app\commands;

use app\models\Number;
use Yii;
use yii\console\Controller;


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

        $numbers =
            Number::find()
                ->andWhere(['status' => Number::STATUS_HOLD])
                ->andWhere("number like '7495%'")
                ->all(); /** @var Number[] $numbers */

        foreach ($numbers as $number) {
            Number::dao()->stopHold($number);
            echo $number->number . " unholded\n";
        }

    }
}
