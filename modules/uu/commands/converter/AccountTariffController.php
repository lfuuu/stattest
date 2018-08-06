<?php

namespace app\modules\uu\commands\converter;

use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffHeap;
use app\modules\uu\models\AccountTariffLog;
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffPeriod;
use app\modules\uu\models\TariffStatus;
use yii\console\Controller;

class AccountTariffController extends Controller
{
    public function actionRestoreDates()
    {
        $accountTariffTableName = AccountTariff::tableName();
        $accountTariffHeapTableName = AccountTariffHeap::tableName();
        $accountTariffLogTableName = AccountTariffLog::tableName();
        $tariffPeriodTableName = TariffPeriod::tableName();
        $tariffTableName = Tariff::tableName();

        $tariffTestStatuses = implode(',', TariffStatus::TEST_LIST);
        $db = AccountTariffHeap::getDb();
        $transaction = $db->beginTransaction();
        try {
            echo 'Сброс устаревших данных' . PHP_EOL;
            AccountTariffHeap::updateAll([
                'test_connect_date' => null,
                'date_sale' => null,
                'date_before_sale' => null,
                'disconnect_date' => null,
            ]);
            echo 'Удаление временной таблицы, если она существует...' . PHP_EOL;
            $db->createCommand("
                DROP TEMPORARY TABLE IF EXISTS {$accountTariffHeapTableName}_virtual;
            ")->execute();
            echo 'Создание временной таблицы...' . PHP_EOL;
            $db->createCommand("
                CREATE TEMPORARY TABLE {$accountTariffHeapTableName}_virtual (INDEX(account_tariff_id)) AS
                  SELECT
                    uat.id account_tariff_id,
                    prepared.test_connect_date test_connect_date,
                    prepared.disconnect_date disconnect_date,
                    prepared.sale_date sale_date,
                    prepared.sale_before_date sale_before_date
                  FROM {$accountTariffTableName} uat
                    LEFT JOIN (
                      -- основной суб-запрос, собирающий всю необходимую информацию для обновления
                      SELECT
                        uat.id account_tariff_id,
                        selected_test_connect_date.actual_from_utc test_connect_date,
                        selected_disconnect_date.actual_from_utc disconnect_date,
                        -- если дата продажи + 2 недели больше, чем даты допродажи, то дата допродажи и будет датой продажи
                        CASE WHEN DATE_ADD(selected_date_sale.actual_from_utc, INTERVAL 2 WEEK) >= selected_date_before_sale.actual_from_utc
                          THEN selected_date_before_sale.actual_from_utc
                        END sale_date,
                        -- если дата продажи + 2 недели меньше, чем дата допродажи, то дата допродажи сохраняется
                        CASE WHEN DATE_ADD(selected_date_sale.actual_from_utc, INTERVAL 2 WEEK) < selected_date_before_sale.actual_from_utc
                          THEN selected_date_before_sale.actual_from_utc
                        END sale_before_date
                      FROM {$accountTariffTableName} uat
                        -- получение информации для колонки 'Дата включения на тестовый тариф, utc'
                        LEFT JOIN (
                          SELECT
                            uatl.account_tariff_id,
                            MIN(uatl.actual_from_utc) actual_from_utc
                          FROM {$accountTariffLogTableName} uatl
                            INNER JOIN {$tariffPeriodTableName} utp
                              ON uatl.tariff_period_id = utp.id
                            INNER JOIN {$tariffTableName} ut
                              ON ut.id = utp.tariff_id
                          WHERE
                            uatl.tariff_period_id IS NOT NULL
                            AND
                            ut.tariff_status_id IN ({$tariffTestStatuses})
                          GROUP BY
                            uatl.account_tariff_id
                        ) selected_test_connect_date
                          ON uat.id = selected_test_connect_date.account_tariff_id
                        -- получение информации для колонки 'Дата отключения, utc'
                        LEFT JOIN (
                          SELECT
                            uatl.account_tariff_id,
                            MAX(uatl.actual_from_utc) actual_from_utc
                          FROM
                            {$accountTariffLogTableName} uatl
                          WHERE
                            uatl.tariff_period_id IS NULL
                          GROUP BY
                            uatl.account_tariff_id
                        ) selected_disconnect_date
                          ON uat.id = selected_disconnect_date.account_tariff_id
                        -- получение минимальной даты по всем коммерческим услугам конкретного клиента
                        LEFT JOIN (
                          SELECT
                            uat.client_account_id,
                            MIN(uatl.actual_from_utc) actual_from_utc
                          FROM
                            {$accountTariffLogTableName} uatl
                            INNER JOIN {$accountTariffTableName} uat
                              ON uatl.account_tariff_id = uat.id
                            LEFT JOIN {$tariffPeriodTableName} utp
                              ON uatl.tariff_period_id = utp.id
                            LEFT JOIN {$tariffTableName} ut
                              ON utp.tariff_id = ut.id
                          WHERE
                            uatl.tariff_period_id IS NOT NULL
                            AND
                            ut.tariff_status_id NOT IN ({$tariffTestStatuses})
                            AND
                            uat.prev_account_tariff_id IS NULL
                          GROUP BY uat.client_account_id
                        ) selected_date_sale
                          ON uat.client_account_id = selected_date_sale.client_account_id
                        -- получение минимальной даты по каждой коммерческой услуге конкретного клиента
                        LEFT JOIN (
                          SELECT
                            uat.id account_tariff_id,
                            MIN(uatl.actual_from_utc) actual_from_utc
                          FROM
                            {$accountTariffTableName} uat
                            LEFT JOIN {$accountTariffLogTableName} uatl
                              ON uat.id = uatl.account_tariff_id
                            LEFT JOIN {$tariffPeriodTableName} utp
                              ON uatl.tariff_period_id = utp.id
                            LEFT JOIN {$tariffTableName} ut
                              ON utp.tariff_id = ut.id
                          WHERE
                            uatl.tariff_period_id IS NOT NULL
                            AND
                            ut.tariff_status_id NOT IN ({$tariffTestStatuses})
                            AND
                            uat.prev_account_tariff_id IS NULL
                          GROUP BY
                            uat.id
                        ) selected_date_before_sale
                          ON uat.id = selected_date_before_sale.account_tariff_id
                    ) prepared
                      ON uat.id = prepared.account_tariff_id;
            ")->execute();
            echo 'Обновление данных...' . PHP_EOL;
            $testTariffStatutes = implode(',', TariffStatus::TEST_LIST);
            $db->createCommand("
                INSERT INTO
                  {$accountTariffHeapTableName} (
                    account_tariff_id,
                    test_connect_date,
                    date_sale,
                    date_before_sale,
                    disconnect_date
                  )
                SELECT
                  account_tariff_id,
                  test_connect_date,
                  sale_date,
                  sale_before_date,
                  disconnect_date
                FROM
                  {$accountTariffHeapTableName}_virtual uathv
                ON DUPLICATE KEY UPDATE
                  test_connect_date = uathv.test_connect_date,
                  date_sale = uathv.sale_date,
                  date_before_sale = uathv.sale_before_date,
                  disconnect_date = uathv.disconnect_date
                ;
            ")->execute();
            echo 'Удаление временной таблицы, если она существует...' . PHP_EOL;
            $db->createCommand("
                DROP TEMPORARY TABLE IF EXISTS {$accountTariffHeapTableName}_virtual;
            ")->execute();
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            echo $e->getMessage() . PHP_EOL;
        }
    }
}