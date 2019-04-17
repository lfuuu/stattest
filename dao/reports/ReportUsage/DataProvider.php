<?php

namespace app\dao\reports\ReportUsage;

use Yii;

class DataProvider
{
    /**
     * Получение имени последней партицированной таблицы CallsRaw
     *
     * @return string
     */
    public static function getCallsRawLastTable()
    {
        $tableName = Yii::$app->dbPgStatistic
            ->createCommand(
                "SELECT tablename FROM pg_tables WHERE (tablename LIKE 'calls_raw_20%') ORDER BY tablename DESC LIMIT 1")
            ->queryScalar();

        return $tableName;
    }
}