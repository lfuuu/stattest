<?php

namespace app\classes\grid;

use DateTime;
use app\helpers\DateTimeZoneHelper;
use Closure;
use Exception;
use Yii;
use yii\helpers\BaseInflector;
use yii\helpers\StringHelper;

class ExportGridView extends GridView
{
    public $path;
    public $fromDateAttribute;
    public $toDateAttribute;
    public $tmpFiles;
    public $downloadedPartsCount;
    public $totalParts;
    public $statusManagerObject;

    /**
     * @throws Exception
     */
    public function init()
    {
        $this->formatter = Yii::$app->formatter;
        $this->path = static::getPath();
        $this->fromDateAttribute = 'connect_time_from';
        $this->toDateAttribute = 'connect_time_to';
        $this->initializeColumns();
        $this->downloadedPartsCount = 0;
        $this->totalParts = 1;
        $this->tmpFiles = [];
    }

    /**
     * Инициализация колонок
     *
     * @throws Exception
     */
    protected function initializeColumns()
    {
        $columns = $this->getColumns();
        foreach ($columns as $columnData) {
            if (isset($columnData['value'])) {
                $this->columns[] = $columnData['value'];
            } elseif (isset($columnData['attribute'])) {
                if (isset($columnData['class'])) {
                    $class = $columnData['class'];
                    unset($columnData['class']);
                    $constructorParams = $columnData + ['grid' => ($this)];
                    $this->columns[] = new $class($constructorParams);
                } else {
                    $this->columns[] = $columnData['attribute'];
                }
            }
        }
    }

    /**
     * Получить строку значений
     *
     * @param $model
     * @return array
     */
    protected function getValuesRow($model)
    {
        $result = [];
        foreach ($this->columns as $column) {
            if (is_object($column) && method_exists($column, 'renderDataCell')) {
                $result[] = strip_tags($column->renderDataCell($model, $column->attribute, 0));
            } elseif ($column instanceof Closure) {
                $result[] = strip_tags($column($model));
            } else {
                $result[] = isset($model[$column]) ? $model[$column] : '';
            }
        }
        return $result;
    }

    /**
     * Получить имя файла для экспорта
     *
     * @return string
     */
    public function getFilename()
    {
        $name = StringHelper::basename(get_class($this->filterModel));
        $name = (($substr = strstr($name, 'Filter', true)) !== false) ? $substr : $name;
        $name = BaseInflector::underscore($name);
        return $name  . '_' . time() . '_' . substr(md5(rand(1, 10000)), 0, 5) . '.csv';
    }

    /**
     * Формирует csv отчет
     *
     * @return string
     * @throws Exception
     */
    public function export()
    {
        if (!$this->filterModel->hasMethod('search')) {
            throw new Exception('Функция search не найдена');
        }

        if (!$this->isHeavyReport()) {
            return $this->createFile();
        } else {
            $dateFrom = new DateTime($this->filterModel->{$this->fromDateAttribute});
            $dateFromPlusMonth = (new DateTime($this->filterModel->{$this->fromDateAttribute}))->modify('+1 month');
            $dateTo = new DateTime($this->filterModel->{$this->toDateAttribute});

            while ($dateFrom->diff($dateTo)->m >= 1) {
                $this->filterModel->{$this->fromDateAttribute} = $dateFrom->format(DateTimeZoneHelper::DATETIME_FORMAT);
                $this->filterModel->{$this->toDateAttribute} = $dateFromPlusMonth->format(DateTimeZoneHelper::DATETIME_FORMAT);

                $this->tmpFiles[] = $this->createFile();
                if ($this->statusManagerObject) {
                    $this->statusManagerObject->setTmpFiles(json_encode($this->tmpFiles));
                }

                $dateFromPlusMonth->modify('+1 month');
                $dateFrom->modify('+1 month');
            }

            if ($dateFrom->diff($dateTo)->d >= 1) {
                $this->filterModel->{$this->fromDateAttribute} = $dateFrom->format(DateTimeZoneHelper::DATETIME_FORMAT);
                $this->filterModel->{$this->toDateAttribute} = $dateTo->format(DateTimeZoneHelper::DATETIME_FORMAT);
                $this->tmpFiles[] = $this->createFile();
                if ($this->statusManagerObject) {
                    $this->statusManagerObject->setTmpFiles(json_encode($this->tmpFiles));
                }
            }
            return $this->assembleReport();
        }
    }

    /**
     * Записать строку в файл
     *
     * @param bool|resource $handle
     * @param array $row
     * @throws Exception
     */
    protected function writeRow($handle, $row)
    {
        if (fputcsv($handle, $row) === false) {
            throw new Exception('Ошибка записи данных');
        }
    }

    /**
     * Вернуть путь до папки, где хранятся скачанные отчеты
     *
     * @return string
     */
    public static function getPath()
    {
        $path = Yii::getAlias('@runtime/reports/');
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        return $path;
    }

    /**
     * Получить заголовки для csv файла
     *
     * @return array
     * @throws Exception
     */
    protected function getHeaders()
    {
        $columns = $this->getColumns();
        $attributeLabels = $this->filterModel->attributeLabels();
        $headers = [];
        foreach ($columns as $column) {
            if (isset($column['attribute'])) {
                $headers[] = isset($attributeLabels[$column['attribute']]) ? $attributeLabels[$column['attribute']] : $column['attribute'];
            } elseif (isset($column['label'])) {
                $headers[] = $column['label'];
            } else {
                $headers[] = '';
            }
        }
        return $headers;
    }

    /**
     * Получить массив колонок для GridView
     *
     * @return array
     * @throws Exception
     */
    protected function getColumns()
    {
        if (!$this->filterModel || !$this->filterModel->hasMethod('getColumns')) {
            throw new Exception('Функция getColumns не найдена');
        }
        return $this->filterModel->getColumns();
    }

    /**
     * Создать файл отчета
     *
     * @return string
     * @throws Exception
     */
    protected function createFile()
    {
        $dataProvider = $this->filterModel->search();
        if (!$dataProvider || !isset(class_implements($dataProvider)['yii\data\DataProviderInterface'])) {
            throw new Exception('Неправильный DataProvider');
        }
        $dataProvider->pagination = false;
        $filename = $this->getFilename();

        $fullpath = $this->path . $filename;
        $fileExist = file_exists($fullpath);
        if (($handle = fopen($fullpath, 'a')) === false) {
            throw new Exception('Ошибка открытия файла');
        }

        if (!$fileExist) {
            $this->writeRow($handle, $this->getHeaders());
        }

        $models = array_values($dataProvider->getModels());
        foreach ($models as $model) {
            $this->writeRow($handle, $this->getValuesRow($model));
        }
        fclose($handle);
        ++$this->downloadedPartsCount;
        if ($this->totalParts > 1) {
            echo 'скачано: ' . $this->downloadedPartsCount . '/' . $this->totalParts . PHP_EOL;
        }

        return $filename;
    }

    /**
     * Собрать отчет из частей
     *
     * @throws Exception
     */
    protected function assembleReport()
    {
        $filename = static::getFilename();
        $path = static::getPath();
        $fullPath = $path . $filename;
        foreach ($this->tmpFiles as $tmpFilename) {
            $tmpFullPath = $path . $tmpFilename;
            if ((!is_file($tmpFullPath) || !is_writable($tmpFullPath))) {
                throw new Exception('Файл не существует или нет доступа');
            }
            $this->append($tmpFullPath, $fullPath);
        }

        foreach ($this->tmpFiles as $tmpFilename) {
            $tmpFullPath = $path . $tmpFilename;
            if (!unlink($tmpFullPath)) {
                throw new Exception('Ошибка при удалении временных файлов');
            };
        }

        if (!$this->isPartsDownloaded()) {
            throw new Exception('Не все части отчета были скачаны');
        }

        return $filename;
    }

    /**
     * Получить количество частей, из которых будет составлен отчет
     *
     * @return int
     * @throws Exception
     */
    public function getTotalPartsAmount()
    {
        if (!($dateFrom = $this->filterModel->{$this->fromDateAttribute}) ||
            !($dateTo = $this->filterModel->{$this->toDateAttribute})) {
            throw new Exception('Временной период не определен.');
        }

        $dateTimeFrom = new DateTime($dateFrom);
        $dateTimeTo = new DateTime($dateTo);
        $amount = 1;

        if ($dateTimeFrom->diff($dateTimeTo)->m > 1) {
            $amount = $dateTimeFrom->diff($dateTimeTo)->m;
            $dateTimeFrom->diff($dateTimeTo)->d > 1 && ++$amount;
        }

        return $amount;
    }

    /**
     * Добавить содержимое одного отчета в другой
     *
     * @param $fromFile
     * @param $toFile
     * @throws Exception
     */
    protected function append($fromFile, $toFile)
    {
        $fileExist = file_exists($toFile);

        if (($fromFileHandle = fopen($fromFile, 'r')) === false
            || ($toFileHandle = fopen($toFile, 'a')) === false) {
            throw new Exception('Ошибка при открытии файла');
        }

        if (!$fileExist) {
            $this->writeRow($toFileHandle, $this->getHeaders());
        }

        $counter = 0;
        while (($row = fgetcsv($fromFileHandle)) !== false) {
            if (++$counter == 1) {
                continue;
            }
            $this->writeRow($toFileHandle, $row);
        }
        fclose($fromFileHandle);
        fclose($toFileHandle);
    }


    /**
     * @return bool
     * @throws Exception
     */
    protected function isPartsDownloaded()
    {
        return $this->downloadedPartsCount != 0 && $this->downloadedPartsCount == $this->totalParts;
    }

    /**
     * @return bool
     * @throws Exception
     */
    protected function isHeavyReport()
    {
        if (!$this->filterModel->hasProperty($this->fromDateAttribute)
            || !$this->filterModel->hasProperty($this->toDateAttribute)) {
            return false;
        }
        $this->totalParts = $this->getTotalPartsAmount();
        return $this->totalParts > 1;
    }
}

