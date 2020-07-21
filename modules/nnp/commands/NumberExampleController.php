<?php

namespace app\modules\nnp\commands;

use Yii;
use yii\console\Controller;

class NumberExampleController extends Controller
{
    /**
     * Полный пересчёт таблицы nnp.number_example на уровне СУБД
     *
     */
    public function actionRenew()
    {
        $time0 = microtime(true);
        echo 'Status: ' .  Yii::$app->dbPg
            ->createCommand("select nnp.number_example_renew();")
            ->queryScalar() . PHP_EOL;
        echo 'Done in ' . round(microtime(true) - $time0, 2).' sec' . PHP_EOL;
    }
}
