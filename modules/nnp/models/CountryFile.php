<?php

namespace app\modules\nnp\models;

use app\modules\nnp\media\CountryMedia;
use app\modules\nnp\media\ImportServiceUploaded;
use Yii;
use yii\base\InvalidParamException;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $country_code
 * @property string $name
 * @property string $comment
 * @property int $user_id
 * @property string $ts
 *
 * @property Country $country
 * @property CountryMedia $mediaManager
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
        return $this->hasOne(Country::className(), ['code' => 'country_code']);
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
     * @return string Лог
     * @throws \yii\base\InvalidParamException
     * @throws \LogicException
     * @throws \RuntimeException
     * @throws \yii\db\Exception
     */
    public static function importById($countryFileId)
    {
        $countryFile = CountryFile::findOne(['id' => $countryFileId]);
        if (!$countryFile) {
            throw new InvalidParamException('Неправильный файл');
        }

        $country = $countryFile->country;
        $mediaManager = $country->getMediaManager();
        $importServiceUploaded = new ImportServiceUploaded([
            'countryCode' => $country->code,
            'url' => $mediaManager->getUnzippedFilePath($countryFile),
            'delimiter' => ';',
        ]);
        $isOk = $importServiceUploaded->run();
        $log = $importServiceUploaded->getLogAsString();
        if (!$isOk) {
            throw new \RuntimeException($log);
        }

        return $log;
    }
}
