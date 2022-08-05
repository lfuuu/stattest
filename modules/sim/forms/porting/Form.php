<?php

namespace app\modules\sim\forms\porting;

use app\helpers\DateTimeZoneHelper;
use app\models\danycom\PhoneHistory;
use app\modules\sim\columns\PhoneHistory\StateColumn;
use Yii;
use yii\base\InvalidParamException;

class Form extends \app\classes\Form
{
    const STORE_SUB_PATH_PORTING = 'files/porting/import';

    protected array $values = [];
    protected int $size = 0;
    protected array $headers = [];

    protected array $inserts = [];
    public string $errorMessage = '';
    public array $warningLines = [];

    public string $path = '';
    public bool $isToSave = false;

    /**
     * Получить путь к хранилищу
     *
     * @return string
     */
    protected static function getBasePath()
    {
        return rtrim(Yii::$app->params['STORE_PATH'], DIRECTORY_SEPARATOR);
    }

    /**
     * Check directory and create it (/tmp/test/dir)
     *
     * @param $dirPath
     */
    protected static function checkDirectory($dirPath)
    {
        if (!is_dir($dirPath)) {
            mkdir($dirPath, 0775, true);
        }
    }

    /**
     * @param $fileName string
     * @return string
     */
    protected static function getDirPath()
    {
        $path = self::getBasePath();

        $subPath = self::STORE_SUB_PATH_PORTING . DIRECTORY_SEPARATOR . date('Y-m');
        $dirData = explode(DIRECTORY_SEPARATOR, trim($subPath, DIRECTORY_SEPARATOR));

        foreach ($dirData as $p) {
            $path .= DIRECTORY_SEPARATOR . $p;
            self::checkDirectory($path);
        }

        return $path . DIRECTORY_SEPARATOR;
    }

    /**
     * @return string
     */
    protected function getFilePath()
    {
        $fileName = sprintf("%s-%s.csv", date('d_H-i-s'), uniqid());

        return self::getDirPath() . $fileName;
    }

    /**
     * Конструктор
     *
     */
    public function init()
    {

    }

    /**
     * @return int
     */
    public function getCount()
    {
        return count($this->inserts);
    }

    /**
     * @param array $file
     * @return string|null
     */
    public function addFile(array $file)
    {
        if (!file_exists($file['tmp_name']) || !is_file($file['tmp_name'])) {
            return null;
        }

        $filePath = $this->getFilePath();
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            return null;
        }

        return $filePath;
    }

    /**
     * @return bool
     * @throws \yii\db\Exception
     */
    public function runPorting()
    {
        $prepare = false;
        try {
            $prepare = $this->preparePorting();
        } catch (\Exception $e) {
            $this->errorMessage = 'Ошибка при парсинге файла: ' . $e->getMessage();
            return false;
        }

        if ($prepare && $this->isToSave) {
            $transaction = PhoneHistory::getDb()->beginTransaction();
            try {
                $this->processInserts();
                $this->renameUploadedFile();
                $transaction->commit();

                return true;
            } catch (\Exception $e) {
                $transaction->rollBack();
                $this->errorMessage = 'Ошибка при сохранении данных: ' . $e->getMessage();

                return false;
            }
        }

        return $prepare;
    }

    protected function preparePorting()
    {
        if (!$this->path) {
            throw new InvalidParamException('путь не указан');
        }

        if (!file_exists($this->path)) {
            throw new InvalidParamException('файл не существует');
        }

        $this->readData();
        return $this->processData();
    }

    protected function readData()
    {
        $filePorting = $this->path;

        $resourceSource = fopen($filePorting,'r');

        $i = 0;
        $this->values = [];
        $this->headers = [];
        $this->size = 0;
        $createdAt =
            (new \DateTime())
                ->format(DateTimeZoneHelper::DATETIME_FORMAT);

        $isDetectSeparator = false;
        while(!feof($resourceSource)){
            if (!$isDetectSeparator) {
                $rowData = fread($resourceSource,100);
                $sep1 = strpos($rowData, ',') ?: 0;
                $sep2 = strpos($rowData, ';') ?: 0;

                if ($sep1 == $sep2) {
                    throw new \Exception('разделитель не определен');
                }

                $separator = $sep1 > $sep2 ? ',' : ';';

                fseek($resourceSource, 0);
                $isDetectSeparator = true;
            }
            $rowData = fgetcsv($resourceSource,0,$separator,'"','"');

            if(empty($rowData[0])){
                continue;
            }

            $rowData = array_map(
                function($item){
                    return preg_replace('/(\n|\r)/','', trim($item));
                },
                $rowData
            );

            if(empty($rowData[0])){
                continue;
            }
            if (!$i++ && !is_numeric($rowData[0])) {
                // Шапка (первая строчка с названиями полей) - пропустить
                $this->headers = $rowData;
                $this->headers[16] = 'created_at';

                continue;
            }

            ++$i;

            $row = $rowData;

            array_splice($row, 3, 1, [$row[3], $row[2]]);
            $row[16] = $createdAt;

            $this->size = max($this->size, count($row));
            $this->values[] = $row;
        }

        fclose($resourceSource);
    }

    /**
     * @return bool
     */
    protected function processData()
    {
        $errors = [];
        $inserts = [];
        $i = 0;
        foreach ($this->values as $item) {
            $i++;

            $item += array_fill(count($item), $this->size - count($item), '');

            // checking phone
            $position = 2;
            $phoneContact = $item[$position];
            if (
                !preg_match('/^\d+$/', $phoneContact)
                || substr($phoneContact, 0, 1) != '9'
                || strlen($phoneContact) != 10
            ) {
                $errors[$i+1][$this->headers[$position]] = 'Неверный номер: ' . $phoneContact;
            }

            // checking state
            $position = 8;
            $state = $item[$position];
            if (!StateColumn::isValid($state)) {
                $errors[$i+1][$this->headers[$position]] = 'Неверный статус: ' . $state;
            }

            $inserts[] = $item;
        }

        $this->warningLines = $errors;
        $this->inserts = $inserts;

        return empty($errors);
    }

    protected function processInserts()
    {
        PhoneHistory::getDb()->createCommand()->batchInsert(
            PhoneHistory::tableName(),
            [
                'process_id',
                'date_request',
                'phone_contact',
                'number', 'phone_ported',
                'process_type',
                'from',
                'to',
                'state',
                'state_current',
                'region',
                'date_ported',
                'last_message',
                'date_sent',
                'last_sender',
                'code',
                'created_at'
            ],
            $this->inserts
        )->execute();
    }

    protected function renameUploadedFile()
    {
        rename($this->path, $this->path . '_');
    }
}
