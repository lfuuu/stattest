<?php

namespace app\modules\nnp\commands;

use app\classes\helpers\CalculateGrowthRate;
use app\classes\helpers\DependecyHelper;
use app\classes\helpers\file\SortCsvFileHelper;
use app\modules\nnp\classes\FtpSsh2Downloader;
use app\modules\nnp\classes\RouteMncDownloader;
use app\modules\nnp\models\Country;
use app\modules\nnp\models\NdcType;
use app\modules\nnp\models\Number;
use app\modules\nnp\models\NumberRange;
use app\modules\nnp\models\NumberRangePrefix;
use yii\base\InvalidConfigException;
use yii\console\ExitCode;
use yii\db\Expression;
use yii\web\NotFoundHttpException;


class PortedFromUvrController extends PortedController
{
    const allowedPrefix = '79';

    protected function readData()
    {
        // blank
    }

    /**
     * Step 1. Filter the NUM file, leaving only mobile ones (maybe skipped)
     *
     * @param $fileName
     * @return void
     * @throws \Exception
     */
    public function actionStep1($fileName = null)
    {

        echo PHP_EOL . date('r') . ': Start porting numbers';
        $filePath = \Yii::getAlias('@runtime/' . $fileName);

        try {
            $this->checkInFileName($filePath);

            $zip = new \ZipArchive();
            if ($zip->open($filePath) !== true) {
                throw new \InvalidArgumentException('Не удалось открыть ZIP архив');
            }

            $csvFilename = $zip->getNameIndex(0);
            if (pathinfo($csvFilename, PATHINFO_EXTENSION) !== 'csv') {
                throw new \InvalidArgumentException('В архиве нет CSV файла');
            }


            $outputFilename = \Yii::getAlias('@runtime/' . 'MOB_' . $csvFilename);

            $inputStream = $zip->getStream($csvFilename);
            $outputFile = fopen($outputFilename, 'w');
            if (!$inputStream || !$outputFile) {
                throw new \RuntimeException('Ошибка открытия потоков');
            }

            echo PHP_EOL;

            // Потоковая обработка данных
            $counter = 0;
            $counterAll = 0;
            while (($row = fgetcsv($inputStream)) !== false) {
                $counterAll++;

                if (($counterAll % 1000000) == 0) {
                    echo "\r" . sprintf('%4s', ($counterAll / 1000000) . 'M');
                }

                if (empty($row[0]) || strpos($row[0], self::allowedPrefix) !== 0) {
                    continue;
                }

                fputcsv($outputFile, $row);
                $counter++;
            }

            echo ' ' . __LINE__;
            // Закрываем ресурсы
            fclose($inputStream);
            fclose($outputFile);
            $zip->close();

            echo "Готово! Найдено $counter записей. Результат в: $outputFilename";


        } catch (\Exception $e) {
            \Yii::error($e);

            echo PHP_EOL;
            throw $e;
        }

        echo PHP_EOL . date('r') . ': Stop porting numbers';
        echo PHP_EOL;

    }

    private function checkInFileName($fileName, $checkExt = 'zip')
    {
        if (!$fileName) {
            throw new \InvalidArgumentException('Файл не задан');
        }

        if (!file_exists($fileName)) {
            throw new \InvalidArgumentException('Файл не найден');
        }

        if (!is_readable($fileName)) {
            throw new \InvalidArgumentException('Файл не возможно прочитать');
        }

        if ($checkExt !== null && pathinfo($fileName, PATHINFO_EXTENSION) !== $checkExt) {
            throw new \InvalidArgumentException('Файл не ' . strtoupper($checkExt) . '-файл');
        }
    }

    public function actionGroup($fileName = null)
    {
        echo PHP_EOL . date('r') . ': Start step 2';

        $inFilePath = \Yii::getAlias('@runtime/' . $fileName);
        $outFilePath = \Yii::getAlias('@runtime/' . 'GROUPED_' . $fileName);

        $this->checkInFileName($inFilePath, 'csv');

        $inputFile = fopen($inFilePath, 'r');
        $outputFile = fopen($outFilePath, 'w');

        $collector = [];     // Массив для хранения данных [хеш => строка]
        $counter = 0;   // Счётчик обработанных строк
        $counterAll = 0;   // Счётчик обработанных строк

        $start = null;
        $end = null;
        $row = null;
        $kk = null;

        echo PHP_EOL;

        while (($row = fgets($inputFile)) !== false) {
            $counterAll++;
            if (($counterAll % 1000000) == 0) {
                echo "\rcountAll: " . ($counterAll / 1000000) . 'M|count: ' . $counter;
            }

            if ($counterAll >= 10000000) {
                break;
            }

            $row = trim($row);

            if (!$row) continue; // Пропуск пустых

            $pos = strpos($row, ';');
            $num = substr($row, 0, $pos);

            if (!is_numeric($num)) {
                continue;
            }

            $data = substr($row, $pos + 1);

            if (!$start) { // init
                $start = $end = $num;
                $kk = $data;
                continue;
            }

            if ($num <= $end) {
                continue; // игнорим дубликаты и не отсортированные данные
            }

            if ($num == $end + 1 && $kk == $data) {
                $end = $num; // расширяем диапазон
                continue;
            }

            $this->addValueInCollector($outputFile, $counter, $collector, $start, $end, null, $data);
            $start = $end = $num;
            $kk = $data;
        }

        // Записываем оставшиеся данные
        if (!empty($row)) {
            $this->addValueInCollector($outputFile, $counter, $collector, $start, $end, true, $data);
        }

        fclose($inputFile);
        fclose($outputFile);
        echo PHP_EOL . date('r') . ': End step 2';
        echo PHP_EOL;
    }

    public function addValueInCollector($csvHandle, &$counter, &$collector, $start, $end, $isForceSave = null, $data = '')
    {
        $line = $start . ';' . $end . ';' . $data;
        $collector[] = $line;
        $counter++;

        if ($isForceSave || (($counter % 10000) == 0 && count($collector) >= 1000000)) {
            fputs($csvHandle, implode(PHP_EOL, $collector) . PHP_EOL);
            $collector = []; // Очищаем массив
            echo "Записано $counter строк. Память: " . memory_get_usage() . " байт\n";
            $counter = 0;
        }
    }

    /**
     * На основе основного и полного файла выгрузки из УВР создает файл модификации, где у номера есть информация - портирован он или у домашнего оператора находится.
     *
     * @param $fileName
     * @return void
     */
    public function actionMakeMod($fileName = null)
    {
        // alter table nnp_ported.number add is_active int default 1 not null;
        // alter table nnp_ported.number add created_at timestamptz default current_timestamp not null;
        // alter table nnp_ported.number add deleted_at timestamptz ;
        // CREATE UNIQUE INDEX idx_number_active ON nnp_ported.number (full_number) WHERE is_active = 1;
        // alter table nnp_ported.number  drop constraint number_pkey;


        echo PHP_EOL . date('r') . ': Start delta process';

        if (preg_match('/^DELTA_/', $fileName)) {
            $phoneIdx = 1;
            $operatorIdx = 2;
        } elseif (preg_match('/^NUM_/', $fileName)) {
            $phoneIdx = 0;
            $operatorIdx = 1;
        } elseif (preg_match('/^MOB_NUM_/', $fileName)) {
            $phoneIdx = 0;
            $operatorIdx = 1;
        } else {
            throw new \InvalidArgumentException('Это не DELTA-файл и не ZIP-файл');
        }

        $modFilePath = \Yii::getAlias('@runtime/' . $this->fileNameAddPrefix($fileName, 'MOD'));
        @unlink($modFilePath);
        $outFileHandler = fopen($modFilePath, 'w');

        $filePath = \Yii::getAlias('@runtime/' . $fileName);
        $fileHandler = $this->getFileStream($filePath);

        $mncRanges = $this->getMncRanges();
        $opers = $this->getOper();
        $operatorCodes = $this->getOperatorCodes();
        $operatorMncs = $this->getOperatorMnc();

        echo PHP_EOL;

        $counter = 0;
        $countAll = 0;
        $stat = [
            '=' => 0,
            '?' => 0,
            '*' => 0,
            's' => 0
        ];

        $speedProc = new CalculateGrowthRate();

        while (($row = fgetcsv($fileHandler, 1024, ';')) !== false) {
            $countAll++;

//            if ($countAll < 115000000) {
//                if (($countAll % 1000000) == 0) {
//                    echo PHP_EOL . "countAll: " . number_format($countAll / 1000000).'M';
//                }
//                continue;
//            }


            $phone = $row[$phoneIdx];

            if (($countAll % 1000000) == 0) {
                $speed = $speedProc->calculate($countAll);

                echo PHP_EOL . "countAll: " . number_format($countAll / 1000000) . 'M| count: ' . number_format($counter) . ' (=' . number_format($stat['=']) . ', *' . number_format($stat['*']) . ', ?' . number_format($stat['?']) . ', s' . number_format($stat['s']) . ') ... ' . $phone . ' speed: ' . number_format($speed);

                $speedPerc = round($speed / 15000);
                echo ' [ ' . str_pad('=', $speedPerc, '-') . ' ]';
            }


            if (
                empty($phone)
                || !is_numeric($phone)
            ) {
                continue;
            }

            if (
                strpos($phone, self::allowedPrefix) !== 0
            ) {
                $stat['s']++;
                continue;
            }

            $counter++;

            $operatorId = $row[$operatorIdx];

            $row = [
                'phone' => $phone,
                'bdpn_operator_id' => $operatorId,
            ];

            // MCN operator ID by operator from OPER file
            $row['bdpn_operator'] = $opers[$operatorId] ?? null;
            $mcnOperatorId = null;
            $mncOperator = null;
            $ro = $row['bdpn_operator'];
            if ($ro && isset($ro['bdpn_code'])) {
                if (isset($operatorCodes[$ro['bdpn_code']])) {
                    $mcnOperatorId = $operatorCodes[$ro['bdpn_code']];
                    $mncOperator = $operatorMncs[$ro['bdpn_code']] ?? $ro['mnc'] ?? null;
                } else {
                    print_r($row);
                    print_r($ro);
//                    echo PHP_EOL . ' ERROR: Не найден operator code: ' . $ro['bdpn_code'];
//                    continue;

                    throw new \LogicException('Не найден operator code: ' . $ro['bdpn_code']);
                }
            }

            if ($ro['bdpn_code'] == 'Mirandam') {
                continue;
            }

            $row['result'] = [
                'mnc' => $mncOperator,
                'region_code_fz' => null,
                'operator_by_bdpn_code' => $mcnOperatorId
            ];

//            $row['number_range'] = $this->findValueInRanges($numberRanges, $phone);
            $row['mnc_range'] = $this->findValueInRanges($mncRanges, $phone);

            // MCN operator by mnc range
            $rangeOperatorId = null;
            $regionFz = null;
            $nr = $row['mnc_range'];
            if ($nr && isset($nr['operator_id'])) {
                $rangeOperatorId = $nr['operator_id'];
                $regionFz = $nr['region_code_fz'];
            }
            $row['result']['operator_from_mnc_range'] = $rangeOperatorId;
            $row['result']['region_code_fz'] = $regionFz;

            $op = '?';
            if (isset($row['result']['operator_by_bdpn_code']) && isset($row['result']['operator_from_mnc_range'])) {
                if ($row['result']['operator_by_bdpn_code'] == $row['result']['operator_from_mnc_range']) {
                    $op = '=';
                } else {
                    $op = '*';
                }
            }
            $stat[$op]++;


//            if (false) {
//                echo PHP_EOL . sprintf("(%1s) %11s - D%2s%2s - %10s - %5s/%5s, %25s => %25s",
//                        $op, $row['phone'], $row['result']['region_code_fz'], $row['result']['mnc'], $row['bdpn_operator']['bdpn_code'],
//                        $row['result']['operator_from_mnc_range'], $row['result']['operator_by_bdpn_code'],
//                        $row['mnc_range']['operator'], $row['bdpn_operator'][1]
//                    );
//            }

            if ($op == '*' || $op == '=') {
                fwrite($outFileHandler, implode(';', [$phone, $ro['bdpn_code'], $regionFz, $mncOperator, $row['result']['operator_by_bdpn_code'], $op]) . "\n");
            }

//            if ($counter >= 40) {
//                break;
//            }
        }

        fclose($fileHandler);

        echo PHP_EOL . date('r') . ': End Step 3';
        echo PHP_EOL;
    }

    private function getFileStream($filePath)
    {
        if (pathinfo($filePath, PATHINFO_EXTENSION) == 'zip') {
            return $this->getZipFirstFileStream($filePath);
        }

        return fopen($filePath, 'r');
    }

    private function getZipFirstFileStream($filePath)
    {
        $zip = new \ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new \InvalidArgumentException('Не удалось открыть ZIP архив');
        }

        $csvFilename = $zip->getNameIndex(0);
        if (pathinfo($csvFilename, PATHINFO_EXTENSION) !== 'csv') {
            throw new \InvalidArgumentException('В архиве нет CSV файла');
        }

        $inputStream = $zip->getStream($csvFilename);
        if (!$inputStream) {
            throw new \RuntimeException('Ошибка открытия потоков');
        }

        return $inputStream;
    }

    public function getOper($fileName = 'OPR_2025_06_09_00_00_00.zip')
    {
        // OPR_2025_03_25_00_00_00.csv
        /**
         *     [11275] => Array
         * (
         * [0] => ООО "МСН Телеком"
         * [1] => ООО "МСН Телеком"
         * [2] => 7727752084
         * [3] => 20=mMSNTELECOM,37=mMSNTELECOM,42=mMSNTELECOM
         * [4] =>
         * )
         */
        echo PHP_EOL . date('r') . ': Start operator load';

        $filePath = \Yii::getAlias('@runtime/' . $fileName);

        $this->checkInFileName($filePath, 'zip');

        $zip = new \ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new \InvalidArgumentException('Не удалось открыть ZIP архив');
        }

        $csvFilename = $zip->getNameIndex(0);
        if (pathinfo($csvFilename, PATHINFO_EXTENSION) !== 'csv') {
            throw new \InvalidArgumentException('В архиве нет CSV файла');
        }

        $inputStream = $zip->getStream($csvFilename);
        if (!$inputStream) {
            throw new \RuntimeException('Ошибка открытия потоков');
        }

        // Потоковая обработка данных
        $collector = [];
        while (($row = fgetcsv($inputStream, 1024, ';')) !== false) {
            $row['bdpn_code'] = $this->getOpCode($row[4]);
            $row['mnc'] = $this->getMncCode($row[4]);
            $collector[$row[0]] = $row;
        }

        echo PHP_EOL . date('r') . ': End operator load';

        return $collector;
    }

    private function getOpCode($str)
    {
        $operatorCode = $str;
        if (!$operatorCode) {
            return null;
        }

        if (($pos = strpos($operatorCode, ',')) !== false) {
            $operatorCode = substr($operatorCode, 0, $pos);
        }

        $pos = strpos($operatorCode, '=m');
        if ($pos === false) {
            return null;
        }

        return substr($operatorCode, $pos + 2);
    }

    private function getMncCode($str)
    {
        $operatorCode = $str;
        if (!$operatorCode) {
            return null;
        }

        if (($pos = strpos($operatorCode, ',')) !== false) {
            $operatorCode = substr($operatorCode, 0, $pos);
        }

        $pos = strpos($operatorCode, '=m');
        if ($pos === false) {
            return null;
        }

        return substr($operatorCode, 0, $pos);
    }

    private function getPortedData()
    {
        $sql = <<<SQL
with a as (
    SELECT operator_source, max(operator_id) as operator_id, mnc, count(*) cnt
    FROM nnp_ported.number t
             join nnp.operator o on o.id = t.operator_id
    WHERE t.country_code = 643
--       and o.cnt > 0
--       and full_number between 79002261333 and 79003998744
    group by operator_source, mnc
)
, b as (
    select operator_source, max(cnt) max_cnt
    from a
    group by operator_source
)
select a.* from a
join b on a.operator_source = b.operator_source and cnt = b.max_cnt
SQL;

        return array_merge([
            [
                'operator_source' => 'MOYOPER',
                'operator_id' => 43453,
                'mnc' => '20'
            ],
            [
                'operator_source' => 'KOMTEX',
                'operator_id' => 2074,
                'mnc' => '20'
            ],
            [
                'operator_source' => 'BEZLIMIT',
                'operator_id' => 41882,
                'mnc' => '55'
            ],
            [
                'operator_source' => 'ALFAMOBAIL',
                'operator_id' => 46419,
                'mnc' => '19'
            ],
            [
                'operator_source' => 'OBIT',
                'operator_id' => 625,
                'mnc' => '04'
            ], [
                'operator_source' => 'GLONASS',
                'operator_id' => 7670,
                'mnc' => '77'
            ], [
                'operator_source' => 'OMEGA',
                'operator_id' => 50324,
                'mnc' => '02'
            ], [
                'operator_source' => 'ROS',
                'operator_id' => 45049,
                'mnc' => '97'
            ], [
                'operator_source' => 'Center2M',
                'operator_id' => 4586,
                'mnc' => '51'
            ], [
                'operator_source' => 'SunSim',
                'operator_id' => 36884,
                'mnc' => '44'
            ], [
                'operator_source' => 'Quantech',
                'operator_id' => 3116,
                'mnc' => '24'
            ], [
                'operator_source' => 'SKNT',
                'operator_id' => 3971,
                'mnc' => '14'
            ], [
                'operator_source' => 'Sintonik',
                'operator_id' => 11993,
                'mnc' => '20'
            ], [
                'operator_source' => 'VIKOM',
                'operator_id' => 34349,
                'mnc' => '20'
            ], [
                'operator_source' => 'KANTRI',
                'operator_id' => 9372,
                'mnc' => '44'
            ],

        ], \Yii::$app->dbPg->createCommand($sql)->cache(86400)->queryAll());
    }

    private function getOperatorCodes()
    {
        $vv = \yii\helpers\ArrayHelper::index($this->getPortedData(), 'operator_source');
        $vv = array_map(fn($a) => $a['operator_id'], $vv);

        return $vv;
    }

    private function getOperatorMnc()
    {
        $vv = \yii\helpers\ArrayHelper::index($this->getPortedData(), 'operator_source');
        $vv = array_map(fn($a) => $a['mnc'], $vv);
        return $vv;
    }

    private function getNumberRange(&$numberRanges, $number)
    {
        $cnt = 0;
        foreach ($numberRanges as $nr) {
            $cnt++;
            if ($nr['number_from'] <= $number and $number <= $nr['number_to']) {
                return $nr + ['cnt' => $cnt];
            }
        }
        return ['cnt' => $cnt];

    }

    private function findValueInRanges(&$numberRanges, $number)
    {
        $cnt = 0;
        $left = 0;
        $right = count($numberRanges) - 1;
        $bestMatch = null;

        while ($left <= $right) {
            $cnt++;
            $mid = $left + (int)(($right - $left) / 2);

            if ($numberRanges[$mid]['number_from'] <= $number) {
                // Проверяем, является ли текущий диапазон кандидатом
                $bestMatch = $mid;
                $left = $mid + 1;
            } else {
                $right = $mid - 1;
            }
        }

        if ($bestMatch !== null
            && $number >= $numberRanges[$bestMatch]['number_from']
            && $number <= $numberRanges[$bestMatch]['number_to']) {
            return $numberRanges[$bestMatch] + ['cnt' => $cnt];
        }

        return null;
    }

    private function getNumberRanges()
    {
        return NumberRange::find()
            ->where([
                'is_active' => true,
                'country_code' => Country::RUSSIA,
                'ndc_type_id' => NdcType::ID_MOBILE
            ])
            ->select([
                'number_from' => 'full_number_from',
                'number_to' => 'full_number_to',
            ])->addSelect(['operator_id', 'operator_source', 'region_id', 'region_source'])
            ->orderBy(['full_number_from' => SORT_ASC])
            ->cache(DependecyHelper::TIMELIFE_DAY)
            ->asArray()->all();
//
//        file_put_contents('./nr.dat', serialize($numberRanges));
//        $numberRanges = unserialize(file_get_contents('./nr.dat'));

    }

    private function getMncRanges()
    {
        return NumberRange::find()
            ->from('nnp.route_mnc')
            ->where([
                'is_active' => true,
                'country_code' => Country::RUSSIA,
            ])
            ->select([
                'number_from' => 'full_number_from',
                'number_to' => 'full_number_to',
            ])->addSelect(['operator_id', 'operator', 'region_code_fz', 'mnc', 'route'])
            ->orderBy(['full_number_from' => SORT_ASC])
            ->cache(DependecyHelper::TIMELIFE_DAY)
            ->asArray()->all();

    }

    public function fileNameAddPrefix($fileName = null, $add = null)
    {
        if (!$fileName || !$add) {
            throw new \InvalidArgumentException('Не заданы обязательные параметры имени файла');
        }

        $m = [];
        if (!preg_match('/^(.+)_(202.+)\.(.+)$/', $fileName, $m) || count($m) != 4) {
            throw new \LogicException('Не найден префикс файла');
        }

        return $m[1] . '_' . $add . '_' . $m[2] . '.csv';
    }

    public function actionSort($fileName = 'MOD.csv')
    {
        $inFilePath = \Yii::getAlias('@runtime/' . $fileName);

        $this->checkInFileName($inFilePath, 'csv');

        $sortedFilePath = \Yii::getAlias('@runtime/' . $this->fileNameAddPrefix($fileName, 'SORTED'));
        @unlink($sortedFilePath);

        SortCsvFileHelper::me()->sortFile(
            $inFilePath,
            $sortedFilePath,
            false);
    }

    public function actionApplyMod($fileName)
    {
        $filePath = \Yii::getAlias('@runtime/' . $fileName);

        $this->checkInFileName($filePath, 'csv');

        $handler = $this->getFileStream($filePath);

        $collectorEq = [];
        $collectorMod = [];
        $counterAll = 0;
        while ($row = fgetcsv($handler, 1024, ';')) {
            $counterAll++;
            $l = [
                'phone' => $row[0],
                'bpmn_code' => $row[1],
                'region_code_fz' => $row[2],
                'mnc' => $row[3],
                'operator_id' => $row[4],
                'op' => $row[5],
            ];

//            echo PHP_EOL . sprintf('(%1s) %11s D%2s%2s - % 5d', $l['op'], $l['phone'], $l['region_code_fz'], $l['mnc'], $l['operator_id']);

            $this->apply_addCollector($collectorEq, $collectorMod, $l);
            if (($counterAll % 1000000) == 0) {
                echo PHP_EOL . date('r') . ": count: " . ($counterAll / 1000000) . 'M | ' . $l['phone'];
            }
        }

        if ($collectorEq) {
            $this->flushEqCollector($collectorEq);
        }

        if ($collectorMod) {
            $this->flushModCollector($collectorMod);
        }

        fclose($handler);
    }

    private function apply_addCollector(&$collectorEq, &$collectorMod, $row)
    {
        if ($row['op'] == '=') {
            $this->processEq($collectorEq, $row);
        } elseif ($row['op'] == '*') {
            $this->processMod($collectorMod, $row);
        }
    }

    private function processEq(&$collector, $row)
    {
        $collector[$row['phone']] = 1;

        if (count($collector) > 50000) {
            $this->flushEqCollector($collector);
        }
    }

    private function processMod(&$collector, $row)
    {
        $collector[$row['phone']] = $row;
        if (count($collector) > 50000) {
            $this->flushModCollector($collector);
        }
    }

    private function flushEqCollector(&$collector, $isHasEmpty = true)
    {
//        print_r($collector);

//        Number::updateAll(
//            [
//                'is_active' => 0,
//                'deleted_at' => (new Expression('current_timestamp'))
//            ], [
//                'country_code' => Country::RUSSIA,
//                'full_number' => array_keys($collector),
//                'is_active' => 1,
//            ]);

        $result = Number::deleteAll([
            'country_code' => Country::RUSSIA,
            'full_number' => array_keys($collector),
//                'is_active' => 1,
        ]);

        if ($isHasEmpty) {
            $collector = [];
        }

        if ($result) {
            echo PHP_EOL . 'flush EQ collector: ' . $result . ' row(s)';
        }
    }

    private function flushModCollector(&$collector)
    {
        $portedData = Number::find()
            ->where([
                'country_code' => Country::RUSSIA,
//                'is_active' => 1,
                'full_number' => array_keys($collector)
            ])
            ->indexBy('full_number')
            ->asArray()
            ->all();

        $insertData = [];
        $deleteData = [];
        foreach ($collector as $phone => $row) {
            $isAdd = false;
            if (!isset($portedData[$phone])) {
                $isAdd = true;
            } else {
                $ported = $portedData[$phone];
                if (
                    $ported['operator_source'] != $row['bpmn_code']
                    || $ported['region_code_fz'] != $row['region_code_fz']
                    || (int)$ported['mnc'] != (int)$row['mnc']
                ) {
                    $isAdd = true;
                    $deleteData[$phone] = 1;
                }
            }

            if (!$isAdd) {
                continue;
            }

            $insertData[] = [
                $phone, // full_number
                Country::RUSSIA, // country_code
                $row['bpmn_code'], // operator_source
                $row['operator_id'], // operator_id,
                $row['mnc'], // mnc
                'D' . $row['region_code_fz'] . $row['mnc'], // route
                $row['region_code_fz'], // region_code_fz
                //                ported_date
//                1, // is_active
            ];
        }

        if ($deleteData) {
            echo PHP_EOL . 'flush Mod Collector: delete escalate';
            $this->flushEqCollector($deleteData);
        }

        if ($insertData) {
            $result = Number::getDb()->createCommand()->batchInsert(Number::tableName(), [
                'full_number',
                'country_code',
                'operator_source',
                'operator_id',
                'mnc',
                'route',
                'region_code_fz',
//            'ported_date',
//            'is_active',
            ], $insertData)->execute();

            if ($result) {
                echo PHP_EOL . 'flush Mod Collector: insert ' . $result . ' row(s)';
            }
        }

        $collector = [];
    }
}