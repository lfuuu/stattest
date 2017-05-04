<?php

namespace app\widgets\GridViewExport\drivers;

use Yii;
use yii\base\Component;
use yii\base\Exception;

class CsvDriver extends Component implements ExportDriver
{

    private static
        $folder = '@runtime/',
        $delimiter = ';';

    /**
     * @return string
     */
    public function getName()
    {
        return 'CSV';
    }

    /**
     * @return string
     */
    public function getMimeType()
    {
        return 'application/csv';
    }

    /**
     * @return string
     */
    public function getExtension()
    {
        return '.csv';
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return 'glyphicon glyphicon-floppy-open';
    }

    /**
     * @param int $key
     * @param array $columns
     * @return boolean|int
     */
    public function createHeader($key, $columns = [])
    {
        $fileName = Yii::getAlias(self::$folder) . $key . $this->extension;

        if ($file = fopen($fileName, 'a+')) {
            fputcsv($file, $columns, self::$delimiter);
            fclose($file);

            return $key;
        }

        return false;
    }

    /**
     * @param int $key
     * @param array $rows
     * @throws Exception
     */
    public function setData($key, $rows = [])
    {
        $fileName = Yii::getAlias(self::$folder) . $key . $this->extension;

        if (!file_exists($fileName) || !($file = fopen($fileName, 'a+'))) {
            throw new Exception('Export file "' . $fileName . '" not found');
        }

        foreach ($rows as $row) {
            fputcsv($file, $row, self::$delimiter);
        }

        fclose($file);
    }

    /**
     * @param int $key
     * @param bool|true $deleteAfter
     */
    public function fetchFile($key, $deleteAfter = true)
    {
        $fileName = Yii::getAlias(self::$folder) . $key . $this->extension;
        $file = file_get_contents($fileName);

        if ($deleteAfter) {
            @unlink($fileName);
        }

        return $file;
    }

}