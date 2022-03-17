<?php

namespace app\widgets\GridViewExport\drivers;

use Yii;
use yii\base\Component;
use yii\base\Exception;
use yii\base\InvalidParamException;

class CsvDriver extends Component implements ExportDriver
{

    private static
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
     * @throws InvalidParamException
     */
    public function createHeader($key, $columns = [])
    {
        $fileName = $this->getBasePatch() . $key . $this->extension;

        if ($file = fopen($fileName, 'a+')) {
            // Append BOM to fix UTF-8 in "Microsoft Office Excel"
            fwrite($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
            fputcsv($file, $columns, self::$delimiter);
            fclose($file);

            return $key;
        }

        return false;
    }

    /**
     * @param int $key
     * @param array $rows
     * @throws InvalidParamException
     * @throws Exception
     */
    public function setData($key, $rows = [])
    {
        $fileName = $this->getBasePatch() . $key . $this->extension;

        if (!file_exists($fileName) || !($file = fopen($fileName, 'a+'))) {
            throw new Exception('Export file "' . $fileName . '" not found');
        }

        foreach ($rows as $row) {
            // Format price field for "Microsoft Office Excel"
            $row = array_map(function ($column) {
                if (is_numeric($column)) {
                    $column = str_replace('.', ',', $column);
                }
                return $column;
            }, $row);

            fputcsv($file, $row, self::$delimiter);
        }

        fclose($file);
    }

    /**
     * @param int $key
     * @param bool|true $deleteAfter
     * @return string
     * @throws InvalidParamException
     */
    public function fetchFile($key, $deleteAfter = true)
    {
        $fileName = $this->getBasePatch() . $key . $this->extension;
        $file = file_get_contents($fileName);

        if ($deleteAfter) {
            @unlink($fileName);
        }

        return $file;
    }

    public function getBasePatch()
    {
        return rtrim(Yii::$app->params['STORE_PATH'], '/') . '/runtime/';
    }

}