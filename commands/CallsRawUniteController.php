<?php

namespace app\commands;

use Yii;
use yii\console\Controller;

class CallsRawUniteController extends Controller
{
    /**
     * Склеивание данных из таблицы call_raw на уровне СУБД
     *
     */
    public function actionMakeUnite()
    {
        echo 'Status: ' .  Yii::$app->dbPg
            ->createCommand("select calls_raw_unite.make_calls_raw_unite();")
            ->queryScalar() . PHP_EOL;
    }
}
