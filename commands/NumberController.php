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
use app\modules\uu\models\ServiceType;
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
            ->innerJoin(['u' => AccountTariff::tableName()], 'u.voip_number = v.number AND service_type_id = ' . ServiceType::ID_VOIP)
            ->select('v.number')
            ->andWhere('v.number is not null')
            ->andWhere('v.client_id != u.client_account_id OR u.id != v.uu_account_tariff_id')
            ->andWhere('u.tariff_period_id IS NOT NULL')
            ->column();

        $numbersUsageVoip = Number::find()
            ->from(Number::tableName() . ' v')
            ->innerJoin(['uv' => UsageVoip::tableName()], 'uv.id = v.usage_id and cast(now() as date) between actual_from and actual_to')
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
            $allNumbers = [];
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
                    $operatorToId = $data['nnp_operator_id'];
                    if ($number->nnp_operator_id != $operatorToId) {
                        $updates[] = [
                            'number' => $number->number,
                            'nnp_operator_id' => $operatorToId
                        ];
                        echo sprintf('operator been changed %s -> %s! ', $number->nnp_operator_id, $operatorToId);

                        if ($operatorToId == $operatorId) {
                            echo 'Ported to MCN!';

                            $eventsTo[] = [
                                'client_id' => $number->client_id,
                                'number' => $number->number,
                                'date_ported' => $date,
                                'operator_from_id' => $number->nnp_operator_id,
                                'operator_from_name' => '',
                                'operator_to_id' => $operatorId,
                                'operator_to_name' => $operatorName,
                            ];
                            $allNumbers[$number->number] = $number->number;
                            $allOperatorIds[$number->nnp_operator_id] = $number->nnp_operator_id;
                        } else if ($number->nnp_operator_id == $operatorId) {
                            echo 'Ported from MCN!';

                            $eventsFrom[] = [
                                'client_id' => $number->client_id,
                                'number' => $number->number,
                                'date_ported' => $date,
                                'operator_from_id' => $operatorId,
                                'operator_from_name' => $operatorName,
                                'operator_to_id' => $operatorToId,
                                'operator_to_name' => '',
                            ];
                            $allNumbers[$number->number] = $number->number;
                            $allOperatorIds[$operatorToId] = $operatorToId;
                        }
                    }
                }
                echo PHP_EOL;
            }

            $this->processReleasedAndPorted($updates, $allNumbers, $allOperatorIds, $eventsFrom, $eventsTo, $isProcess);
        }

        echo 'Done!' . PHP_EOL;
    }

    /**
     * @param array $updates
     * @param array $allNumbers
     * @param array $allOperatorIds
     * @param array $eventsFrom
     * @param array $eventsTo
     * @param bool $isProcess
     * @return bool
     * @throws \yii\db\Exception
     */
    protected function processReleasedAndPorted(array $updates, array $allNumbers, array $allOperatorIds, array $eventsFrom, array $eventsTo, $isProcess)
    {
        if (empty($updates)) {
            return true;
        }

        $accountTariffsByNumber = [];
        $allNumbers = array_filter($allNumbers);
        if ($allNumbers) {
            $accountTariffsByNumber = AccountTariff::find()
                ->from(AccountTariff::tableName() . ' u')
                ->andWhere([
                    'u.voip_number' => $allNumbers,
                    'u.service_type_id' => ServiceType::ID_VOIP,
                ])
                ->andWhere('id = (SELECT MAX(id) FROM `uu_account_tariff` ua where ua.voip_number = u.voip_number)')
                ->indexBy('voip_number')
                ->all();
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
                if (empty($data['client_id'])) {
                    $number = $data['number'];
                    $data['client_id'] = !empty($accountTariffsByNumber[$number]) ? $accountTariffsByNumber[$number]->client_account_id : '';
                }
                $operatorId = $data['operator_to_id'];
                $data['operator_to_name'] = !empty($operators[$operatorId]) ? $operators[$operatorId]->name : '';

                if ($isProcess) {
                    ImportantEvents::create(
                        ImportantEventsNames::PORTING_FROM_MCN,
                        ImportantEventsSources::SOURCE_STAT,
                        $data
                    );
                } else {
                    echo ('Event ' . ImportantEventsNames::PORTING_FROM_MCN . ': ' . json_encode($data, JSON_UNESCAPED_UNICODE)) . PHP_EOL;
                }
            }

            foreach ($eventsTo as $data) {
                if (empty($data['client_id'])) {
                    $number = $data['number'];
                    $data['client_id'] = !empty($accountTariffsByNumber[$number]) ? $accountTariffsByNumber[$number]->client_account_id : '';
                }
                $operatorId = $data['operator_from_id'];
                $data['operator_from_name'] = !empty($operators[$operatorId]) ? $operators[$operatorId]->name : '';

                if ($isProcess) {
                    ImportantEvents::create(
                        ImportantEventsNames::PORTING_TO_MCN,
                        ImportantEventsSources::SOURCE_STAT,
                        $data
                    );
                } else {
                    echo ('Event ' . ImportantEventsNames::PORTING_TO_MCN . ': ' . json_encode($data, JSON_UNESCAPED_UNICODE)) . PHP_EOL;
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

    /**
     * Temporary
     * @param int $isProcess
     */
    public function actionActualizeTemp($isProcess = 0)
    {
        $log = <<<EOL
Fetching number 79006311844: 16 of 759 (1%)... operator been changed 6261 -> 6720! Ported to MCN!
Fetching number 79015625656: 25 of 759 (3%)... operator been changed 6720 -> 6261! Ported from MCN!
Fetching number 79015867671: 26 of 759 (3%)... operator been changed 6720 -> 35501! Ported from MCN!
Fetching number 79016262013: 28 of 759 (3%)... operator been changed 6720 -> 6261! Ported from MCN!
Fetching number 79016262015: 29 of 759 (3%)... operator been changed 6720 -> 6261! Ported from MCN!
Fetching number 79016280250: 31 of 759 (3%)... operator been changed 6720 -> 35501! Ported from MCN!
Fetching number 79016283759: 45 of 759 (5%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79016284624: 47 of 759 (6%)... operator been changed 6720 -> 6264! Ported from MCN!
Fetching number 79030000170: 53 of 759 (6%)... operator been changed 6720 -> 35310! Ported from MCN!
Fetching number 79031724808: 58 of 759 (7%)... operator been changed 6720 -> 6261! Ported from MCN!
Fetching number 79035600588: 62 of 759 (8%)... operator been changed 6720 -> 6261! Ported from MCN!
Fetching number 79036700263: 65 of 759 (8%)... operator been changed 6720 -> 7675! Ported from MCN!
Fetching number 79036724445: 66 of 759 (8%)... operator been changed 6720 -> 6261! Ported from MCN!
Fetching number 79036725545: 67 of 759 (8%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79037112479: 70 of 759 (9%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79037434313: 73 of 759 (9%)... operator been changed 6720 -> 7687! Ported from MCN!
Fetching number 79037446939: 74 of 759 (9%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79055477977: 90 of 759 (11%)... operator been changed 6720 -> 7675! Ported from MCN!
Fetching number 79055613344: 91 of 759 (11%)... operator been changed 6720 -> 6261! Ported from MCN!
Fetching number 79055787465: 92 of 759 (11%)... operator been changed 6720 -> 6261! Ported from MCN!
Fetching number 79057970949: 94 of 759 (12%)... operator been changed 6720 -> 6330! Ported from MCN!
Fetching number 79060468568: 96 of 759 (12%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79060777234: 97 of 759 (12%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79067170004: 100 of 759 (13%)... operator been changed 6720 -> 6457! Ported from MCN!
Fetching number 79096322092: 105 of 759 (13%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79096613146: 106 of 759 (13%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79099100582: 107 of 759 (13%)... operator been changed 6720 -> 7687! Ported from MCN!
Fetching number 79099977475: 108 of 759 (14%)... operator been changed 6720 -> 7675! Ported from MCN!
Fetching number 79119529335: 119 of 759 (15%)... operator been changed 6264 -> 6720! Ported to MCN!
Fetching number 79150009121: 134 of 759 (17%)... operator been changed 6720 -> 6457! Ported from MCN!
Fetching number 79150859598: 135 of 759 (17%)... operator been changed 6720 -> 6457! Ported from MCN!
Fetching number 79151627677: 136 of 759 (17%)... operator been changed 6720 -> 7687! Ported from MCN!
Fetching number 79153973979: 139 of 759 (18%)... operator been changed 6720 -> 6261! Ported from MCN!
Fetching number 79154567363: 140 of 759 (18%)... operator been changed 6720 -> 7675! Ported from MCN!
Fetching number 79161827262: 143 of 759 (18%)... operator been changed 6720 -> 6264! Ported from MCN!
Fetching number 79162030362: 145 of 759 (18%)... operator been changed 6720 -> 35501! Ported from MCN!
Fetching number 79165087081: 151 of 759 (19%)... operator been changed 6720 -> 7642! Ported from MCN!
Fetching number 79165335364: 152 of 759 (19%)... operator been changed 6720 -> 6457! Ported from MCN!
Fetching number 79165434445: 154 of 759 (20%)... operator been changed 6720 -> 6457! Ported from MCN!
Fetching number 79165466232: 155 of 759 (20%)... operator been changed 6720 -> 6261! Ported from MCN!
Fetching number 79166545647: 158 of 759 (20%)... operator been changed 6720 -> 7642! Ported from MCN!
Fetching number 79168815445: 165 of 759 (21%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79168842899: 166 of 759 (21%)... operator been changed 6720 -> 6457! Ported from MCN!
Fetching number 79169870911: 168 of 759 (22%)... operator been changed 6720 -> 6457! Ported from MCN!
Fetching number 79169878564: 169 of 759 (22%)... operator been changed 6720 -> 6264! Ported from MCN!
Fetching number 79182101265: 178 of 759 (23%)... operator been changed 6720 -> 6264! Ported from MCN!
Fetching number 79191055905: 186 of 759 (24%)... operator been changed 6720 -> 6330! Ported from MCN!
Fetching number 79191055906: 187 of 759 (24%)... operator been changed 6720 -> 6330! Ported from MCN!
Fetching number 79197207308: 189 of 759 (24%)... operator been changed 6720 -> 6261! Ported from MCN!
Fetching number 79197777119: 190 of 759 (24%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79199987849: 191 of 759 (25%)... operator been changed 6720 -> 6330! Ported from MCN!
Fetching number 79213700400: 193 of 759 (25%)... operator been changed 6720 -> 7675! Ported from MCN!
Fetching number 79218852164: 197 of 759 (25%)... operator been changed 6261 -> 6720! Ported to MCN!
Fetching number 79256575205: 207 of 759 (27%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79260113827: 209 of 759 (27%)... operator been changed 6720 -> 7675! Ported from MCN!
Fetching number 79261507420: 213 of 759 (27%)... operator been changed 6720 -> 7675! Ported from MCN!
Fetching number 79261591440: 214 of 759 (28%)... operator been changed 6720 -> 6457! Ported from MCN!
Fetching number 79262148943: 216 of 759 (28%)... operator been changed 6720 -> 6457! Ported from MCN!
Fetching number 79262162340: 217 of 759 (28%)... operator been changed 6720 -> 7642! Ported from MCN!
Fetching number 79262179373: 218 of 759 (28%)... operator been changed 6720 -> 35501! Ported from MCN!
Fetching number 79262538300: 220 of 759 (28%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79262759990: 221 of 759 (28%)... operator been changed 6720 -> 7642! Ported from MCN!
Fetching number 79263304388: 222 of 759 (29%)... operator been changed 6720 -> 6457! Ported from MCN!
Fetching number 79263405534: 223 of 759 (29%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79265328181: 226 of 759 (29%)... operator been changed 6720 -> 5090! Ported from MCN!
Fetching number 79265549405: 227 of 759 (29%)... operator been changed 6720 -> 6261! Ported from MCN!
Fetching number 79265574880: 228 of 759 (29%)... operator been changed 6720 -> 6261! Ported from MCN!
Fetching number 79265579097: 229 of 759 (30%)... operator been changed 6720 -> 6261! Ported from MCN!
Fetching number 79266031713: 231 of 759 (30%)... operator been changed 6720 -> 6330! Ported from MCN!
Fetching number 79268109948: 234 of 759 (30%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79268585677: 236 of 759 (30%)... operator been changed 6720 -> 6557! Ported from MCN!
Fetching number 79268926110: 238 of 759 (31%)... operator been changed 6720 -> 7675! Ported from MCN!
Fetching number 79296336666: 248 of 759 (32%)... operator been changed 6720 -> 6457! Ported from MCN!
Fetching number 79296435402: 249 of 759 (32%)... operator been changed 6720 -> 6457! Ported from MCN!
Fetching number 79299784887: 250 of 759 (32%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79311110126: 256 of 759 (33%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79311112605: 288 of 759 (37%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79311113453: 299 of 759 (39%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79311113458: 300 of 759 (39%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79311113459: 301 of 759 (39%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79311113460: 302 of 759 (39%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79311113461: 303 of 759 (39%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79311113462: 304 of 759 (39%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79311113463: 305 of 759 (40%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79500370500: 329 of 759 (43%)... operator been changed 6720 -> 7687! Ported from MCN!
Fetching number 79528544150: 349 of 759 (45%)... operator been changed 6264 -> 6720! Ported to MCN!
Fetching number 79581963705: 363 of 759 (47%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79581967513: 376 of 759 (49%)... operator been changed 6720 -> 6457! Ported from MCN!
Fetching number 79581969605: 380 of 759 (49%)... operator been changed 6720 -> 6261! Ported from MCN!
Fetching number 79581971315: 382 of 759 (50%)... operator been changed 6720 -> 7675! Ported from MCN!
Fetching number 79581980234: 385 of 759 (50%)... operator been changed 6720 -> 6261! Ported from MCN!
Fetching number 79581981811: 389 of 759 (51%)... operator been changed 6720 -> 6261! Ported from MCN!
Fetching number 79581982039: 390 of 759 (51%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79581984805: 394 of 759 (51%)... operator been changed 6720 -> 7675! Ported from MCN!
Fetching number 79581985308: 396 of 759 (52%)... operator been changed 6720 -> 6457! Ported from MCN!
Fetching number 79581986110: 400 of 759 (52%)... operator been changed 6720 -> 6330! Ported from MCN!
Fetching number 79581986990: 402 of 759 (52%)... operator been changed 6720 -> 6457! Ported from MCN!
Fetching number 79581987141: 403 of 759 (52%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79581987545: 407 of 759 (53%)... operator been changed 6720 -> 6264! Ported from MCN!
Fetching number 79581987577: 408 of 759 (53%)... operator been changed 6720 -> 40081! Ported from MCN!
Fetching number 79581987990: 411 of 759 (54%)... operator been changed 6720 -> 6457! Ported from MCN!
Fetching number 79581988841: 414 of 759 (54%)... operator been changed 6720 -> 6330! Ported from MCN!
Fetching number 79581988843: 415 of 759 (54%)... operator been changed 6720 -> 6330! Ported from MCN!
Fetching number 79581989553: 418 of 759 (54%)... operator been changed 6720 -> 6261! Ported from MCN!
Fetching number 79581989807: 421 of 759 (55%)... operator been changed 6720 -> 7642! Ported from MCN!
Fetching number 79581990393: 422 of 759 (55%)... operator been changed 6720 -> 6330! Ported from MCN!
Fetching number 79581990399: 423 of 759 (55%)... operator been changed 6720 -> 6264! Ported from MCN!
Fetching number 79581991270: 426 of 759 (55%)... operator been changed 6720 -> 6457! Ported from MCN!
Fetching number 79581991902: 428 of 759 (56%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79581992100: 429 of 759 (56%)... operator been changed 6720 -> 6261! Ported from MCN!
Fetching number 79581992393: 430 of 759 (56%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79581992579: 431 of 759 (56%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79581992906: 432 of 759 (56%)... operator been changed 6720 -> 6557! Ported from MCN!
Fetching number 79581993622: 439 of 759 (57%)... operator been changed 6720 -> 6457! Ported from MCN!
Fetching number 79581994264: 441 of 759 (57%)... operator been changed 6720 -> 35501! Ported from MCN!
Fetching number 79581995541: 444 of 759 (58%)... operator been changed 6720 -> 6261! Ported from MCN!
Fetching number 79581996482: 451 of 759 (59%)... operator been changed 6720 -> 35501! Ported from MCN!
Fetching number 79581996637: 452 of 759 (59%)... operator been changed 6720 -> 7675! Ported from MCN!
Fetching number 79581997545: 455 of 759 (59%)... operator been changed 6720 -> 6264! Ported from MCN!
Fetching number 79581997710: 456 of 759 (59%)... operator been changed 6720 -> 40081! Ported from MCN!
Fetching number 79582000200: 459 of 759 (60%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79582000259: 460 of 759 (60%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79582000264: 461 of 759 (60%)... operator been changed 6720 -> 6457! Ported from MCN!
Fetching number 79582000345: 462 of 759 (60%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79582000650: 466 of 759 (61%)... operator been changed 6720 -> 6264! Ported from MCN!
Fetching number 79582000698: 467 of 759 (61%)... operator been changed 6720 -> 6261! Ported from MCN!
Fetching number 79582000750: 468 of 759 (61%)... operator been changed 6720 -> 35501! Ported from MCN!
Fetching number 79582001255: 470 of 759 (61%)... operator been changed 6720 -> 6261! Ported from MCN!
Fetching number 79582001320: 471 of 759 (61%)... operator been changed 6720 -> 7675! Ported from MCN!
Fetching number 79582001496: 472 of 759 (62%)... operator been changed 6720 -> 6261! Ported from MCN!
Fetching number 79582002023: 475 of 759 (62%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79582002081: 476 of 759 (62%)... operator been changed 6720 -> 6457! Ported from MCN!
Fetching number 79582002254: 478 of 759 (62%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79582002319: 479 of 759 (62%)... operator been changed 6720 -> 6261! Ported from MCN!
Fetching number 79582002988: 480 of 759 (63%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79582003494: 482 of 759 (63%)... operator been changed 6720 -> 5201! Ported from MCN!
Fetching number 79582003592: 484 of 759 (63%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79582003845: 486 of 759 (63%)... operator been changed 6720 -> 6457! Ported from MCN!
Fetching number 79582003874: 487 of 759 (64%)... operator been changed 6720 -> 6457! Ported from MCN!
Fetching number 79582004010: 488 of 759 (64%)... operator been changed 6720 -> 6457! Ported from MCN!
Fetching number 79582004890: 490 of 759 (64%)... operator been changed 6720 -> 6261! Ported from MCN!
Fetching number 79582005273: 493 of 759 (64%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79582005551: 494 of 759 (64%)... operator been changed 6720 -> 6264! Ported from MCN!
Fetching number 79582005581: 495 of 759 (65%)... operator been changed 6720 -> 7675! Ported from MCN!
Fetching number 79582005744: 496 of 759 (65%)... operator been changed 6720 -> 7675! Ported from MCN!
Fetching number 79582007262: 499 of 759 (65%)... operator been changed 6720 -> 35501! Ported from MCN!
Fetching number 79582007348: 500 of 759 (65%)... operator been changed 6720 -> 6457! Ported from MCN!
Fetching number 79582007545: 502 of 759 (66%)... operator been changed 6720 -> 6264! Ported from MCN!
Fetching number 79582008806: 504 of 759 (66%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79582008909: 505 of 759 (66%)... operator been changed 6720 -> 6457! Ported from MCN!
Fetching number 79582009967: 507 of 759 (66%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79582010125: 508 of 759 (66%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79582010126: 509 of 759 (66%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79582011017: 512 of 759 (67%)... operator been changed 6720 -> 5090! Ported from MCN!
Fetching number 79582011592: 514 of 759 (67%)... operator been changed 6720 -> 35501! Ported from MCN!
Fetching number 79582012050: 515 of 759 (67%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79582012281: 518 of 759 (68%)... operator been changed 6720 -> 35501! Ported from MCN!
Fetching number 79582012652: 519 of 759 (68%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79582013012: 520 of 759 (68%)... operator been changed 6720 -> 6261! Ported from MCN!
Fetching number 79582016595: 530 of 759 (69%)... operator been changed 6720 -> 7675! Ported from MCN!
Fetching number 79582017595: 533 of 759 (70%)... operator been changed 6720 -> 7675! Ported from MCN!
Fetching number 79582018102: 534 of 759 (70%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79582018321: 535 of 759 (70%)... operator been changed 6720 -> 6457! Ported from MCN!
Fetching number 79582066090: 547 of 759 (71%)... operator been changed 6720 -> 6457! Ported from MCN!
Fetching number 79582068587: 548 of 759 (72%)... operator been changed 6720 -> 6457! Ported from MCN!
Fetching number 79582069400: 549 of 759 (72%)... operator been changed 6720 -> 6457! Ported from MCN!
Fetching number 79582172240: 558 of 759 (73%)... operator been changed 6720 -> 7675! Ported from MCN!
Fetching number 79582172800: 559 of 759 (73%)... operator been changed 6720 -> 7675! Ported from MCN!
Fetching number 79582173095: 560 of 759 (73%)... operator been changed 6720 -> 35501! Ported from MCN!
Fetching number 79585350053: 569 of 759 (74%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79585350320: 570 of 759 (74%)... operator been changed 6720 -> 6261! Ported from MCN!
Fetching number 79585351394: 574 of 759 (75%)... operator been changed 6720 -> 7675! Ported from MCN!
Fetching number 79585352945: 576 of 759 (75%)... operator been changed 6720 -> 7675! Ported from MCN!
Fetching number 79585354317: 579 of 759 (76%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79585354544: 580 of 759 (76%)... operator been changed 6720 -> 6261! Ported from MCN!
Fetching number 79585359989: 588 of 759 (77%)... operator been changed 6720 -> 6261! Ported from MCN!
Fetching number 79585540094: 589 of 759 (77%)... operator been changed 6720 -> 6261! Ported from MCN!
Fetching number 79585545351: 594 of 759 (78%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79585545457: 595 of 759 (78%)... operator been changed 6720 -> 6261! Ported from MCN!
Fetching number 79585549290: 603 of 759 (79%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79585549383: 604 of 759 (79%)... operator been changed 6720 -> 6457! Ported from MCN!
Fetching number 79585763216: 609 of 759 (80%)... operator been changed 6720 -> 6261! Ported from MCN!
Fetching number 79585763437: 611 of 759 (80%)... operator been changed 6720 -> 6261! Ported from MCN!
Fetching number 79585768216: 614 of 759 (80%)... operator been changed 6720 -> 6261! Ported from MCN!
Fetching number 79585768494: 615 of 759 (80%)... operator been changed 6720 -> 6261! Ported from MCN!
Fetching number 79586446589: 622 of 759 (81%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79586446961: 623 of 759 (81%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79586848489: 625 of 759 (82%)... operator been changed 6720 -> 7675! Ported from MCN!
Fetching number 79602351170: 632 of 759 (83%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79629442439: 635 of 759 (83%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79637557558: 639 of 759 (84%)... operator been changed 6720 -> 7675! Ported from MCN!
Fetching number 79647917411: 640 of 759 (84%)... operator been changed 6720 -> 7687! Ported from MCN!
Fetching number 79652574995: 642 of 759 (84%)... operator been changed 6720 -> 6261! Ported from MCN!
Fetching number 79663273757: 649 of 759 (85%)... operator been changed 6720 -> 6261! Ported from MCN!
Fetching number 79671377505: 651 of 759 (85%)... operator been changed 6667 -> 6720! Ported to MCN!
Fetching number 79686481499: 656 of 759 (86%)... operator been changed 6720 -> 6261! Ported from MCN!
Fetching number 79772636878: 669 of 759 (88%)... operator been changed 6720 -> 6261! Ported from MCN!
Fetching number 79772878527: 672 of 759 (88%)... operator been changed 6720 -> 35501! Ported from MCN!
Fetching number 79773119372: 673 of 759 (88%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79773356085: 674 of 759 (88%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79776059207: 679 of 759 (89%)... operator been changed 6720 -> 6261! Ported from MCN!
Fetching number 79778191015: 680 of 759 (89%)... operator been changed 6720 -> 35501! Ported from MCN!
Fetching number 79778272365: 681 of 759 (89%)... operator been changed 6720 -> 6457! Ported from MCN!
Fetching number 79778620601: 682 of 759 (89%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79779173200: 685 of 759 (90%)... operator been changed 6720 -> 35501! Ported from MCN!
Fetching number 79779569474: 689 of 759 (90%)... operator been changed 6720 -> 6261! Ported from MCN!
Fetching number 79818052784: 694 of 759 (91%)... operator been changed 6264 -> 6720! Ported to MCN!
Fetching number 79859604180: 712 of 759 (93%)... operator been changed 6720 -> 6261! Ported from MCN!
Fetching number 79910006854: 720 of 759 (94%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79910007035: 721 of 759 (94%)... operator been changed 6720 -> 6457! Ported from MCN!
Fetching number 79910009770: 722 of 759 (94%)... operator been changed 6720 -> 6261! Ported from MCN!
Fetching number 79910080236: 723 of 759 (95%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79910127019: 725 of 759 (95%)... operator been changed 6720 -> 35501! Ported from MCN!
Fetching number 79955003183: 728 of 759 (95%)... operator been changed 6720 -> 7675! Ported from MCN!
Fetching number 79959033777: 729 of 759 (95%)... operator been changed 6720 -> 6264! Ported from MCN!
Fetching number 79959233223: 731 of 759 (96%)... operator been changed 6720 -> 6264! Ported from MCN!
Fetching number 79995503676: 737 of 759 (96%)... operator been changed 6720 -> 6457! Ported from MCN!
Fetching number 79996734689: 741 of 759 (97%)... operator been changed 6720 -> 7675! Ported from MCN!
Fetching number 79998558005: 742 of 759 (97%)... operator been changed 6720 -> 7675! Ported from MCN!
Fetching number 79998647427: 743 of 759 (97%)... operator been changed 6557 -> 6720! Ported to MCN!
Fetching number 79999096525: 746 of 759 (98%)... operator been changed 6720 -> 6667! Ported from MCN!
Fetching number 79999238373: 748 of 759 (98%)... operator been changed 6720 -> 6264! Ported from MCN!
EOL;

        if ($operatorId = Number::getMCNOperatorId()) {
            $operator = Operator::findOne(['id' => $operatorId]);
            $operatorName = $operator ? $operator->name : '';

            $dateTime = new \DateTime();
            $date = $dateTime->format('d.m.Y');

            $updates = [];
            $allNumbers = [];
            $allOperatorIds = [];
            $eventsFrom = [];
            $eventsTo = [];

            foreach (explode(PHP_EOL, $log) as $line) {
                $parts = explode(' ', $line);
                $number = intval($parts[2]);
                $operatorFromId = intval($parts[10]);
                $operatorToId = intval($parts[12]);
                $data = [
                    'client_id' => null,
                    'number' => $number,
                    'date_ported' => $date,
                    'operator_from_id' => $operatorFromId,
                    'operator_from_name' => $operatorFromId == $operatorId ? $operatorName : '',
                    'operator_to_id' => $operatorToId,
                    'operator_to_name' => $operatorToId == $operatorId ? $operatorName : '',
                ];

                if (empty($updates)) {
                    $updates[] = [
                        'number' => $number,
                        'nnp_operator_id' => $operatorToId
                    ];
                }

                if ($operatorFromId == $operatorId) {
                    $allNumbers[$number] = $number;
                    $allOperatorIds[$operatorToId] = $operatorToId;

                    $eventsFrom[] = $data;
                } else  if ($operatorToId == $operatorId) {
                    $allNumbers[$number] = $number;
                    $allOperatorIds[$operatorFromId] = $operatorFromId;

                    $eventsTo[] = $data;
                }
            }

            $this->processReleasedAndPorted($updates, $allNumbers, $allOperatorIds, $eventsFrom, $eventsTo, $isProcess);
        }

    }
}
