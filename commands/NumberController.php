<?php

namespace app\commands;

use app\classes\Assert;
use app\classes\enum\VoipRegistrySourceEnum;
use app\classes\voip\EmptyNumberFiller;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\billing\CallsRaw;
use app\models\ClientAccount;
use app\models\CounterInteropTrunk;
use app\models\EventQueue;
use app\models\important_events\ImportantEvents;
use app\models\important_events\ImportantEventsNames;
use app\models\important_events\ImportantEventsSources;
use app\models\LogTarif;
use app\models\Number;
use app\models\Region;
use app\models\Sorm7800;
use app\models\UsageVoip;
use app\modules\nnp\models\City;
use app\modules\nnp\models\Country;
use app\modules\nnp\models\NdcType;
use app\modules\nnp\models\Operator;
use app\modules\nnp\models\Region as nnpRegion;
use app\modules\sim\models\ImsiPartner;
use app\modules\uu\models\AccountTariff;
use DateTime;
use DateTimeImmutable;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\db\ActiveQuery;
use yii\db\Command;
use yii\helpers\ArrayHelper;

class NumberController extends Controller
{
    const CHUNK_SIZE_UPDATE = 500;

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
                    $today->format(DateTimeZoneHelper::DATE_FORMAT)
                ],
                [
                    "=",
                    "actual_to",
                    $yesterday->format(DateTimeZoneHelper::DATE_FORMAT)
                ]
            ])->all();

        foreach ($usages as $usage) {

            Number::dao()->actualizeStatusByE164($usage->E164);
            echo $today->format(DateTimeZoneHelper::DATE_FORMAT) . ": " . $usage->E164 . "\n";
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

        $loaded = ArrayHelper::index(\Yii::$app->dbPgSlave->createCommand("
          SELECT 
            ROUND(CAST(SUM(CASE WHEN cost > 0 THEN cost ELSE 0 END) AS NUMERIC), 2) AS income_sum, 
            ROUND(CAST(SUM(CASE WHEN cost < 0 THEN cost ELSE 0 END) AS NUMERIC), 2) AS outcome_sum, 
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

        return ExitCode::OK;
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

        return ExitCode::OK;
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

        return ExitCode::OK;
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

        return ExitCode::OK;
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
                `number` VARCHAR(15) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
                `calls_per_month` INT(11) NOT NULL,
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
            $sql = 'DROP TEMPORARY TABLE voip_numbers_tmp';
            Yii::$app->db->createCommand($sql)->execute();

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        echo PHP_EOL . date(DATE_ATOM) . PHP_EOL;
    }

    /**
     * Актуализировать статус всех используемых номеров
     *
     * @param bool $isReal
     */
    public function actionActualStatusAll($isReal = false)
    {
        $numbers = Number::find()->where([
            'status' => [
                Number::STATUS_INSTOCK,
                Number::STATUS_ACTIVE_TESTED,
                Number::STATUS_ACTIVE_COMMERCIAL,
                Number::STATUS_ACTIVE_CONNECTED,
                Number::STATUS_NOTACTIVE_RESERVED,
                Number::STATUS_NOTACTIVE_HOLD
            ]
        ]);

        $transaction = null;

        foreach ($numbers->each() as $number) {
            ob_start();

            $startStatus = $number->status;

            if (!$isReal) {
                $transaction = Yii::$app->db->beginTransaction();
            }

            $strOut = PHP_EOL . $number->number;
            Number::dao()->actualizeStatus($number);

            $number->refresh();

            if ($number->status != $startStatus) {
                $strOut .= " " . $startStatus . " => " . $number->status;
                echo $strOut;
            }

            if (!$isReal) {
                $transaction->rollBack();
            }
        }
    }

    /**
     * Актуализация статуса номера. Запускается каждый час.
     */
    public function actionActualHourly()
    {
        $now = new DateTime('now');
        $now->setTimezone(new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC));

        $startHour = clone $now;
        $startHour->setTime($now->format('H'), 0, 0);

        $endHour = clone $startHour;
        $endHour->setTime($now->format('H'), 59, 59);

        $numbers = [];

        $dtFormat = DateTimeZoneHelper::DATETIME_FORMAT;

        echo PHP_EOL . $startHour->format($dtFormat) . ":";

        // включение/отключение услуги
        $numbers += UsageVoip::find()->where([
            'or',
            ['between', 'activation_dt', $startHour->format($dtFormat), $endHour->format($dtFormat)],
            ['between', 'expire_dt', $startHour->format($dtFormat), $endHour->format($dtFormat)]
        ])
            ->select('E164')
            ->column();

        // применения тарифа
        if ($now->format("H") == 0) {
            $numbers += array_map(
                function (\app\models\LogTarif $logTariff) {
                    return $logTariff->usageVoip->E164;
                },
                LogTarif::find()
                    ->where([
                        'service' => UsageVoip::tableName(),
                        'date_activation' => $now->format(DateTimeZoneHelper::DATE_FORMAT)
                    ])
                    ->with('usageVoip')
                    ->all());
        }

        $numbers = array_unique($numbers);

        foreach ($numbers as $numberStr) {
            echo PHP_EOL . $numberStr;

            $number = Number::findOne(['number' => $numberStr]);

            if (!$number) {
                continue;
            }

            $prevStatus = $number->status;

            Number::dao()->actualizeStatus($number);

            $number->refresh();

            if ($prevStatus != $number->status) {
                echo " " . $prevStatus . " => " . $number->status;
            }
        }
    }

    /**
     * Заливка ННП данных в redis
     */
    public function actionFullNnpToRedis()
    {
        $this->_redisGetAndSet(Operator::find()->asArray(), 'operator');
        $this->_redisGetAndSet(Country::find()->asArray(), 'country', 'code');
        $this->_redisGetAndSet(City::find()->asArray(), 'city');
        $this->_redisGetAndSet(nnpRegion::find()->asArray(), 'region');
        $this->_redisGetAndSet(NdcType::find()->asArray(), 'ndcType');

        $this->_redisGetAndSet(Operator::find()->asArray(), 'operatorEn', 'id', 'name_translit');
        $this->_redisGetAndSet(Country::find()->asArray(), 'countryEn', 'code', 'name_eng');
        $this->_redisGetAndSet(City::find()->asArray(), 'cityEn', 'id', 'name_translit');
    }

    private function _redisGetAndSet(ActiveQuery $query, $prefix, $id = 'id', $name = 'name')
    {
        echo PHP_EOL . $prefix;

        /** @var yii\redis\Cache $redis */
        $redis = \Yii::$app->redis;

        foreach ($query->each() as $o) {
            $redis->set($prefix . ':' . $o[$id], $o[$name]);
        }
    }

    /**
     * Сохранение номеров 7800 для отдачи в СОРМ
     *
     * @param int $regionId
     * @return int
     * @throws ModelValidationException
     */
    public function actionSorm7800($regionId)
    {
        Assert::isNotEmpty($regionId);

        $code = Region::find()->select('code')->where(['id' => $regionId])->scalar();

        Assert::isNotEmpty($code);

        $fromNumber = $code . str_repeat('0', 11 - strlen($code));
        $toNumber = $code . str_repeat('9', 11 - strlen($code));

        $from7800 = '78000000000';
        $to7800 = '78009999999';
        $dateStr = (new DateTimeImmutable('now'))->modify('-3 hours')->format(DateTimeZoneHelper::DATETIME_FORMAT);

        // получаем номера 7800 из таблицы звонков для запрашиваеого региона
        $query = CallsRaw::find()
            ->where(['>', 'connect_time', $dateStr]);

        $query2 = clone $query;

        $query->select(['number' => 'src_number'])
            ->andWhere(['between', 'src_number', $from7800, $to7800])
            ->andWhere(['between', 'dst_number', $fromNumber, $toNumber]);

        $query2->select(['number' => 'dst_number'])
            ->andWhere(['between', 'dst_number', $from7800, $to7800])
            ->andWhere(['between', 'src_number', $fromNumber, $toNumber]);

        $numbers = $query->union($query2)
            ->asArray()
            ->column();


        if (!$numbers) {
            return ExitCode::OK;
        }

        // осталвляем только наши номера
        $numbers = Number::find()
            ->where(['number' => $numbers])
            ->select('number')
            ->column();


        if (!$numbers) {
            return ExitCode::OK;
        }

        // уже сохраненные номера
        $alreadyNumbers = Sorm7800::find()
            ->where([
                'region_id' => $regionId,
                'number' => $numbers
            ])
            ->select('number')
            ->column();

        $toAdd = array_diff($numbers, $alreadyNumbers);

        // сохранение
        if ($toAdd) {
            foreach ($toAdd as $number) {
                $record = new Sorm7800();
                $record->region_id = $regionId;
                $record->number = $number;

                if (!$record->save()) {
                    throw new ModelValidationException($record);
                }

                echo PHP_EOL . $number;
            }
        }
    }

    public function actionFillNonUsedNumbers()
    {
        $e = new EmptyNumberFiller();

        \Yii::$app->db->createCommand('truncate voip_service_empty')->execute();

        $all = $e->get();
        array_walk($all, function (&$a) {
            $a = [$a['number'], $a['client_id'], $a['activation_dt'], $a['expire_dt'] ?: null];
        });

        \Yii::$app->db
            ->createCommand()
            ->batchInsert('voip_service_empty', [
                'number',
                'client_id',
                'activation_dt',
                'expire_dt'
            ], $all)
            ->execute();
    }

    /**
     * @param Command $command
     * @param int $isProcess
     * @return int
     * @throws \yii\db\Exception
     */
    protected function execCommandOrPrint(Command $command, $isProcess = 0)
    {
        if ($isProcess) {
            return $command->execute();
        }

        echo '---' .PHP_EOL;
        echo $command->getRawSql() .PHP_EOL;
        echo '---' .PHP_EOL;

        return 0;
    }

    /**
     * Генерит sql для массового UPDATE'а
     *
     * @param $table string table name
     * @param $key string conditional primary key, see the case in switch
     * @param $val string modify the primary key
     * @param $data array $key and the data carrier corresponding to the $val primary key
     * @return string batch update SQL
     */
    protected function getBatchUpdateSql($table, $key, $val, $data){
        $ids = implode(",", array_column($data, $key));

        $condition = " ";
        foreach ($data as $v){
            $condition .= "WHEN {$v[$key]} THEN {$v[$val]} ";
        }
        $sql = "UPDATE `{$table}` SET  {$val} = (CASE {$key} {$condition} END) WHERE {$key} IN ({$ids})";

        return $sql;
    }

    /**
     * Шаг 1. Актуализация данных в таблице номеров voip_numbers.
     * Проставляем всем мобильным номерам МСН Телеком
     * с источником = 'operaror', источник 'regulator' и MVNO-партнер Теле2.
     *
     * @param int $isProcess обработать
     * @throws \yii\db\Exception
     */
    public function actionActualizeSourceOperator($isProcess = 0)
    {
        if ($operatorId = Number::getMCNOperatorId()) {
            $attributes = [
                'source' => VoipRegistrySourceEnum::REGULATOR,
                'mvno_partner_id' => ImsiPartner::ID_TELE2,
            ];
            $condition = [
                'ndc_type_id' => NdcType::ID_MOBILE,
                'nnp_operator_id' => $operatorId,
                'source' => VoipRegistrySourceEnum::OPERATOR,
            ];

            $command = Number::getDb()->createCommand();
            $command->update(Number::tableName(), $attributes, $condition);

            $count = $this->execCommandOrPrint($command, $isProcess);
            echo('Numbers updated: ' . $count) . PHP_EOL;
        }

        echo 'Done!' . PHP_EOL;
    }

    /**
     * Шаг 2. Прогоняем все мобильные номера ДЭНИ КОЛЛ через API проверки оператора
     * и если оператор МСН - обновляем поле nnp_operator_id.
     *
     * @param int $isProcess обработать
     * @param int $offset
     * @param int $limit
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function actionActualizeDeniCall($isProcess = 0, $offset = 0, $limit = 0)
    {
        if ($operatorId = Number::getMCNOperatorId()) {
            $condition = [
                'ndc_type_id' => NdcType::ID_MOBILE,
                'nnp_operator_id' => Operator::ID_DENI_CALL,
            ];

            $numbers = Number::find()
                ->andWhere($condition)
                ->orderBy('number');

            if ($offset) {
                $numbers->offset($offset);
            }

            if ($limit) {
                $numbers->limit($limit);
            }

            $updates = [];
            $total = $numbers->count();
            echo ('Found numbers to check: ' . $total) . PHP_EOL;

            $total = $limit ?? $total;
            $i = $offset;
            foreach ($numbers->each() as $number) {
                /** @var Number $number */
                $percent = intval(100*$i / $total);
                echo sprintf("Fetching number %s: %s of %s-%s (%s%%)... ", $number->number, ++$i, $offset, $offset + $total, $percent);

                try {
                    $isMcnNumber = $number->isMcnNumber();
                } catch (\Exception $e) {
                    echo 'error: ' . $e->getMessage(). ', sleeping 5 sec... ';
                    sleep(5);
                    $isMcnNumber = $number->isMcnNumber();
                }

                if ($isMcnNumber) {
                    $updates[] = [
                        'number' => $number->number,
                        'nnp_operator_id' => $operatorId
                    ];
                    echo 'has been ported to MCN!';
                }
                echo PHP_EOL;
            }
        }

        if ($updates) {
            echo('Numbers ported found: ' . count($updates)) . PHP_EOL;

            foreach (array_chunk($updates, static::CHUNK_SIZE_UPDATE) as $chunk) {
                $sql = $this->getBatchUpdateSql(Number::tableName(), 'number', 'nnp_operator_id', $chunk);
                $commandPorted = Number::getDb()->createCommand($sql);

                $count = $this->execCommandOrPrint($commandPorted, $isProcess);
                echo ('Numbers updated: ' . $count) . PHP_EOL;
            }
        }

        echo 'Done!' . PHP_EOL;
    }

    /**
     * Шаг 3. Удаляем все мобильные номера, оставшиеся после проверки принадлежащими ДЭНИ КОЛЛ.
     *
     * @param int $isProcess обработать
     * @throws \yii\db\Exception
     */
    public function actionDeleteDeniCall($isProcess = 0)
    {
        $condition = [
            'ndc_type_id' => NdcType::ID_MOBILE,
            'nnp_operator_id' => Operator::ID_DENI_CALL,
        ];

        $command = Number::getDb()->createCommand();
        $command->delete(Number::tableName(), $condition);

        $count = $this->execCommandOrPrint($command, $isProcess);
        echo('Numbers deleted: ' . $count) . PHP_EOL;

        echo 'Done!' . PHP_EOL;
    }

    /**
     * Шаг 4. Для всех номеров, у которых не совпадает client_id
     * со значением client_account_id активной записи из uu_account_tariff,
     * обновляем client_id и uu_account_tariff_id.
     *
     * @param int $isProcess обработать
     * @throws \yii\db\Exception
     */
    public function actionActualizeForeignKeys($isProcess = 0)
    {
        $numbersUuUsage = Number::find()
            ->from(Number::tableName() . ' v')
            ->innerJoin(['u' => AccountTariff::tableName()], 'u.voip_number = v.number')
            ->select('v.number')
            ->andWhere('v.number is not null')
            ->andWhere('v.client_id != u.client_account_id OR u.id != v.uu_account_tariff_id')
            ->andWhere('u.tariff_period_id is not null IS NOT NULL')
            ->column();

        $numbersUsageVoip = Number::find()
            ->from(Number::tableName() . ' v')
            ->innerJoin(['uv' => UsageVoip::tableName()], 'uv.id = v.usage_id')
            ->innerJoin(['c' => ClientAccount::tableName()], 'c.client = uv.client')
            ->select('v.number')
            ->andWhere('v.number is not null')
            ->andWhere('v.usage_id is not null')
            ->andWhere('c.id != v.client_id')
            ->column();

        $numbers = array_merge($numbersUuUsage, $numbersUsageVoip);
        $numbers = array_unique($numbers);
        if ($numbers) {
            sort($numbers);
            $count = 0;
            $i = 0;

            $transaction = Number::getDb()->beginTransaction();
            try {
                foreach ($numbers as $number) {
                    echo sprintf('Creating actualizing event, number %s: %s of %s', $number, ++$i, count($numbers)) . PHP_EOL;

                    if ($isProcess) {
                        EventQueue::go(EventQueue::ACTUALIZE_NUMBER, ['number' => $number]);
                    }
                }

                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollBack();
                $errorText = $e->getMessage();

                echo ('Error occurred while actualizing: ' . $errorText) . PHP_EOL;
            }

            echo ('Total numbers actualized: ' . $count) . PHP_EOL;
        }

        echo 'Done!' . PHP_EOL;
    }

    /**
     * Шаг 5. Проверяем все мобильные номера в статусе 'Откреплен' через API проверки оператора
     * и если изменился оператор - обновляем поле nnp_operator_id и генерим важное событие
     *
     * @param int $isProcess обработать
     * @throws \yii\db\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionActualizeReleased($isProcess = 0)
    {
        if ($operatorId = Number::getMCNOperatorId()) {
            $condition = [
                'ndc_type_id' => NdcType::ID_MOBILE,
                'status' => Number::STATUS_RELEASED,
            ];
            $operator = Operator::findOne(['id' => $operatorId]);
            $operatorName = $operator ? $operator->name : '';

            $dateTime = new \DateTime();
            $date = $dateTime->format('d.m.Y');

            $numbers = Number::find()
                ->andWhere($condition)
                ->orderBy('number');

            $updates = [];
            $allOperatorIds = [];
            $eventsFrom = [];
            $eventsTo = [];

            $total = $numbers->count();
            $i = 0;
            foreach ($numbers->each() as $number) {
                /** @var Number $number */
                $percent = intval(100*$i / $total);
                echo sprintf("Fetching number %s: %s of %s (%s%%)... ", $number->number, ++$i, $total, $percent);

                try {
                    $data = $number->getNnpInfoData();
                } catch (\Exception $e) {
                    echo 'error: ' . $e->getMessage(). ', sleeping 5 sec... ';
                    sleep(5);
                    $data = $number->getNnpInfoData();
                }

                if (!empty($data['nnp_operator_id'])) {
                    $newOperatorId = $data['nnp_operator_id'];
                    if ($number->nnp_operator_id != $newOperatorId) {
                        $updates[] = [
                            'number' => $number->number,
                            'nnp_operator_id' => $newOperatorId
                        ];
                        echo sprintf('operator been changed %s -> %s! ', $number->nnp_operator_id, $newOperatorId);

                        if ($newOperatorId == $operatorId) {
                            echo 'Ported to MCN!';

                            $eventsTo[] = [
                                'date' => $date,
                                'client_id' => $number->client_id,
                                'operator_from_id' => $number->nnp_operator_id,
                                'operator_from_name' => '',
                                'operator_to_id' => $operatorId,
                                'operator_to_name' => $operatorName,
                            ];
                            $allOperatorIds[$number->nnp_operator_id] = $number->nnp_operator_id;
                        } else if ($number->nnp_operator_id == $operatorId) {
                            echo 'Ported from MCN!';

                            $eventsFrom[] = [
                                'date' => $date,
                                'client_id' => $number->client_id,
                                'operator_from_id' => $operatorId,
                                'operator_from_name' => $operatorName,
                                'operator_to_id' => $newOperatorId,
                                'operator_to_name' => '',
                            ];
                            $allOperatorIds[$newOperatorId] = $newOperatorId;
                        }
                    }
                }
                echo PHP_EOL;
            }

            $this->processReleasedAndPorted($updates, $allOperatorIds, $eventsFrom, $eventsTo, $isProcess);
        }

        echo 'Done!' . PHP_EOL;
    }

    /**
     * @param array $updates
     * @param array $allOperatorIds
     * @param array $eventsFrom
     * @param array $eventsTo
     * @param bool $isProcess
     * @return bool
     * @throws \yii\db\Exception
     */
    protected function processReleasedAndPorted(array $updates, array $allOperatorIds, array $eventsFrom, array $eventsTo, $isProcess)
    {
        if (empty($updates)) {
            return true;
        }

        $operators = [];
        $allOperatorIds = array_filter($allOperatorIds);
        if ($allOperatorIds) {
            $operators = Operator::find()
                ->andWhere(['id' => $allOperatorIds])
                ->indexBy('id')
                ->all();
        }

        $transaction = Number::getDb()->beginTransaction();
        try {
            echo ('Found numbers with operator changed: ' . count($updates)) . PHP_EOL;

            foreach (array_chunk($updates, static::CHUNK_SIZE_UPDATE) as $chunk) {
                $sql = $this->getBatchUpdateSql(Number::tableName(), 'number', 'nnp_operator_id', $chunk);
                $commandPorted = Number::getDb()->createCommand($sql);

                $count = $this->execCommandOrPrint($commandPorted, $isProcess);
                echo ('Numbers updated: ' . $count) . PHP_EOL;
            }

            foreach ($eventsFrom as $data) {
                $operatorId = $data['operator_to_id'];
                $data['operator_from_name'] = !empty($operators[$operatorId]) ? $operators[$operatorId]->name : '';

                if ($isProcess) {
                    ImportantEvents::create(
                        ImportantEventsNames::PORTING_FROM_MCN,
                        ImportantEventsSources::SOURCE_STAT,
                        $data
                    );
                }
            }

            foreach ($eventsTo as $data) {
                $operatorId = $data['operator_from_id'];
                $data['operator_from_name'] = !empty($operators[$operatorId]) ? $operators[$operatorId]->name : '';

                if ($isProcess) {
                    ImportantEvents::create(
                        ImportantEventsNames::PORTING_TO_MCN,
                        ImportantEventsSources::SOURCE_STAT,
                        $data
                    );
                }
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            $errorText = $e->getMessage();

            echo ('Error occurred while updating: ' . $errorText) . PHP_EOL;
        }

        return true;
    }
}
