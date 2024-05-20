<?php

namespace app\modules\nnp\commands;

use app\modules\nnp\models\Country;
use app\modules\nnp\models\Operator;
use yii\console\ExitCode;
use yii\db\Query;
use yii\web\NotFoundHttpException;

/**
 * <list>
 * <list_item><phone_number>19191080</phone_number><equipment>001</equipment><valid_from>2016-07-07 20:00:00</valid_from><valid_until></valid_until><actual_provi
 * der>602</actual_provider><previous_provider>941</previous_provider><block_owner>941</block_owner><update_ts>2016-07-07 12:00:25</update_ts></list_item>
 * <list_item><phone_number>19191081</phone_number><equipment>001</equipment><valid_from>2016-07-07 20:00:00</valid_from><valid_until></valid_until><actual_provi
 * der>602</actual_provider><previous_provider>941</previous_provider><block_owner>941</block_owner><update_ts>2016-07-07 12:00:26</update_ts></list_item>
 * <list_item><phone_number>19191082</phone_number><equipment>001</equipment><valid_from>2016-07-07 20:00:00</valid_from><valid_until></valid_until><actual_provi
 * der>602</actual_provider><previous_provider>941</previous_provider><block_owner>941</block_owner><update_ts>2016-07-07 12:00:26</update_ts></list_item>
 * <list_item><phone_number>19191083</phone_number><equipment>001</equipment><valid_from>2016-07-07 20:00:00</valid_from><valid_until></valid_until><actual_provi
 * der>602</actual_provider><previous_provider>941</previous_provider><block_owner>941</block_owner><update_ts>2016-07-07 12:00:26</update_ts></list_item>
 * <list_item><phone_number>19191084</phone_number><equipment>001</equipment><valid_from>2016-07-07 20:00:00</valid_from><valid_until></valid_until><actual_provi
 * der>602</actual_provider><previous_provider>941</previous_provider><block_owner>941</block_owner><update_ts>2016-07-07 12:00:26</update_ts></list_item>
 * <list_item><phone_number>19191085</phone_number><equipment>001</equipment><valid_from>2016-07-07 20:00:00</valid_from><valid_until></valid_until><actual_provi
 * der>602</actual_provider><previous_provider>941</previous_provider><block_owner>941</block_owner><update_ts>2016-07-07 12:00:26</update_ts></list_item>
 * <list_item><phone_number>19191086</phone_number><equipment>001</equipment><valid_from>2016-07-07 20:00:00</valid_from><valid_until></valid_until><actual_provi
 * der>602</actual_provider><previous_provider>941</previous_provider><block_owner>941</block_owner><update_ts>2016-07-07 12:00:26</update_ts></list_item>
 * <list_item><phone_number>19191087</phone_number><equipment>001</equipment><valid_from>2016-07-07 20:00:00</valid_from><valid_until></valid_until><actual_provi
 * der>602</actual_provider><previous_provider>941</previous_provider><block_owner>941</block_owner><update_ts>2016-07-07 12:00:26</update_ts></list_item>
 * <list_item><phone_number>19191088</phone_number><equipment>001</equipment><valid_from>2016-07-07 20:00:00</valid_from><valid_until></valid_until><actual_provi
 * der>602</actual_provider><previous_provider>941</previous_provider><block_owner>941</block_owner><update_ts>2016-07-07 12:00:26</update_ts></list_item>
 * <list_item><phone_number>19191089</phone_number><equipment>001</equipment><valid_from>2016-07-07 20:00:00</valid_from><valid_until></valid_until><actual_provi
 * der>602</actual_provider><previous_provider>941</previous_provider><block_owner>941</block_owner><update_ts>2016-07-07 12:00:26</update_ts></list_item>
 */
class PortedHungaryController extends PortedController
{
    const SCHEMA = [
        'table' => 'nnp_ported.number',
        'pk' => 'full_number',
        'fields' => [
            'full_number' => 'BIGINT NOT NULL',
            'operator_source' => 'CHARACTER VARYING(255)',
            'operator_id' => 'integer',
        ],
        'set' => [
            'operator_id' => <<< SQL
CASE WHEN number_tmp.operator_id IS NOT NULL THEN number_tmp.operator_id ELSE
    CASE WHEN number.operator_source = number_tmp.operator_source THEN number.operator_id ELSE
    NULL
    END
END
SQL

        ]
    ];

    private $_errors = [];

    private $_operators = [];
    /**
     * @inheritdoc
     * @throws \yii\base\InvalidParamException
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\db\Exception
     * @throws \LogicException
     */
    protected function readData()
    {
        $fileUrl = \Yii::getAlias('@runtime/' . $this->fileName);
        $fp = fopen($fileUrl, 'r');
        if (!$fp) {
            throw new NotFoundHttpException('Ошибка чтения файла ' . $fileUrl);
        }

        if (!$this->_operators) {
            $this->loadOperators();
        }

        // check operator
        $isFirst = true;
        $this->_errors = [];
        while (($row = fgets($fp)) !== false) {
            if ($isFirst) {
                // skip first line
                $isFirst = false;
                continue;
            }

            $this->getRecord($row);
        }

        if ($this->_errors) {
            $this->printErrors();

            \Yii::$app->end(ExitCode::UNSPECIFIED_ERROR);
        }

        echo PHP_EOL . 'Data completeness checked';

        fseek($fp, 0);

        $insertValues = [];
        $isFirst = true;
        $this->startTrackingForDeletion();
        while (($row = fgets($fp)) !== false) {

            if ($isFirst) {
                // skip first line
                $isFirst = false;
                continue;
            }

            $insertValues[] = $this->getRecord($row);;

            if (count($insertValues) >= self::CHUNK_SIZE) {
                $this->insertValues(Country::HUNGARY, $insertValues, ['full_number', 'operator_source', 'operator_id']);
            }
        }

        fclose($fp);

        if ($insertValues) {
            $this->insertValues(Country::HUNGARY, $insertValues, ['full_number', 'operator_source', 'operator_id']);
        }

        $this->endTrackingForDeletion(Country::HUNGARY);
        $this->actionNotifyEventPortedNumber();
    }

    private function loadOperators()
    {
        $query = (new Query())
            ->select(['id', 'src_code' => 'operator_src_code', 'name'])
            ->from(Operator::tableName())
            ->where(['country_code' => \app\models\Country::HUNGARY])
            ->andWhere(['NOT', ['operator_src_code' => null]])
            ->createCommand(\Yii::$app->dbPgNnp)->query();

        foreach ($query as $row) {
            foreach (explode(',', $row['src_code']) as $item) {
                $this->_operators[$item] = [
                    'id' => $row['id'],
                    'name' => $row['name']
                ];
            }
        }
    }

    private function getRecord($row)
    {
        $row = trim($row);

        if (!$row) {
            return false;
        }

        $record = explode(';', $row);

        if (!$record || count($record) != 6) {
            echo 'Неправильные данные: ' . $row . PHP_EOL;

            $this->addError('Неправильные данные', $row);
            return false;
        }

        list($number, $_equipment, $_validFrom, $_validUntil, $actualProvider, $blockProvider) = $record;


        if (!$number || !is_numeric($number)) {
            $this->addError('Неправильный номер', $row);
            return false;
        }

        $number = Country::HUNGARY_PREFIX . $number;

        if (!isset($this->_operators[$actualProvider])) {
            $this->addError('Operator not found: ' . $actualProvider, ' ('.$number.')');
            return false;
        }

        $operator = $this->_operators[$actualProvider];

        return [$number, $operator['name'], $operator['id']];
    }

    private function addError($errKey, $errStr)
    {
        if (!isset($this->_errors[$errKey])) {
            $this->_errors[$errKey] = [];
        }

        if (count($this->_errors[$errKey]) <= 10) {
            $this->_errors[$errKey][] = $errStr;
        } else {
            if (!isset($this->_errors[$errKey][12])) {
                $this->_errors[$errKey][12] = 0;
            }
            $this->_errors[$errKey][12] +=1;
        }
    }

    private function printErrors()
    {
        foreach ($this->_errors as $errKey => $errStrs){
            foreach ($errStrs as $idx => $errStr) {
                echo PHP_EOL . 'Error: ' . $errKey . ': ' . ( $idx == 12 ? ' ...+' : '') . $errStr;
            }
        }
        echo PHP_EOL;
    }
}
