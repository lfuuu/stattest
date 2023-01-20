<?php

namespace app\modules\nnp\commands;

use app\helpers\DateTimeZoneHelper;
use app\modules\nnp\models\Number;
use app\modules\nnp\models\Number as nnpNumber;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\db\Connection;

abstract class PortedController extends Controller
{
    const CHUNK_SIZE = 500000;

    /** @var Connection */
    protected $_db = null;

    public $fileName = '';

    /**
     * Список возможных параметров при вызове метода
     *
     * @param string $actionID
     * @return string[]
     */
    public function options($actionID)
    {
        return array_merge(parent::options($actionID), ['fileName']);
    }

    /**
     * Импортировать данные
     *
     * @return int
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\db\Exception
     * @throws \LogicException
     */
    public function actionImport()
    {
//        if (NumberRange::isTriggerEnabled()) {
//            throw new \LogicException('Импорт невозможен, потому что триггер включен');
//        }

        if (!$this->fileName) {
            throw new \LogicException('Не указан fileName');
        }

        $this->_db = Yii::$app->dbPgNnp;
        $this->_db->enableLogging = false; // чтобы память не утекала
        // $transaction = $this->_db->beginTransaction();
        try {
            echo PHP_EOL . 'Начало импорта: ' . date(DateTimeZoneHelper::DATETIME_FORMAT) . PHP_EOL;

            $this->readData();
            // $transaction->commit();
            echo PHP_EOL . 'Окончание импорта: ' . date(DateTimeZoneHelper::DATETIME_FORMAT) . PHP_EOL;
            return ExitCode::OK;

        } catch (\Exception $e) {
            // $transaction->rollBack();
            Yii::error('Ошибка импорта');
            Yii::error($e);
            echo 'Ошибка: ' . $e->getMessage();
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Читать данные
     *
     * @return void
     */
    abstract protected function readData();

    /**
     * Импорт данных
     *
     * @param int $countryCode
     * @param array $insertValues
     * @throws \yii\db\Exception
     * @throws \LogicException
     */
    protected function insertValues($countryCode, &$insertValues)
    {
        $schema = get_class($this)::SCHEMA;

        if (!$schema || !isset($schema['fields']) || !isset($schema['pk'])) {
            throw new \InvalidArgumentException('Schema not configured');
        }

        $tableName = $schema['table'] ?? nnpNumber::tableName();

        $fieldsWithType = '';
        $fields = [];
        $fieldsStr = '';
        $textFields = [];
        foreach ($schema['fields'] as $field => $type) {
            $fieldsWithType .= ($fieldsWithType ? ', ' : '') . $field . ' ' . $type;
            $fieldsStr .= ($fieldsStr ? ', ' : '') . $field;
            $fields[] = $field;
            $type = strtolower($type);
            if (strpos($type, 'character') !== false || strpos($type, 'text') !== false) {
                $textFields[$field] = 1;
            }
        }

        $pk = $schema['pk'];

        // Создать временную таблицу
        $sql = <<<SQL
            CREATE TEMPORARY TABLE number_tmp
            (
                id SERIAL NOT NULL,
                {$fieldsWithType}
            )
SQL;

        // CONSTRAINT number_tmp_pkey PRIMARY KEY (id)
        $this->_db->createCommand('DROP TABLE IF EXISTS number_tmp')->execute();
        $this->_db->createCommand($sql)->execute();

//        $q = $this->_db->createCommand()->batchInsert('number_tmp', $fields, $insertValues)->rawSql;

        // Добавить в нее данные
        $this->_db->createCommand()
            ->batchInsert('number_tmp', $fields, $insertValues)
            ->execute();
        $insertValues = [];


        // удалить дубли
        $sql = <<<SQL
        WITH t1 AS (SELECT MAX(id) AS max_id, {$pk} FROM number_tmp GROUP BY {$pk} HAVING COUNT(*) > 1)
        DELETE FROM number_tmp
        USING t1
        WHERE number_tmp.{$pk} = t1.{$pk} AND number_tmp.id < t1.max_id
SQL;
        $affectedRows = $this->_db->createCommand($sql)->execute();
        echo sprintf('Дублей: %d' . PHP_EOL, $affectedRows);

        $sql = <<<SQL
            UPDATE
                {$tableName} number
            SET
SQL;

        $sqlSet = '';
        $sqlWhereOr = '';
        foreach ($fields as $field) {
            $updateSetSql = null;
            if (isset($schema['set'][$field])) {
                $updateSetSql = $schema['set'][$field];
            }
            $sqlSet .= ($sqlSet ? ',' . PHP_EOL : '') . "{$field} = " . ($updateSetSql ?? "number_tmp.{$field}");
            if ($field != $pk) {
                $castType = !isset($textFields[$field]) ? "::text" : "";
                $sqlWhereOr .= ($sqlWhereOr ? PHP_EOL . ' OR ' : '') . "coalesce(number.{$field}{$castType}, '') != coalesce(" . ($updateSetSql ?? "number_tmp.{$field}") . $castType . ", '')";
            }
        }
        $sql .= <<<SQL
                {$sqlSet}
            FROM
                number_tmp
            WHERE
                number.{$pk} = number_tmp.{$pk}
                and (
                    {$sqlWhereOr}
                )
SQL;

        $affectedRows = $this->_db->createCommand($sql)->execute();
        echo sprintf('Обновлено: %d' . PHP_EOL, $affectedRows);

        // удалить из временной таблицы уже обработанное
        $sql = <<<SQL
            DELETE FROM
                number_tmp
            USING
                {$tableName} number
            WHERE
                number.{$pk} = number_tmp.{$pk}
SQL;
        $this->_db->createCommand($sql)->execute();

        // добавить в основную таблицу всё оставшееся из временной
        $sql = <<<SQL
            INSERT INTO
                {$tableName}
            (
                country_code,
                {$fieldsStr}
            )
            WITH t1 AS (SELECT MAX(id) as id, {$pk} FROM number_tmp GROUP BY {$pk} HAVING COUNT(*) > 1)
            SELECT 
                :country_code as country_code, 
                {$fieldsStr}
            FROM
                number_tmp
SQL;
        $affectedRows = $this->_db->createCommand($sql, [':country_code' => $countryCode])->execute();
        echo sprintf('Добавлено: %d' . PHP_EOL, $affectedRows);

        $sql = <<<SQL
            DROP TABLE number_tmp
SQL;
        $this->_db->createCommand($sql)->execute();
    }

    public function actionNotifyEventPortedNumber()
    {
        Number::notifySync();

        return ExitCode::OK;
    }
}
