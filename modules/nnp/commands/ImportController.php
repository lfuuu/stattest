<?php
namespace app\modules\nnp\commands;

use app\classes\Connection;
use app\helpers\DateTimeZoneHelper;
use app\models\billing\InstanceSettings;
use app\modules\nnp\models\Country;
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
    const FILE_ID_HUNGARY = '0B9ds-UaQbaC7Tm45Q2l2czhrWHM';
    const FILE_ID_SLOVAKIA = '0B9ds-UaQbaC7SHJHY0JnNHpRN00';
    const FILE_ID_AUSTRIA = '0B9ds-UaQbaC7UHo2M3VfM3I5d2M';
    const FILE_ID_GERMANY = '0B9ds-UaQbaC7MDRNLTl5WVN2Y0k';
    const FILE_ID_CZECH = '0B9ds-UaQbaC7VzNPMzljR2VTMms';
    const FILE_ID_ROMANIA = '0B9ds-UaQbaC7Ynpwb1ZhZTFUV3M';
    const FILE_ID_CROATIA = '0B9ds-UaQbaC7UERUWmpVeEZ4THM';
    const FILE_ID_SERBIA = '0B9ds-UaQbaC7N1p6Zm5sNWhmMWs';

    /** @var Connection */
    private $_db = null;

    private $_triggerTables = [
        // 'nnp.account_tariff_light',
        'nnp.country',
        'nnp.destination',
        'nnp.number_range',
        'nnp.number_range_prefix',
        'nnp.operator',
        // 'nnp.package',
        // 'nnp.package_minute',
        // 'nnp.package_price',
        // 'nnp.package_pricelist',
        'nnp.prefix',
        'nnp.prefix_destination',
        'nnp.region',
    ];

    /**
     * @param string $id the ID of this controller.
     * @param Module $module the module that this controller belongs to.
     * @param array $config name-value pairs that will be used to initialize the object properties.
     */
    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->_db = Yii::$app->dbPgNnp;
    }

    /**
     * Импортировать Россию из Россвязи. 2 минуты. Сначала надо disable-trigger, потом enable-trigger
     *
     * @link http://www.rossvyaz.ru/activity/num_resurs/registerNum/
     */
    public function actionRus()
    {
        $this->_import('_importRusCallback', Country::RUSSIA);
    }

    /**
     * Импортировать Россию из Россвязи. Callback
     *
     * @link http://www.rossvyaz.ru/activity/num_resurs/registerNum/
     * @throws \yii\db\Exception
     */
    private function _importRusCallback()
    {
        /**
         * Ссылки на файлы для скачивания
         * [url => ndc_type_id]
         *
         * @link http://www.rossvyaz.ru/activity/num_resurs/registerNum/
         */
        $rusUrls = [
            'http://www.rossvyaz.ru/docs/articles/Kody_ABC-3kh.csv' => NdcType::ID_GEOGRAPHIC,
            'http://www.rossvyaz.ru/docs/articles/Kody_ABC-4kh.csv' => NdcType::ID_GEOGRAPHIC,
            'http://www.rossvyaz.ru/docs/articles/Kody_ABC-8kh.csv' => NdcType::ID_FREEPHONE,
            'http://www.rossvyaz.ru/docs/articles/Kody_DEF-9kh.csv' => NdcType::ID_MOBILE,
        ];
        foreach ($rusUrls as $url => $ndcTypeId) {
            $this->_importFromTxt(
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
                            Country::RUSSIA . trim($row[0]) . trim($row[1]), // full_number_from
                            Country::RUSSIA . trim($row[0]) . trim($row[2]), // full_number_to
                            null, // date_resolution
                            null, // detail_resolution
                            null, // status_number
                        ];
                }
            );
        }
    }

    /**
     * Импортировать Словакию из Excel. 3 сек. Сначала надо disable-trigger, потом enable-trigger
     *
     * @link http://confluence.welltime.ru/pages/viewpage.action?pageId=8356629
     */
    public function actionSlovakia()
    {
        $this->_import('_importSlovakiaCallback', Country::SLOVAKIA);
    }

    /**
     * Импортировать Словакию из Excel. Callback
     *
     * @link http://confluence.welltime.ru/pages/viewpage.action?pageId=8356629
     */
    private function _importSlovakiaCallback()
    {
        $this->_importCallback(self::FILE_ID_SLOVAKIA, Country::SLOVAKIA, self::EXCEL5, 'm-d-y');
    }

    /**
     * Импортировать Венгрию из Excel. 5 сек. Сначала надо disable-trigger, потом enable-trigger
     *
     * @link http://confluence.welltime.ru/pages/viewpage.action?pageId=8356629
     */
    public function actionHungary()
    {
        $this->_import('_importHungaryCallback', Country::HUNGARY);
    }

    /**
     * Импортировать Венгрию из Excel. Callback
     *
     * @link http://confluence.welltime.ru/pages/viewpage.action?pageId=8356629
     */
    private function _importHungaryCallback()
    {
        $this->_importCallback(self::FILE_ID_HUNGARY, Country::HUNGARY, self::EXCEL5, 'Y.m.d');
    }

    /**
     * Импортировать Германию из Excel. 10 минут и 3Гб оперативки. Сначала надо disable-trigger, потом enable-trigger
     *
     * @link http://confluence.welltime.ru/pages/viewpage.action?pageId=8356629
     */
    public function actionGermany()
    {
        $this->_import('_importGermanyCallback', Country::GERMANY);
    }

    /**
     * Импортировать Германию из Excel. Callback
     *
     * @link http://confluence.welltime.ru/pages/viewpage.action?pageId=8356629
     */
    private function _importGermanyCallback()
    {
        $this->_importCallback(self::FILE_ID_GERMANY, Country::GERMANY, self::EXCEL2007, 'd-m-y');
    }

    /**
     * Импортировать Австрию из Excel. 5 минут и 1.5Гб оперативки. Сначала надо disable-trigger, потом enable-trigger
     *
     * @link http://confluence.welltime.ru/pages/viewpage.action?pageId=8356629
     */
    public function actionAustria()
    {
        $this->_import('_importAustriaCallback', Country::AUSTRIA);
    }

    /**
     * Импортировать Австрию из Excel. Callback
     *
     * @link http://confluence.welltime.ru/pages/viewpage.action?pageId=8356629
     */
    private function _importAustriaCallback()
    {
        $this->_importCallback(self::FILE_ID_AUSTRIA, Country::AUSTRIA, self::EXCEL2007, 'd.m.Y');
    }

    /**
     * Импортировать Чехию из Excel. Сначала надо disable-trigger, потом enable-trigger
     *
     * @link http://confluence.welltime.ru/pages/viewpage.action?pageId=8356629
     */
    public function actionCzech()
    {
        $this->_import('_importCzechCallback', Country::CZECH);
    }

    /**
     * Импортировать Чехию из Excel. Callback
     *
     * @link http://confluence.welltime.ru/pages/viewpage.action?pageId=8356629
     */
    private function _importCzechCallback()
    {
        $this->_importCallback(self::FILE_ID_CZECH, Country::CZECH, self::EXCEL5, 'd.m.Y');
    }

    /**
     * Импортировать Румынию из Excel. Сначала надо disable-trigger, потом enable-trigger
     *
     * @link http://confluence.welltime.ru/pages/viewpage.action?pageId=8356629
     */
    public function actionRomania()
    {
        $this->_import('_importRomaniaCallback', Country::ROMANIA);
    }

    /**
     * Импортировать Румынию из Excel. Callback
     *
     * @link http://confluence.welltime.ru/pages/viewpage.action?pageId=8356629
     */
    private function _importRomaniaCallback()
    {
        $this->_importCallback(self::FILE_ID_ROMANIA, Country::ROMANIA, self::EXCEL2007, 'm-d-y');
    }

    /**
     * Импортировать Хорватию из Excel. Сначала надо disable-trigger, потом enable-trigger
     *
     * @link http://confluence.welltime.ru/pages/viewpage.action?pageId=8356629
     */
    public function actionCroatia()
    {
        $this->_import('_importCroatiaCallback', Country::CROATIA);
    }

    /**
     * Импортировать Хорватию из Excel. Callback
     *
     * @link http://confluence.welltime.ru/pages/viewpage.action?pageId=8356629
     */
    private function _importCroatiaCallback()
    {
        $this->_importCallback(self::FILE_ID_CROATIA, Country::CROATIA, self::EXCEL5, 'm-d-y');
    }

    /**
     * Импортировать Сербию из Excel. Сначала надо disable-trigger, потом enable-trigger
     *
     * @link http://confluence.welltime.ru/pages/viewpage.action?pageId=8356629
     */
    public function actionSerbia()
    {
        $this->_import('_importSerbiaCallback', Country::SERBIA);
    }

    /**
     * Импортировать Сербию из Excel. Callback
     *
     * @link http://confluence.welltime.ru/pages/viewpage.action?pageId=8356629
     */
    private function _importSerbiaCallback()
    {
        $this->_importCallback(self::FILE_ID_SERBIA, Country::SERBIA, self::EXCEL2007, 'm-d-y');
    }

    /**
     * Импортировать из Excel. Callback
     *
     * @param string $fileId
     * @param int $countryCode
     * @param string $excelFormat
     * @param string $dateFormat
     * @link http://confluence.welltime.ru/pages/viewpage.action?pageId=8356629
     * @throws \Exception
     */
    private function _importCallback($fileId, $countryCode, $excelFormat = self::EXCEL2007, $dateFormat = 'd-m-y')
    {
        /** @var NdcType[] $ndcTypes */
        $ndcTypes = NdcType::find()
            ->indexBy('name')
            ->all();

        $this->_importFromExcel(
            'https://docs.google.com/uc?export=download&id=' . $fileId,
            function ($row) use (&$ndcTypes, $countryCode, $dateFormat) {
                /**
                 * 0 - Префикс страны
                 * 1 - NDC
                 * 2 - Исходный тип NDC текстом
                 * 3 - Нормализованный тип NDC числом
                 * 4 - Диапазон с
                 * 5 - Диапазон по
                 * 6 - Регион
                 * 7 - Оператор
                 * 8 - Дата принятия решения о выделении диапазона. Не всегда указано
                 * 9 - Номер решения о выделении диапазона. Не всегда указано
                 * 10 - Статус номера. Не всегда указано
                 */

                $ndc = $row[1];

                $ndcTypeId = $row[3];

                $numberFrom = str_replace(' ', '', $row[4]); // number_from
                if (!$numberFrom) {
                    $numberFrom = $ndc;
                    $ndc = null;
                }

                if (!is_numeric($numberFrom)) {
                    throw new InvalidParamException('Ошибочный number_from (' . $numberFrom . ')');
                }

                $numberTo = str_replace(' ', '', $row[5]); // number_to
                if (!$numberTo) {
                    $numberTo = $numberFrom;
                }

                if (!is_numeric($numberTo)) {
                    throw new InvalidParamException('Ошибочный number_to (' . $numberTo . ')');
                }

                $dateResolution = isset($row[8]) ? $row[8] : null;
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
                        $row[7], // operator_source
                        $row[6], // region_source
                        $countryCode . $ndc . $numberFrom, // full_number_from
                        $countryCode . $ndc . $numberTo, // full_number_to
                        $dateResolution ?: null, // date_resolution
                        isset($row[9]) ? $row[9] : null, // detail_resolution
                        isset($row[10]) ? $row[10] : null, // status_number
                    ];
            },
            $excelFormat
        );
    }

    /**
     * Импортировать из Excel
     *
     * @param string $filePath
     * @param callable $callbackRow param $row, return ['ndc', 'number_from', 'number_to', 'ndc_type_id', 'operator_source', 'region_source', 'full_number_from', 'full_number_to', 'date_resolution', 'detail_resolution', 'status_number']
     * @param string $excelFormat Excel2007 | Excel5
     * @return bool
     * @throws \Exception
     */
    private function _importFromExcel($filePath, $callbackRow, $excelFormat = self::EXCEL2007)
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
        // $reader->setReadDataOnly(true); // при этом теряется формат даты
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
                    $this->_db->createCommand()->batchInsert(
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
            $this->_db->createCommand()->batchInsert(
                $tableName,
                ['ndc', 'number_from', 'number_to', 'ndc_type_id', 'operator_source', 'region_source', 'full_number_from', 'full_number_to', 'date_resolution', 'detail_resolution', 'status_number'],
                $insertValues
            );
        }
    }

    /**
     * Импортировать из txt-файла
     *
     * @param string $filePath
     * @param callable $callbackRow param $row, return ['ndc', 'number_from', 'number_to', 'ndc_type_id', 'operator_source', 'region_source', 'full_number_from', 'full_number_to', 'date_resolution', 'detail_resolution', 'status_number']
     * @throws \yii\db\Exception
     */
    private function _importFromTxt($filePath, $callbackRow)
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
            echo '. ';
            $this->_db->createCommand()->batchInsert(
                $tableName,
                ['ndc', 'number_from', 'number_to', 'ndc_type_id', 'operator_source', 'region_source', 'full_number_from', 'full_number_to', 'date_resolution', 'detail_resolution', 'status_number'],
                $insertValues
            );
        }

        echo PHP_EOL;

    }

    /**
     * Импортировать
     *
     * @param string $callbackMethod
     * @param int $countryCode
     * @return int
     */
    private function _import($callbackMethod, $countryCode)
    {
        $transaction = $this->_db->beginTransaction();
        try {

            echo PHP_EOL . 'Импортировать. ' . date(DATE_ATOM) . PHP_EOL;

            $this->_preImport();
            $this->$callbackMethod();
            $this->_postImport($countryCode);

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
    private function _preImport()
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
        $this->_db->createCommand($sql)->execute();
    }

    /**
     * После импорта
     * Из временной таблицы перенести в постоянную
     *
     * @param string $countryCode
     * @throws \yii\db\Exception
     */
    private function _postImport($countryCode)
    {
        echo PHP_EOL;

        $tableName = NumberRange::tableName();

        // всё выключить
        $sql = <<<SQL
    UPDATE {$tableName}
    SET is_active = false, date_stop = now()
    WHERE is_active AND country_code = :country_code
SQL;
        $affectedRowsBefore = $this->_db->createCommand($sql, [':country_code' => $countryCode])->execute();
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
        $affectedRowsUpdated = $this->_db->createCommand($sql)->execute();
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
        status_number
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
        status_number
    FROM
        number_range_tmp
SQL;
        $affectedRowsAdded = $this->_db->createCommand($sql, [':country_code' => $countryCode])->execute();
        printf("Added: %d\n", $affectedRowsAdded);

        $affectedRowsTotal = $affectedRowsUpdated + $affectedRowsAdded;
        $affectedRowsDelta = $affectedRowsBefore ? $affectedRowsTotal / $affectedRowsBefore : 1;
        printf("Total: %d, %.2f\n", $affectedRowsTotal, $affectedRowsDelta * 100);

        $sql = <<<SQL
DROP TABLE number_range_tmp
SQL;
        $this->_db->createCommand($sql)->execute();

        if ($affectedRowsDelta < self::DELTA_MIN) {
            throw new \LogicException('После обновления осталось ' . $affectedRowsDelta . '% исходных данных. Нужно вручную разобраться в причинах');
        }
    }

    /**
     * Выключить триггеры
     *
     * @throws \yii\db\Exception
     */
    public function actionDisableTrigger()
    {
        foreach ($this->_triggerTables as $triggerTable) {
            $sql = sprintf("SELECT nnp.disable_trigger('%s','notify')", $triggerTable);
            $this->_db
                ->createCommand($sql)
                ->execute();
        }
    }

    /**
     * Включить триггеры и синхронизировать данные по региональным серверам
     *
     * @throws \yii\db\Exception
     */
    public function actionEnableTrigger()
    {
        foreach ($this->_triggerTables as $triggerTable) {
            $sql = sprintf("SELECT nnp.enable_trigger('%s','notify')", $triggerTable);
            $this->_db
                ->createCommand($sql)
                ->execute();
        }

        // синхронизировать данные по региональным серверам
        $sql = "select from event.notify_nnp_all(:p_server_id)";
        $activeQuery = InstanceSettings::find()
            ->where(['active' => true]);
        foreach ($activeQuery->each() as $instanceSettings) {
            $this->_db->createCommand($sql, [
                ':p_server_id' => $instanceSettings->id,
            ])->execute();
        }
    }

}
