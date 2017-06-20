<?php

namespace app\modules\nnp\media;

use app\classes\Connection;
use app\helpers\DateTimeZoneHelper;
use app\modules\nnp\models\NumberRange;
use UnexpectedValueException;
use Yii;

abstract class ImportService
{
    // Защита от сбоя обновления. Если после обновления осталось менее 70% исходного - не обновлять
    const DELTA_MIN = 0.7;

    const CHUNK_SIZE = 1000;

    /** @var Connection */
    private $_db = null;

    protected $log = [];

    /**
     * Основной метод
     * Вызывается после _pre и перед _post
     * Внутри себя должен вызвать _importFromTxt
     */
    protected abstract function callbackMethod();

    /**
     * Преобразовать строчку файла в фиксированный массив данных
     *
     * @param int $i Номер строки
     * @param string[] $row ячейки строки csv-файла
     * @return string[] ['ndc', 'number_from', 'number_to', 'ndc_type_id', 'operator_source', 'region_source', 'full_number_from', 'full_number_to', 'date_resolution', 'detail_resolution', 'status_number']
     */
    protected abstract function callbackRow($i, $row);

    /**
     * Импортировать
     *
     * @param int $countryCode
     * @return bool
     * @throws \LogicException
     * @throws \yii\db\Exception
     */
    public function run($countryCode)
    {
        $this->_db = Yii::$app->dbPgNnp;
        $transaction = $this->_db->beginTransaction();
        try {

            if (NumberRange::isTriggerEnabled()) {
                throw new \LogicException('Импорт невозможен, потому что триггер включен');
            }

            $this->addLog(PHP_EOL . 'Начало импорта: ' . date(DateTimeZoneHelper::DATETIME_FORMAT) . PHP_EOL);
            $this->_preImport();
            $this->callbackMethod();
            $this->_postImport($countryCode);

            $transaction->commit();

            $this->addLog(PHP_EOL . 'Окончание импорта: ' . date(DateTimeZoneHelper::DATETIME_FORMAT) . PHP_EOL);
            return true;

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('Ошибка импорта');
            Yii::error($e);
            $this->addLog('Ошибка: ' . $e->getMessage());
            // $this->addLog($e->getTraceAsString());
            return false;
        }
    }

    /**
     * Импортировать из txt-файла
     *
     * @param string $filePath как локальный, так и http
     * @throws \yii\db\Exception
     * @throws \UnexpectedValueException
     * @throws \LogicException
     */
    protected function importFromTxt($filePath)
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new UnexpectedValueException('Error fopen ' . $filePath);
        }

        $tableName = 'number_range_tmp';
        $insertValues = [];

        $i = 0;
        while (($row = fgetcsv($handle)) !== false) {

            if (count($row) < 6) {
                throw new \LogicException('Wrong string ' . implode(',', $row));
            }

            // Преобразовать строчку файла в фиксированный массив данных
            $callbackRow = $this->callbackRow($i++, $row);
            if (!$callbackRow) {
                continue;
            }

            $insertValues[] = $callbackRow;

            if (count($insertValues) % self::CHUNK_SIZE === 0) {
                $this->addLog('. ');
                $this->_db->createCommand()->batchInsert(
                    $tableName,
                    ['ndc', 'number_from', 'number_to', 'ndc_type_id', 'operator_source', 'region_source', 'full_number_from', 'full_number_to', 'date_resolution', 'detail_resolution', 'status_number'],
                    $insertValues
                )->execute();
                $insertValues = [];
            }
        }

        if (!feof($handle)) {
            throw new UnexpectedValueException('Error fgets ' . $filePath);
        }

        fclose($handle);

        if (count($insertValues)) {
            $this->addLog('.. ');
            $this->_db->createCommand()->batchInsert(
                $tableName,
                ['ndc', 'number_from', 'number_to', 'ndc_type_id', 'operator_source', 'region_source', 'full_number_from', 'full_number_to', 'date_resolution', 'detail_resolution', 'status_number'],
                $insertValues
            )->execute();
        }

        $this->addLog(PHP_EOL);
    }

    /**
     * Перед импортом
     * Создать временную таблицу для записи в нее всех новых значений
     *
     * @throws \yii\db\Exception
     */
    private function _preImport()
    {
        $sql = <<<SQL
CREATE TEMPORARY TABLE number_range_tmp
(
  ndc integer,
  number_from bigint,
  number_to bigint,
  ndc_type_id integer,
  operator_source character varying(255),
  region_source character varying(255),
  full_number_from bigint NOT NULL,
  full_number_to bigint NOT NULL,
  date_resolution date,
  detail_resolution character varying(255),
  status_number character varying(255)
)
SQL;
        $this->_db->createCommand($sql)->execute();
    }

    /**
     * После импорта
     * Из временной таблицы перенести в постоянную
     *
     * @param string $countryCode
     * @throws \yii\db\Exception
     * @throws \LogicException
     */
    private function _postImport($countryCode)
    {
        $this->addLog(PHP_EOL);

        $tableName = NumberRange::tableName();

        // выключить всё, кроме больших диапазонов по всей стране
        $sql = <<<SQL
    UPDATE {$tableName}
    SET is_active = false, date_stop = now()
    WHERE is_active 
        AND country_code = :country_code 
        AND (ndc_type_id IS NOT NULL OR operator_id IS NOT NULL OR region_id IS NOT NULL OR ndc IS NOT NULL)
SQL;
        $affectedRowsBefore = $this->_db->createCommand($sql, [':country_code' => $countryCode])->execute();
        $this->addLog(sprintf('Было: %d' . PHP_EOL, $affectedRowsBefore));

        // обновить и включить
        $sql = <<<SQL
    UPDATE
        {$tableName} number_range
    SET
        is_active = true,
        operator_source = number_range_tmp.operator_source,
        region_source = number_range_tmp.region_source,
        ndc_type_id = number_range_tmp.ndc_type_id,
        operator_id = CASE WHEN number_range.operator_source = number_range_tmp.operator_source THEN number_range.operator_id ELSE NULL END,
        region_id = CASE WHEN number_range.region_source = number_range_tmp.region_source THEN number_range.region_id ELSE NULL END,
        date_resolution = number_range_tmp.date_resolution,
        detail_resolution = number_range_tmp.detail_resolution,
        status_number = number_range_tmp.status_number,
        date_stop = null
    FROM
        number_range_tmp
    WHERE
        number_range.full_number_from = number_range_tmp.full_number_from
        AND number_range.number_to = number_range_tmp.number_to
SQL;
        $affectedRowsUpdated = $this->_db->createCommand($sql)->execute();
        $this->addLog(sprintf('Обновлено: %d' . PHP_EOL, $affectedRowsUpdated));

        // удалить из временной таблицы уже обработанное
        $sql = <<<SQL
    DELETE FROM
        number_range_tmp
    USING
        {$tableName} number_range
    WHERE
        number_range.full_number_from = number_range_tmp.full_number_from
        AND number_range.number_to = number_range_tmp.number_to
SQL;
        $this->_db->createCommand($sql)->execute();

        // добавить в основную таблицу всё оставшееся из временной
        $sql = <<<SQL
    INSERT INTO
        {$tableName}
    (
        country_code,
        ndc,
        number_from,
        number_to,
        ndc_type_id,
        operator_source,
        region_source,
        full_number_from,
        full_number_to,
        date_resolution,
        detail_resolution,
        status_number,
        insert_time
    )
    SELECT 
        :country_code as country_code, 
        ndc,
        number_from,
        number_to,
        ndc_type_id,
        operator_source,
        region_source,
        full_number_from,
        full_number_to,
        date_resolution,
        detail_resolution,
        status_number,
        NOW()
    FROM
        number_range_tmp
SQL;
        $affectedRowsAdded = $this->_db->createCommand($sql, [':country_code' => $countryCode])->execute();
        $this->addLog(sprintf('Добавлено: %d' . PHP_EOL, $affectedRowsAdded));

        $affectedRowsTotal = $affectedRowsUpdated + $affectedRowsAdded;
        $affectedRowsDelta = $affectedRowsBefore ? $affectedRowsTotal / $affectedRowsBefore : 1;
        $this->addLog(sprintf('Стало: %d (%.2f%%)' . PHP_EOL, $affectedRowsTotal, $affectedRowsDelta * 100));

        $sql = <<<SQL
DROP TABLE number_range_tmp
SQL;
        $this->_db->createCommand($sql)->execute();

        if ($affectedRowsDelta < self::DELTA_MIN) {
            throw new \LogicException('Стало слишком мало записей');
        }
    }

    /**
     * @param string $message
     */
    protected function addLog($message)
    {
        $this->log[] = $message;
    }

    /**
     * @return string[]
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * @return string
     */
    public function getLogAsString()
    {
        return implode('', $this->log);
    }
}