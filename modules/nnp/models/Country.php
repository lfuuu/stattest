<?php

namespace app\modules\nnp\models;

use app\classes\model\ActiveRecord;
use app\modules\nnp\media\CountryMedia;
use Yii;
use yii\helpers\Url;

/**
 * @property int $code
 * @property string $name
 * @property string $name_rus
 * @property string $name_eng
 * @property string $alpha_3
 * @property string $prefixes integer[]
 *
 * @link https://ru.wikipedia.org/wiki/%D0%9E%D0%B1%D1%89%D0%B5%D1%80%D0%BE%D1%81%D1%81%D0%B8%D0%B9%D1%81%D0%BA%D0%B8%D0%B9_%D0%BA%D0%BB%D0%B0%D1%81%D1%81%D0%B8%D1%84%D0%B8%D0%BA%D0%B0%D1%82%D0%BE%D1%80_%D1%81%D1%82%D1%80%D0%B0%D0%BD_%D0%BC%D0%B8%D1%80%D0%B0
 */
class Country extends ActiveRecord
{
    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait {
        getList as getListTrait;
    }

    const RUSSIA = 643;
    const HUNGARY = 348;
    const GERMANY = 276;
    const SLOVAKIA = 703;
    const AUSTRIA = 40;
    const CZECH = 203;

    const RUSSIA_PREFIX = 7;
    const HUNGARY_PREFIX = 36;

    public static $primaryField = 'code';

    private $_prefixes = null;

    /**
     * Имена полей
     *
     * @return array [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'code' => 'Код',
            'name' => 'Эндоним',
            'name_rus' => 'Русское название',
            'name_eng' => 'Английское название',
            'prefixes' => 'Префиксы',
            'alpha_3' => '3х-буквенный код',
        ];
    }

    /**
     * Имя таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'nnp.country';
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
     * Вернуть список всех доступных значений
     *
     * @param bool|string $isWithEmpty false - без пустого, true - с '----', string - с этим значением
     * @param bool $isWithNullAndNotNull
     * @param string $indexBy
     * @return string[]
     */
    public static function getList(
        $isWithEmpty = false,
        $isWithNullAndNotNull = false,
        $indexBy = 'code'
    ) {
        return self::getListTrait(
            $isWithEmpty,
            $isWithNullAndNotNull,
            $indexBy,
            $select = 'name_rus',
            $orderBy = ['name_rus' => SORT_ASC],
            $where = []
        );
    }


    /**
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function getUrl()
    {
        return self::getUrlById($this->code);
    }

    /**
     * @param int $code
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public static function getUrlById($code)
    {
        return Url::to(['/nnp/country/', 'CountryFilter[code]' => $code]);
    }

    /**
     * @return CountryMedia
     */
    public function getMediaManager()
    {
        return new CountryMedia($this);
    }

    /**
     * Преобразовать postgres-ный массив в php-шный
     *
     * @return int[]
     */
    public function getPrefixes()
    {
        if ($this->_prefixes !== null) {
            return $this->_prefixes;
        }

        $prefixes = $this->prefixes;
        $prefixes = str_replace(['{', '}'], '', $prefixes);
        return $this->_prefixes = explode(',', $prefixes);
    }
}
