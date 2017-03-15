<?php
namespace app\models;

use yii\db\ActiveRecord;

class Language extends ActiveRecord
{
    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait {
        getList as getListTrait;
    }

    const LANGUAGE_RUSSIAN = 'ru-RU';
    const LANGUAGE_ENGLISH = 'en-EN';
    const LANGUAGE_MAGYAR = 'hu-HU';
    const LANGUAGE_GERMANY = 'de-DE';
    const LANGUAGE_SLOVAK = 'sk-SK';

    const LANGUAGE_DEFAULT = self::LANGUAGE_RUSSIAN;

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'language';
    }

    /**
     * Вернуть список всех доступных значений
     *
     * @param bool|string $isWithEmpty false - без пустого, true - с '----', string - с этим значением
     * @param bool $isWithNullAndNotNull
     * @return string[]
     */
    public static function getList(
        $isWithEmpty = false,
        $isWithNullAndNotNull = false
    ) {
        return self::getListTrait(
            $isWithEmpty,
            $isWithNullAndNotNull,
            $indexBy = 'code',
            $select = 'name',
            $orderBy = ['order' => SORT_ASC],
            $where = []
        );
    }
}
