<?php

namespace app\classes\helpers\file;

use app\classes\Singleton;

class SortCsvFileHelper extends Singleton
{
    public int $sortedColumnIdx = 0;

    public function sortFile($inputFile, $outputFile, $isHasHeader = true, $delimiter = ';', $sortedColumnIdx = 0, $chunkSize = 1000000)
    {
        $this->sortedColumnIdx = $sortedColumnIdx;

        // Открываем исходный файл
        $inputHandle = fopen($inputFile, 'r');
        if (!$inputHandle) {
            throw new \InvalidArgumentException("Не удалось открыть исходный файл");
        }

        // Читаем заголовок (если есть)
        $header = $isHasHeader ? fgetcsv($inputHandle, 0, $delimiter) : null;

        $tempFiles = [];
        $chunk = [];

        // Чтение и сортировка чанков
        while (($row = fgetcsv($inputHandle, 0, $delimiter)) !== false) {
            $chunk[] = $row;
            if (count($chunk) >= $chunkSize) {
                $tempFiles[] = $this->sortAndSaveChunk($chunk, $delimiter);
                $chunk = [];
            }
        }

        // Обработка последнего чанка
        if (!empty($chunk)) {
            $tempFiles[] = $this->sortAndSaveChunk($chunk, $delimiter);
        }

        fclose($inputHandle);

        // Слияние отсортированных чанков
        $this->mergeChunks($tempFiles, $outputFile, $header, $delimiter);

        // Удаление временных файлов
        foreach ($tempFiles as $tempFile) {
            unlink($tempFile);
        }
    }

    public function sortAndSaveChunk(&$chunk, $delimiter)
    {
        // Сортировка чанка по первому столбцу
        usort($chunk, function($a, $b) {
            return strcmp($a[$this->sortedColumnIdx], $b[$this->sortedColumnIdx]);
        });

        // Создание временного файла
        $tempFile = tempnam(sys_get_temp_dir(), 'csvsort_');
        $handle = fopen($tempFile, 'w');

        foreach ($chunk as $row) {
            fputcsv($handle, $row, $delimiter);
        }

        fclose($handle);
        return $tempFile;
    }


    public function mergeChunks($tempFiles, $outputFile, $header, $delimiter) {
        // Открываем выходной файл
        $outputHandle = fopen($outputFile, 'w');
        if (!$outputHandle) {
            throw new \InvalidArgumentException("Не удалось создать выходной файл");
        }

        // Записываем заголовок
        if ($header !== null) {
            fputcsv($outputHandle, $header, $delimiter);
        }

        // Инициализация дескрипторов и буфера
        $handles = [];
        $buffer = [];

        foreach ($tempFiles as $tempFile) {
            $handle = fopen($tempFile, 'r');
            $handles[] = $handle;
            $row = fgetcsv($handle, 0, $delimiter);
            $buffer[] = $row;
        }

        // Процесс слияния
        while (!empty($buffer)) {
            // Находим минимальный элемент
            $minVal = null;
            $minIndex = -1;

            foreach ($buffer as $index => $row) {
                if ($row === false) continue;

                if ($minIndex === -1 || strcmp($row[$this->sortedColumnIdx], $minVal) < 0) {
                    $minIndex = $index;
                    $minVal = $row[0];
                }
            }

            if ($minIndex === -1) break;

            // Записываем строку
            fputcsv($outputHandle, $buffer[$minIndex], $delimiter);

            // Читаем следующую строку из выбранного файла
            $nextRow = fgetcsv($handles[$minIndex], 0, $delimiter);
            if ($nextRow !== false) {
                $buffer[$minIndex] = $nextRow;
            } else {
                unset($buffer[$minIndex]);
                fclose($handles[$minIndex]);
                unset($handles[$minIndex]);

                // Переиндексация массивов
                $buffer = array_values($buffer);
                $handles = array_values($handles);
            }
        }

        fclose($outputHandle);
    }
}