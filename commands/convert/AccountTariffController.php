<?php

namespace app\commands\convert;

use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffLog;
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffPeriod;
use app\modules\uu\models\TariffStatus;
use yii\console\Controller;

class AccountTariffController extends Controller
{
    public function actionSetDate()
    {
        $accountTariffTableName = AccountTariff::tableName();
        $accountTariffLog = AccountTariffLog::tableName();
        $tariffTableName = Tariff::tableName();
        $tariffPeriodTableName = TariffPeriod::tableName();

        $db = AccountTariff::getDb();

        // Создание временной таблицы для столбца test_connect_date
        $db->createCommand("
          CREATE TEMPORARY TABLE IF NOT EXISTS uatl_test_connect_date(INDEX(account_tariff_id)) AS (
            SELECT
              uatl.account_tariff_id,
              MIN(uatl.actual_from_utc) actual_from_utc
            FROM {$accountTariffLog} uatl
              INNER JOIN {$tariffPeriodTableName} utp
                ON uatl.tariff_period_id = utp.id
              INNER JOIN {$tariffTableName} ut
                ON ut.id = utp.tariff_id
            WHERE
              uatl.tariff_period_id IS NOT NULL AND
              ut.tariff_status_id IN (" . TariffStatus::ID_TEST . ", " . TariffStatus::ID_VOIP_8800_TEST . ")
            GROUP BY uatl.account_tariff_id
          )
        ")->execute();

        // Создание временной таблицы для столбца disconnect_date
        $db->createCommand("
            CREATE TEMPORARY TABLE IF NOT EXISTS uatl_disconnect_date(INDEX(account_tariff_id)) AS (
              SELECT
                  account_tariff_id,
                  MIN(actual_from_utc) actual_from_utc
                FROM
                  {$accountTariffLog}
                WHERE
                  tariff_period_id IS NULL
                GROUP BY
                  account_tariff_id
            )
        ")->execute();

        // Обновление данных
        $db->createCommand("
            UPDATE {$accountTariffTableName} account_tariff
              LEFT JOIN  uatl_test_connect_date 
                ON account_tariff.id = uatl_test_connect_date.account_tariff_id
              LEFT JOIN  uatl_disconnect_date
                ON account_tariff.id = uatl_disconnect_date.account_tariff_id
            SET 
              account_tariff.test_connect_date = uatl_test_connect_date.actual_from_utc,
              account_tariff.disconnect_date = uatl_disconnect_date.actual_from_utc
        ")->execute();
    }
}