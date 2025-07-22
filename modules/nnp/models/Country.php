<?php

namespace app\modules\nnp\models;

use app\classes\Html;
use app\classes\model\ActiveRecord;
use app\modules\nnp\media\CountryMedia;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ArrayExpression;
use yii\helpers\Url;

/**
 * @property int $code
 * @property string $name
 * @property string $name_rus
 * @property string $name_eng
 * @property string $alpha_3
 * @property string $mcc
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

    // custom flags
    public static $flagsMap = [
        //self::SLOVAKIA => 'si',
    ];

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
            'prefix' => 'Префикс',
            'prefixes' => 'Префиксы',
            'alpha_2' => '2х-буквенный код',
            'alpha_3' => '3х-буквенный код',
            'is_open_numbering_plan' => 'Открытый номерной план?',
            'use_weak_matching' => 'Использовать слабое соответствие?',
            'default_operator' => 'Оператор по-умолчания',
            'default_type_ndc' => 'NDC тип по-умолчанию',
            'mcc' => 'MCC (Mobile Country Code)'
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
     * @return array
     */
    public function rules()
    {
        return [
            [['is_open_numbering_plan', 'use_weak_matching'], 'boolean'],
            [['default_operator', 'default_type_ndc', 'code', 'prefix', 'mcc'], 'integer'],
            [['name', 'name_rus', 'name_eng'], 'string'],
            [['is_open_numbering_plan', 'use_weak_matching', 'alpha_2', 'alpha_3', 'name', 'name_eng', 'name_rus', 'prefix', 'prefixes'], 'required'],
            ['alpha_2', 'string', 'min' => 2, 'max' => 2],
            ['alpha_3', 'string', 'min' => 3, 'max' => 3],
            ['prefixes', 'each', 'rule' => ['integer']]
        ];
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
     * Вернуть html: имя + ссылка
     *
     * @return string
     */
    public function getLink()
    {
        return Html::a(Html::encode($this->name), $this->getUrl());
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

        if ($prefixes instanceof ArrayExpression) {
            return $this->_prefixes = $prefixes->getValue();
        }

        $prefixes = str_replace(['{', '}'], '', $prefixes);
        return $this->_prefixes = explode(',', $prefixes);
    }

    /**
     * @return ActiveQuery
     */
    public function getOperator()
    {
        return $this->hasOne(Operator::class, ['id' => 'default_operator']);
    }

    /**
     * Вернуть код флага
     *
     * @return string
     */
    public function getFlagCode()
    {
        return !empty(self::$flagsMap[$this->code]) ?
            self::$flagsMap[$this->code] :
            strtolower(substr($this->alpha_2, 0, 2));
    }
}
