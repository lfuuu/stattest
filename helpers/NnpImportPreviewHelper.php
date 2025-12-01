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

        if (count($trimmed) < count($expectedHeader)) {
            $errors[] = 'В шапке слишком мало колонок. Ожидается: ' . implode(';', $expectedHeader);
        }

        $max = min(count($trimmed), count($expectedHeader));
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

        if (count($trimmed) > count($expectedHeader)) {
            $warnings[] = 'В шапке есть дополнительные столбцы, которые будут проигнорированы.';
        }

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
     * Проверка одной строки (кроме шапки):
     * - вызывает ImportServiceUploaded (старые проверки)
     * - добавляет новые ошибки/варнинги
     * - ищет пересечения диапазонов номеров по CC
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

                // 1) Лишние пробелы в Type (колонка 2)
                if (isset($row[2]) && $row[2] !== trim($row[2])) {
                    $extraErrors[] = 'Поле Type содержит лишние пробелы в начале или в конце.';
                    $rowStatus[2]  = true;
                }

                // 2) From/To – строковые числа и From <= To
                $fromSn = (string)$numberRangeImport->number_from;
                $toSn   = (string)$numberRangeImport->number_to;

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

                // 3) Обязательность CC / Type_id
                if ($numberRangeImport->country_prefix === '' || $numberRangeImport->country_prefix === null) {
                    $extraErrors[] = 'CC (префикс страны) пустой.';
                    $rowStatus[0]  = true;
                }

                if ($numberRangeImport->ndc_type_id === null || $numberRangeImport->ndc_type_id === '') {
                    $extraErrors[] = 'Type_id (ID типа NDC) пустой.';
                    $rowStatus[3]  = true;
                }

                // 4) Проверка пересечения диапазонов по CC
                $ccKey = (string)$numberRangeImport->country_prefix;
                $ndc   = (string)$numberRangeImport->ndc_str;

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

                // 5) Пустой NDC — предупреждение (как было)
                if (empty($numberRangeImport->ndc_str) && $numberRangeImport->ndc_str != '0') {
                    $extraWarnings[] = 'Пустой NDC - диапазон не будет загружен';
                }

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

                // 6) старая логика дублей диапазона по key
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
