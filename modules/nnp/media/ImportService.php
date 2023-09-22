<?php

namespace app\modules\nnp\media;

use app\classes\Connection;
use app\helpers\DateTimeZoneHelper;
use app\models\billing\EventFlag;
use app\modules\nnp\models\Country;
use app\modules\nnp\models\NdcType;
use app\modules\nnp\models\NumberRange;
use app\modules\nnp2\models\ImportHistory;
use UnexpectedValueException;
use Yii;
use yii\base\Model;
use yii\db\Expression;

abstract class ImportService extends Model
{
    // Защита от сбоя обновления. Если после обновления осталось менее 70% исходного - не обновлять
    const DELTA_MIN = 0.7;

    const CHUNK_SIZE = 1000;
    const TABLE_TEMP = 'number_range_tmp';

    /** @var Connection */
    private $_db = null;

    protected $log = [];

    public $countryCode;

    /** @var Country */
    protected $country;

    public $countryFileId = null;

    protected $ndcTypeList = [];

    public $delimiter = ',';
    /**
     * @var ImportHistory
     */
    protected ImportHistory $importHistory;

    /**
     * Основной метод
     * Вызывается после _pre и перед _post
     * Внутри себя должен вызвать _importFromTxtRecalc
     */
    protected abstract function callbackMethod();

    /**
     * Преобразовать строчку файла в фиксированный массив данных
     *
     * @param int $i Номер строки
     * @param string[] $row ячейки строки csv-файла
     * @return string[] ['ndc', 'number_from', 'number_to', 'ndc_type_id', 'operator_source', 'region_source', 'city_source', 'full_number_from', 'full_number_to', 'date_resolution', 'detail_resolution', 'status_number', 'ndc_type_source']
     */
    protected abstract function callbackRow($i, $row);

    /**
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \yii\db\Exception
     */
    public function init()
    {
        $this->country = Country::findOne(['code' => $this->countryCode]);
        if (!$this->country) {
            throw new \InvalidArgumentException('Неправильная страна');
        }

//        if (NumberRange::isTriggerEnabled()) {
//            throw new \LogicException('Импорт невозможен, потому что триггер включен');
//        }

        $this->ndcTypeList = NdcType::getList();
    }

    /**
     * Импортировать
     *
     * @param ImportHistory $importHistory
     * @return bool
     * @throws \yii\db\Exception
     */
    public function run(ImportHistory $importHistory)
    {
        $this->importHistory = $importHistory;

        $this->_db = Yii::$app->dbPgNnp;
        $transaction = $this->_db->beginTransaction();
        try {
            $this->addLog(PHP_EOL . 'Начало импорта: ' . date(DateTimeZoneHelper::DATETIME_FORMAT) . PHP_EOL);
            $this->_preImport();
            $this->callbackMethod();
            $this->_postImport();

            $this->_makeSyncEvent($this->countryCode, $this->countryFileId);

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

        $insertValues = [];

        $i = 0;
        $processed = 0;
        while (($row = fgetcsv($handle, $rowLength = 4096, $this->delimiter)) !== false) {

            if (count($row) < 6) {
                if (count($row) > 1 || $row[0]) {
                    $this->addLog('Wrong string ' . implode(',', $row));
                }

                continue;
            }

            $row += array_fill(count($row), 11, null);

            // Преобразовать строчку файла в фиксированный массив данных
            $callbackRow = $this->callbackRow($i++, $row);
            if (!$callbackRow) {
                continue;
            }

            $insertValues[] = $callbackRow;

            if (count($insertValues) % self::CHUNK_SIZE === 0) {
                $this->batchInsertValues($insertValues);
                $processed += count($insertValues);
                $insertValues = [];
            }
        }

        if (!feof($handle)) {
            throw new UnexpectedValueException('Error fgets ' . $filePath);
        }

        fclose($handle);

        $this->batchInsertValues($insertValues, '.. ');
        $processed += count($insertValues);

        $this->addLog(PHP_EOL);

        $this->importHistory->lines_load = $i;
        $this->importHistory->lines_processed = $processed;
        $this->importHistory->markGettingReady($processed);
    }

    /**
     * @param $insertValues
     * @param string $logComment
     * @throws \yii\db\Exception
     */
    protected function batchInsertValues($insertValues, $logComment = '. ')
    {
        if (count($insertValues)) {
            $this->addLog($logComment);
            $this->_db->createCommand()->batchInsert(
                self::TABLE_TEMP,
                [
                    'ndc', 'ndc_str', 'number_from', 'number_to',
                    'ndc_type_id', 'operator_source',
                    'region_source', 'city_source',
                    'full_number_from', 'full_number_to',
                    'date_resolution', 'detail_resolution',
                    'status_number', 'ndc_type_source'
                ],
                $insertValues
            )->execute();
        }
    }

    /**
     * Перед импортом
     * Создать временную таблицу для записи в нее всех новых значений
     *
     * @throws \yii\db\Exception
     */
    private function _preImport()
    {
        $tableTmp = self::TABLE_TEMP;

        $sql = <<<SQL
CREATE TEMPORARY TABLE {$tableTmp}
(
  ndc integer,
  ndc_str character varying(255),
  number_from bigint,
  number_to bigint,
  ndc_type_id integer,
  operator_source character varying(255),
  region_source character varying(255),
  city_source character varying(255),
  full_number_from bigint NOT NULL,
  full_number_to bigint NOT NULL,
  date_resolution date,
  detail_resolution character varying(255),
  status_number character varying(255),
  ndc_type_source character varying(255)
)
SQL;
        $this->_db->createCommand($sql)->execute();
    }

    /**
     * После импорта
     * Из временной таблицы перенести в постоянную
     *
     * @throws \yii\db\Exception
     * @throws \LogicException
     */
    private function _postImport()
    {
        $this->addLog(PHP_EOL);

        $tableName = NumberRange::tableName();
        $tableTmp = self::TABLE_TEMP;

        // выключить всё, кроме больших диапазонов по всей стране
        $sql = <<<SQL
    UPDATE {$tableName}
    SET is_active = false, date_stop = now()
    WHERE is_active 
        AND country_code = :country_code 
        AND (ndc_type_id IS NOT NULL OR operator_id IS NOT NULL OR region_id IS NOT NULL OR ndc IS NOT NULL)
SQL;
        $affectedRowsBefore = $this->_db->createCommand($sql, [':country_code' => $this->country->code])->execute();
        $this->addLog(sprintf('Было: %d' . PHP_EOL, $affectedRowsBefore));

        // обновить и включить
        $sql = <<<SQL
    UPDATE
        {$tableName} number_range
    SET
        is_active = true,
        operator_source = {$tableTmp}.operator_source,
        region_source = {$tableTmp}.region_source,
        city_source = {$tableTmp}.city_source,
        ndc_type_id = {$tableTmp}.ndc_type_id,
        operator_id = CASE WHEN number_range.operator_source = {$tableTmp}.operator_source THEN number_range.operator_id ELSE NULL END,
        region_id = CASE WHEN number_range.region_source = {$tableTmp}.region_source THEN number_range.region_id ELSE NULL END,
        city_id = CASE WHEN number_range.city_source = {$tableTmp}.city_source THEN number_range.city_id ELSE NULL END,
        date_resolution = {$tableTmp}.date_resolution,
        detail_resolution = {$tableTmp}.detail_resolution,
        status_number = {$tableTmp}.status_number,
        ndc_type_source = {$tableTmp}.ndc_type_source,
        date_stop = null
    FROM
        {$tableTmp}
    WHERE
        number_range.full_number_from = {$tableTmp}.full_number_from
        AND number_range.number_to = {$tableTmp}.number_to
SQL;
        $affectedRowsUpdated = $this->_db->createCommand($sql)->execute();
        $this->addLog(sprintf('Обновлено: %d' . PHP_EOL, $affectedRowsUpdated));

        // удалить из временной таблицы уже обработанное
        $sql = <<<SQL
    DELETE FROM
        {$tableTmp}
    USING
        {$tableName} number_range
    WHERE
        number_range.full_number_from = {$tableTmp}.full_number_from
        AND number_range.number_to = {$tableTmp}.number_to
SQL;
        $this->_db->createCommand($sql)->execute();

        // добавить в основную таблицу всё оставшееся из временной
        $sql = <<<SQL
    INSERT INTO
        {$tableName}
    (
        country_code,
        ndc,
        ndc_str,
        number_from,
        number_to,
        ndc_type_id,
        operator_source,
        region_source,
        city_source,
        full_number_from,
        full_number_to,
        date_resolution,
        detail_resolution,
        status_number,
        ndc_type_source,
        insert_time
    )
    SELECT 
        :country_code as country_code, 
        ndc,
        ndc_str,
        number_from,
        number_to,
        ndc_type_id,
        operator_source,
        region_source,
        city_source,
        full_number_from,
        full_number_to,
        date_resolution,
        detail_resolution,
        status_number,
        ndc_type_source,
        NOW()
    FROM
        {$tableTmp}
SQL;
        $affectedRowsAdded = $this->_db->createCommand($sql, [':country_code' => $this->country->code])->execute();
        $this->addLog(sprintf('Добавлено: %d' . PHP_EOL, $affectedRowsAdded));

        $affectedRowsTotal = $affectedRowsUpdated + $affectedRowsAdded;
        $affectedRowsDelta = $affectedRowsBefore ? $affectedRowsTotal / $affectedRowsBefore : 1;
        $this->addLog(sprintf('Стало: %d (%.2f%%)' . PHP_EOL, $affectedRowsTotal, $affectedRowsDelta * 100));

        $sql = <<<SQL
DROP TABLE {$tableTmp}
SQL;
        $this->_db->createCommand($sql)->execute();

        if ($affectedRowsDelta < self::DELTA_MIN) {
            // throw new \LogicException('Стало слишком мало записей');
        }

        $this->importHistory->ranges_before = $affectedRowsBefore;
        $this->importHistory->ranges_updated = $affectedRowsUpdated;
        $this->importHistory->ranges_new = $affectedRowsAdded;
    }

    private function _makeSyncEvent($countryCode, $fileId)
    {
        $expression = new Expression("NOW() AT TIME ZONE 'utc'");
        $now = (new \yii\db\Query)->select($expression)->scalar(EventFlag::getDb());

        EventFlag::upsert('last_import_file_country_code', $countryCode);
        EventFlag::upsert('last_import_file_id', $fileId);
        EventFlag::upsert('last_import_file_date', $now);
        EventFlag::upsert('last_import_file_user_id', \Yii::$app->user->getId());
        EventFlag::upsert('is_nnp_sync_need', 1);
    }

    /**
     * @param string $message
     */
    protected function addLog($message)
    {
        if (trim($message, '.')) {
            $message = 'Импорт v1. ' . $message;
        }
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
