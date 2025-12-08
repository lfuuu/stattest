<?php

namespace app\modules\nnp\classes\helpers;

use app\modules\nnp\media\ImportServiceUploaded;
use yii\helpers\Html;
use yii\helpers\Url;
use app\modules\nnp\classes\helpers\RangesTreeHelper;

class ImportPreviewHelper
{
    public const STATUS_OK      = 0;
    public const STATUS_ERROR   = 1;
    public const STATUS_WARNING = 2;

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
        array $errorLines,
        array $warningLines,
        array $alreadyRead,
        array $rangesByPrefix,
        $countryCode,
        int $countryFileId,
        bool $isFileOK
    ): array {
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
            return [$rowStatus, false, $errorLines, $warningLines, null, $alreadyRead, $rangesByPrefix];
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
            return [$rowStatus, false, $errorLines, $warningLines, null, $alreadyRead, $rangesByPrefix];
        }

        // --- Обработка данных ---
        if (!($lineNumber == 1 && !is_numeric($row[0]))) {

            $numberRangeImport = $importServiceUploaded->getNumberRangeByRow($row);
            $rowStatus         = array_map(
                function ($hasError) {
                    return $hasError ? self::STATUS_ERROR : self::STATUS_OK;
                },
                $importServiceUploaded->getRowHasError($numberRangeImport)
            );

            $key = sprintf(
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
                return [$rowStatus, false, $errorLines, $warningLines, null, $alreadyRead, $rangesByPrefix];
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

            // --- Пересечения диапазонов ---
            $ccKey = (string)$numberRangeImport->country_prefix;

            if (!isset($rangesByPrefix[$ccKey])) {
                $rangesByPrefix[$ccKey] = null;
            }

            if (ctype_digit($ndc) && ctype_digit($fromSn) && ctype_digit($toSn)) {

                $fullFrom = (int)($ndc . $fromSn);
                $fullTo   = (int)($ndc . $toSn);

                $overlaps = [];
                RangesTreeHelper::search($rangesByPrefix[$ccKey], $fullFrom, $fullTo, $overlaps);

                foreach ($overlaps as $prev) {
                    $msgCurr = sprintf(
                        'Пересечение диапазонов: (CC %s) NDC %s %s-%s пересекается с NDC %s %s-%s (строка %d)',
                        $ccKey,
                        $ndc, $fromSn, $toSn,
                        $prev['ndc'], $prev['from_sn'], $prev['to_sn'],
                        $prev['line']
                    );

                    $msgPrev = sprintf(
                        'Пересечение диапазонов: (CC %s) NDC %s %s-%s пересекается с NDC %s %s-%s (строка %d)',
                        $ccKey,
                        $prev['ndc'], $prev['from_sn'], $prev['to_sn'],
                        $ndc, $fromSn, $toSn,
                        $lineNumber
                    );

                    $extraErrors[] = $msgCurr;
                    $rowStatus[4] = $rowStatus[5] = self::STATUS_ERROR;

                    $errorLines[$prev['line']] =
                        (isset($errorLines[$prev['line']]) ? $errorLines[$prev['line']] . PHP_EOL : '') .
                        $msgPrev;

                    $isFileOK = false;
                }

                $rangesByPrefix[$ccKey] = RangesTreeHelper::insert(
                    $rangesByPrefix[$ccKey],
                    $fullFrom,
                    $fullTo,
                    $lineNumber,
                    $ndc,
                    $fromSn,
                    $toSn
                );
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
                    "Диапазон $key уже добавлен в строке " .
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

        return [$rowStatus, $isFileOK, $errorLines, $warningLines, $oldLine, $alreadyRead, $rangesByPrefix];
    }
}
