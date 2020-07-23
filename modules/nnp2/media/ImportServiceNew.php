<?php

namespace app\modules\nnp2\media;

use app\classes\Connection;
use app\helpers\DateTimeZoneHelper;
use app\modules\nnp\models\Country;
use app\modules\nnp2\media\related\CityRelated;
use app\modules\nnp2\media\related\GeoRelated;
use app\modules\nnp2\media\related\NdcTypeRelated;
use app\modules\nnp2\media\related\OperatorRelated;
use app\modules\nnp2\media\related\RegionRelated;
use app\modules\nnp2\models\City;
use app\modules\nnp2\models\GeoPlace;
use app\modules\nnp2\models\ImportHistory;
use app\modules\nnp2\models\NdcType;
use app\modules\nnp2\models\NumberRange;
use app\modules\nnp2\models\Operator;
use app\modules\nnp2\models\Region;
use UnexpectedValueException;
use Yii;
use yii\base\Model;

abstract class ImportServiceNew extends Model
{
    // Защита от сбоя обновления. Если после обновления осталось менее 70% исходного - не обновлять
    const DELTA_MIN = 0.7;

    const CHUNK_SIZE = 5000;
    const TABLE_TEMP_NUMBER = 'nnp2.tmp_number_range';

    protected $tmpTableName = '';

    /** @var Connection */
    protected $db = null;

    protected $log = [];

    public $countryCode;

    /** @var Country */
    protected $country;

    /** @var RegionRelated */
    protected $regionRelated;

    /** @var GeoRelated */
    protected $geoRelated;

    /** @var CityRelated */
    protected $cityRelated;

    /** @var OperatorRelated */
    protected $operatorRelated;

    /** @var NdcTypeRelated */
    protected $ndcTypeRelated;

    public $delimiter = ',';
    /**
     * @var array
     */
    protected $rows = [];

    protected $errorRows = [];
    /**
     * @var ImportHistory
     */
    protected ImportHistory $importHistory;

    protected $tableNameByCountry;

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
     * @return string[] ['geo_place_id', 'ndc_type_id', 'operator_id', 'number_from', 'number_to', 'full_number_from', 'full_number_to', 'allocation_reason', 'allocation_date_start', 'comment',]
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

        $this->db = Yii::$app->dbPgNnp2;
        $this->tmpTableName = self::TABLE_TEMP_NUMBER;

        $this->regionRelated = new RegionRelated($this->db, $this->country->code);

        $this->cityRelated = new CityRelated($this->db, $this->country->code);
        $this->cityRelated->setRegionRelated($this->regionRelated);

        $this->geoRelated = new GeoRelated($this->db, $this->country->code);
        $this->geoRelated->setRegionRelated($this->regionRelated);
        $this->geoRelated->setCityRelated($this->cityRelated);

        $this->ndcTypeRelated = new NdcTypeRelated($this->db, $this->country->code);

        $this->operatorRelated = new OperatorRelated($this->db, $this->country->code);
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

        $this->db = Yii::$app->dbPgNnp2;
        $transaction = $this->db->beginTransaction();
        try {
            $this->addLog(PHP_EOL . 'Начало импорта: ' . date(DateTimeZoneHelper::DATETIME_FORMAT) . PHP_EOL);
            $this->preImport();
            $this->callbackMethod();
            $this->postImport();
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
     * @throws \app\exceptions\ModelValidationException
     */
    protected function readFromTxt($filePath)
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new UnexpectedValueException('Error fopen ' . $filePath);
        }

        // count lines in file
        $total = 1;
        try {
            $total = intval(exec('wc -l ' . escapeshellarg($this->url) . ' 2>/dev/null'));
        } catch (\Throwable $e) {}
        $this->importHistory->lines_load = $total;

        $i = 0;
        $this->rows = [];
        while (($row = fgetcsv($handle, $rowLength = 4096, $this->delimiter)) !== false) {
            if (!$i++ && !is_numeric($row[0])) {
                // Шапка (первая строчка с названиями полей) - пропустить
                continue;
            }

            if (count($row) < 6) {
                if (count($row) > 1 || $row[0]) {
                    $this->addLog('Wrong string ' . implode(',', $row));
                }

                continue;
            }

            $row = array_map('trim', $row);
            $row += array_fill(count($row), 11, null);

            $this->rows[] = $row;
            $this->importHistory->markReading($i, $total);
        }

        if (!feof($handle)) {
            throw new UnexpectedValueException('Error fgets ' . $filePath);
        }

        fclose($handle);

        $this->importHistory->lines_load = $i;
        $this->importHistory->markReading();
    }

    /**
     * Импортировать из txt-файла
     *
     * @throws \app\exceptions\ModelValidationException
     * @throws \yii\db\Exception
     */
    protected function importData()
    {
        $this->prepareAll();
        $this->importHistory->markPrepared();

        foreach ($this->rows as $i => $row) {
            $this->checkRelated($row);
        }

        $this->createRelated();
        $this->importHistory->markRelations();

        $this->errorRows = [];

        $total = count($this->rows);
        $insertValues = [];
        foreach ($this->rows as $i => $row) {
            // Преобразовать строчку файла в фиксированный массив данных
            $callbackRow = $this->callbackRow($i, $row);
            if (!$callbackRow) {
                continue;
            }

            $insertValues[] = $callbackRow;

            $this->importHistory->markGettingReady($i + 1, $total);
        }
        $this->importHistory->lines_processed = count($insertValues);
        $this->importHistory->markGettingReady();

        $this->processInsertValues($insertValues);
        $this->importHistory->markInserting();

        $this->addLog(PHP_EOL);
    }

    /**
     * @param $insertValues
     * @throws \yii\db\Exception
     */
    protected function processInsertValues($insertValues)
    {
        $total = count($insertValues);
        if (!$total) {
            return;
        }

        $i = 0;
        foreach (array_chunk($insertValues, static::CHUNK_SIZE) as $chunk) {
            $this->batchInsertValues($chunk, $i, $total);
            $i += count($chunk);
        }
    }

    /**
     * @param $insertValues
     * @param $i
     * @param $total
     * @throws \yii\db\Exception
     * @throws \app\exceptions\ModelValidationException
     */
    protected function batchInsertValues($insertValues, $i, $total)
    {
        if (count($insertValues)) {
            $this->db->createCommand()->batchInsert(
                $this->tmpTableName,
                [
                    'country_code',
                    'geo_place_id',
                    'ndc_type_id', 'operator_id',

                    'number_from', 'number_to',
                    'full_number_from', 'full_number_to',

                    'cnt',

                    'is_valid',

                    'allocation_reason', 'allocation_date_start',
                    'comment',

                    'insert_time',
                ],
                $insertValues
            )->execute();

            $this->importHistory->markInserting($i, $total);
        }
    }

    /**
     * @param bool $refresh
     * @return string
     */
    protected function getTableNameByCountry($refresh = false)
    {
        if ($refresh || is_null($this->tableNameByCountry)) {
            $this->tableNameByCountry = sprintf("%s_%s", NumberRange::tableName(), $this->country->code);

            if ($this->db->getTableSchema($this->tableNameByCountry, true) === null) {
                $this->tableNameByCountry = '';
            }
        }

        return $this->tableNameByCountry;
    }

    /**
     * Перед импортом
     * Создать временную таблицу для записи в нее всех новых значений
     *
     * @throws \yii\db\Exception
     */
    protected function preImport()
    {
        $tableTmp = $this->tmpTableName;

$sql = <<<SQL
DROP TABLE IF EXISTS {$tableTmp};
SQL;
$this->db->createCommand($sql)->execute();

        $sql = <<<SQL
--CREATE TEMPORARY TABLE {$tableTmp}
CREATE TABLE {$tableTmp}
(
  country_code integer,
  geo_place_id integer,
  ndc_type_id integer,
  operator_id integer,
  
  number_from bigint,
  number_to bigint,
  full_number_from bigint NOT NULL,
  full_number_to bigint NOT NULL,
  
  cnt                   bigint  default 1                                         not null,
  
  is_valid         boolean default false ,
  
  allocation_reason character varying(255),
  allocation_date_start date,
  comment character varying(255),
  
  previous_id     integer,
  
  range_short_id     integer,
  range_short_old_id integer,
  
  insert_time date
)
SQL;
        $this->db->createCommand($sql)->execute();
    }

    /**
     * После импорта
     * Из временной таблицы перенести в постоянную
     *
     * @throws \app\exceptions\ModelValidationException
     * @throws \yii\db\Exception
     */
    protected function postImport()
    {
        $tableName = NumberRange::tableName();
        $tableNameByCountry = $this->getTableNameByCountry();

        $tableNdcType = NdcType::tableName();
        $tableOperator = Operator::tableName();
        $tableGeo = GeoPlace::tableName();

        $tableTmp = $this->tmpTableName;

        // *****
        // выключить всё, кроме больших диапазонов по всей стране
        $sql = <<<SQL
    UPDATE
        {$tableName}
    SET
        is_active = false,
        stop_time = now()
    WHERE is_active
        AND country_code = :country_code
SQL;
        $affectedRowsBefore = $this->db->createCommand($sql, [':country_code' => $this->country->code])->execute();
        $this->addLog(sprintf('Было: %d' . PHP_EOL, $affectedRowsBefore));

        $this->importHistory->markOldUpdated();

        $affectedRowsUpdated = 0;
        if ($tableNameByCountry) {
            // *****
            // обновить (старые) - проставим им активность
            $sql = <<<SQL
    UPDATE
        {$tableNameByCountry} number_range
    SET
        is_active = true,
        --is_active = tmp.is_valid,
        
        --allocation_date_stop = null,
        stop_time = null,
        
        --allocation_date_start = tmp.allocation_date_start,
        --allocation_reason = tmp.allocation_reason,
        
        comment = tmp.comment
    FROM
        {$tableTmp} tmp
    WHERE
        TRUE
        --number_range.is_active IS FALSE
        
        AND number_range.full_number_from = tmp.full_number_from
        AND number_range.number_to = tmp.number_to
        AND number_range.geo_place_id = tmp.geo_place_id
        AND number_range.ndc_type_id = tmp.ndc_type_id
        AND number_range.operator_id = tmp.operator_id
        --AND number_range.allocation_reason = tmp.allocation_reason
        --AND number_range.allocation_date_start = tmp.allocation_date_start
        --AND (
        --    (
        --        number_range.allocation_date_start IS NULL
        --        AND tmp.allocation_date_start IS NULL
        --    )
        --    OR
        --    (number_range.allocation_date_start = tmp.allocation_date_start)
        --)
SQL;
            $affectedRowsUpdated = $this->db->createCommand($sql)->execute();
            $this->addLog(sprintf('Обновлено: %d' . PHP_EOL, $affectedRowsUpdated));

            // *****
            // удалить из временной таблицы уже обработанное (старые)
            $sql = <<<SQL
    DELETE FROM
        {$tableTmp} tmp
    USING
        {$tableNameByCountry} number_range
    WHERE
        number_range.full_number_from = tmp.full_number_from
        AND number_range.number_to = tmp.number_to
        AND number_range.geo_place_id = tmp.geo_place_id
        AND number_range.ndc_type_id = tmp.ndc_type_id
        AND number_range.operator_id = tmp.operator_id
        --AND number_range.allocation_reason = tmp.allocation_reason
        --AND number_range.allocation_date_start = tmp.allocation_date_start
        --AND (
        --    (
        --        number_range.allocation_date_start IS NULL
        --        AND tmp.allocation_date_start IS NULL
        --    )
        --    OR
        --    (number_range.allocation_date_start = tmp.allocation_date_start)
        --)
SQL;
            $this->db->createCommand($sql)->execute();
        }

        // *****
        // проверки на валидность
        // проверка ndc_type
        $sql = <<<SQL
UPDATE
    {$tableTmp} tmp
SET
    is_valid = ndc_type.is_valid
FROM {$tableNdcType} ndc_type
WHERE
  ndc_type.id = tmp.ndc_type_id
SQL;
        $this->db->createCommand($sql)->execute();

        // проверка operator
        $sql = <<<SQL
UPDATE
    {$tableTmp} tmp
SET
    is_valid = operator.is_valid
FROM {$tableOperator} operator
WHERE
  tmp.is_valid
  AND operator.id = tmp.operator_id
SQL;
        $this->db->createCommand($sql)->execute();

        // проверка гео
        $sql = <<<SQL
UPDATE
    {$tableTmp} tmp
SET
    is_valid = geo_place.is_valid
FROM {$tableGeo} geo_place
WHERE
  tmp.is_valid
  AND geo_place.id = tmp.geo_place_id
SQL;
        $this->db->createCommand($sql)->execute();

        $this->importHistory->markRelationsChecked();



        if ($tableNameByCountry) {
            // *****
            // сохраняем историю
            // изменились связанные сущности
            $sql = <<<SQL
    UPDATE
        {$tableTmp} tmp
    SET
        previous_id = number_range.id
    FROM
        {$tableNameByCountry} number_range
    WHERE
        number_range.is_active IS FALSE
        AND number_range.full_number_from = tmp.full_number_from
        AND number_range.number_to = tmp.number_to
SQL;
            $this->db->createCommand($sql)->execute();

            // изменились правые границы
            $sql = <<<SQL
    UPDATE
        {$tableTmp} tmp
    SET
        previous_id = number_range.id
    FROM
        {$tableNameByCountry} number_range
    WHERE
        number_range.is_active IS FALSE
        AND tmp.previous_id IS NULL
        AND number_range.full_number_from = tmp.full_number_from
        AND number_range.number_to > tmp.number_to
SQL;
            $this->db->createCommand($sql)->execute();

            // изменились левые границы
            $sql = <<<SQL
    UPDATE
        {$tableTmp} tmp
    SET
        previous_id = number_range.id
    FROM
        {$tableNameByCountry} number_range
    WHERE
        number_range.is_active IS FALSE
        AND tmp.previous_id IS NULL
        AND number_range.full_number_to = tmp.full_number_to
        AND number_range.number_from < tmp.number_from
SQL;
            $this->db->createCommand($sql)->execute();

            // изменились границы
            $sql = <<<SQL
    UPDATE
        {$tableTmp} tmp
    SET
        previous_id = number_range.id
    FROM
        {$tableNameByCountry} number_range
    WHERE
        number_range.is_active IS FALSE
        AND tmp.previous_id IS NULL
        AND number_range.full_number_from < tmp.full_number_from
        AND number_range.full_number_to > tmp.full_number_to
SQL;
            $this->db->createCommand($sql)->execute();
        }



        // *****
        // добавить в основную таблицу всё оставшееся из временной
        $sql = <<<SQL
    INSERT INTO
        {$tableName}
    (
        country_code,
        geo_place_id,
        
        ndc_type_id,
        operator_id,
        
        number_from,
        number_to,
        
        full_number_from,
        full_number_to,
        
        cnt,
        
        is_active,
        is_valid,
        
        allocation_date_start,
        allocation_reason,
        comment,
        
        previous_id,
        
        insert_time
    )
    SELECT 
        country_code,
        geo_place_id,
        
        ndc_type_id,
        operator_id,

        number_from,
        number_to,
        
        full_number_from,
        full_number_to,
        
        cnt,
        
        true,
        is_valid,
        
        allocation_date_start,
        allocation_reason,
        comment,
        
        previous_id,
        
        NOW()
    FROM
        {$tableTmp}
SQL;
        $affectedRowsAdded = $this->db->createCommand($sql)->execute();
        if ($affectedRowsAdded == 0) {
            // при добавлении в партицию возвращает пустой результат
            $affectedRowsAdded = $this->db->createCommand("select count(*) from {$tableTmp}")->queryScalar();
        }

        $this->addLog(sprintf('Добавлено всего: %d' . PHP_EOL, $affectedRowsAdded));

        $this->importHistory->markNewAdded();

        if ($affectedRowsAdded) {
            // убираем дубликаты из новых
            $tableNameByCountry = $this->getTableNameByCountry(true);

            $sql = <<<SQL
    UPDATE
        {$tableNameByCountry} nr
    SET
        is_active = false
    FROM
         (
             SELECT
                 max(id) id,
                 number_from,
                 full_number_to
             FROM
                 {$tableNameByCountry}
             WHERE
                is_active
             GROUP BY number_from, full_number_to
         ) nr_stat
    WHERE
        nr.is_active
        AND nr.number_from = nr_stat.number_from
        AND nr.full_number_to = nr_stat.full_number_to
        AND nr.id <> nr_stat.id
SQL;
            $affectedRowsDuplicates = $this->db->createCommand($sql)->execute();

            if ($affectedRowsDuplicates) {
                // вычитаем из новых
                $affectedRowsAdded -= $affectedRowsDuplicates;

                $this->addLog(sprintf('Дубликатов: %d' . PHP_EOL, $affectedRowsDuplicates));
            }

            $this->importHistory->ranges_duplicates = $affectedRowsDuplicates;
        }

        $this->importHistory->markUpdatedFixed();

        $affectedRowsTotal = $affectedRowsUpdated + $affectedRowsAdded;
        $affectedRowsDelta = $affectedRowsBefore ? $affectedRowsTotal / $affectedRowsBefore : 1;
        $this->addLog(sprintf('Стало: %d (%.2f%%)' . PHP_EOL, $affectedRowsTotal, $affectedRowsDelta * 100));

        // *****
        // удаляем временную таблицу
        $sql = <<<SQL
DROP TABLE {$tableTmp}
SQL;
        //$this->_db->createCommand($sql)->execute();

//        if ($affectedRowsDelta < self::DELTA_MIN) {
//            throw new \LogicException('Стало слишком мало записей');
//        }

        $this->importHistory->ranges_before = $affectedRowsBefore;
        $this->importHistory->ranges_updated = $affectedRowsUpdated;
        $this->importHistory->ranges_new = $affectedRowsAdded;

        $this->updateCntOperators();
        $this->updateCntRegions();
        $this->updateCntCities();
    }

    /**
     * Обновить столбец cnt
     *
     * @return string
     * @throws \yii\db\Exception
     */
    protected function updateCntOperators()
    {
        $tableNameByCountry = $this->getTableNameByCountry();
        if (!$tableNameByCountry) {
            return true;
        }

        $operatorTableName = Operator::tableName();

        // set to 0
        $sqlClear = <<<SQL
UPDATE {$operatorTableName}
    SET cnt = 0
WHERE
    country_code = :country_code
SQL;
        $this->db->createCommand($sqlClear, [':country_code' => $this->country->code])->execute();
        unset($sqlClear);

        $sql = <<<SQL
    UPDATE {$operatorTableName}
        SET cnt = operator_stat.cnt
    FROM 
        (
            SELECT
                operator_id,
                SUM(cnt) cnt
            FROM
                {$tableNameByCountry}
            WHERE
                operator_id IS NOT NULL
                AND is_active
            GROUP BY
                operator_id
        ) operator_stat
    WHERE {$operatorTableName}.id = operator_stat.operator_id
SQL;
        $this->db->createCommand($sql)->execute();

        return true;
    }

    /**
     * Обновить столбец cnt
     *
     * @return string
     * @throws \yii\db\Exception
     */
    protected function updateCntRegions()
    {
        $tableNameByCountry = $this->getTableNameByCountry();
        if (!$tableNameByCountry) {
            return true;
        }

        $regionTableName = Region::tableName();
        $tableGeo = GeoPlace::tableName();

        // set to 0
        $sqlClear = <<<SQL
UPDATE {$regionTableName}
    SET cnt = 0
WHERE
    country_code = :country_code
SQL;
        $this->db->createCommand($sqlClear, [':country_code' => $this->country->code])->execute();
        unset($sqlClear);

        $sql = <<<SQL
    UPDATE {$regionTableName}
        SET cnt = relation_stat.cnt
    FROM 
        (
            SELECT
                geo.region_id,
                SUM(nr.cnt) cnt
            FROM
                {$tableNameByCountry} nr, {$tableGeo} geo
            WHERE
                geo.region_id IS NOT NULL
                AND nr.is_active
                AND geo.id = nr.geo_place_id
            GROUP BY
                region_id
        ) relation_stat
    WHERE {$regionTableName}.id = relation_stat.region_id
SQL;
        $this->db->createCommand($sql)->execute();

        return true;
    }

    /**
     * Обновить столбец cnt
     *
     * @return string
     * @throws \yii\db\Exception
     */
    protected function updateCntCities()
    {
        $tableNameByCountry = $this->getTableNameByCountry();
        if (!$tableNameByCountry) {
            return true;
        }

        $cityTableName = City::tableName();
        $tableGeo = GeoPlace::tableName();

        // set to 0
        $sqlClear = <<<SQL
UPDATE {$cityTableName}
    SET cnt = 0
WHERE
    country_code = :country_code
SQL;
        $this->db->createCommand($sqlClear, [':country_code' => $this->country->code])->execute();
        unset($sqlClear);

        $sql = <<<SQL
    UPDATE {$cityTableName}
        SET cnt = relation_stat.cnt
    FROM 
        (
            SELECT
                geo.city_id,
                SUM(nr.cnt) cnt
            FROM
                {$tableNameByCountry} nr, {$tableGeo} geo
            WHERE
                geo.city_id IS NOT NULL
                AND nr.is_active
                AND geo.id = nr.geo_place_id
            GROUP BY
                city_id
        ) relation_stat
    WHERE {$cityTableName}.id = relation_stat.city_id
SQL;
        $this->db->createCommand($sql)->execute();

        return true;
    }

    /**
     * @param string $message
     */
    protected function addLog($message)
    {
        if (trim($message, '.')) {
            $message = 'Импорт v2. ' . $message;
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

    /**
     * Проверить, что значение является натуральным числом
     *
     * @param string|int|null $value Пустое привести к null, непустое к int
     * @param bool $isEmptyAllowed Что возвращать для пустых
     * @param bool $isConvertToInt
     * @return bool
     */
    private function _checkNatural(&$value, $isEmptyAllowed, $isConvertToInt = true)
    {
        $value = trim($value);
        if (!$value) {
            $value = null;
            return $isEmptyAllowed;
        }

        if (!preg_match('/^\d+$/', $value)) {
            return false;
        }

        if ($isConvertToInt) {
            $value = (int)$value;
        }

        return true;
    }

    /**
     * Проверить, что значение является строкой. Можно пустой
     *
     * @param string $value
     * @return bool
     */
    private function _checkString(&$value)
    {
        $value = trim($value);
        return $value === '' || !is_numeric($value);
    }

    /**
     * @param array $row
     */
    protected function checkRelated($row)
    {
        $this->ndcTypeRelated->checkToAdd($row[2], $row[3]);
        $this->operatorRelated->checkToAdd($row[8]);
    }

    /**
     * @throws \yii\db\Exception
     */
    protected function prepareAll()
    {
        $rows = [];
        foreach ($this->rows as $i => $row) {
            $ndcTypeId = $row[3]; // parent_id
            if (!$ndcTypeId) {
                // get parent_id
                $ndcTypeId = $this->ndcTypeRelated->getRealNdcTypeId($ndcTypeId);
            }
            if ($ndcTypeId == 6) {
                // log error
                continue;
            }
            // TODO: checkToAdd - > startAdd
            // TODO: like GeoPlace
            $this->ndcTypeRelated->checkToAdd($row[2], $ndcTypeId);

            $this->operatorRelated->checkToAdd($row[8]);

            $regionName = $this->regionRelated->checkToAdd($row[6]);
            $cityName = $this->cityRelated->checkToAdd($regionName, $row[7]);

            $ndc = $this->geoRelated->startAdd($row[1], $regionName, $cityName);
            if (!$ndc) {
                // log error
                continue;
            }

            // update with transformed values
            $row[1] = $ndc;
            $row[6] = $regionName;
            $row[7] = $cityName;

            $rows[] = $row;
            $this->geoRelated->commitAdd();
        }
        $this->rows = $rows;

        //
        $this->ndcTypeRelated->addNew();

        $this->operatorRelated->addNew();

        $this->regionRelated->addNew();

        $this->cityRelated->loadNew();
        $this->cityRelated->addNew();

        $this->geoRelated->loadNew();
        $this->geoRelated->addNew();
    }

    /**
     * @throws \app\exceptions\ModelValidationException
     * @throws \yii\db\Exception
     */
    protected function createRelated()
    {
        $this->geoRelated->addNew();

        $this->ndcTypeRelated->addNew();

        // operators
        $this->operatorRelated->addNew();
    }
}