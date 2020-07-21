<?php

namespace app\modules\nnp\models;

use app\classes\model\ActiveRecord;
use app\modules\nnp\media\CountryMedia;
use app\modules\nnp\media\ImportServiceUploaded;
use app\modules\nnp2\media\ImportServiceUploadedNew;
use app\modules\nnp2\models\ImportHistory;
use Yii;
use yii\base\InvalidParamException;

/**
 * @property int $id
 * @property int $country_code
 * @property string $name
 * @property string $comment
 * @property int $user_id
 * @property string $ts
 *
 * @property-read Country $country
 * @property-read CountryMedia $mediaManager
 *
 * @method static CountryFile findOne($condition)
 * @method static CountryFile[] findAll($condition)
 */
class CountryFile extends ActiveRecord
{
    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait {
        getList as getListTrait;
    }

    /**
     * Имена полей
     *
     * @return array [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'country_code' => 'Страна',
            'name' => 'Имя исходного файла',
            'comment' => 'Комментарий',
            'user_id' => 'Кто',
            'ts' => 'Когда',
        ];
    }

    /**
     * Имя таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'nnp.country_files';
    }

    /**
     * Returns the database connection
     *
     * @return \yii\db\Connection
     */
    public static function getDb()
    {
        return Yii::$app->dbPgNnp;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCountry()
    {
        return $this->hasOne(Country::class, ['code' => 'country_code']);
    }

    /**
     * @return CountryMedia
     */
    public function getMediaManager()
    {
        return new CountryMedia($this->country);
    }

    /**
     * Импортировать файл
     *
     * @param int $countryFileId
     * @param bool $old
     * @param bool $new
     * @return string Лог
     * @throws \app\exceptions\ModelValidationException
     * @throws \yii\db\Exception
     */
    public static function importById($countryFileId, $old = true, $new = true)
    {
        $countryFile = CountryFile::findOne(['id' => $countryFileId]);
        if (!$countryFile) {
            throw new InvalidParamException('Неправильный файл');
        }

        $country = $countryFile->country;
        $mediaManager = $country->getMediaManager();

        // import v1
        $logOld = '';
        $doneOld = true;
        if ($old) {
            $recordOld = ImportHistory::startFile($countryFile);
            $importOld = new ImportServiceUploaded([
                'countryCode' => $country->code,
                'url' => $mediaManager->getUnzippedFilePath($countryFile),
                'delimiter' => ';',
            ]);
            $doneOld = $importOld->run($recordOld);
            $recordOld->finish($doneOld);
            $logOld = $importOld->getLogAsString();
        }
        //

        // import v2
        $logNew = '';
        $doneNew = true;
        if ($new) {
            if ($logOld) {
                $logOld .= PHP_EOL . PHP_EOL . '******************************************' . PHP_EOL;
            }

            $recordNew = ImportHistory::startFile($countryFile, 2);
            $importNew = new ImportServiceUploadedNew([
                'countryCode' => $country->code,
                'url' => $mediaManager->getUnzippedFilePath($countryFile),
                'delimiter' => ';',
            ]);
            $doneNew = $importNew->run($recordNew);
            $recordNew->finish($doneNew);
            $logNew = $importNew->getLogAsString();
        }
        //

        $logFull = $logOld . $logNew;
        if (!$doneOld || !$doneNew) {
            throw new \RuntimeException($logFull);
        }

        return $logFull;
    }
}
