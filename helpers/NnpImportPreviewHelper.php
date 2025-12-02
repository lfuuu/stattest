<?php

namespace app\helpers;

use app\modules\nnp\media\ImportServiceUploaded;
use yii\helpers\Html;
use yii\helpers\Url;

class NnpImportPreviewHelper
{
    /**
     * Ожидаемые заголовки CSV.
     *
     * @return array
     */
    public static function getExpectedHeader()
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
     * Определение разделителя CSV (',' или ';') по первой строке.
     *
     * @param resource $handle
     * @return string
     */
    public static function detectDelimiter($handle)
    {
        $delimiter = ';';

        $pos = ftell($handle);
        $line = fgets($handle);
        if ($line !== false) {
            $hasSemicolon = strpos($line, ';') !== false;
            $hasComma     = strpos($line, ',') !== false;

            if ($hasComma && !$hasSemicolon) {
                $delimiter = ',';
            } elseif ($hasSemicolon && !$hasComma) {
                $delimiter = ';';
            }
        }
        fseek($handle, $pos);

        return $delimiter;
    }

    /**
     * Доп. проверка шапки (1-я строка, если это именно заголовок).
     *
     * @param array $row
     * @return array ['errors' => [], 'warnings' => []]
     */
    public static function validateHeaderRow(array $row)
    {
        $errors   = [];
        $warnings = [];

        $expectedHeader = self::getExpectedHeader();
        $trimmed        = array_map('trim', $row);

        $cols    = count($trimmed);
        $expCols = count($expectedHeader);

        if ($cols !== $expCols) {
            $errors[] = sprintf(
                'Количество столбцов в шапке должно быть %d, сейчас %d.',
                $expCols,
                $cols
            );
        }

        $max = min($cols, $expCols);
        for ($i = 0; $i < $max; $i++) {
            if ($trimmed[$i] !== $expectedHeader[$i]) {
                $errors[] = sprintf(
                    'Неверное имя колонки %d: ожидалось "%s", получено "%s"',
                    $i + 1,
                    $expectedHeader[$i],
                    $trimmed[$i]
                );
            }
        }

        // дополнительных колонок быть не должно, поэтому отдельный warning не нужен

        return ['errors' => $errors, 'warnings' => $warnings];
    }

    /**
     * Вставка диапазона в интервал-дерево (по полному национальному номеру).
     *
     * @param array|null $node
     * @param int $from
     * @param int $to
     * @param int $line
     * @param string $ndc
     * @param string $fromSn
     * @param string $toSn
     * @return array
     */
    protected static function rangesTreeInsert($node, $from, $to, $line, $ndc, $fromSn, $toSn)
    {
        if ($node === null) {
            return [
                'from'     => $from,
                'to'       => $to,
                'ndc'      => $ndc,
                'from_sn'  => $fromSn,
                'to_sn'    => $toSn,
                'line'     => $line,
                'max'      => $to,
                'left'     => null,
                'right'    => null,
            ];
        }

        if ($from < $node['from']) {
            $node['left'] = self::rangesTreeInsert($node['left'], $from, $to, $line, $ndc, $fromSn, $toSn);
        } else {
            $node['right'] = self::rangesTreeInsert($node['right'], $from, $to, $line, $ndc, $fromSn, $toSn);
        }

        $leftMax  = isset($node['left']['max']) ? $node['left']['max'] : $node['to'];
        $rightMax = isset($node['right']['max']) ? $node['right']['max'] : $node['to'];

        $node['max'] = max($node['to'], $leftMax, $rightMax);

        return $node;
    }

    /**
     * Поиск всех пересекающихся диапазонов в дереве.
     *
     * @param array|null $node
     * @param int $from
     * @param int $to
     * @param array $result
     * @return void
     */
    protected static function rangesTreeSearch($node, $from, $to, array &$result)
    {
        if ($node === null) {
            return;
        }

        if ($node['left'] !== null && $node['left']['max'] >= $from) {
            self::rangesTreeSearch($node['left'], $from, $to, $result);
        }

        if ($node['from'] <= $to && $node['to'] >= $from) {
            $result[] = $node;
        }

        if ($node['right'] !== null && $node['right']['max'] >= $from) {
            self::rangesTreeSearch($node['right'], $from, $to, $result);
        }
    }

    /**
     * Проверка одной строки (кроме шапки).
     *
     * Возвращает:
     *  [$rowStatus, $isFileOK, $errorLines, $warningLines, $oldLine, $alreadyRead, $rangesByPrefix]
     *
     * @param int $lineNumber
     * @param array $row
     * @param ImportServiceUploaded $importServiceUploaded
     * @param array $errorLines
     * @param array $warningLines
     * @param array $alreadyRead
     * @param array $rangesByPrefix
     * @param int|string $countryCode
     * @param int $countryFileId
     * @param bool $isFileOK
     * @return array
     */
    public static function checkRow(
        $lineNumber,
        array $row,
        ImportServiceUploaded $importServiceUploaded,
        array $errorLines,
        array $warningLines,
        array $alreadyRead,
        array $rangesByPrefix,
        $countryCode,
        $countryFileId,
        $isFileOK
    ) {
        $oldLine   = null;
        $rowStatus = [];

        $expectedHeader = self::getExpectedHeader();
        $expectedCols   = count($expectedHeader);

        // 0. Пустая строка — отдельная ошибка
        $allEmpty = true;
        foreach ($row as $cell) {
            if (trim($cell) !== '') {
                $allEmpty = false;
                break;
            }
        }
        if ($allEmpty) {
            $text = 'Пустая строка в файле не допускается.';
            $errorLines[$lineNumber] = $text;
            foreach ($row as $idx => $_) {
                $rowStatus[$idx] = true;
            }
            $isFileOK = false;

            return [$rowStatus, $isFileOK, $errorLines, $warningLines, $oldLine, $alreadyRead, $rangesByPrefix];
        }

        // 1. Строгое количество столбцов
        $cols = count($row);
        if ($cols !== $expectedCols) {
            $text = sprintf(
                'Количество столбцов в строке должно быть %d, сейчас %d.',
                $expectedCols,
                $cols
            );
            $errorLines[$lineNumber] = $text;
            foreach ($row as $idx => $_) {
                $rowStatus[$idx] = true;
            }
            $isFileOK = false;

            return [$rowStatus, $isFileOK, $errorLines, $warningLines, $oldLine, $alreadyRead, $rangesByPrefix];
        }

        // шапка обрабатывается отдельно во вьюхе
        if (!($lineNumber == 1 && !is_numeric($row[0]))) {
            $numberRangeImport = $importServiceUploaded->getNumberRangeByRow($row);
            $rowStatus         = $importServiceUploaded->getRowHasError($numberRangeImport);

            $key = sprintf(
                '(%s) %s %s - %s %s',
                $numberRangeImport->country_prefix,
                $numberRangeImport->ndc_str,
                $numberRangeImport->number_from,
                $numberRangeImport->ndc_str,
                $numberRangeImport->number_to
            );

            if ($errors = $numberRangeImport->getErrors()) {
                // старые ошибки из модели
                $isFileOK = false;

                $text = '';
                foreach ($errors as $field => $errorList) {
                    foreach ($errorList as $value) {
                        $text .= sprintf("%s: %s", $field, $value) . PHP_EOL;
                    }
                }
                $errorLines[$lineNumber] = $text;
            } else {
                // наши дополнительные проверки
                $extraErrors   = [];
                $extraWarnings = [];

                // 1. Лишние пробелы во всех 9 полях
                foreach ($row as $idx => $cell) {
                    if ($cell !== '' && $cell !== trim($cell)) {
                        $fieldName = $expectedHeader[$idx] ?? ('#' . $idx);
                        $extraErrors[] = sprintf(
                            'Поле %s содержит лишние пробелы в начале или в конце.',
                            $fieldName
                        );
                        $rowStatus[$idx] = true;
                    }
                }

                // 2. Обязательность полей (CC,NDC,Type,Type_id,From,To,Operator)
                $cc       = isset($row[0]) ? trim($row[0]) : '';
                $ndc      = isset($row[1]) ? trim($row[1]) : '';
                $type     = isset($row[2]) ? trim($row[2]) : '';
                $typeId   = $numberRangeImport->ndc_type_id;
                $typeIdSn = isset($row[3]) ? trim($row[3]) : '';
                $fromSn   = (string)$numberRangeImport->number_from;
                $toSn     = (string)$numberRangeImport->number_to;
                $operator = isset($row[8]) ? trim($row[8]) : '';

                if ($cc === '') {
                    $extraErrors[] = 'CC (префикс страны) не может быть пустым.';
                    $rowStatus[0]  = true;
                }

                // NDC обязателен, кроме коротких номеров (Type_id = 6)
                if ((string)$typeId !== '6') {
                    if ($ndc === '') {
                        $extraErrors[] = 'NDC не может быть пустым.';
                        $rowStatus[1]  = true;
                    }
                }

                if ($type === '') {
                    $extraErrors[] = 'Type не может быть пустым.';
                    $rowStatus[2]  = true;
                }

                if ($typeIdSn === '' || $typeId === null || $typeId === '') {
                    $extraErrors[] = 'Type_id (ID типа NDC) не может быть пустым.';
                    $rowStatus[3]  = true;
                }

                if ($fromSn === '' || !ctype_digit($fromSn)) {
                    $extraErrors[] = 'From должно быть числовой строкой.';
                    $rowStatus[4]  = true;
                }

                if ($toSn === '' || !ctype_digit($toSn)) {
                    $extraErrors[] = 'To должно быть числовой строкой.';
                    $rowStatus[5]  = true;
                }

                if (ctype_digit($fromSn) && ctype_digit($toSn)) {
                    $fromInt = (int)$fromSn;
                    $toInt   = (int)$toSn;
                    if ($fromInt > $toInt) {
                        $extraErrors[] = 'From не может быть больше To.';
                        $rowStatus[4]  = true;
                        $rowStatus[5]  = true;
                    }
                }

                if ($operator === '') {
                    $extraErrors[] = 'Operator не может быть пустым.';
                    $rowStatus[8]  = true;
                }

                // 3. Region/City пустые для негеографических номеров (Type_id != 1)
                $region = isset($row[6]) ? trim($row[6]) : '';
                $city   = isset($row[7]) ? trim($row[7]) : '';

                if ((int)$typeId !== 1) {
                    if ($region !== '' || $city !== '') {
                        $extraErrors[] = 'Для негеографических номеров поля Region и City должны быть пустыми.';
                        if ($region !== '') {
                            $rowStatus[6] = true;
                        }
                        if ($city !== '') {
                            $rowStatus[7] = true;
                        }
                    }
                }

                // 4. Проверка пересечения диапазонов по CC
                $ccKey = (string)$numberRangeImport->country_prefix;

                if (!isset($rangesByPrefix[$ccKey])) {
                    $rangesByPrefix[$ccKey] = null;
                }

                if (ctype_digit($ndc) && ctype_digit($fromSn) && ctype_digit($toSn)) {
                    // полный национальный номер как NDC + From/To (строка)
                    $fullFromStr = $ndc . $fromSn;
                    $fullToStr   = $ndc . $toSn;

                    $fullFrom = (int)$fullFromStr;
                    $fullTo   = (int)$fullToStr;

                    $overlaps = [];
                    self::rangesTreeSearch($rangesByPrefix[$ccKey], $fullFrom, $fullTo, $overlaps);

                    foreach ($overlaps as $prev) {
                        $msgCurrent = sprintf(
                            'Пересечение диапазонов: (CC %s) NDC %s %s-%s пересекается с NDC %s %s-%s (строка %d)',
                            $ccKey,
                            $ndc,
                            $fromSn,
                            $toSn,
                            $prev['ndc'],
                            $prev['from_sn'],
                            $prev['to_sn'],
                            $prev['line']
                        );

                        $msgPrev = sprintf(
                            'Пересечение диапазонов: (CC %s) NDC %s %s-%s пересекается с NDC %s %s-%s (строка %d)',
                            $ccKey,
                            $prev['ndc'],
                            $prev['from_sn'],
                            $prev['to_sn'],
                            $ndc,
                            $fromSn,
                            $toSn,
                            $lineNumber
                        );

                        // ошибка для текущей строки
                        $extraErrors[] = $msgCurrent;
                        $rowStatus[4]  = true;
                        $rowStatus[5]  = true;

                        // ошибка для предыдущей строки (в списке ошибок)
                        $errorLines[$prev['line']] =
                            (isset($errorLines[$prev['line']]) && $errorLines[$prev['line']] !== '' ? $errorLines[$prev['line']] . PHP_EOL : '') .
                            $msgPrev;

                        $isFileOK = false;
                    }

                    $rangesByPrefix[$ccKey] = self::rangesTreeInsert(
                        $rangesByPrefix[$ccKey],
                        $fullFrom,
                        $fullTo,
                        $lineNumber,
                        $ndc,
                        $fromSn,
                        $toSn
                    );
                }

                // 5. старый warning про пустой NDC мы больше не используем (заменён на ошибку/исключение для type_id=6)

                if (!empty($extraErrors)) {
                    $isFileOK = false;
                    $errorLines[$lineNumber] =
                        (isset($errorLines[$lineNumber]) && $errorLines[$lineNumber] !== '' ? $errorLines[$lineNumber] . PHP_EOL : '') .
                        implode(PHP_EOL, $extraErrors);
                }

                if (!empty($extraWarnings)) {
                    $warningLines[$lineNumber] =
                        (isset($warningLines[$lineNumber]) && $warningLines[$lineNumber] !== '' ? $warningLines[$lineNumber] . PHP_EOL : '') .
                        implode(PHP_EOL, $extraWarnings);
                }

                // 6. старая логика дублей диапазона по key
                if (isset($alreadyRead[$key])) {
                    $oldLine = $alreadyRead[$key];

                    $warningLines[$lineNumber] =
                        (isset($warningLines[$lineNumber]) && $warningLines[$lineNumber] !== '' ? $warningLines[$lineNumber] . PHP_EOL : '') .
                        ("Диапазон $key уже добавлен в " . Html::a(
                            'строке ' . $oldLine,
                            Url::to([
                                '/nnp/import/step3',
                                'countryCode' => $countryCode,
                                'fileId'      => $countryFileId,
                                'offset'      => $oldLine,
                                'limit'       => min($lineNumber - $oldLine, 100),
                            ]) . '#line' . $oldLine
                        ));
                }
            }

            $alreadyRead[$key] = $lineNumber;
        }

        return [$rowStatus, $isFileOK, $errorLines, $warningLines, $oldLine, $alreadyRead, $rangesByPrefix];
    }
}
