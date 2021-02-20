<?php

namespace app\classes\voip\forms;

use app\classes\enum\VoipRegistrySourceEnum;
use app\classes\voip\forms\NumberCommandForm\ActualizeData;
use app\models\ClientAccount;
use app\models\EventQueue;
use app\models\important_events\ImportantEvents;
use app\models\important_events\ImportantEventsNames;
use app\models\important_events\ImportantEventsSources;
use app\models\Number;
use app\models\UsageVoip;
use app\modules\nnp\models\NdcType;
use app\modules\nnp\models\Operator;
use app\modules\sim\models\ImsiPartner;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use yii\base\BaseObject;
use yii\db\Command;

class NumberCommandForm extends BaseObject
{
    const CHUNK_SIZE_UPDATE = 500;

    public bool $isProcess = false;
    public int $offset = 0;
    public int $limit = 0;

    /**
     * Выполнить или отобразить
     *
     * @param Command $command
     * @return int
     * @throws \yii\db\Exception
     */
    protected function execCommandOrPrint(Command $command)
    {
        if ($this->isProcess) {
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
     * Актуализация номеров
     *
     * @param ActualizeData $actualizeData
     * @return bool
     * @throws \yii\db\Exception
     */
    protected function processReleasedAndPorted(ActualizeData $actualizeData)
    {
        $updates = $actualizeData->updates;

        $transaction = Number::getDb()->beginTransaction();
        try {
            echo ('Found numbers with operator changed: ' . count($updates)) . PHP_EOL;

            foreach (array_chunk($updates, static::CHUNK_SIZE_UPDATE) as $chunk) {
                $sql = $this->getBatchUpdateSql(Number::tableName(), 'number', 'nnp_operator_id', $chunk);
                $commandPorted = Number::getDb()->createCommand($sql);

                $count = $this->execCommandOrPrint($commandPorted);
                echo ('Numbers updated: ' . $count) . PHP_EOL;
            }

            foreach ($actualizeData->eventsFrom as $data) {
                $data['client_id'] =
                    empty($data['client_id']) ?
                        $actualizeData->getClientIdByNumber($data['number']) :
                        $data['client_id'];

                $operatorId = $data['operator_to_id'];
                $data['operator_to_name'] =
                    empty($data['operator_to_name']) ?
                        $actualizeData->getOperatorNameById($operatorId) :
                        $data['operator_to_name'];

                echo ('Event ' . ImportantEventsNames::PORTING_FROM_MCN . ': ' . json_encode($data, JSON_UNESCAPED_UNICODE)) . '... ';
                if ($this->isProcess) {
                    ImportantEvents::create(
                        ImportantEventsNames::PORTING_FROM_MCN,
                        ImportantEventsSources::SOURCE_STAT,
                        $data
                    );

                    echo 'created.' . PHP_EOL;
                } else {
                    echo 'not created.' . PHP_EOL;
                }
            }

            foreach ($actualizeData->eventsTo as $data) {
                $data['client_id'] =
                    empty($data['client_id']) ?
                        $actualizeData->getClientIdByNumber($data['number']) :
                        $data['client_id'];

                $operatorId = $data['operator_from_id'];
                $data['operator_from_name'] =
                    empty($data['operator_from_name']) ?
                        $actualizeData->getOperatorNameById($operatorId) :
                        $data['operator_from_name'];

                echo ('Event ' . ImportantEventsNames::PORTING_TO_MCN . ': ' . json_encode($data, JSON_UNESCAPED_UNICODE)) . '... ';
                if ($this->isProcess) {
                    ImportantEvents::create(
                        ImportantEventsNames::PORTING_TO_MCN,
                        ImportantEventsSources::SOURCE_STAT,
                        $data
                    );

                    echo 'created!' . PHP_EOL;
                } else {
                    echo 'not created.' . PHP_EOL;
                }
            }

            $i = 0;
            foreach ($actualizeData->portedNumbers as $number) {
                echo sprintf('Creating porting event for actualizing number %s: %s of %s', $number, ++$i, count($actualizeData->portedNumbers)) . PHP_EOL;

                if ($this->isProcess) {
                    EventQueue::go(EventQueue::NUMBER_HAS_BEEN_PORTED, ['number' => $number]);
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
     * Проставляем всем мобильным номерам МСН Телеком
     * с источником = 'operaror', источник 'regulator' и MVNO-партнер Теле2.
     *
     * @return bool
     * @throws \yii\db\Exception
     */
    public function actualizeSourceOperator()
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

            $count = $this->execCommandOrPrint($command);
            echo('Numbers updated: ' . $count) . PHP_EOL;
        }

        return true;
    }

    /**
     * Прогоняем все мобильные номера ДЭНИ КОЛЛ через API проверки оператора
     * и если оператор МСН - обновляем поле nnp_operator_id.
     *
     * @return bool
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function actualizeDeniCall()
    {
        if ($operatorId = Number::getMCNOperatorId()) {
            $condition = [
                'ndc_type_id' => NdcType::ID_MOBILE,
                'nnp_operator_id' => Operator::ID_DENI_CALL,
            ];

            $numbers = Number::find()
                ->andWhere($condition)
                ->orderBy('number');

            $offset = $this->offset;
            $limit = $this->limit;

            if ($offset) {
                $numbers->offset($offset);
            }

            if ($limit) {
                $numbers->limit($limit);
            }

            $updates = [];
            $total = $numbers->count();
            echo ('Found numbers to check: ' . $total) . PHP_EOL;

            $total = $limit ? $limit : $total;
            $i = $offset;
            foreach ($numbers->each() as $number) {
                /** @var Number $number */
                $percent = intval(100* ($i-$offset) / $total);
                echo sprintf("Fetching number %s: %s of %s-%s (%s%%)... ", $number->number, ++$i, $offset, $offset + $total, $percent);

                try {
                    $isMcnNumber = $number->isMcnNumber();
                } catch (\Exception $e) {
                    echo 'error: ' . $e->getMessage(). ', sleeping 5 sec... ';
                    sleep(5);
                    $isMcnNumber = $number->isMcnNumber();
                }

                if ($isMcnNumber) {
                    $updates[$number->number] = [
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

            foreach (array_chunk($updates, static::CHUNK_SIZE_UPDATE, true) as $chunk) {
                $commandPorted = Number::getDb()->createCommand();
                $commandPorted->update(Number::tableName(), ['nnp_operator_id' => $operatorId], ['number' => array_keys($chunk)]);

                $count = $this->execCommandOrPrint($commandPorted);
                echo ('Numbers updated: ' . $count) . PHP_EOL;
            }
        }

        return true;
    }

    /**
     * Удаляем все мобильные номера, оставшиеся после проверки принадлежащими ДЭНИ КОЛЛ.
     *
     * @return bool
     * @throws \yii\db\Exception
     */
    public function deleteDeniCall()
    {
        $condition = [
            'ndc_type_id' => NdcType::ID_MOBILE,
            'nnp_operator_id' => Operator::ID_DENI_CALL,
        ];

        $command = Number::getDb()->createCommand();
        $command->delete(Number::tableName(), $condition);

        $count = $this->execCommandOrPrint($command);
        echo('Numbers deleted: ' . $count) . PHP_EOL;

        return true;
    }

    /**
     * Для всех номеров, у которых не совпадает client_id
     * со значением client_account_id активной записи из uu_account_tariff,
     * обновляем client_id и uu_account_tariff_id.
     *
     * @return bool
     * @throws \yii\db\Exception
     */
    public function actualizeForeignKeys()
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

                    if ($this->isProcess) {
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

        return true;
    }

    /**
     * Проверяем все мобильные номера в статусе 'Откреплен' через API проверки оператора
     * и если изменился оператор - обновляем поле nnp_operator_id и генерим важное событие
     *
     * @throws \yii\db\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actualizeReleased()
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
            $portedNumbers = [];

            $total = $numbers->count();
            $i = 0;
            foreach ($numbers->each() as $number) {
                /** @var Number $number */
                $percent = intval(100* ($i - 0) / $total);
                echo sprintf("Fetching number %s: %s of %s (%s%%)... ", $number->number, ++$i, $total, $percent);

                try {
                    $data = $number->getNnpInfoData();
                } catch (\Exception $e) {
                    echo 'error: ' . $e->getMessage(). ', sleeping 5 sec... ';
                    sleep(5);
                    $data = $number->getNnpInfoData();
                }

                if (!empty($data['nnp_operator_id'])) {
                    $voipNumber = $number->number;
                    $operatorToId = $data['nnp_operator_id'];
                    if ($number->nnp_operator_id != $operatorToId) {
                        $updates[] = [
                            'number' => $voipNumber,
                            'nnp_operator_id' => $operatorToId
                        ];
                        echo sprintf('operator has been changed %s -> %s! ', $number->nnp_operator_id, $operatorToId);

                        if ($operatorToId == $operatorId) {
                            echo 'Ported to MCN!';

                            $eventsTo[] = [
                                'client_id' => $number->client_id,
                                'number' => $voipNumber,
                                'date_ported' => $date,
                                'operator_from_id' => $number->nnp_operator_id,
                                'operator_from_name' => '',
                                'operator_to_id' => $operatorId,
                                'operator_to_name' => $operatorName,
                            ];
                            $allNumbers[$voipNumber] = $voipNumber;
                            $allOperatorIds[$number->nnp_operator_id] = $number->nnp_operator_id;

                            if ($number->isPorted()) {
                                $portedNumbers[$voipNumber] = $voipNumber;
                            }
                        } else if ($number->nnp_operator_id == $operatorId) {
                            echo 'Ported from MCN!';

                            $eventsFrom[] = [
                                'client_id' => $number->client_id,
                                'number' => $voipNumber,
                                'date_ported' => $date,
                                'operator_from_id' => $operatorId,
                                'operator_from_name' => $operatorName,
                                'operator_to_id' => $operatorToId,
                                'operator_to_name' => '',
                            ];
                            $allNumbers[$voipNumber] = $voipNumber;
                            $allOperatorIds[$operatorToId] = $operatorToId;
                        }
                    }
                }
                echo PHP_EOL;
            }

            $actualizeData = new ActualizeData([
                'updates' => $updates,
                'allNumbers' => $allNumbers,
                'allOperatorIds' => $allOperatorIds,
                'eventsFrom' => $eventsFrom,
                'eventsTo' => $eventsTo,
                'portedNumbers' => $portedNumbers,
            ]);
            $this->processReleasedAndPorted($actualizeData);
        }

        return true;
    }

    /**
     * Temporary
     *
     * @throws \yii\db\Exception
     */
    public function actualizeTemp()
    {
        $log = <<<EOL
Fetching number 79006311844: 16 of 759 (1%)... operator has been changed 6261 -> 6720! Ported to MCN!
Fetching number 79015625656: 25 of 759 (3%)... operator has been changed 6720 -> 6261! Ported from MCN!
Fetching number 79015867671: 26 of 759 (3%)... operator has been changed 6720 -> 35501! Ported from MCN!
Fetching number 79016262013: 28 of 759 (3%)... operator has been changed 6720 -> 6261! Ported from MCN!
Fetching number 79016262015: 29 of 759 (3%)... operator has been changed 6720 -> 6261! Ported from MCN!
Fetching number 79016280250: 31 of 759 (3%)... operator has been changed 6720 -> 35501! Ported from MCN!
Fetching number 79016283759: 45 of 759 (5%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79016284624: 47 of 759 (6%)... operator has been changed 6720 -> 6264! Ported from MCN!
Fetching number 79030000170: 53 of 759 (6%)... operator has been changed 6720 -> 35310! Ported from MCN!
Fetching number 79031724808: 58 of 759 (7%)... operator has been changed 6720 -> 6261! Ported from MCN!
Fetching number 79035600588: 62 of 759 (8%)... operator has been changed 6720 -> 6261! Ported from MCN!
Fetching number 79036700263: 65 of 759 (8%)... operator has been changed 6720 -> 7675! Ported from MCN!
Fetching number 79036724445: 66 of 759 (8%)... operator has been changed 6720 -> 6261! Ported from MCN!
Fetching number 79036725545: 67 of 759 (8%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79037112479: 70 of 759 (9%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79037434313: 73 of 759 (9%)... operator has been changed 6720 -> 7687! Ported from MCN!
Fetching number 79037446939: 74 of 759 (9%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79055477977: 90 of 759 (11%)... operator has been changed 6720 -> 7675! Ported from MCN!
Fetching number 79055613344: 91 of 759 (11%)... operator has been changed 6720 -> 6261! Ported from MCN!
Fetching number 79055787465: 92 of 759 (11%)... operator has been changed 6720 -> 6261! Ported from MCN!
Fetching number 79057970949: 94 of 759 (12%)... operator has been changed 6720 -> 6330! Ported from MCN!
Fetching number 79060468568: 96 of 759 (12%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79060777234: 97 of 759 (12%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79067170004: 100 of 759 (13%)... operator has been changed 6720 -> 6457! Ported from MCN!
Fetching number 79096322092: 105 of 759 (13%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79096613146: 106 of 759 (13%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79099100582: 107 of 759 (13%)... operator has been changed 6720 -> 7687! Ported from MCN!
Fetching number 79099977475: 108 of 759 (14%)... operator has been changed 6720 -> 7675! Ported from MCN!
Fetching number 79119529335: 119 of 759 (15%)... operator has been changed 6264 -> 6720! Ported to MCN!
Fetching number 79150009121: 134 of 759 (17%)... operator has been changed 6720 -> 6457! Ported from MCN!
Fetching number 79150859598: 135 of 759 (17%)... operator has been changed 6720 -> 6457! Ported from MCN!
Fetching number 79151627677: 136 of 759 (17%)... operator has been changed 6720 -> 7687! Ported from MCN!
Fetching number 79153973979: 139 of 759 (18%)... operator has been changed 6720 -> 6261! Ported from MCN!
Fetching number 79154567363: 140 of 759 (18%)... operator has been changed 6720 -> 7675! Ported from MCN!
Fetching number 79161827262: 143 of 759 (18%)... operator has been changed 6720 -> 6264! Ported from MCN!
Fetching number 79162030362: 145 of 759 (18%)... operator has been changed 6720 -> 35501! Ported from MCN!
Fetching number 79165087081: 151 of 759 (19%)... operator has been changed 6720 -> 7642! Ported from MCN!
Fetching number 79165335364: 152 of 759 (19%)... operator has been changed 6720 -> 6457! Ported from MCN!
Fetching number 79165434445: 154 of 759 (20%)... operator has been changed 6720 -> 6457! Ported from MCN!
Fetching number 79165466232: 155 of 759 (20%)... operator has been changed 6720 -> 6261! Ported from MCN!
Fetching number 79166545647: 158 of 759 (20%)... operator has been changed 6720 -> 7642! Ported from MCN!
Fetching number 79168815445: 165 of 759 (21%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79168842899: 166 of 759 (21%)... operator has been changed 6720 -> 6457! Ported from MCN!
Fetching number 79169870911: 168 of 759 (22%)... operator has been changed 6720 -> 6457! Ported from MCN!
Fetching number 79169878564: 169 of 759 (22%)... operator has been changed 6720 -> 6264! Ported from MCN!
Fetching number 79182101265: 178 of 759 (23%)... operator has been changed 6720 -> 6264! Ported from MCN!
Fetching number 79191055905: 186 of 759 (24%)... operator has been changed 6720 -> 6330! Ported from MCN!
Fetching number 79191055906: 187 of 759 (24%)... operator has been changed 6720 -> 6330! Ported from MCN!
Fetching number 79197207308: 189 of 759 (24%)... operator has been changed 6720 -> 6261! Ported from MCN!
Fetching number 79197777119: 190 of 759 (24%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79199987849: 191 of 759 (25%)... operator has been changed 6720 -> 6330! Ported from MCN!
Fetching number 79213700400: 193 of 759 (25%)... operator has been changed 6720 -> 7675! Ported from MCN!
Fetching number 79218852164: 197 of 759 (25%)... operator has been changed 6261 -> 6720! Ported to MCN!
Fetching number 79256575205: 207 of 759 (27%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79260113827: 209 of 759 (27%)... operator has been changed 6720 -> 7675! Ported from MCN!
Fetching number 79261507420: 213 of 759 (27%)... operator has been changed 6720 -> 7675! Ported from MCN!
Fetching number 79261591440: 214 of 759 (28%)... operator has been changed 6720 -> 6457! Ported from MCN!
Fetching number 79262148943: 216 of 759 (28%)... operator has been changed 6720 -> 6457! Ported from MCN!
Fetching number 79262162340: 217 of 759 (28%)... operator has been changed 6720 -> 7642! Ported from MCN!
Fetching number 79262179373: 218 of 759 (28%)... operator has been changed 6720 -> 35501! Ported from MCN!
Fetching number 79262538300: 220 of 759 (28%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79262759990: 221 of 759 (28%)... operator has been changed 6720 -> 7642! Ported from MCN!
Fetching number 79263304388: 222 of 759 (29%)... operator has been changed 6720 -> 6457! Ported from MCN!
Fetching number 79263405534: 223 of 759 (29%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79265328181: 226 of 759 (29%)... operator has been changed 6720 -> 5090! Ported from MCN!
Fetching number 79265549405: 227 of 759 (29%)... operator has been changed 6720 -> 6261! Ported from MCN!
Fetching number 79265574880: 228 of 759 (29%)... operator has been changed 6720 -> 6261! Ported from MCN!
Fetching number 79265579097: 229 of 759 (30%)... operator has been changed 6720 -> 6261! Ported from MCN!
Fetching number 79266031713: 231 of 759 (30%)... operator has been changed 6720 -> 6330! Ported from MCN!
Fetching number 79268109948: 234 of 759 (30%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79268585677: 236 of 759 (30%)... operator has been changed 6720 -> 6557! Ported from MCN!
Fetching number 79268926110: 238 of 759 (31%)... operator has been changed 6720 -> 7675! Ported from MCN!
Fetching number 79296336666: 248 of 759 (32%)... operator has been changed 6720 -> 6457! Ported from MCN!
Fetching number 79296435402: 249 of 759 (32%)... operator has been changed 6720 -> 6457! Ported from MCN!
Fetching number 79299784887: 250 of 759 (32%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79311110126: 256 of 759 (33%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79311112605: 288 of 759 (37%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79311113453: 299 of 759 (39%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79311113458: 300 of 759 (39%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79311113459: 301 of 759 (39%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79311113460: 302 of 759 (39%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79311113461: 303 of 759 (39%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79311113462: 304 of 759 (39%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79311113463: 305 of 759 (40%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79500370500: 329 of 759 (43%)... operator has been changed 6720 -> 7687! Ported from MCN!
Fetching number 79528544150: 349 of 759 (45%)... operator has been changed 6264 -> 6720! Ported to MCN!
Fetching number 79581963705: 363 of 759 (47%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79581967513: 376 of 759 (49%)... operator has been changed 6720 -> 6457! Ported from MCN!
Fetching number 79581969605: 380 of 759 (49%)... operator has been changed 6720 -> 6261! Ported from MCN!
Fetching number 79581971315: 382 of 759 (50%)... operator has been changed 6720 -> 7675! Ported from MCN!
Fetching number 79581980234: 385 of 759 (50%)... operator has been changed 6720 -> 6261! Ported from MCN!
Fetching number 79581981811: 389 of 759 (51%)... operator has been changed 6720 -> 6261! Ported from MCN!
Fetching number 79581982039: 390 of 759 (51%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79581984805: 394 of 759 (51%)... operator has been changed 6720 -> 7675! Ported from MCN!
Fetching number 79581985308: 396 of 759 (52%)... operator has been changed 6720 -> 6457! Ported from MCN!
Fetching number 79581986110: 400 of 759 (52%)... operator has been changed 6720 -> 6330! Ported from MCN!
Fetching number 79581986990: 402 of 759 (52%)... operator has been changed 6720 -> 6457! Ported from MCN!
Fetching number 79581987141: 403 of 759 (52%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79581987545: 407 of 759 (53%)... operator has been changed 6720 -> 6264! Ported from MCN!
Fetching number 79581987577: 408 of 759 (53%)... operator has been changed 6720 -> 40081! Ported from MCN!
Fetching number 79581987990: 411 of 759 (54%)... operator has been changed 6720 -> 6457! Ported from MCN!
Fetching number 79581988841: 414 of 759 (54%)... operator has been changed 6720 -> 6330! Ported from MCN!
Fetching number 79581988843: 415 of 759 (54%)... operator has been changed 6720 -> 6330! Ported from MCN!
Fetching number 79581989553: 418 of 759 (54%)... operator has been changed 6720 -> 6261! Ported from MCN!
Fetching number 79581989807: 421 of 759 (55%)... operator has been changed 6720 -> 7642! Ported from MCN!
Fetching number 79581990393: 422 of 759 (55%)... operator has been changed 6720 -> 6330! Ported from MCN!
Fetching number 79581990399: 423 of 759 (55%)... operator has been changed 6720 -> 6264! Ported from MCN!
Fetching number 79581991270: 426 of 759 (55%)... operator has been changed 6720 -> 6457! Ported from MCN!
Fetching number 79581991902: 428 of 759 (56%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79581992100: 429 of 759 (56%)... operator has been changed 6720 -> 6261! Ported from MCN!
Fetching number 79581992393: 430 of 759 (56%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79581992579: 431 of 759 (56%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79581992906: 432 of 759 (56%)... operator has been changed 6720 -> 6557! Ported from MCN!
Fetching number 79581993622: 439 of 759 (57%)... operator has been changed 6720 -> 6457! Ported from MCN!
Fetching number 79581994264: 441 of 759 (57%)... operator has been changed 6720 -> 35501! Ported from MCN!
Fetching number 79581995541: 444 of 759 (58%)... operator has been changed 6720 -> 6261! Ported from MCN!
Fetching number 79581996482: 451 of 759 (59%)... operator has been changed 6720 -> 35501! Ported from MCN!
Fetching number 79581996637: 452 of 759 (59%)... operator has been changed 6720 -> 7675! Ported from MCN!
Fetching number 79581997545: 455 of 759 (59%)... operator has been changed 6720 -> 6264! Ported from MCN!
Fetching number 79581997710: 456 of 759 (59%)... operator has been changed 6720 -> 40081! Ported from MCN!
Fetching number 79582000200: 459 of 759 (60%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79582000259: 460 of 759 (60%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79582000264: 461 of 759 (60%)... operator has been changed 6720 -> 6457! Ported from MCN!
Fetching number 79582000345: 462 of 759 (60%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79582000650: 466 of 759 (61%)... operator has been changed 6720 -> 6264! Ported from MCN!
Fetching number 79582000698: 467 of 759 (61%)... operator has been changed 6720 -> 6261! Ported from MCN!
Fetching number 79582000750: 468 of 759 (61%)... operator has been changed 6720 -> 35501! Ported from MCN!
Fetching number 79582001255: 470 of 759 (61%)... operator has been changed 6720 -> 6261! Ported from MCN!
Fetching number 79582001320: 471 of 759 (61%)... operator has been changed 6720 -> 7675! Ported from MCN!
Fetching number 79582001496: 472 of 759 (62%)... operator has been changed 6720 -> 6261! Ported from MCN!
Fetching number 79582002023: 475 of 759 (62%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79582002081: 476 of 759 (62%)... operator has been changed 6720 -> 6457! Ported from MCN!
Fetching number 79582002254: 478 of 759 (62%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79582002319: 479 of 759 (62%)... operator has been changed 6720 -> 6261! Ported from MCN!
Fetching number 79582002988: 480 of 759 (63%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79582003494: 482 of 759 (63%)... operator has been changed 6720 -> 5201! Ported from MCN!
Fetching number 79582003592: 484 of 759 (63%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79582003845: 486 of 759 (63%)... operator has been changed 6720 -> 6457! Ported from MCN!
Fetching number 79582003874: 487 of 759 (64%)... operator has been changed 6720 -> 6457! Ported from MCN!
Fetching number 79582004010: 488 of 759 (64%)... operator has been changed 6720 -> 6457! Ported from MCN!
Fetching number 79582004890: 490 of 759 (64%)... operator has been changed 6720 -> 6261! Ported from MCN!
Fetching number 79582005273: 493 of 759 (64%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79582005551: 494 of 759 (64%)... operator has been changed 6720 -> 6264! Ported from MCN!
Fetching number 79582005581: 495 of 759 (65%)... operator has been changed 6720 -> 7675! Ported from MCN!
Fetching number 79582005744: 496 of 759 (65%)... operator has been changed 6720 -> 7675! Ported from MCN!
Fetching number 79582007262: 499 of 759 (65%)... operator has been changed 6720 -> 35501! Ported from MCN!
Fetching number 79582007348: 500 of 759 (65%)... operator has been changed 6720 -> 6457! Ported from MCN!
Fetching number 79582007545: 502 of 759 (66%)... operator has been changed 6720 -> 6264! Ported from MCN!
Fetching number 79582008806: 504 of 759 (66%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79582008909: 505 of 759 (66%)... operator has been changed 6720 -> 6457! Ported from MCN!
Fetching number 79582009967: 507 of 759 (66%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79582010125: 508 of 759 (66%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79582010126: 509 of 759 (66%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79582011017: 512 of 759 (67%)... operator has been changed 6720 -> 5090! Ported from MCN!
Fetching number 79582011592: 514 of 759 (67%)... operator has been changed 6720 -> 35501! Ported from MCN!
Fetching number 79582012050: 515 of 759 (67%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79582012281: 518 of 759 (68%)... operator has been changed 6720 -> 35501! Ported from MCN!
Fetching number 79582012652: 519 of 759 (68%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79582013012: 520 of 759 (68%)... operator has been changed 6720 -> 6261! Ported from MCN!
Fetching number 79582016595: 530 of 759 (69%)... operator has been changed 6720 -> 7675! Ported from MCN!
Fetching number 79582017595: 533 of 759 (70%)... operator has been changed 6720 -> 7675! Ported from MCN!
Fetching number 79582018102: 534 of 759 (70%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79582018321: 535 of 759 (70%)... operator has been changed 6720 -> 6457! Ported from MCN!
Fetching number 79582066090: 547 of 759 (71%)... operator has been changed 6720 -> 6457! Ported from MCN!
Fetching number 79582068587: 548 of 759 (72%)... operator has been changed 6720 -> 6457! Ported from MCN!
Fetching number 79582069400: 549 of 759 (72%)... operator has been changed 6720 -> 6457! Ported from MCN!
Fetching number 79582172240: 558 of 759 (73%)... operator has been changed 6720 -> 7675! Ported from MCN!
Fetching number 79582172800: 559 of 759 (73%)... operator has been changed 6720 -> 7675! Ported from MCN!
Fetching number 79582173095: 560 of 759 (73%)... operator has been changed 6720 -> 35501! Ported from MCN!
Fetching number 79585350053: 569 of 759 (74%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79585350320: 570 of 759 (74%)... operator has been changed 6720 -> 6261! Ported from MCN!
Fetching number 79585351394: 574 of 759 (75%)... operator has been changed 6720 -> 7675! Ported from MCN!
Fetching number 79585352945: 576 of 759 (75%)... operator has been changed 6720 -> 7675! Ported from MCN!
Fetching number 79585354317: 579 of 759 (76%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79585354544: 580 of 759 (76%)... operator has been changed 6720 -> 6261! Ported from MCN!
Fetching number 79585359989: 588 of 759 (77%)... operator has been changed 6720 -> 6261! Ported from MCN!
Fetching number 79585540094: 589 of 759 (77%)... operator has been changed 6720 -> 6261! Ported from MCN!
Fetching number 79585545351: 594 of 759 (78%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79585545457: 595 of 759 (78%)... operator has been changed 6720 -> 6261! Ported from MCN!
Fetching number 79585549290: 603 of 759 (79%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79585549383: 604 of 759 (79%)... operator has been changed 6720 -> 6457! Ported from MCN!
Fetching number 79585763216: 609 of 759 (80%)... operator has been changed 6720 -> 6261! Ported from MCN!
Fetching number 79585763437: 611 of 759 (80%)... operator has been changed 6720 -> 6261! Ported from MCN!
Fetching number 79585768216: 614 of 759 (80%)... operator has been changed 6720 -> 6261! Ported from MCN!
Fetching number 79585768494: 615 of 759 (80%)... operator has been changed 6720 -> 6261! Ported from MCN!
Fetching number 79586446589: 622 of 759 (81%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79586446961: 623 of 759 (81%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79586848489: 625 of 759 (82%)... operator has been changed 6720 -> 7675! Ported from MCN!
Fetching number 79602351170: 632 of 759 (83%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79629442439: 635 of 759 (83%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79637557558: 639 of 759 (84%)... operator has been changed 6720 -> 7675! Ported from MCN!
Fetching number 79647917411: 640 of 759 (84%)... operator has been changed 6720 -> 7687! Ported from MCN!
Fetching number 79652574995: 642 of 759 (84%)... operator has been changed 6720 -> 6261! Ported from MCN!
Fetching number 79663273757: 649 of 759 (85%)... operator has been changed 6720 -> 6261! Ported from MCN!
Fetching number 79671377505: 651 of 759 (85%)... operator has been changed 6667 -> 6720! Ported to MCN!
Fetching number 79686481499: 656 of 759 (86%)... operator has been changed 6720 -> 6261! Ported from MCN!
Fetching number 79772636878: 669 of 759 (88%)... operator has been changed 6720 -> 6261! Ported from MCN!
Fetching number 79772878527: 672 of 759 (88%)... operator has been changed 6720 -> 35501! Ported from MCN!
Fetching number 79773119372: 673 of 759 (88%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79773356085: 674 of 759 (88%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79776059207: 679 of 759 (89%)... operator has been changed 6720 -> 6261! Ported from MCN!
Fetching number 79778191015: 680 of 759 (89%)... operator has been changed 6720 -> 35501! Ported from MCN!
Fetching number 79778272365: 681 of 759 (89%)... operator has been changed 6720 -> 6457! Ported from MCN!
Fetching number 79778620601: 682 of 759 (89%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79779173200: 685 of 759 (90%)... operator has been changed 6720 -> 35501! Ported from MCN!
Fetching number 79779569474: 689 of 759 (90%)... operator has been changed 6720 -> 6261! Ported from MCN!
Fetching number 79818052784: 694 of 759 (91%)... operator has been changed 6264 -> 6720! Ported to MCN!
Fetching number 79859604180: 712 of 759 (93%)... operator has been changed 6720 -> 6261! Ported from MCN!
Fetching number 79910006854: 720 of 759 (94%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79910007035: 721 of 759 (94%)... operator has been changed 6720 -> 6457! Ported from MCN!
Fetching number 79910009770: 722 of 759 (94%)... operator has been changed 6720 -> 6261! Ported from MCN!
Fetching number 79910080236: 723 of 759 (95%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79910127019: 725 of 759 (95%)... operator has been changed 6720 -> 35501! Ported from MCN!
Fetching number 79955003183: 728 of 759 (95%)... operator has been changed 6720 -> 7675! Ported from MCN!
Fetching number 79959033777: 729 of 759 (95%)... operator has been changed 6720 -> 6264! Ported from MCN!
Fetching number 79959233223: 731 of 759 (96%)... operator has been changed 6720 -> 6264! Ported from MCN!
Fetching number 79995503676: 737 of 759 (96%)... operator has been changed 6720 -> 6457! Ported from MCN!
Fetching number 79996734689: 741 of 759 (97%)... operator has been changed 6720 -> 7675! Ported from MCN!
Fetching number 79998558005: 742 of 759 (97%)... operator has been changed 6720 -> 7675! Ported from MCN!
Fetching number 79998647427: 743 of 759 (97%)... operator has been changed 6557 -> 6720! Ported to MCN!
Fetching number 79999096525: 746 of 759 (98%)... operator has been changed 6720 -> 6667! Ported from MCN!
Fetching number 79999238373: 748 of 759 (98%)... operator has been changed 6720 -> 6264! Ported from MCN!
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
                $operatorFromId = intval($parts[11]);
                $operatorToId = intval($parts[13]);
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

            $actualizeData = new ActualizeData([
                'updates' => $updates,
                'allNumbers' => $allNumbers,
                'allOperatorIds' => $allOperatorIds,
                'eventsFrom' => $eventsFrom,
                'eventsTo' => $eventsTo,
            ]);
            $this->processReleasedAndPorted($actualizeData);
        }

        return true;
    }

    /**
     * Прогоняем все мобильные номера МСН Телеком через API проверки оператора
     * и если оператор не МСН - выводим список, не обрабатываем.
     *
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function actualizeMcnNumbers()
    {
        if ($operatorId = Number::getMCNOperatorId()) {
            $condition = [
                'ndc_type_id' => NdcType::ID_MOBILE,
                'nnp_operator_id' => $operatorId,
                'status' => Number::STATUS_ACTIVE_COMMERCIAL,
            ];

            $operator = Operator::findOne(['id' => $operatorId]);
            $operatorName = $operator ? $operator->name : '';

            $dateTime = new \DateTime();
            $date = $dateTime->format('d.m.Y');

            $numbers = Number::find()
                ->andWhere($condition)
                ->orderBy('number');

            $offset = $this->offset;
            $limit = $this->limit;

            if ($offset) {
                $numbers->offset($offset);
            }

            if ($limit) {
                $numbers->limit($limit);
            }

            $updates = [];
            $allNumbers = [];
            $allOperatorIds = [];
            $eventsFrom = [];

            $total = $numbers->count();
            echo ('Found numbers to check: ' . $total) . PHP_EOL;

            $total = $limit ? $limit : $total;
            $i = $offset;
            foreach ($numbers->each() as $number) {
                /** @var Number $number */
                $percent = intval(100* ($i-$offset) / $total);
                echo sprintf("Fetching number %s: %s of %s (%s%%)... ", $number->number, ++$i, $total, $percent);

                try {
                    $data = $number->getNnpInfoData();
                } catch (\Exception $e) {
                    echo 'error: ' . $e->getMessage(). ', sleeping 5 sec... ';
                    sleep(5);
                    $data = $number->getNnpInfoData();
                }

                if (!empty($data['nnp_operator_id'])) {
                    $voipNumber = $number->number;
                    $operatorToId = $data['nnp_operator_id'];
                    if ($number->nnp_operator_id != $operatorToId) {
                        echo sprintf('operator has been changed %s -> %s! ', $number->nnp_operator_id, $operatorToId);

                        $updates[$voipNumber] = [
                            'number' => $voipNumber,
                            'nnp_operator_id' => $operatorToId
                        ];

                        $eventsFrom[] = [
                            'client_id' => $number->client_id,
                            'number' => $voipNumber,
                            'date_ported' => $date,
                            'operator_from_id' => $operatorId,
                            'operator_from_name' => $operatorName,
                            'operator_to_id' => $operatorToId,
                            'operator_to_name' => '',
                        ];
                        $allNumbers[$voipNumber] = $voipNumber;
                        $allOperatorIds[$operatorToId] = $operatorToId;
                    }
                } else {
                    echo '!!! EMPTY operator_id !!!';
                }
                echo PHP_EOL;
            }

            $this->isProcess = false; // не обрабатываем
            $actualizeData = new ActualizeData([
                'updates' => $updates,
                'allNumbers' => $allNumbers,
                'allOperatorIds' => $allOperatorIds,
                'eventsFrom' => $eventsFrom,
                'eventsTo' => [],
            ]);
            $this->processReleasedAndPorted($actualizeData);
        }

        return true;
    }

    /**
     * Прогоняем все мобильные номера не МСН Телеком через API проверки оператора
     * и если изменился оператор на МСН - обновляем поле nnp_operator_id,
     * события не создаём, т.к. это ошибка в операторе, которую мы правим.
     *
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function actualizeNotMcnNumbers()
    {
        if ($operatorId = Number::getMCNOperatorId()) {
            $condition = [
                'ndc_type_id' => NdcType::ID_MOBILE,
                'status' => Number::STATUS_ACTIVE_COMMERCIAL,
            ];

            $operator = Operator::findOne(['id' => $operatorId]);
            $operatorName = $operator ? $operator->name : '';

            $dateTime = new \DateTime();
            $date = $dateTime->format('d.m.Y');

            $numbers = Number::find()
                ->andWhere($condition)
                ->andWhere('nnp_operator_id != ' . $operatorId)
                ->orderBy('number');

            $offset = $this->offset;
            $limit = $this->limit;

            if ($offset) {
                $numbers->offset($offset);
            }

            if ($limit) {
                $numbers->limit($limit);
            }

            $updates = [];
            $allNumbers = [];
            $allOperatorIds = [];
            $eventsFrom = [];
            $eventsTo = [];

            $total = $numbers->count();
            echo ('Found numbers to check: ' . $total) . PHP_EOL;

            $total = $limit ? $limit : $total;
            $i = $offset;
            foreach ($numbers->each() as $number) {
                /** @var Number $number */
                $percent = intval(100* ($i-$offset) / $total);
                echo sprintf("Fetching number %s: %s of %s (%s%%)... ", $number->number, ++$i, $total, $percent);

                try {
                    $data = $number->getNnpInfoData();
                } catch (\Exception $e) {
                    echo 'error: ' . $e->getMessage(). ', sleeping 5 sec... ';
                    sleep(5);
                    $data = $number->getNnpInfoData();
                }

                if (!empty($data['nnp_operator_id'])) {
                    $voipNumber = $number->number;
                    $operatorFromId = $number->nnp_operator_id;
                    $operatorToId = $data['nnp_operator_id'];
                    if ($operatorToId == $operatorId) {
                        echo 'Ported to MCN!';

                        $updates[$voipNumber] = [
                            'number' => $voipNumber,
                            'nnp_operator_id' => $operatorToId
                        ];

                        $eventsTo[] = [
                            'client_id' => $number->client_id,
                            'number' => $voipNumber,
                            'date_ported' => $date,
                            'operator_from_id' => $operatorFromId,
                            'operator_from_name' => '',
                            'operator_to_id' => $operatorId,
                            'operator_to_name' => $operatorName,
                        ];
                        $allNumbers[$voipNumber] = $voipNumber;
                        $allOperatorIds[$operatorFromId] = $operatorFromId;
                    } else if ($number->nnp_operator_id != $operatorToId) {
                        echo sprintf('operator has been changed %s -> %s! ', $number->nnp_operator_id, $operatorToId);

                        $operatorFrom = Operator::findOne(['id' => $operatorFromId]);
                        $operatorFromName = $operatorFrom ? $operatorFrom->name : '';

                        $eventsFrom[] = [
                            'client_id' => $number->client_id,
                            'number' => $voipNumber,
                            'date_ported' => $date,
                            'operator_from_id' => $operatorFromId,
                            'operator_from_name' => $operatorFromName,
                            'operator_to_id' => $operatorToId,
                            'operator_to_name' => '',
                        ];
                        $allNumbers[$voipNumber] = $voipNumber;
                        $allOperatorIds[$operatorToId] = $operatorToId;
                    }
                } else {
                    echo '!!! EMPTY operator_id !!!';
                }
                echo PHP_EOL;
            }

            $actualizeData = new ActualizeData([
                'updates' => $updates,
                'allNumbers' => $allNumbers,
                'allOperatorIds' => $allOperatorIds,
                'eventsFrom' => $this->isProcess ? [] : $eventsFrom, // без событий
                'eventsTo' => $this->isProcess ? [] : $eventsTo, // без событий
            ]);
            $this->processReleasedAndPorted($actualizeData);
        }

        return true;
    }

    /**
     * Находим все активные услуги, номера которых отсутствуют
     * и выключаем данные услуги
     */
    public function clearAccountTariffs()
    {
        $accountTariffs = AccountTariff::find()
            ->from(AccountTariff::tableName() . ' u')
            ->leftJoin(['v' => Number::tableName()], 'v.number = u.voip_number')
            ->andWhere('u.voip_number is not null')
            ->andWhere('u.tariff_period_id IS NOT NULL')
            ->andWhere('v.number is null');

        $total = $accountTariffs->count();
        $i = 0;
        foreach ($accountTariffs->each() as $accountTariff) {
            /** @var AccountTariff $accountTariff */

            echo sprintf('accountTariff id#%s, number %s: %s of %s... ', $accountTariff->id, $accountTariff->voip_number, ++$i, $total);
            if ($this->isProcess) {
                $accountTariff->setClosed();

                echo 'has been processed!' . PHP_EOL;
            } else {
                echo 'skipped.' . PHP_EOL;
            }
        }

        return true;
    }

}