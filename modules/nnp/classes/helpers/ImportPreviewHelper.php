<?php

namespace app\modules\nnp\classes\helpers;

use app\models\EventQueue;
use app\modules\nnp\models\CountryFile;
use app\modules\nnp\media\ImportServiceUploaded;
use yii\helpers\Html;
use yii\helpers\Url;
use app\modules\nnp\classes\helpers\RangesTreeHelper;

class ImportPreviewHelper
{
    public const STATUS_OK      = 0;
    public const STATUS_ERROR   = 1;
    public const STATUS_WARNING = 2;

    private const PROGRESS_TEMPLATE = "Count all: %d\ncount: %d";

    /**
     * Добавляет строку в log_error для отладки фоновой проверки.
     */
    private static function appendDebugLog(EventQueue $eventQueue, string $message): void
    {
        $eventQueue->log_error = trim($eventQueue->log_error . PHP_EOL . sprintf('[%s] %s', date('Y-m-d H:i:s'), $message));
        $eventQueue->save(false, ['log_error']);
    }

    /**
     * Обновляет счётчик прогресса, сохраняя уже записанные отладочные строки.
     */
    private static function updateProgress(EventQueue $eventQueue, int $total, int $done): void
    {
        $rows = array_values(array_filter(explode(PHP_EOL, (string)$eventQueue->log_error), 'strlen'));

        $debugRows = [];
        foreach ($rows as $row) {
            if (strpos($row, 'Count all: ') === 0 || strpos($row, 'count: ') === 0) {
                continue;
            }
            $debugRows[] = $row;
        }

        $logError = sprintf(self::PROGRESS_TEMPLATE, $total, $done);
        if ($debugRows) {
            $logError .= PHP_EOL . implode(PHP_EOL, $debugRows);
        }

        $eventQueue->log_error = $logError;
        $eventQueue->save(false, ['log_error']);
    }

    /**
     * Ожидаемые заголовки CSV.
     */
    public static function getExpectedHeader(): array
    {
        return [
            'CC',
            'NDC',
            'Type',
            'Type_id',
            'From',
            'To',
            'Region',
            'City',
            'Operator',
        ];
    }

    /**
     * Определение разделителя CSV по первой строке.
     */
    public static function detectDelimiter($handle): string
    {
        $delimiter = ';';

        $pos  = ftell($handle);
        $line = fgets($handle);
        if ($line !== false) {
            $hasSemicolon = strpos($line, ';') !== false;
            $hasComma     = strpos($line, ',') !== false;

            if ($hasComma && !$hasSemicolon) {
                $delimiter = ',';
            }
        }
        fseek($handle, $pos);

        return $delimiter;
    }

    /**
     * Очередная проверка файла с прогрессом.
     */
    public static function runQueuedCheck(CountryFile $countryFile, EventQueue $eventQueue): void
    {
        $filePath = $countryFile->country->getMediaManager()->getUnzippedFilePath($countryFile);
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \RuntimeException(sprintf('Ошибка чтения файла %s: %s', $filePath, json_encode(error_get_last())));
        }

        $startTime  = microtime(true);
        $lastHeartbeatRow = 0;
        $lastHeartbeatTime = $startTime;
        $delimiter  = self::detectDelimiter($handle);
        $fileSize   = filesize($filePath) ?: 0;
        if ($fileSize > 0) {
            self::updateProgress($eventQueue, $fileSize, 0);
        }
        $debugEnabled = filter_var(getenv('NNP_DEBUG_LOGS') ?: '0', FILTER_VALIDATE_BOOL);
        $profilingEnabled = filter_var(getenv('NNP_DEBUG_PROFILING') ?: '0', FILTER_VALIDATE_BOOL) || $debugEnabled;
        $saveAllRecords = filter_var(getenv('NNP_SAVE_ALL_RECORDS') ?: '0', FILTER_VALIDATE_BOOL);
        $recordsCoverage = $saveAllRecords ? 'full' : 'errors_only';

        $profiling = $profilingEnabled
            ? [
                'rows' => 0,
                'row_time' => 0.0,
                'row_time_max' => 0.0,
                'row_time_line' => null,
                'row_time_cc' => null,
                'row_time_ndc' => null,
            ]
            : null;

        $debugLogger = $debugEnabled
            ? function (string $message) use ($eventQueue): void {
                self::appendDebugLog($eventQueue, $message);
            }
            : null;

        if ($debugLogger) {
            $debugLogger(sprintf('started: file=%s, delimiter="%s"', $filePath, $delimiter));
        }

        $rowNumber = 0;
        $linesCount = 0;
        $isFileOK = true;
        $errorLines = [];
        $warningLines = [];
        $alreadyRead = [];
        $rangesByPrefix = [];
        $segmentsMeta = [];
        $records = [];
        $headerChecked = false;
        $importServiceUploaded = new ImportServiceUploaded(['countryCode' => $countryFile->country->code]);
        try {
            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                $rowNumber++;
                $rowStartedAt = microtime(true);

                if ($rowNumber === 1 && !is_numeric($row[0])) {
                    if (!$headerChecked) {
                        $headerChecked = true;
                        $headerResult  = self::validateHeaderRow($row);

                        if (!empty($headerResult['errors'])) {
                            $isFileOK               = false;
                            $errorLines[$rowNumber] = implode(PHP_EOL, $headerResult['errors']);
                        }
                        if (!empty($headerResult['warnings'])) {
                            $warningLines[$rowNumber] =
                                (isset($warningLines[$rowNumber]) && $warningLines[$rowNumber] !== '' ? $warningLines[$rowNumber] . PHP_EOL : '') .
                                implode(PHP_EOL, $headerResult['warnings']);
                        }
                    }
                    continue;
                }

                [$rowStatus, $oldLine] = self::checkRow(
                    $rowNumber,
                    $row,
                    $importServiceUploaded,
                    $errorLines,
                    $warningLines,
                    $alreadyRead,
                    $rangesByPrefix,
                    $segmentsMeta,
                    $countryFile->country->code,
                    $countryFile->id,
                    $isFileOK,
                    $debugLogger,
                    $rowStartedAt,
                    $profiling
                );

                $needsRecord = $saveAllRecords || $oldLine !== null || self::hasNonOkStatus($rowStatus);

                if ($needsRecord) {
                    $records[$rowNumber] = [
                        $rowStatus,
                        $oldLine,
                    ];
                }

                if ($fileSize > 0 && $rowNumber % 2000 === 0) {
                    self::updateProgress($eventQueue, $fileSize, ftell($handle));
                }

                if ($debugLogger && is_array($profiling) && $rowNumber % 5000 === 0) {
                    $avgRow = $profiling['rows'] > 0 ? $profiling['row_time'] / $profiling['rows'] : 0.0;
                    $deltaRows = $rowNumber - $lastHeartbeatRow;
                    $deltaTime = microtime(true) - $lastHeartbeatTime;
                    $recentRate = $deltaTime > 0 ? $deltaRows / $deltaTime : 0.0;
                    $debugLogger(sprintf(
                        'heartbeat: row=%d, mem=%.2f MB, elapsed=%.1fs, avgRow=%.5fs, maxRow=%.5fs(line %s %s/%s) recentRate=%.1f rows/s over last %.1fs',
                        $rowNumber,
                        memory_get_usage(true) / 1024 / 1024,
                        microtime(true) - $startTime,
                        $avgRow,
                        $profiling['row_time_max'],
                        $profiling['row_time_line'] ?? 'n/a',
                        $profiling['row_time_cc'] ?? 'n/a',
                        $profiling['row_time_ndc'] ?? 'n/a',
                        $recentRate,
                        $deltaTime
                    ));
                    $lastHeartbeatRow = $rowNumber;
                    $lastHeartbeatTime = microtime(true);
                }
            }
        } catch (\Throwable $e) {
            if ($debugLogger) {
                $debugLogger(sprintf(
                    'exception on row %d: %s at %s:%d',
                    $rowNumber,
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine()
                ));
            }
            throw $e;
        }

        $linesCount = $rowNumber;

        $progressTotal = self::validateOverlaps(
            $rangesByPrefix,
            $segmentsMeta,
            $records,
            $errorLines,
            $isFileOK,
            $debugLogger,
            $eventQueue,
            $linesCount
        );

        // Финальный прогресс фиксируем даже если total не кратен 100.
        self::updateProgress($eventQueue, $progressTotal, $progressTotal);

        if ($debugLogger && is_array($profiling)) {
            $avgRow = $profiling['rows'] > 0 ? $profiling['row_time'] / $profiling['rows'] : 0.0;

            $debugLogger(sprintf(
                'completed: rows=%d, mem=%.2f MB, elapsed=%.1fs, avgRow=%.5fs, maxRow=%.5fs(line %s %s/%s)',
                $rowNumber,
                memory_get_usage(true) / 1024 / 1024,
                microtime(true) - $startTime,
                $avgRow,
                $profiling['row_time_max'],
                $profiling['row_time_line'] ?? 'n/a',
                $profiling['row_time_cc'] ?? 'n/a',
                $profiling['row_time_ndc'] ?? 'n/a'
            ));
        }

        $data = [
            'isFileOK' => $isFileOK,
            'records' => $records,
            'errorLines' => $errorLines,
            'warningLines' => $warningLines,
            'checked' => true,
            'recordsCoverage' => $recordsCoverage,
        ];

        $countryFile->setCachedPreviewData(json_encode($data));

        fclose($handle);
    }

    /**
     * Финальная проверка пересечений после полного чтения файла.
     */
    private static function validateOverlaps(
        array $rangesByPrefix,
        array $segmentsMeta,
        array &$records,
        array &$errorLines,
        bool &$isFileOK,
        ?callable $debugLogger,
        EventQueue $eventQueue,
        int $linesCount
    ): int {
        $overlapCount = 0;
        $segmentsTotal = 0;
        $processedSegments = 0;
        $nextProgressAt = 20000;
        $defaultStatuses = array_fill(0, count(self::getExpectedHeader()), self::STATUS_OK);

        foreach ($rangesByPrefix as $types) {
            foreach ($types as $ndcs) {
                foreach ($ndcs as $segments) {
                    $segmentsTotal += count($segments);
                }
            }
        }

        $progressTotal = $linesCount + $segmentsTotal;
        if ($segmentsTotal > 0) {
            self::updateProgress($eventQueue, $progressTotal, $linesCount);
        }

        foreach ($rangesByPrefix as $ccKey => $types) {
            foreach ($types as $typeBucket => $ndcs) {
                foreach ($ndcs as $ndc => $segments) {
                    RangesTreeHelper::scanOverlaps($segments, function (array $curr, array $prev) use (&$records, &$errorLines, &$isFileOK, &$overlapCount, $ccKey, $typeBucket, $ndc, $defaultStatuses, $segmentsMeta): void {
                        $currMeta = $segmentsMeta[$curr[2]] ?? ['ndc' => $ndc, 'from_sn' => '', 'to_sn' => ''];
                        $prevMeta = $segmentsMeta[$prev[2]] ?? ['ndc' => $ndc, 'from_sn' => '', 'to_sn' => ''];

                        $msgCurr = sprintf(
                            'Пересечение диапазонов: (CC %s) NDC %s %s-%s пересекается с NDC %s %s-%s (строка %d)',
                            $ccKey,
                            $ndc, $currMeta['from_sn'], $currMeta['to_sn'],
                            $prevMeta['ndc'], $prevMeta['from_sn'], $prevMeta['to_sn'],
                            $prev[2]
                        );

                        $msgPrev = sprintf(
                            'Пересечение диапазонов: (CC %s) NDC %s %s-%s пересекается с NDC %s %s-%s (строка %d)',
                            $ccKey,
                            $prevMeta['ndc'], $prevMeta['from_sn'], $prevMeta['to_sn'],
                            $ndc, $currMeta['from_sn'], $currMeta['to_sn'],
                            $curr[2]
                        );

                        foreach ([[$curr[2], $msgCurr], [$prev[2], $msgPrev]] as $pair) {
                            $line = $pair[0];
                            $msg = $pair[1];

                            if (!isset($records[$line])) {
                                $records[$line] = [$defaultStatuses, null];
                            }

                            if (isset($records[$line][0][4])) {
                                $records[$line][0][4] = self::STATUS_ERROR;
                            }
                            if (isset($records[$line][0][5])) {
                                $records[$line][0][5] = self::STATUS_ERROR;
                            }

                            $errorLines[$line] = (isset($errorLines[$line]) ? $errorLines[$line] . PHP_EOL : '') . $msg;
                        }

                        $isFileOK = false;
                        $overlapCount++;
                    });

                    if ($segmentsTotal > 0) {
                        $processedSegments += count($segments);
                        if ($processedSegments >= $segmentsTotal || $processedSegments >= $nextProgressAt) {
                            self::updateProgress($eventQueue, $progressTotal, $linesCount + $processedSegments);
                            $nextProgressAt += 20000;
                        }
                    }
                }
            }
        }

        if ($segmentsTotal > 0 && $processedSegments < $segmentsTotal) {
            self::updateProgress($eventQueue, $progressTotal, $linesCount + $processedSegments);
        }

        if ($debugLogger && $overlapCount > 0) {
            $debugLogger(sprintf('overlap scan completed: total overlaps=%d', $overlapCount));
        }

        return $progressTotal;
    }

    /**
     * Расчёт данных прогресса из log_error.
     */
    public static function getProgressData(EventQueue $eventQueue): array
    {
        $countAll = 0;
        $count = 0;
        $progressStyle = 'info';
        $error = '';
        $eta = '';

        $rows = array_values(array_filter(explode(PHP_EOL, trim($eventQueue->log_error))));

        if ($rows && strpos($rows[0], 'Count all: ') !== false) {
            $countAll = (int)str_replace('Count all: ', '', $rows[0]);
            $count = $countAll;

            foreach ($rows as $row) {
                if (strpos($row, 'count: ') === 0) {
                    $count = (int)str_replace('count: ', '', $row);
                }
            }

            if ($count === $countAll && $countAll !== 0 && (!isset($rows[1]) || strpos($rows[1], 'count: ') !== 0)) {
                $progressStyle = 'error';
                $error = end($rows);
            }
        } elseif ($rows) {
            $progressStyle = 'error';
            $error = $rows[0];
        }

        if ($eventQueue->status === EventQueue::STATUS_OK) {
            $count = $countAll;
            $progressStyle = 'success';
            $error = '';
        }

        $progressValue = $countAll ? round($count / ($countAll / 100)) : 0;

        if ($countAll > 0 && $count > 0 && $count < $countAll && $progressStyle !== 'error') {
            $eta = self::calculateEta($countAll, $count, $rows);
        }

        return [$countAll, $count, $progressStyle, $progressValue, $error, $eta];
    }

    private static function calculateEta(int $total, int $done, array $rows): string
    {
        $timestamps = [];
        $progressSamples = [];

        foreach ($rows as $row) {
            if (!preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $row, $matches)) {
                continue;
            }

            $timestamp = strtotime($matches[1]);
            if ($timestamp === false) {
                continue;
            }

            $timestamps[] = $timestamp;

            if (preg_match('/row=(\d+)/', $row, $rowMatch)) {
                $progressSamples[] = [$timestamp, (int)$rowMatch[1]];
            }
        }

        $rate = 0.0;
        $referenceTs = null;

        // Используем последние 2-3 heartbeat-замера (row=...) для более живой оценки.
        $samplesCount = count($progressSamples);
        if ($samplesCount >= 2) {
            $window = array_slice($progressSamples, max(0, $samplesCount - 3));
            $first = $window[0];
            $last  = $window[count($window) - 1];
            $deltaRows = $last[1] - $first[1];
            $deltaTime = $last[0] - $first[0];
            if ($deltaRows > 0 && $deltaTime > 0) {
                $rate = $deltaRows / $deltaTime;
                $referenceTs = $last[0];
            }
        }

        // Fallback: общий средний темп по всем отметкам времени.
        if (!$rate && count($timestamps) >= 2) {
            $startTs = $timestamps[0];
            $lastTs  = end($timestamps);
            $elapsed = $lastTs - $startTs;
            if ($elapsed > 0) {
                $rate = $done / $elapsed;
                $referenceTs = $lastTs;
            }
        }

        if ($rate <= 0 || $referenceTs === null) {
            return '';
        }

        $remainingSeconds = (int)round(($total - $done) / $rate);
        if ($remainingSeconds < 1) {
            return '';
        }

        $etaTime = $referenceTs + $remainingSeconds;

        return sprintf(
            'Оценка завершения: ≈%s (до %s)',
            self::formatDuration($remainingSeconds),
            date('H:i', $etaTime)
        );
    }

    private static function formatDuration(int $seconds): string
    {
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $secs = $seconds % 60;

        $parts = [];
        if ($hours > 0) {
            $parts[] = $hours . ' ч';
        }
        if ($minutes > 0) {
            $parts[] = $minutes . ' мин';
        }
        if (!$hours && !$minutes) {
            $parts[] = max(1, $secs) . ' с';
        }

        return implode(' ', $parts);
    }

    /**
     * Проверка заголовка CSV.
     */
    public static function validateHeaderRow(array $row): array
    {
        $errors   = [];
        $warnings = [];

        $expected = self::getExpectedHeader();
        $trimmed  = array_map('trim', $row);

        $cols = count($trimmed);
        $exp  = count($expected);

        if ($cols !== $exp) {
            $errors[] = sprintf(
                'Количество столбцов должно быть %d, сейчас %d.',
                $exp,
                $cols
            );
        }

        $max = min($cols, $exp);
        for ($i = 0; $i < $max; $i++) {
            if ($trimmed[$i] !== $expected[$i]) {
                $errors[] = sprintf(
                    'Неверное имя колонки %d: ожидалось "%s", получено "%s"',
                    $i + 1,
                    $expected[$i],
                    $trimmed[$i]
                );
            }
        }

        return ['errors' => $errors, 'warnings' => $warnings];
    }

    /**
     * Проверка одной строки CSV.
     */
    public static function checkRow(
        int $lineNumber,
        array $row,
        ImportServiceUploaded $importServiceUploaded,
        array &$errorLines,
        array &$warningLines,
        array &$alreadyRead,
        array &$rangesByPrefix,
        array &$segmentsMeta,
        $countryCode,
        int $countryFileId,
        bool &$isFileOK,
        ?callable $debugLogger = null,
        ?float $rowStartedAt = null,
        ?array &$profiling = null
    ): array {
        $rowStartedAt = $rowStartedAt ?? microtime(true);
        $profiling = $profiling ?? null;
        $oldLine   = null;
        $rowStatus = [];

        $expectedHeader = self::getExpectedHeader();
        $expectedCols   = count($expectedHeader);

        // trim всей строки
        $row = array_map('trim', $row);

        // --- Пустая строка ---
        $allEmpty = true;
        foreach ($row as $c) {
            if ($c !== '') {
                $allEmpty = false;
                break;
            }
        }

        if ($allEmpty) {
            $errorLines[$lineNumber] = 'Пустая строка в файле не допускается.';
            foreach ($row as $idx => $_) {
                $rowStatus[$idx] = self::STATUS_ERROR;
            }
            $isFileOK = false;
            return [$rowStatus, null];
        }

        // --- Количество столбцов ---
        $cols = count($row);
        if ($cols !== $expectedCols) {
            $errorLines[$lineNumber] = sprintf(
                'Количество столбцов в строке должно быть %d, сейчас %d.',
                $expectedCols,
                $cols
            );
            foreach ($row as $idx => $_) {
                $rowStatus[$idx] = self::STATUS_ERROR;
            }
            $isFileOK = false;
            return [$rowStatus, null];
        }

        // --- Обработка данных ---
        if (!($lineNumber == 1 && !is_numeric($row[0]))) {

            $numberRangeImport = $importServiceUploaded->getNumberRangeByRow($row);

            $rowStatus = array_map(
                fn($hasError) => $hasError ? self::STATUS_ERROR : self::STATUS_OK,
                $importServiceUploaded->getRowHasError($numberRangeImport)
            );

            $key = implode('|', [
                $numberRangeImport->country_prefix,
                $numberRangeImport->ndc_str,
                $numberRangeImport->number_from,
                $numberRangeImport->number_to,
            ]);

            $keyDisplay = sprintf(
                '(%s) %s %s - %s %s',
                $numberRangeImport->country_prefix,
                $numberRangeImport->ndc_str,
                $numberRangeImport->number_from,
                $numberRangeImport->ndc_str,
                $numberRangeImport->number_to
            );

            // Ошибки из модели
            if ($errors = $numberRangeImport->getErrors()) {
                $text = '';
                foreach ($errors as $field => $errorList) {
                    foreach ($errorList as $value) {
                        $text .= sprintf("%s: %s", $field, $value) . PHP_EOL;
                    }
                }
                $errorLines[$lineNumber] = $text;
                $isFileOK = false;
                return [$rowStatus, null];
            }

            // --- Дополнительные проверки ---
                $extraErrors = [];

            $cc       = $row[0];
            $ndc      = $row[1];
            $type     = $row[2];
            $typeIdSn = $row[3];
            $typeId   = $numberRangeImport->ndc_type_id;
            $fromSn   = (string)$numberRangeImport->number_from;
            $toSn     = (string)$numberRangeImport->number_to;
            $region   = $row[6];
            $city     = $row[7];
            $operator = $row[8];

            if ($cc === '') {
                $extraErrors[] = 'CC (префикс страны) не может быть пустым.';
                $rowStatus[0] = self::STATUS_ERROR;
            }

            if ((string)$typeId !== '6' && $ndc === '') {
                $extraErrors[] = 'NDC не может быть пустым.';
                $rowStatus[1] = self::STATUS_ERROR;
            }

            if ($type === '') {
                $extraErrors[] = 'Type не может быть пустым.';
                $rowStatus[2] = self::STATUS_ERROR;
            }

            if ($typeIdSn === '') {
                $extraErrors[] = 'Тип NDC не может быть пустым.';
                $rowStatus[3] = self::STATUS_ERROR;
            }

            if ($fromSn === '' || !ctype_digit($fromSn)) {
                $extraErrors[] = 'Начальное значение должно быть числом.';
                $rowStatus[4] = self::STATUS_ERROR;
            }

            if ($toSn === '' || !ctype_digit($toSn)) {
                $extraErrors[] = 'Конечное значение должно быть числом.';
                $rowStatus[5] = self::STATUS_ERROR;
            }

            if (ctype_digit($fromSn) && ctype_digit($toSn) && (int)$fromSn > (int)$toSn) {
                $extraErrors[] = 'Начальное значение не может быть больше конечного.';
                $rowStatus[4] = $rowStatus[5] = self::STATUS_ERROR;
            }

            if ($operator === '') {
                $extraErrors[] = 'Operator не может быть пустым.';
                $rowStatus[8] = self::STATUS_ERROR;
            }

            // --- Region/City только для гео (type_id = 1) ---
            if ((int)$typeId !== 1) {
                if ($region !== '' || $city !== '') {
                    $extraWarnings[] = 'Для негеографических номеров Region и City должны быть пустыми.';
                    if ($region !== '') {
                        $rowStatus[6] = self::STATUS_WARNING;
                    }
                    if ($city !== '') {
                        $rowStatus[7] = self::STATUS_WARNING;
                    }
                }
            }

            // --- Подготовка к оффлайновой проверке пересечений ---
            $ccKey = (string)$numberRangeImport->country_prefix;

            if (!isset($rangesByPrefix[$ccKey])) {
                $rangesByPrefix[$ccKey] = [];
            }

            $typeBucket = ctype_digit((string)$typeId) ? (string)$typeId : 'unknown';

            if (!array_key_exists($typeBucket, $rangesByPrefix[$ccKey])) {
                $rangesByPrefix[$ccKey][$typeBucket] = [];
            }

            if (ctype_digit($ndc) && ctype_digit($fromSn) && ctype_digit($toSn)) {
                if (!array_key_exists($ndc, $rangesByPrefix[$ccKey][$typeBucket])) {
                    $rangesByPrefix[$ccKey][$typeBucket][$ndc] = [];
                }

                $rangesByPrefix[$ccKey][$typeBucket][$ndc][] = [
                    (int)($ndc . $fromSn),
                    (int)($ndc . $toSn),
                    $lineNumber,
                ];

                $segmentsMeta[$lineNumber] = [
                    'ndc' => $ndc,
                    'from_sn' => $fromSn,
                    'to_sn' => $toSn,
                ];
            }

            // --- Запись ошибок ---
            if ($extraErrors) {
                $isFileOK = false;
                $errorLines[$lineNumber] =
                    (isset($errorLines[$lineNumber]) ? $errorLines[$lineNumber] . PHP_EOL : '') .
                    implode(PHP_EOL, $extraErrors);
            }

            if (isset($alreadyRead[$key])) {
                $oldLine = $alreadyRead[$key];

                $warningLines[$lineNumber] =
                    (isset($warningLines[$lineNumber]) ? $warningLines[$lineNumber] . PHP_EOL : '') .
                    "Диапазон $keyDisplay уже добавлен в строке " .
                    Html::a(
                        $oldLine,
                        Url::to([
                            '/nnp/import/step3',
                            'countryCode' => $countryCode,
                            'fileId'      => $countryFileId,
                            'offset'      => $oldLine,
                            'limit'       => min($lineNumber - $oldLine, 100),
                        ]) . '#line'.$oldLine
                    );
            }

            $alreadyRead[$key] = $lineNumber;

        }

        if (is_array($profiling)) {
            $profiling['rows']++;
            $profiling['row_time'] += microtime(true) - $rowStartedAt;
            if (($profiling['row_time_max'] ?? 0) < ($profiling['row_time_last'] = microtime(true) - $rowStartedAt)) {
                $profiling['row_time_max'] = $profiling['row_time_last'];
                $profiling['row_time_line'] = $lineNumber;
                $profiling['row_time_cc'] = $ccKey ?? null;
                $profiling['row_time_ndc'] = $ndc ?? null;
            }
        }

        return [$rowStatus, $oldLine];
    }

    private static function hasNonOkStatus(array $rowStatus): bool
    {
        foreach ($rowStatus as $status) {
            if ($status !== self::STATUS_OK) {
                return true;
            }
        }

        return false;
    }

}