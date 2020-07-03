<?php

namespace app\modules\nnp2\commands;

use Yii;
use yii\console\Controller;

class RangeShortController extends Controller
{
    /**
     * Полный пересчёт таблицы nnp2.range_short на уровне СУБД
     *
     */
    public function actionRenew()
    {
        $time0 = microtime(true);;
        echo 'Status: ' .  Yii::$app->dbPgNnp2
            ->createCommand("select nnp2.range_short_renew();")
            ->queryScalar() . PHP_EOL;
        echo 'Done in ' . round(microtime(true) - $time0, 2).' sec' . PHP_EOL;
    }
}
