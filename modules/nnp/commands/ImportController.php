<?php
namespace app\modules\nnp\commands;

use app\classes\Connection;
use app\helpers\DateTimeZoneHelper;
use app\models\billing\Server;
use app\models\Country;
use app\modules\nnp\models\NdcType;
use app\modules\nnp\models\NumberRange;
use UnexpectedValueException;
use Yii;
use yii\base\InvalidParamException;
use yii\base\Module;
use yii\console\Controller;

/**
 * Импорт из справочников
 */
class ImportController extends Controller
{
    // Защита от сбоя обновления. Если после обновления осталось менее 70% исходного - не обновлять
    const DELTA_MIN = 0.7;

    const EXCEL2007 = 'Excel2007';
    const EXCEL5 = 'Excel5';

    /** @link http://confluence.welltime.ru/pages/viewpage.action?pageId=8356629 */
    const FILE_ID_SLOVAKIA = '0B9ds-UaQbaC7X1BodzJVOTItNXc';
    const FILE_ID_HUNGARY = '0B9ds-UaQbaC7OXNISlVZX3hhYVU';
    const FILE_ID_GERMANY = '0B9ds-UaQbaC7MDRNLTl5WVN2Y0k';
    const FILE_ID_AUSTRIA = '0B9ds-UaQbaC7UHo2M3VfM3I5d2M';
    const FILE_ID_CZECH = '0B9ds-UaQbaC7VzNPMzljR2VTMms';

    /** @var Connection */
    protected $db = null;

    /**
     * @param string $id the ID of this controller.
     * @param Module $module the module that this controller belongs to.
     * @param array $config name-value pairs that will be used to initialize the object properties.
     */
    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->db = Yii::$app->dbPgNnp;
    }

    /**
     * Импортировать Россию из Россвязи. 2 минуты
     * @link http://www.rossvyaz.ru/activity/num_resurs/registerNum/
     */
    public function actionRus()
    {
        $this->import('importRusCallback', Country::PREFIX_RUSSIA);
    }

    /**
     * Импортировать Россию из Россвязи. Callback
     * @link http://www.rossvyaz.ru/activity/num_resurs/registerNum/
     */
    public function importRusCallback()
    {
        /**
         * Ссылки на файлы для скачивания
         * [url => ndc_type_id]
         * @link http://www.rossvyaz.ru/activity/num_resurs/registerNum/
         */
        $rusUrls = [
            'http://www.rossvyaz.ru/docs/articles/Kody_ABC-3kh.csv' => NdcType::ID_ABC,
            'http://www.rossvyaz.ru/docs/articles/Kody_ABC-4kh.csv' => NdcType::ID_ABC,
            'http://www.rossvyaz.ru/docs/articles/Kody_ABC-8kh.csv' => NdcType::ID_ABC,
            'http://www.rossvyaz.ru/docs/articles/Kody_DEF-9kh.csv' => NdcType::ID_DEF,
        ];
        foreach ($rusUrls as $url => $ndcTypeId) {
            $this->importFromTxt(
                $url,
                function ($row) use ($ndcTypeId) {
                    return
                        [
                            (int)$row[0], // ndc
                            (int)$row[1], // number_from
                            (int)$row[2], // number_to
                            $ndcTypeId, // ndc_type_id
                            trim($row[4]), // operator_source
                            trim($row[5]), // region_source
                            Country::PREFIX_RUSSIA . trim($row[0]) . trim($row[1]), // full_number_from
                            Country::PREFIX_RUSSIA . trim($row[0]) . trim($row[2]), // full_number_to
                            null, // date_resolution
                            null, // detail_resolution
                            null, // status_number
                        ];
                }
            );
        }
    }

    /**
     * Импортировать Словакию из Excel. 3 сек
     * @link http://confluence.welltime.ru/pages/viewpage.action?pageId=8356629
     */
    public function actionSlovakia()
    {
        $this->import('importSlovakiaCallback', Country::PREFIX_SLOVAKIA);
    }

    /**
     * Импортировать Словакию из Excel. Callback
     * @link http://confluence.welltime.ru/pages/viewpage.action?pageId=8356629
     */
    protected function importSlovakiaCallback()
    {
        $this->importFromExcel(
            'https://docs.google.com/uc?export=download&id=' . self::FILE_ID_SLOVAKIA,
            function ($row) {
                /**
                 * 0 - number_from
                 * 1 - number_to
                 * 2 - кол-во номеров
                 * 3 - оператор
                 * 4 - дата решения
                 * 5 - номер решения
                 */

                $numberFrom = str_replace(' ', '', $row[0]); // number_from
                if (!is_numeric($numberFrom)) {
                    throw new InvalidParamException('Ошибочный number_from ' . $numberFrom);
                }

                $numberTo = str_replace(' ', '', $row[1]); // number_to
                if (!is_numeric($numberTo)) {
                    throw new InvalidParamException('Ошибочный number_to ' . $numberTo);
                }

                $dateResolution = $row[4];
                if ($dateResolution) {
                    $dateResolutionDateTime = \DateTimeImmutable::createFromFormat('m-d-y', $dateResolution);
                    if ($dateResolutionDateTime) {
                        $dateResolution = $dateResolutionDateTime->format(DateTimeZoneHelper::DATE_FORMAT);
                    } else {
                        echo 'Ошибочный date_resolution ' . $dateResolution . PHP_EOL;
                    }
                }
                return
                    [
                        null, // ndc
                        $numberFrom, // number_from
                        $numberTo, // number_to
                        null, // ndc_type_id
                        $row[3], // operator_source
                        null, // region_source
                        Country::PREFIX_SLOVAKIA . $numberFrom, // full_number_from
                        Country::PREFIX_SLOVAKIA . $numberTo, // full_number_to
                        $dateResolution, // date_resolution
                        $row[5], // detail_resolution
                        null, // status_number
                    ];
            },
            $excelFormat = self::EXCEL2007
        );
    }

    /**
     * Импортировать Венгрию из Excel. 5 сек
     * @link http://confluence.welltime.ru/pages/viewpage.action?pageId=8356629
     */
    public function actionHungary()
    {
        $this->import('importHungaryCallback', Country::PREFIX_HUNGARY);
    }

    /**
     * Импортировать Венгрию из Excel. Callback
     * @link http://confluence.welltime.ru/pages/viewpage.action?pageId=8356629
     */
    protected function importHungaryCallback()
    {
        $this->importCallback(self::FILE_ID_HUNGARY, Country::PREFIX_HUNGARY, self::EXCEL5, 'Y.m.d');
    }

    /**
     * Импортировать Германию из Excel. 10 минут и 3Гб оперативки
     * @link http://confluence.welltime.ru/pages/viewpage.action?pageId=8356629
     */
    public function actionGermany()
    {
        $this->import('importGermanyCallback', Country::PREFIX_GERMANY);
    }

    /**
     * Импортировать Германию из Excel. Callback
     * @link http://confluence.welltime.ru/pages/viewpage.action?pageId=8356629
     */
    protected function importGermanyCallback()
    {
        $this->importCallback(self::FILE_ID_GERMANY, Country::PREFIX_GERMANY, self::EXCEL2007, 'd-m-y');
    }

    /**
     * Импортировать Австрию из Excel. 5 минут и 1.5Гб оперативки
     * @link http://confluence.welltime.ru/pages/viewpage.action?pageId=8356629
     */
    public function actionAustria()
    {
        $this->import('importAustriaCallback', Country::PREFIX_AUSTRIA);
    }

    /**
     * Импортировать Австрию из Excel. Callback
     * @link http://confluence.welltime.ru/pages/viewpage.action?pageId=8356629
     */
    protected function importAustriaCallback()
    {
        $this->importCallback(self::FILE_ID_AUSTRIA, Country::PREFIX_AUSTRIA, self::EXCEL2007, 'd.m.Y');
    }

    /**
     * Импортировать Австрию из Excel
     * @link http://confluence.welltime.ru/pages/viewpage.action?pageId=8356629
     */
    public function actionCzech()
    {
        $this->import('importCzechCallback', Country::PREFIX_CZECH);
    }

    /**
     * Импортировать Австрию из Excel. Callback
     * @link http://confluence.welltime.ru/pages/viewpage.action?pageId=8356629
     */
    protected function importCzechCallback()
    {
        $this->importCallback(self::FILE_ID_CZECH, Country::PREFIX_CZECH, self::EXCEL5, 'd.m.Y');
    }

    /**
     * Импортировать из Excel. Callback
     * @link http://confluence.welltime.ru/pages/viewpage.action?pageId=8356629
     */
    protected function importCallback($fileId, $countryPrefix, $excelFormat = self::EXCEL2007, $dateFormat = 'd-m-y')
    {
        /** @var NdcType[] $ndcTypes */
        $ndcTypes = NdcType::find()
            ->indexBy('name')
            ->all();

        $this->importFromExcel(
            'https://docs.google.com/uc?export=download&id=' . $fileId,
            function ($row) use (&$ndcTypes, $countryPrefix, $dateFormat) {
                /**
                 * 0 - Префикс страны
                 * 1 - NDC
                 * 2 - Тип DNC
                 * 3 - Диапазон с
                 * 4 - Диапазон по
                 * 5 - Регион
                 * 6 - Оператор
                 * 7 - Дата принятия решения о выделении диапазона. Не всегда указано
                 * 8 - Номер решения о выделении диапазона. Не всегда указано
                 * 9 - Статус номера. Не всегда указано
                 */

                $ndc = $row[1];

                $ndcTypeName = $row[2];
                if ($ndcTypeName) {
                    if (!isset($ndcTypes[$ndcTypeName])) {
                        $ndcType = new NdcType;
                        $ndcType->name = $ndcTypeName;
                        if (!$ndcType->save()) {
                            throw new InvalidParamException('Ошибочный ndc_type ' . $ndcTypeName . '. ' . implode('. ', $ndcType->getFirstErrors()));
                        }
                        $ndcTypes[$ndcTypeName] = $ndcType;
                        unset($ndcType);
                    }
                    $ndcTypeId = $ndcTypes[$ndcTypeName]->id;
                } else {
                    $ndcTypeId = null;
                }

                $numberFrom = str_replace(' ', '', $row[3]); // number_from
                if (!$numberFrom) {
                    $numberFrom = $ndc;
                    $ndc = null;
                }
                if (!is_numeric($numberFrom)) {
                    throw new InvalidParamException('Ошибочный number_from (' . $numberFrom . ')');
                }

                $numberTo = str_replace(' ', '', $row[4]); // number_to
                if (!$numberTo) {
                    $numberTo = $numberFrom;
                }
                if (!is_numeric($numberTo)) {
                    throw new InvalidParamException('Ошибочный number_to (' . $numberTo . ')');
                }

                $dateResolution = isset($row[7]) ? $row[7] : null;
                if ($dateResolution) {
                    $dateResolutionDateTime = \DateTimeImmutable::createFromFormat($dateFormat, $dateResolution);
                    if ($dateResolutionDateTime) {
                        $dateResolution = $dateResolutionDateTime->format(DateTimeZoneHelper::DATE_FORMAT);
                    } else {
                        echo 'Ошибочный date_resolution ' . $dateResolution . PHP_EOL;
                    }
                }

                return
                    [
                        $ndc ?: null, // ndc
                        $numberFrom, // number_from
                        $numberTo, // number_to
                        $ndcTypeId, // ndc_type_id
                        $row[6], // operator_source
                        $row[5], // region_source
                        $countryPrefix . $ndc . $numberFrom, // full_number_from
                        $countryPrefix . $ndc . $numberTo, // full_number_to
                        $dateResolution ?: null, // date_resolution
                        isset($row[8]) ? $row[8] : null, // detail_resolution
                        isset($row[9]) ? $row[9] : null, // status_number
                    ];
            },
            $excelFormat
        );
    }

    /**
     * Импортировать из Excel
     * @param string $filePath
     * @param callable $callbackRow param $row, return ['ndc', 'number_from', 'number_to', 'ndc_type_id', 'operator_source', 'region_source', 'full_number_from', 'full_number_to', 'date_resolution', 'detail_resolution', 'status_number']
     * @param string $excelFormat Excel2007 | Excel5
     * @return bool
     * @throws \Exception
     */
    protected function importFromExcel($filePath, $callbackRow, $excelFormat = self::EXCEL2007)
    {
        if (strpos($filePath, 'http') === 0) {
            // Reader требует локальный файл
            // поэтому сначала скачает его
            $filePathNew = Yii::$app->basePath . '/runtime/nnp.xlsx';
            if (!@copy($filePath, $filePathNew)) {
                throw new \Exception('Ошибка скачивания файла ' . $filePath);
            }
            $filePath = $filePathNew;
            unset($filePathNew);
        }


        $reader = \PHPExcel_IOFactory::createReader($excelFormat);
//        $reader->setReadDataOnly(true); // при этом теряется формат даты
        $excel = $reader->load($filePath);
        if (!$excel) {
            return false;
        }

        $excelWorksheet = $excel->getActiveSheet();
        if (!$excelWorksheet) {
            return false;
        }

        $tableName = 'number_range_tmp';
        $insertValues = [];

        foreach ($excelWorksheet->getRowIterator() as $excelRow) {
            $row = [];
            /** @var \PHPExcel_Cell $cell */
            foreach ($excelRow->getCellIterator() as $cell) {
                $cellValue = $cell->getFormattedValue();
                $cellValue = trim($cellValue, "  \t\r\n");
                $row[] = $cellValue;
            }

            try {

                $insertValues[] = $callbackRow($row);

                if (count($insertValues) % 1000 === 0) {
                    echo '. ';
                    $this->db->createCommand()->batchInsert(
                        $tableName,
                        ['ndc', 'number_from', 'number_to', 'ndc_type_id', 'operator_source', 'region_source', 'full_number_from', 'full_number_to', 'date_resolution', 'detail_resolution', 'status_number'],
                        $insertValues
                    )->execute();
                    $insertValues = [];
                }

            } catch (InvalidParamException $e) {
                echo $e->getMessage() . PHP_EOL;
            }
        }

        if (count($insertValues)) {
            echo '. ';
            $this->db->createCommand()->batchInsert(
                $tableName,
                ['ndc', 'number_from', 'number_to', 'ndc_type_id', 'operator_source', 'region_source', 'full_number_from', 'full_number_to', 'date_resolution', 'detail_resolution', 'status_number'],
                $insertValues
            );
        }
    }

    /**
     * Импортировать из txt-файла
     * @param $filePath
     * @param callable $callbackRow param $row, return ['ndc', 'number_from', 'number_to', 'ndc_type_id', 'operator_source', 'region_source', 'full_number_from', 'full_number_to', 'date_resolution', 'detail_resolution', 'status_number']
     */
    protected function importFromTxt($filePath, $callbackRow)
    {
        $handle = fopen($filePath, "r");
        if (!$handle) {
            throw new UnexpectedValueException('Error fopen ' . $filePath);
        }

        $tableName = 'number_range_tmp';
        $insertValues = [];

        fgets($handle, 4096); // заголовок нам не нужен
        while (($buffer = fgets($handle, 4096)) !== false) {
            $buffer = trim($buffer);
            if (!$buffer) {
                continue;
            }
            $buffer = iconv("cp1251", "utf-8//TRANSLIT", $buffer);
            $row = explode(';', $buffer);
            if (count($row) < 6) {
                echo 'Wrong string ' . $buffer;
                continue;
            }

            $insertValues[] = $callbackRow($row);

            if (count($insertValues) % 1000 === 0) {
                echo '. ';
                $this->db->createCommand()->batchInsert(
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
            echo '. ';
            $this->db->createCommand()->batchInsert(
                $tableName,
                ['ndc', 'number_from', 'number_to', 'ndc_type_id', 'operator_source', 'region_source', 'full_number_from', 'full_number_to', 'date_resolution', 'detail_resolution', 'status_number'],
                $insertValues
            );
        }

        echo PHP_EOL;

    }

    /**
     * Импортировать
     * @param string $callbackMethod
     * @param int $countryPrefix
     * @return int
     */
    protected function import($callbackMethod, $countryPrefix)
    {
        $transaction = $this->db->beginTransaction();
        try {

            echo PHP_EOL . 'Импортировать. ' . date(DATE_ATOM) . PHP_EOL;

            $this->preImport();
            $this->$callbackMethod();
            $this->postImport($countryPrefix);

            $transaction->commit();

            echo PHP_EOL . date(DATE_ATOM) . PHP_EOL;
            return Controller::EXIT_CODE_NORMAL;

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('Ошибка импорта');
            Yii::error($e);
            printf('%s %s', $e->getMessage(), $e->getTraceAsString());
            return Controller::EXIT_CODE_ERROR;
        }
    }

    /**
     * Перед импортом
     * Создать временную таблицу для записи в нее всех новых значений
     *
     * @throws \yii\db\Exception
     */
    protected function preImport()
    {
        $sql = <<<SQL
CREATE TEMPORARY TABLE number_range_tmp
(
  ndc integer,
  number_from integer,
  number_to integer,
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
        $this->db->createCommand($sql)->execute();
    }

    /**
     * После импорта
     * Из временной таблицы перенести в постоянную
     *
     * @param string $countryPrefix
     * @throws \yii\db\Exception
     */
    protected function postImport($countryPrefix)
    {
        echo PHP_EOL;

        $tableName = NumberRange::tableName();

        // выключить триггеры, иначе все повиснет
        $this->actionDisableTrigger();

        // всё выключить
        $sql = <<<SQL
    UPDATE {$tableName}
    SET is_active = false, date_stop = now()
    WHERE is_active AND country_prefix = :country_prefix
SQL;
        $affectedRowsBefore = $this->db->createCommand($sql, [':country_prefix' => $countryPrefix])->execute();
        printf("They were: %d\n", $affectedRowsBefore);

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
        $affectedRowsUpdated = $this->db->createCommand($sql)->execute();
        printf("Updated: %d\n", $affectedRowsUpdated);

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
        $this->db->createCommand($sql)->execute();

        // добавить в основную таблицу всё оставшееся из временной
        $sql = <<<SQL
    INSERT INTO
        {$tableName}
    (
        country_prefix,
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
        status_number
    )
    SELECT 
        :country_prefix as country_prefix, 
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
        status_number
    FROM
        number_range_tmp
SQL;
        $affectedRowsAdded = $this->db->createCommand($sql, [':country_prefix' => $countryPrefix])->execute();
        printf("Added: %d\n", $affectedRowsAdded);

        $affectedRowsTotal = $affectedRowsUpdated + $affectedRowsAdded;
        $affectedRowsDelta = $affectedRowsBefore ? $affectedRowsTotal / $affectedRowsBefore : 1;
        printf("Total: %d, %.2f\n", $affectedRowsTotal, $affectedRowsDelta * 100);

        $sql = <<<SQL
DROP TABLE number_range_tmp
SQL;
        $this->db->createCommand($sql)->execute();

        if ($affectedRowsDelta < self::DELTA_MIN) {
            throw new \LogicException('После обновления осталось ' . $affectedRowsDelta . '% исходных данных. Нужно вручную разобраться в причинах');
        }

        // включить триггеры обратно
        $this->actionEnableTrigger();
    }

    /**
     * выключить триггеры, иначе все повиснет
     */
    public function actionDisableTrigger()
    {
//        $tableName = NumberRange::tableName();
//        $sql = "ALTER TABLE {$tableName} DISABLE TRIGGER ALL"; // нет прав

        $sql = "SELECT nnp.disable_trigger('nnp.number_range','notify')";
        $this->db->createCommand($sql)->execute();
    }

    /**
     * включить триггеры и синхронизировать данные по региональным серверам
     */
    public function actionEnableTrigger()
    {
//        $tableName = NumberRange::tableName();
//        $sql = "ALTER TABLE {$tableName} ENABLE TRIGGER ALL"; // нет прав

        $sql = "SELECT nnp.enable_trigger('nnp.number_range','notify')";
        $this->db->createCommand($sql)->execute();

        // синхронизировать данные по региональным серверам
        $sql = "select from event.notify(:table_name, 0, :p_server_id)";
        $activeQuery = Server::find();
        foreach ($activeQuery->each() as $server) {
            $this->db->createCommand($sql, [
                ':table_name' => 'nnp_number_range',
                ':p_server_id' => $server->id,
            ])->execute();
        }
    }

}
