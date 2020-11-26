<?php

namespace app\modules\nnp\models;

use app\classes\Html;
use app\classes\model\ActiveRecord;
use app\classes\validators\FormFieldValidator;
use Yii;
use yii\helpers\Url;

/**
 * @property int $id
 * @property int $parent_id
 * @property string $name
 * @property string $name_translit
 * @property int $country_code
 * @property string $iso
 * @property int $cnt
 *
 * @property-read Country $country
 * @property-read Region $parent
 */
class Region extends ActiveRecord
{
    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait {
        getList as getListTrait;
    }

    const nnpRegionToStatCity = [
        54 => \app\models\City::MOSCOW,
        5 => \app\models\City::MOSCOW,
        11 => \app\models\City::MOSCOW,
        89 => 7812, // СПб
        57 => 7812,
        58 => 7861,
        3 => 7342, // Пермь
        47 => 101027, // Калуга
        43 => 7473, // Свердловская обл.
        26594 => 7343,
        27 => 7343,
        2 => 7343,
        46 => 74832, // Брянск

        4 => 7351,
        6 => 7345,
        12 => 100354,
        13 => 7346,
        15 => 100426,
        17 => 100359,
        19 => 7383,
        20 => 100458,
        21 => 115676,
        25 => 7482,
        26 => 100538,
        29 => 101079,
        35 => 116050,
        42 => 100901,
        49 => 7487,
        51 => 101208,
        52 => 117992,
        56 => 115121,
        61 => 102252,
        63 => 102095,
        66 => 102150,
        69 => 102173,
        72 => 116550,
        77 => 116849,
        78 => 78442,
        7595 => 7843,
    ];

    const MIN_CNT = 1000;

    /**
     * @return array
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                \app\classes\behaviors\HistoryChanges::class,
            ]
        );
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
            'parent_id' => 'Регион-родитель',
            'name' => 'Название',
            'name_translit' => 'Название транслитом',
            'country_code' => 'Страна',
            'cnt' => 'Кол-во номеров',
            'iso' => 'ISO',
        ];
    }

    /**
     * Имя таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'nnp.region';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name', 'name_translit'], 'string'],
            [['name', 'name_translit', 'iso'], FormFieldValidator::class],
            ['iso', 'string', 'max' => 3],
            [['country_code', 'parent_id'], 'integer'],
            [['name', 'country_code'], 'required'],
        ];
    }

    /**
     * Подготовка полей для исторических данных
     *
     * @param string $field
     * @param string $value
     * @return string
     */
    public static function prepareHistoryValue($field, $value)
    {
        switch ($field) {

            case 'id':
                return Html::a($value, self::getUrlById($value));

            case 'country_code':
                if ($country = Country::findOne(['code' => $value])) {
                    return $country->getLink();
                }
                break;
        }

        return $value;
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

    public function beforeSave($isInsert)
    {
        if ($this->iso) {
            $this->iso = strtoupper($this->iso);
        }

        return parent::beforeSave($isInsert);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCountry()
    {
        return $this->hasOne(Country::class, ['code' => 'country_code']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(self::class, ['id' => 'parent_id']);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function getUrl()
    {
        return self::getUrlById($this->id);
    }

    /**
     * @param int $id
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public static function getUrlById($id)
    {
        return Url::to(['/nnp/region/edit', 'id' => $id]);
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
     * Вернуть список всех доступных значений
     *
     * @param bool|string $isWithEmpty false - без пустого, true - с '----', string - с этим значением
     * @param bool $isWithNullAndNotNull
     * @param int|int[] $countryCodes
     * @param int $minCnt
     * @return string[]
     */
    public static function getList(
        $isWithEmpty = false,
        $isWithNullAndNotNull = false,
        $countryCodes = null,
        $minCnt = self::MIN_CNT
    ) {
        return self::getListTrait(
            $isWithEmpty,
            $isWithNullAndNotNull,
            $indexBy = 'id',
            $select = 'name',
            $orderBy = ['name' => SORT_ASC],
            $where = [
                'AND',
                $countryCodes ? ['country_code' => $countryCodes] : [],
                $minCnt ? ['>=', 'cnt', $minCnt] : []
            ]
        );
    }
}
