<?php
namespace app\modules\nnp\commands;

use app\classes\Connection;
use app\helpers\DateTimeZoneHelper;
use app\modules\nnp\models\Country;
use app\modules\nnp\models\NdcType;
use app\modules\nnp\models\NumberRange;
use InvalidArgumentException;
use UnexpectedValueException;
use Yii;
use yii\base\InvalidParamException;
use yii\base\Module;
use yii\console\Controller;

/**
 * Импорт из справочников
 *
 * @link http://confluence.welltime.ru/pages/viewpage.action?pageId=8356629
 */
class ImportController extends Controller
{
    // Защита от сбоя обновления. Если после обновления осталось менее 70% исходного - не обновлять
    const DELTA_MIN = 0.7;

    const EXCEL2007 = 'Excel2007';
    const EXCEL5 = 'Excel5';

    const DATE_FORMAT_YMD_DOT = 'Y.m.d';
    const DATE_FORMAT_DMY_DOT = 'd.m.Y';
    const DATE_FORMAT_MDY_HYPHEN = 'm-d-y';
    const DATE_FORMAT_DMY_HYPHEN = 'd-m-y';
    const DATE_FORMAT_MDY_SLASH = 'm/d/Y';

    /**
     * Европа
     *
     * @link http://confluence.welltime.ru/pages/viewpage.action?pageId=10322013
     */
    private $_europe = [
        Country::HUNGARY_CODE => ['0B9ds-UaQbaC7Tm45Q2l2czhrWHM', self::EXCEL5, self::DATE_FORMAT_YMD_DOT],
        Country::SLOVAKIA_CODE => ['0B9ds-UaQbaC7SHJHY0JnNHpRN00', self::EXCEL5, self::DATE_FORMAT_MDY_HYPHEN],
        // Country::AUSTRIA_CODE => ['0B9ds-UaQbaC7UHo2M3VfM3I5d2M', self::EXCEL2007, self::DATE_FORMAT_DMY_DOT], // PHP Fatal error:  Allowed memory size of 4294967296 bytes exhausted (tried to allocate 1073741824 bytes) in /home/httpd/stat.mcn.ru/stat/vendor/phpoffice/phpexcel/Classes/PHPExcel/Worksheet.php on line 1219
        Country::GERMANY_CODE => ['0B9ds-UaQbaC7MDRNLTl5WVN2Y0k', self::EXCEL2007, self::DATE_FORMAT_MDY_SLASH],
        Country::CZECH_CODE => ['0B9ds-UaQbaC7VzNPMzljR2VTMms', self::EXCEL5, self::DATE_FORMAT_DMY_DOT],
        // Country::POLAND_CODE => ['0B96cvSC012ZaXzRtQTExQVVZY2M', self::EXCEL5, self::DATE_FORMAT_MDY_HYPHEN],
        Country::ROMANIA_CODE => ['0B9ds-UaQbaC7Ynpwb1ZhZTFUV3M', self::EXCEL2007, self::DATE_FORMAT_MDY_HYPHEN],
        // Country::BULGARIA_CODE => ['0B96cvSC012ZaS3FGX2gwUndMb3M', self::EXCEL5, self::DATE_FORMAT_MDY_HYPHEN],
        Country::CROATIA_CODE => ['0B9ds-UaQbaC7UERUWmpVeEZ4THM', self::EXCEL5, self::DATE_FORMAT_MDY_HYPHEN],
        Country::SERBIA_CODE => ['0B9ds-UaQbaC7N1p6Zm5sNWhmMWs', self::EXCEL2007, self::DATE_FORMAT_MDY_HYPHEN],
        // Country::BELGIUM_CODE => ['0B7mk5bJgGNORUFV5dU9IUy1WZWM', self::EXCEL5, self::DATE_FORMAT_MDY_HYPHEN],
        // Country::UNITED_KINGDOM_CODE => ['0B96cvSC012ZadGV0czQyZlN1Wms', self::EXCEL5, self::DATE_FORMAT_MDY_HYPHEN],
        // Country::IRELAND_CODE => ['0B96cvSC012ZaT0U4ZXpzek5IVTQ', self::EXCEL5, self::DATE_FORMAT_MDY_HYPHEN],
        // Country::LIECHTENSTEIN_CODE => ['0B7mk5bJgGNORME1LZHMxeHZ1azA', self::EXCEL5, self::DATE_FORMAT_MDY_HYPHEN],
        // Country::LUXEMBOURG_CODE => ['0B7mk5bJgGNORNGIzdDltVFZyOTg', self::EXCEL5, self::DATE_FORMAT_MDY_HYPHEN],
        // Country::MONACO_CODE => ['0B96cvSC012ZaTXRQaHlRZ0J3aWc', self::EXCEL5, self::DATE_FORMAT_MDY_HYPHEN],
        // Country::NETHERLANDS_CODE => ['0B7mk5bJgGNORUVRyT3oxdDRzMFU', self::EXCEL5, self::DATE_FORMAT_MDY_HYPHEN],
        // Country::FRANCE_CODE => ['0B96cvSC012ZaN25aTElCSE5EQUU', self::EXCEL5, self::DATE_FORMAT_MDY_HYPHEN],
        // Country::SWITZERLAND_CODE => ['0B7mk5bJgGNORS2tpNW9meUZDMTA', self::EXCEL5, self::DATE_FORMAT_MDY_HYPHEN],
        // Country::DENMARK_CODE => ['0B0uCc2piA2iSc1ptMUxVT1lTSUk', self::EXCEL5, self::DATE_FORMAT_MDY_HYPHEN],
        // Country::ICELAND_CODE => ['', self::EXCEL5, self::DATE_FORMAT_MDY_HYPHEN],
        // Country::NORWAY_CODE => ['', self::EXCEL5, self::DATE_FORMAT_MDY_HYPHEN],
        // Country::LATVIA_CODE => ['0B96cvSC012ZaYWJVMlBMYVczbzA', self::EXCEL5, self::DATE_FORMAT_MDY_HYPHEN],
        // Country::LITHUANIA_CODE => ['', self::EXCEL5, self::DATE_FORMAT_MDY_HYPHEN],
        // Country::FINLAND_CODE => ['0B96cvSC012ZaN25aTElCSE5EQUU', self::EXCEL5, self::DATE_FORMAT_MDY_HYPHEN],
        // Country::SWEDEN_CODE => ['0B96cvSC012ZaNEpxWjFpaWZDMFE', self::EXCEL5, self::DATE_FORMAT_MDY_HYPHEN],
        // Country::ESTONIA_CODE => ['', self::EXCEL5, self::DATE_FORMAT_MDY_HYPHEN],
        // Country::ALBANIA_CODE => ['', self::EXCEL5, self::DATE_FORMAT_MDY_HYPHEN],
        // Country::ANDORRA_CODE => ['0B96cvSC012ZaQnotZGNVQzJUbXc', self::EXCEL5, self::DATE_FORMAT_MDY_HYPHEN],
        // Country::BOSNIA_CODE => ['', self::EXCEL5, self::DATE_FORMAT_MDY_HYPHEN],
        // Country::VATICAN_CODE => ['', self::EXCEL5, self::DATE_FORMAT_MDY_HYPHEN],
        // Country::GREECE_CODE => ['0B96cvSC012ZaMEFpVEFrU2I2QkE', self::EXCEL5, self::DATE_FORMAT_MDY_HYPHEN],
        // Country::SPAIN_CODE => ['0BxjkzgnzZC8EUXdja0lQRV9HbUU', self::EXCEL5, self::DATE_FORMAT_MDY_HYPHEN],
        // Country::ITALY_CODE => ['0B7mk5bJgGNORRndxczdjSlJJbWs', self::EXCEL5, self::DATE_FORMAT_MDY_HYPHEN],
        // Country::MACEDONIA_CODE => ['0B96cvSC012ZaTzR0Y0NFenNIcWM', self::EXCEL5, self::DATE_FORMAT_MDY_HYPHEN],
        // Country::MALTA_CODE => ['', self::EXCEL5, self::DATE_FORMAT_MDY_HYPHEN],
        // Country::PORTUGAL_CODE => ['0B7mk5bJgGNORdnFmLUxLRnd6V1E', self::EXCEL5, self::DATE_FORMAT_MDY_HYPHEN],
        // Country::SAN_MARINO_CODE => ['0B96cvSC012ZaUXhIQmx5bnhTWFE', self::EXCEL5, self::DATE_FORMAT_MDY_HYPHEN],
        // Country::SLOVENIA_CODE => ['0B96cvSC012ZaWnc3bmFEeWU4WFU', self::EXCEL5, self::DATE_FORMAT_MDY_HYPHEN],
        // Country::MONTENEGRO_CODE => ['0B96cvSC012ZaYk1JUjJyeUdQY3c', self::EXCEL5, self::DATE_FORMAT_MDY_HYPHEN],
        // Country::CYPRUS_CODE => ['', self::EXCEL5, self::DATE_FORMAT_MDY_HYPHEN],
    ];

    /**
     * СНГ
     * Commonwealth of Independent States (CIS)
     *
     * @link http://confluence.welltime.ru/pages/viewpage.action?pageId=10321972
     */
    private $_cis = [
        Country::AZERBAIJAN_CODE => ['0B9ds-UaQbaC7aTBIdzJaeHB5X0k', self::EXCEL2007, ''],
        Country::ARMENIA_CODE => ['0B9ds-UaQbaC7azFCZHV4RC15Znc', self::EXCEL2007, self::DATE_FORMAT_DMY_DOT],
        Country::GEORGIA_CODE => ['0B9ds-UaQbaC7SGpkcmhtWWhmejA', self::EXCEL2007, self::DATE_FORMAT_MDY_HYPHEN],
        Country::BELARUS_CODE => ['0B9ds-UaQbaC7VHY4NkxLTkRxNzQ', self::EXCEL2007, self::DATE_FORMAT_MDY_HYPHEN],
        Country::KAZAKHSTAN_CODE => ['0B9ds-UaQbaC7Nm51REtxWHRxaW8', self::EXCEL2007, self::DATE_FORMAT_DMY_DOT],
        Country::KYRGYZSTAN_CODE => ['0B9ds-UaQbaC7VnBYelFQRHo1bWM', self::EXCEL2007, self::DATE_FORMAT_DMY_DOT],
        Country::MOLDOVA_CODE => ['0B9ds-UaQbaC7NW5MODRDVG5YeEE', self::EXCEL2007, self::DATE_FORMAT_MDY_HYPHEN],
        Country::MONGOLIA_CODE => ['0B9ds-UaQbaC7RWM5WDd5V3NENms', self::EXCEL2007, self::DATE_FORMAT_DMY_DOT],
        Country::TAJIKISTAN_CODE => ['0B9ds-UaQbaC7Z0hwem04VV82TE0', self::EXCEL2007, self::DATE_FORMAT_MDY_HYPHEN],
        Country::TURKMENISTAN_CODE => ['0B9ds-UaQbaC7d21DU1VzajgyM2s', self::EXCEL2007, self::DATE_FORMAT_MDY_HYPHEN],
        Country::UZBEKISTAN_CODE => ['0B9ds-UaQbaC7MTVVbExlcjh0YkU', self::EXCEL2007, self::DATE_FORMAT_MDY_HYPHEN],
        Country::UKRAINE_CODE => ['0B9ds-UaQbaC7ajdiYXdfamNnV1k', self::EXCEL2007, self::DATE_FORMAT_DMY_DOT],
        // Приднестровье
    ];

    /**
     * Азия
     *
     * @link http://confluence.welltime.ru/pages/viewpage.action?pageId=10322017
     */

    /**
     * Африка
     *
     * @link http://confluence.welltime.ru/pages/viewpage.action?pageId=10322019
     */

    /**
     * Америка
     *
     * @link http://confluence.welltime.ru/pages/viewpage.action?pageId=10322021
     */

    /** @var Connection */
    private $_db = null;

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
     * @throws \yii\db\Exception
     */
    public function actionRus()
    {
        $this->_import(
            function () {
                /**
                 * Ссылки на файлы для скачивания
                 * [url => ndc_type_id]
                 *
                 * @link http://www.rossvyaz.ru/activity/num_resurs/registerNum/
                 */
                $rusUrls = [
                    'http://www.rossvyaz.ru/docs/articles/Kody_ABC-3kh.csv' => NdcType::ID_GEOGRAPHIC,
                    'http://www.rossvyaz.ru/docs/articles/Kody_ABC-4kh.csv' => NdcType::ID_GEOGRAPHIC,
                    'http://www.rossvyaz.ru/docs/articles/Kody_ABC-8kh.csv' => NdcType::ID_GEOGRAPHIC,
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
                                    ($row[0] == 800) ? NdcType::ID_FREEPHONE : $ndcTypeId, // ndc_type_id
                                    trim($row[4]), // operator_source
                                    trim($row[5]), // region_source
                                    Country::RUSSIA_PREFIX . trim($row[0]) . trim($row[1]), // full_number_from
                                    Country::RUSSIA_PREFIX . trim($row[0]) . trim($row[2]), // full_number_to
                                    null, // date_resolution
                                    null, // detail_resolution
                                    null, // status_number
                                ];
                        }
                    );
                }

            },
            Country::RUSSIA_CODE);
    }

    /**
     * Импортировать страны Европу из Excel. Сначала надо disable-trigger, потом enable-trigger
     *
     * @param string $countryCode Если указан - только эту страну. Иначе - все
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function actionEurope($countryCode = '')
    {
        $this->_importCountries($this->_europe, $countryCode);
    }

    /**
     * Импортировать страны СНГ из Excel. Сначала надо disable-trigger, потом enable-trigger
     *
     * @param string $countryCode Если указан - только эту страну. Иначе - все
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function actionCis($countryCode = '')
    {
        $this->_importCountries($this->_cis, $countryCode);
    }

    /**
     * Импортировать страны из Excel
     *
     * @param array $countries страны
     * @param string $countryCode Если указан - только эту страну. Иначе - все
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    private function _importCountries($countries, $countryCode)
    {
        if ($countryCode) {
            if (!isset($countries[$countryCode])) {
                throw new InvalidArgumentException('Неизвестный код страны');
            }

            $countries = [$countryCode => $countries[$countryCode]];
        }

        foreach ($countries as $countryCodeTmp => $fileInfo) {
            $this->_import(
                function () use ($fileInfo) {
                    $this->_importCallback($fileInfo[0], $fileInfo[1], $fileInfo[2]);
                },
                $countryCodeTmp
            );
        }
    }

    /**
     * Импортировать из Excel. Callback
     *
     * @param string $fileId
     * @param string $excelFormat
     * @param string $dateFormat
     * @throws \Exception
     */
    private function _importCallback($fileId, $excelFormat = self::EXCEL2007, $dateFormat = 'd-m-y')
    {
        $this->_importFromExcel(
            'https://docs.google.com/uc?export=download&id=' . $fileId,
            function ($row) use ($dateFormat) {
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

                // заполнить недостающие столбцы
                while (count($row) < 11) {
                    $row[] = null;
                }

                list($countryPrefix, $ndc, $ndcTypeOriginal, $ndcTypeId, $numberFrom, $numberTo, $regionSource, $operatorSource, $dateResolution, $detailResolution, $statusNumber) = $row;

                if (!$countryPrefix) {
                    return null;
                }

                if ($operatorSource && ord($operatorSource[0]) == 171) {
                    // какой-то баг Excel с символом «
                    $operatorSource[0] = '';
                }

                $ndc = (int)$ndc;

                $ndcTypeId = (int)$ndcTypeId;

                $numberFrom = str_replace(' ', '', $numberFrom); // number_from
                if (!$numberFrom) {
                    $numberFrom = $ndc;
                    $ndc = null;
                }

                if (!is_numeric($numberFrom)) {
                    throw new InvalidParamException('Ошибочный number_from (' . $numberFrom . ')');
                }

                $numberTo = str_replace(' ', '', $numberTo); // number_to
                if (!$numberTo) {
                    $numberTo = $numberFrom;
                }

                if (!is_numeric($numberTo)) {
                    throw new InvalidParamException('Ошибочный number_to (' . $numberTo . ')');
                }

                if ($dateResolution) {
                    $dateResolutionDateTime = \DateTimeImmutable::createFromFormat($dateFormat, $dateResolution);
                    if ($dateResolutionDateTime) {
                        $dateResolution = $dateResolutionDateTime->format(DateTimeZoneHelper::DATE_FORMAT);
                    } else {
                        echo 'Ошибочный date_resolution ' . $dateResolution . ' (' . $dateFormat . ')' . PHP_EOL;
                    }
                }

                return
                    [
                        $ndc ?: null, // ndc
                        $numberFrom, // number_from
                        $numberTo, // number_to
                        $ndcTypeId ?: null, // ndc_type_id
                        $operatorSource, // operator_source
                        $regionSource, // region_source
                        $countryPrefix . $ndc . $numberFrom, // full_number_from
                        $countryPrefix . $ndc . $numberTo, // full_number_to
                        $dateResolution ?: null, // date_resolution
                        $detailResolution ?: null, // detail_resolution
                        $statusNumber ?: null, // status_number
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

                $insertValuesTmp = $callbackRow($row);
                if (!$insertValuesTmp) {
                    continue;
                }

                $insertValues[] = $insertValuesTmp;

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
            )->execute();
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
            echo '.. ';
            $this->_db->createCommand()->batchInsert(
                $tableName,
                ['ndc', 'number_from', 'number_to', 'ndc_type_id', 'operator_source', 'region_source', 'full_number_from', 'full_number_to', 'date_resolution', 'detail_resolution', 'status_number'],
                $insertValues
            )->execute();
        }

        echo PHP_EOL;

    }

    /**
     * Импортировать
     *
     * @param \Closure $callbackMethod
     * @param int $countryCode
     * @return int
     */
    private function _import($callbackMethod, $countryCode)
    {
        $transaction = $this->_db->beginTransaction();
        try {

            echo PHP_EOL . 'Импортировать ' . $countryCode . '. ' . date(DATE_ATOM) . PHP_EOL;

            $this->_preImport();
            $callbackMethod();
            $this->_postImport($countryCode);

            $transaction->commit();

            echo PHP_EOL . date(DATE_ATOM) . PHP_EOL;
            return Controller::EXIT_CODE_NORMAL;

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('Ошибка импорта');
            Yii::error($e);
            printf(' % s % s', $e->getMessage(), $e->getTraceAsString());
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

        // выключить всё, кроме больших диапазонов по всей стране
        $sql = <<<SQL
    UPDATE {$tableName}
    SET is_active = false, date_stop = now()
    WHERE is_active 
        AND country_code = :country_code 
        AND (ndc_type_id IS NOT NULL OR operator_id IS NOT NULL OR region_id IS NOT NULL OR ndc IS NOT NULL)
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
        printf("Added: %d\n", $affectedRowsAdded);

        $affectedRowsTotal = $affectedRowsUpdated + $affectedRowsAdded;
        $affectedRowsDelta = $affectedRowsBefore ? $affectedRowsTotal / $affectedRowsBefore : 1;
        printf("Total: %d, %.2f\n", $affectedRowsTotal, $affectedRowsDelta * 100);

        $sql = <<<SQL
DROP TABLE number_range_tmp
SQL;
        $this->_db->createCommand($sql)->execute();

        if ($affectedRowsDelta < self::DELTA_MIN) {
            throw new \LogicException('После обновления осталось ' . $affectedRowsDelta . ' % исходных данных . Нужно вручную разобраться в причинах');
        }
    }

    /**
     * Выключить триггеры
     *
     * @throws \yii\db\Exception
     */
    public function actionDisableTrigger()
    {
        NumberRange::disableTrigger();
    }

    /**
     * Включить триггеры и синхронизировать данные по региональным серверам
     *
     * @throws \yii\db\Exception
     */
    public function actionEnableTrigger()
    {
        NumberRange::enableTrigger();
    }

}
