<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

class Language extends ActiveRecord
{

    const LANGUAGE_RUSSIAN = 'ru-RU';
    const LANGUAGE_ENGLISH = 'en-EN';
    const LANGUAGE_MAGYAR = 'hu-HU';
    const LANGUAGE_GERMAN = 'de-DE';

    const LANGUAGE_DEFAULT = self::LANGUAGE_RUSSIAN;

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'language';
    }

    /**
     * @return array
     */
    public static function getList()
    {
        return ArrayHelper::map(self::find()->orderBy(['code' => SORT_DESC])->all(), 'code', 'name');
    }
}
