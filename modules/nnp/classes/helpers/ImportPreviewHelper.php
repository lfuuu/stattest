<?php

namespace app\modules\nnp\classes\helpers;

use app\models\EventQueue;
use app\modules\nnp\models\CountryFile;
use app\modules\nnp\media\ImportServiceUploaded;
use app\helpers\DateTimeZoneHelper;
use app\exceptions\ModelValidationException;
use yii\helpers\Html;
use yii\helpers\Url;
use app\modules\nnp\classes\helpers\RangesTreeHelper;

class ImportPreviewHelper
{
    public const STATUS_OK      = 0;
    public const STATUS_ERROR   = 1;
    public const STATUS_WARNING = 2;

    private const PROGRESS_TEMPLATE = "Count all: %d\ncount: %d";
    private const PROGRESS_ROW_STEP = 2000;
    private const RECORDS_COVERAGE_ERRORS_ONLY = 'errors_only';
    public const ISSUE_ERRORS = 'errors';
    public const ISSUE_WARNINGS = 'warnings';
    public const ISSUE_ALREADY_READ = 'already_read';

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
        if (!$eventQueue->save(false, ['log_error'])) {
            throw new ModelValidationException($eventQueue);
        }
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

        $delimiter  = self::detectDelimiter($handle);
        $fileSize   = filesize($filePath);
        if ($fileSize) {
            self::updateProgress($eventQueue, $fileSize, 0);
        }

        $recordsCoverage = self::RECORDS_COVERAGE_ERRORS_ONLY;

        $rowNumber = 0;
        $linesCount = 0;
        $isFileOK = true;
        $issues = self::initIssues();
        $alreadyRead = [];
        $rangesByPrefix = [];
        $segmentsMeta = [];
        $records = [];
        $importServiceUploaded = new ImportServiceUploaded(['countryCode' => $countryFile->country->code]);
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rowNumber++;

            if ($rowNumber === 1 && self::isHeaderRow($row)) {
                $headerResult = self::validateHeaderRow($row);

                if (!empty($headerResult['errors'])) {
                    foreach ($headerResult['errors'] as $error) {
                        self::appendIssue($issues, self::ISSUE_ERRORS, $rowNumber, $error);
                    }
                    $isFileOK = false;
                    break;
                }

                foreach ($headerResult['warnings'] as $warning) {
                    self::appendIssue($issues, self::ISSUE_WARNINGS, $rowNumber, $warning);
                }

                continue;
            }

            [$rowStatus, $oldLine, $rangeContext] = self::checkRow(
                $rowNumber,
                $row,
                $importServiceUploaded,
                $issues,
                $alreadyRead,
                $countryFile->country->code,
                $countryFile->id,
                $isFileOK
            );

            self::prepareRangeContext($rangeContext, $rangesByPrefix, $segmentsMeta, $rowNumber);

            $needsRecord = $oldLine !== null || self::hasNonOkStatus($rowStatus);

            if ($needsRecord) {
                $records[$rowNumber] = [
                    $rowStatus,
                    $oldLine,
                ];
            }

            if ($fileSize > 0 && $rowNumber % self::PROGRESS_ROW_STEP === 0) {
                self::updateProgress($eventQueue, $fileSize, ftell($handle));
            }
        }

        $linesCount = $rowNumber;

        $progressTotal = self::validateOverlaps(
            $rangesByPrefix,
            $segmentsMeta,
            $records,
            $issues,
            $isFileOK,
            $eventQueue,
            $linesCount
        );

        // Финальный прогресс фиксируем даже если total не кратен 100.
        self::updateProgress($eventQueue, $progressTotal, $progressTotal);

        $data = [
            'isFileOK' => $isFileOK,
            'records' => $records,
            'errorLines' => $issues[self::ISSUE_ERRORS],
            'warningLines' => $issues[self::ISSUE_WARNINGS],
            'alreadyRead' => $issues[self::ISSUE_ALREADY_READ],
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
        array &$issues,
        bool &$isFileOK,
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
                    RangesTreeHelper::scanOverlaps($segments, function (array $curr, array $prev) use (&$records, &$issues, &$isFileOK, &$overlapCount, $ccKey, $typeBucket, $ndc, $defaultStatuses, $segmentsMeta): void {
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

                            self::appendIssue($issues, self::ISSUE_ERRORS, $line, $msg);
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
            DateTimeZoneHelper::formatDurationHuman($remainingSeconds),
            date('H:i', $etaTime)
        );
    }

    /**
     * Проверка заголовка CSV.
     *
     * Правило:
     * - Если в шапке < 9 столбцов — это ERROR (файл невалиден).
     * - Если >= 9 — ошибки по шапке НЕ блокируют файл:
     *   * неправильные названия / лишние пробелы / несовпадение количества (>=9) => WARNING.
     */
    public static function validateHeaderRow(array $row): array
    {
        $errors   = [];
        $warnings = [];

        $expected = self::getExpectedHeader(); // 9 колонок
        $exp      = count($expected);

        $colsRaw = count($row);

        // Фаталим ТОЛЬКО если столбцов меньше ожидаемого минимума.
        if ($colsRaw < $exp) {
            $errors[] = sprintf(
                'Количество столбцов в шапке должно быть минимум %d, сейчас %d.',
                $exp,
                $colsRaw
            );
            return ['errors' => $errors, 'warnings' => $warnings];
        }

        // Любое "не равно 9", но >=9 — это предупреждение (не блокирует файл по шапке).
        if ($colsRaw !== $exp) {
            $warnings[] = sprintf(
                'Количество столбцов в шапке обычно %d, сейчас %d. Это предупреждение.',
                $exp,
                $colsRaw
            );
        }

        // Проверяем первые 9 колонок: пробелы/несовпадения — только WARNING.
        $max = min($colsRaw, $exp);
        for ($i = 0; $i < $max; $i++) {
            $raw     = (string)$row[$i];
            $trimmed = trim($raw);

            // Лишние пробелы — warning
            if ($raw !== $trimmed) {
                $warnings[] = sprintf(
                    'Лишние пробелы в названии колонки %d: ожидалось "%s", получено "%s"',
                    $i + 1,
                    $expected[$i],
                    $raw
                );
            }

            // Неверное имя — warning
            if ($trimmed !== $expected[$i]) {
                $warnings[] = sprintf(
                    'Неверное имя колонки %d: ожидалось "%s", получено "%s". Это предупреждение.',
                    $i + 1,
                    $expected[$i],
                    $trimmed
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
        array &$issues,
        array &$alreadyRead,
        $countryCode,
        int $countryFileId,
        bool &$isFileOK
    ): array {
        $oldLine   = null;
        $rowStatus = [];
        $rangeContext = null;

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
            self::appendIssue($issues, self::ISSUE_ERRORS, $lineNumber, 'Пустая строка в файле не допускается.');
            foreach ($row as $idx => $_) {
                $rowStatus[$idx] = self::STATUS_ERROR;
            }
            $isFileOK = false;
            return [$rowStatus, null, $rangeContext];
        }

        // --- Количество столбцов ---
        $cols = count($row);
        if ($cols !== $expectedCols) {
            $columnsError = sprintf(
                'Количество столбцов в строке должно быть %d, сейчас %d.',
                $expectedCols,
                $cols
            );

            // Логируем несоответствие колонок только один раз на весь файл
            if (!self::hasIssue($issues[self::ISSUE_ERRORS], $columnsError)) {
                self::appendIssue($issues, self::ISSUE_ERRORS, $lineNumber, $columnsError);
            }
            foreach ($row as $idx => $_) {
                $rowStatus[$idx] = self::STATUS_ERROR;
            }
            $isFileOK = false;
            return [$rowStatus, null, $rangeContext];
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
                self::appendIssue($issues, self::ISSUE_ERRORS, $lineNumber, rtrim($text));
                $isFileOK = false;
                return [$rowStatus, null, $rangeContext];
            }

            // --- Дополнительные проверки ---
            $extraErrors = [];
            $extraWarnings = [];

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

            if (ctype_digit($ndc) && ctype_digit($fromSn) && ctype_digit($toSn)) {
                $typeBucket = ctype_digit((string)$typeId) ? (string)$typeId : 'unknown';
                $rangeContext = [
                    'ccKey' => (string)$numberRangeImport->country_prefix,
                    'typeBucket' => $typeBucket,
                    'ndc' => $ndc,
                    'from' => $fromSn,
                    'to' => $toSn,
                ];
            }

            // --- Запись ошибок ---
            if ($extraErrors) {
                $isFileOK = false;
                foreach ($extraErrors as $error) {
                    self::appendIssue($issues, self::ISSUE_ERRORS, $lineNumber, $error);
                }
            }
            if ($extraWarnings) {
                foreach ($extraWarnings as $warning) {
                    self::appendIssue($issues, self::ISSUE_WARNINGS, $lineNumber, $warning);
                }
            }

            if (isset($alreadyRead[$key])) {
                $oldLine = $alreadyRead[$key];

                self::appendIssue(
                    $issues,
                    self::ISSUE_ALREADY_READ,
                    $lineNumber,
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
                    )
                );
            }

            $alreadyRead[$key] = $lineNumber;

        }

        return [$rowStatus, $oldLine, $rangeContext];
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

    private static function prepareRangeContext(?array $rangeContext, array &$rangesByPrefix, array &$segmentsMeta, int $lineNumber): void
    {
        if (!$rangeContext) {
            return;
        }

        $ccKey = $rangeContext['ccKey'];
        $typeBucket = $rangeContext['typeBucket'];
        $ndc = $rangeContext['ndc'];
        $fromSn = $rangeContext['from'];
        $toSn = $rangeContext['to'];

        if (!isset($rangesByPrefix[$ccKey])) {
            $rangesByPrefix[$ccKey] = [];
        }

        if (!array_key_exists($typeBucket, $rangesByPrefix[$ccKey])) {
            $rangesByPrefix[$ccKey][$typeBucket] = [];
        }

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

    public static function isHeaderRow(array $row): bool
    {
        return !ctype_digit((string)$row[0]);
    }

    public static function initIssues(): array
    {
        return [
            self::ISSUE_ERRORS => [],
            self::ISSUE_WARNINGS => [],
            self::ISSUE_ALREADY_READ => [],
        ];
    }

    private static function appendIssue(array &$issues, string $type, int $lineNumber, string $message): void
    {
        if (!isset($issues[$type][$lineNumber])) {
            $issues[$type][$lineNumber] = [];
        }

        $issues[$type][$lineNumber][] = $message;
    }

    private static function hasIssue(array $bucket, string $needle): bool
    {
        foreach ($bucket as $messages) {
            if (in_array($needle, $messages, true)) {
                return true;
            }
        }

        return false;
    }

}