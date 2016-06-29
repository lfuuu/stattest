<?php
namespace app\commands;

use app\models\CounterInteropTrunk;
use app\models\Number;
use app\models\Region;
use app\models\UsageVoip;
use DateTime;
use DateTimeImmutable;
use Yii;
use yii\console\Controller;
use yii\helpers\ArrayHelper;


class NumberController extends Controller
{
    public function actionReleaseFromHold()
    {
        $numbers =
            Number::find()
                ->andWhere(['status' => Number::STATUS_NOTACTIVE_HOLD])
                ->andWhere('hold_to < NOW()')
                ->all();
        /** @var Number[] $numbers */

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
            [
                "or",
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

        foreach ($usages as $usage) {

            Number::dao()->actualizeStatusByE164($usage->E164);
            echo $today->format("Y-m-d") . ": " . $usage->E164 . "\n";
        }
    }

    public function actionPreloadDetailReport()
    {
        echo PHP_EOL . date("r") . ": start";
        if (date("N") > 5) {
            echo PHP_EOL . date("r") . ": non working day";
        } else {
            foreach (Region::find()->all() as $region) {
                echo PHP_EOL . date("r") . ": region " . $region->id;
                Number::dao()->getCallsWithoutUsages($region->id);
            }
        }
        echo PHP_EOL . date("r") . ": end";
    }

    public function actionUpdateInteropCounter()
    {
        echo PHP_EOL . PHP_EOL . date("r");
        $saved = CounterInteropTrunk::find()->indexBy('account_id')->asArray()->all();

        $loaded = ArrayHelper::index(\Yii::$app->dbPg->createCommand("
          select 
            ROUND(CAST(SUM(CASE WHEN cost > 0 THEN cost ELSE 0 END) as NUMERIC), 2) as income_sum, 
            ROUND(CAST(SUM(CASE WHEN cost < 0 THEN cost ELSE 0 END) as NUMERIC), 2) as outcome_sum, 
            account_id
          FROM \"calls_raw\".\"calls_raw_" . date("Ym") . "\" 
          WHERE 
                account_id IS NOT NULL 
            AND trunk_service_id IS NOT NULL 
          GROUP BY account_id")->queryAll(),
            'account_id');

        $savedAccounts = array_keys($saved);
        $loadAccounts = array_keys($loaded);

        $addAccounts = array_diff($loadAccounts, $savedAccounts);
        $delAccounts = array_diff($savedAccounts, $loadAccounts);

        $addRows = [];
        foreach ($addAccounts as $accountId) {
            $row = $loaded[$accountId];
            $addRows[] = [$accountId, $row['income_sum'], $row['outcome_sum']];
        }

        $changedRows = [];
        foreach (array_intersect($savedAccounts, $loadAccounts) as $accountId) {
            $savedRow = $saved[$accountId];
            $loadedRow = $loaded[$accountId];

            if ($savedRow['income_sum'] != $loadedRow['income_sum'] || $savedRow['outcome_sum'] != $loadedRow['outcome_sum']) {

                $changedRow = [];

                if ($savedRow['income_sum'] != $loadedRow['income_sum']) {
                    $changedRow['income_sum'] = $loadedRow['income_sum'];
                }

                if ($savedRow['outcome_sum'] != $loadedRow['outcome_sum']) {
                    $changedRow['outcome_sum'] = $loadedRow['outcome_sum'];
                }

                $changedRows[$accountId] = $changedRow;
            }
        }

        CounterInteropTrunk::getDb()->transaction(function ($db) use ($addRows, $delAccounts, $changedRows) {

            /** @var $db Connection */
            if ($addRows) {
                echo PHP_EOL . "add: [" . var_export($addRows, true) . "]";
                $db->createCommand()->batchInsert(CounterInteropTrunk::tableName(), ['account_id', 'income_sum', 'outcome_sum'], $addRows)->execute();
            }

            if ($delAccounts) {
                echo PHP_EOL . "del: [" . implode(", ", $delAccounts) . "]";
                CounterInteropTrunk::deleteAll(['account_id' => $delAccounts]);
            }

            if ($changedRows) {
                foreach ($changedRows as $accountId => $row) {
                    echo PHP_EOL . "change: " . str_replace(["\n", "array "], "", var_export(['account_id' => $accountId] + $row, true));
                    CounterInteropTrunk::updateAll($row, ['account_id' => $accountId]);
                }
            }
        });
    }

    /**
     * Пересчитать voip_numbers.calls_per_month_0/1/2
     */
    public function actionCalcCallsPerMonth()
    {
        $this->actionCalcCallsPerMonth0();
        $this->actionCalcCallsPerMonth1();
        $this->actionCalcCallsPerMonth2();

        return Controller::EXIT_CODE_NORMAL;
    }

    /**
     * /**
     * Пересчитать voip_numbers.calls_per_month_0
     */
    public function actionCalcCallsPerMonth0()
    {
        $dtFrom = (new \DateTimeImmutable("now", new \DateTimeZone("UTC")))
            ->modify("first day of this month, 00:00:00");

        $this->calcCallsPerMonth('calls_per_month_0', $dtFrom, $dtTo = null);

        return Controller::EXIT_CODE_NORMAL;
    }

    /**
     * Пересчитать voip_numbers.calls_per_month_1
     */
    public function actionCalcCallsPerMonth1()
    {
        $dtTo = (new \DateTimeImmutable("now", new \DateTimeZone("UTC")))
            ->modify("last day of -1 month, 23:59:59");
        $dtFrom = $dtTo->modify("first day of this month, 00:00:00");

        $this->calcCallsPerMonth('calls_per_month_1', $dtFrom, $dtTo);

        return Controller::EXIT_CODE_NORMAL;
    }

    /**
     * Пересчитать voip_numbers.calls_per_month_2
     */
    public function actionCalcCallsPerMonth2()
    {
        $dtTo = (new \DateTimeImmutable("now", new \DateTimeZone("UTC")))
            ->modify("last day of -2 month, 23:59:59");
        $dtFrom = $dtTo->modify("first day of this month, 00:00:00");

        $this->calcCallsPerMonth('calls_per_month_2', $dtFrom, $dtTo);

        return Controller::EXIT_CODE_NORMAL;
    }

    /**
     * Пересчитать voip_numbers.calls_per_month за один календарный месяц
     *
     * @param string $fieldName
     * @param \DateTime|\DateTimeImmutable $dtFrom
     * @param \DateTime|\DateTimeImmutable $dtTo
     */
    protected function calcCallsPerMonth($fieldName, $dtFrom, $dtTo)
    {
        echo PHP_EOL . $fieldName . ' ' . date(DATE_ATOM) . PHP_EOL;

        $transaction = Yii::$app->db->beginTransaction();
        try {
            // временная таблица для результата. Для multi-update
            $sql = 'CREATE TEMPORARY TABLE voip_numbers_tmp (
                `number` varchar(15) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
                `calls_per_month` int(11) NOT NULL,
                PRIMARY KEY (`number`)
                )';
            Yii::$app->db->createCommand($sql)->execute();

            // посчитать
            $values = [];
            $query = \app\models\Number::dao()->getCallsWithoutUsagesQuery($region = null, $dstNumber = null, $dtFrom, $dtTo);
            $command = $query->createCommand();
            foreach ($command->query() as $calls) {

                if (strlen($calls['u']) > 15) {
                    continue; // какой то левый номер
                }

                $values[] = sprintf("('%s', %d)", $calls['u'], $calls['c']);

                if (count($values) % 1000 === 0) {
                    // добавить во временную таблицу
                    $sql = 'INSERT INTO voip_numbers_tmp VALUES ' . implode(', ', $values);
                    Yii::$app->db->createCommand($sql)->execute();
                    $values = [];
                    echo '. ';
                }
            }

            if (count($values)) {
                // добавить во временную таблицу
                $sql = 'INSERT INTO voip_numbers_tmp VALUES ' . implode(', ', $values);
                Yii::$app->db->createCommand($sql)->execute();
            }
            echo '# ';

            // всё сбросить
            \app\models\Number::updateAll([$fieldName => 0]);

            // обновить
            $numberTableName = \app\models\Number::tableName();
            $sql = "UPDATE {$numberTableName}, voip_numbers_tmp
                SET {$numberTableName}.{$fieldName} = voip_numbers_tmp.calls_per_month
                WHERE {$numberTableName}.number = voip_numbers_tmp.number
            ";
            Yii::$app->db->createCommand($sql)->execute();

            // убрать за собой
            $sql = 'DROP TABLE voip_numbers_tmp';
            Yii::$app->db->createCommand($sql)->execute();

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        echo PHP_EOL . date(DATE_ATOM) . PHP_EOL;
    }
}
