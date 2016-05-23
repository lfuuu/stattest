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
    const LANGUAGE_GERMANY = 'de-DE';

    const LANGUAGE_DEFAULT = self::LANGUAGE_RUSSIAN;

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'language';
    }

    /**
     * @param bool|false $withEmpty
     * @return array
     */
    public static function getList($withEmpty = false)
    {
        $list = self::find()->orderBy(['code' => SORT_DESC])->indexBy('code')->all();

        if ($withEmpty) {
            $list = ['' => '----'] + $list;
        }

        return $list;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->name;
    }

}
