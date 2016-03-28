<?php
namespace app\models;

use app\dao\CountryDao;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * @property int $code
 * @property string $alpha_3
 * @property string $name
 * @property int $in_use
 * @property string $lang
 * @property string $currency_id
 */
class Country extends ActiveRecord
{
    const RUSSIA = 643;
    const HUNGARY = 348;
    const GERMANY = 276;

    /**
     * Вернуть имена полей
     * @return [] [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'code' => 'ID',
            'alpha_3' => 'Сокращение',
            'name' => 'Название',
            'in_use' => 'Включен',
            'lang' => 'Язык',
            'currency_id' => 'Валюта',
        ];
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'country';
    }

    /**
     * @return string[]
     */
    public static function primaryKey()
    {
        return ['code'];
    }

    /**
     * @return CountryDao
     */
    public static function dao()
    {
        return CountryDao::me();
    }

    /**
     * @param bool $isWithEmpty
     * @return self[]
     */
    public static function getList($isWithEmpty = false)
    {
        $list = self::find()
            ->where(['in_use' => 1])
            ->orderBy(['code' => SORT_DESC])
            ->indexBy('code')
            ->all();

        if ($isWithEmpty) {
            $list = ['' => ' ---- '] + $list;
        }

        return $list;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }
}