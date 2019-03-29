<?php

namespace app\commands;

use Yii;
use yii\console\Controller;

class CallsRawCacheController extends Controller
{
    /**
     * Кеширование данных из таблицы call_raw на уровне СУБД
     *
     * @param string|null $beginning
     * @param string|null $ending
     */
    public function actionMakeCache($beginning = null, $ending = null)
    {
        $params = $beginning && $ending ?
            ("'" . str_replace('_', ' ', $beginning) . "', '" . str_replace('_', ' ', $ending) . "'") : '';
        echo sprintf('Caching with range: %s', $params) . PHP_EOL;
        echo 'Status: ' .  Yii::$app->dbPg
            ->createCommand("select calls_raw_cache.make_calls_raw_cache({$params});")
            ->queryScalar() . PHP_EOL;
    }
}
