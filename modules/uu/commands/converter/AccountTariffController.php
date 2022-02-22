<?php

namespace app\modules\uu\commands\converter;

use app\exceptions\ModelValidationException;
use app\models\Trouble;
use app\models\TroubleRoistat;
use app\modules\uu\models\AccountEntry;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffHeap;
use app\modules\uu\models\AccountTariffLog;
use app\modules\uu\models\AccountTrouble;
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
            echo 'Удаление временных таблиц uu_account_tariff_heap_virtual, test_connect_date_virtual, disconnect_date_virtual, date_before_sale_virtual если они существуют...' . PHP_EOL;
            $db->createCommand("
                DROP TEMPORARY TABLE IF EXISTS test_connect_date_virtual;
                DROP TEMPORARY TABLE IF EXISTS disconnect_date_virtual;
                DROP TEMPORARY TABLE IF EXISTS date_sale_virtual;
                DROP TEMPORARY TABLE IF EXISTS date_before_sale_virtual;
                DROP TEMPORARY TABLE IF EXISTS {$accountTariffHeapTableName}_virtual;
            ")->execute();
            echo 'Создание временной таблицы test_connect_date_virtual' . PHP_EOL;
            $db->createCommand("
                CREATE TEMPORARY TABLE test_connect_date_virtual (INDEX(account_tariff_id)) AS
                  SELECT
                    uatl.account_tariff_id account_tariff_id,
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
                    uatl.account_tariff_id;
            ")->execute();
            echo 'Создание временной таблицы disconnect_date_virtual' . PHP_EOL;
            $db->createCommand("
                CREATE TEMPORARY TABLE disconnect_date_virtual (INDEX(account_tariff_id)) AS
                  SELECT
                    uatl.account_tariff_id account_tariff_id,
                    MAX(uatl.actual_from_utc) actual_from_utc
                  FROM
                    {$accountTariffLogTableName} uatl
                  WHERE
                    uatl.tariff_period_id IS NULL
                  GROUP BY
                    uatl.account_tariff_id;
            ")->execute();
            echo 'Создание временной таблицы date_sale_virtual' . PHP_EOL;
            $db->createCommand("
                CREATE TEMPORARY TABLE date_sale_virtual (INDEX(client_account_id)) AS
                  SELECT
                    uat.client_account_id client_account_id,
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
                  GROUP BY 
                    uat.client_account_id;
            ")->execute();
            echo 'Создание временной таблицы date_before_sale_virtual' . PHP_EOL;
            $db->createCommand("
                CREATE TEMPORARY TABLE date_before_sale_virtual (INDEX(account_tariff_id)) AS
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
                    uat.id;
            ")->execute();
            echo 'Создание временной таблицы uu_account_tariff_heap_virtual' . PHP_EOL;
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
                        test_connect_date_virtual.actual_from_utc test_connect_date,
                        disconnect_date_virtual.actual_from_utc disconnect_date,
                        -- если дата продажи + 2 недели больше, чем даты допродажи, то дата допродажи и будет датой продажи
                        CASE WHEN DATE_ADD(date_sale_virtual.actual_from_utc, INTERVAL 2 WEEK) >= date_before_sale_virtual.actual_from_utc
                          THEN date_before_sale_virtual.actual_from_utc
                        END sale_date,
                        -- если дата продажи + 2 недели меньше, чем дата допродажи, то дата допродажи сохраняется
                        CASE WHEN DATE_ADD(date_sale_virtual.actual_from_utc, INTERVAL 2 WEEK) < date_before_sale_virtual.actual_from_utc
                          THEN date_before_sale_virtual.actual_from_utc
                        END sale_before_date
                      FROM {$accountTariffTableName} uat
                        -- получение информации для колонки 'Дата включения на тестовый тариф, utc'
                        LEFT JOIN test_connect_date_virtual
                          ON uat.id = test_connect_date_virtual.account_tariff_id
                        -- получение информации для колонки 'Дата отключения, utc'
                        LEFT JOIN disconnect_date_virtual
                          ON uat.id = disconnect_date_virtual.account_tariff_id
                        -- получение минимальной даты по всем коммерческим услугам конкретного клиента
                        LEFT JOIN date_sale_virtual
                          ON uat.client_account_id = date_sale_virtual.client_account_id
                        -- получение минимальной даты по каждой коммерческой услуге конкретного клиента
                        LEFT JOIN date_before_sale_virtual
                          ON uat.id = date_before_sale_virtual.account_tariff_id
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
                  disconnect_date = uathv.disconnect_date;
            ")->execute();
            echo 'Удаление временных таблиц uu_account_tariff_heap_virtual, test_connect_date_virtual, disconnect_date_virtual, date_before_sale_virtual если они существуют...' . PHP_EOL;
            $db->createCommand("
                DROP TEMPORARY TABLE IF EXISTS test_connect_date_virtual;
                DROP TEMPORARY TABLE IF EXISTS disconnect_date_virtual;
                DROP TEMPORARY TABLE IF EXISTS date_sale_virtual;
                DROP TEMPORARY TABLE IF EXISTS date_before_sale_virtual;
                DROP TEMPORARY TABLE IF EXISTS {$accountTariffHeapTableName}_virtual;
            ")->execute();
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            echo $e->getMessage() . PHP_EOL;
        }
    }

    /**
     * Заполнение roistat price
     *
     * @throws ModelValidationException
     */
    public function actionFillRoistatPrice()
    {
        $arr = AccountTrouble::find()
            ->select(['group_concat(account_tariff_id)'])
            ->asArray()
            ->indexBy('trouble_id')
            ->groupBy('trouble_id')
            ->column();

        foreach ($arr as $troubleId => $accountTariffIds) {
            $accountTariffIds = explode(',', $accountTariffIds);
            $packageIds = AccountTariff::find()->select('id')->where(['prev_account_tariff_id' => $accountTariffIds])->column();
            $accountTariffIds = array_unique(array_merge($accountTariffIds, $packageIds));
            $troubleRoistat = TroubleRoistat::findOne(['trouble_id' => $troubleId]);
            $newPrice = AccountEntry::find()->select('sum(price_with_vat)')->where(['account_tariff_id' => $accountTariffIds])->scalar();
            if (!$troubleRoistat || !is_numeric($newPrice)) {
                continue;
            }

            $roistatPrice = round($troubleRoistat->roistat_price, 2);
            $newPrice = round($newPrice, 2);

            if ($roistatPrice == $newPrice) {
                continue;
            }

            $troubleRoistat->roistat_price = $newPrice;
            if (!$troubleRoistat->save()) {
                throw new ModelValidationException($troubleRoistat);
            }
            if ($trouble = $troubleRoistat->trouble) {
                $trouble->setIsChanged();
            }
        }
    }
}
