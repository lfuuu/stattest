<?php

namespace app\modules\nnp2\models;

use app\classes\Html;
use app\classes\model\ActiveRecord;
use app\classes\validators\FormFieldValidator;
use app\modules\nnp\models\Country;
use Yii;
use yii\helpers\Url;

/**
 * @property int $id
 * @property int $parent_id
 * @property string $name
 * @property string $name_translit
 * @property int $country_code
 * @property string $iso
 * @property int $cnt bigint
 *
 * @property-read Country $country
 * @property-read Region $parent
 * @property-read Region[] $childs
 */
class Region extends ActiveRecord
{
    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait {
        getList as getListTrait;
    }

    const MIN_CNT = 0;

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
            'is_valid' => 'Подтверждён',
        ];
    }

    /**
     * Имя таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'nnp2.region';
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
            [['is_valid'], 'boolean'],
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
        return Yii::$app->dbPgNnp2;
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
     * @return \yii\db\ActiveQuery
     */
    public function getChilds()
    {
        return $this->hasMany(self::class, ['parent_id' => 'id']);
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
        return Url::to(['/nnp2/region/edit', 'id' => $id]);
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
     * @param bool $isMainOnly
     * @param int $minCnt
     * @return string[]
     */
    public static function getList(
        $isWithEmpty = false,
        $isWithNullAndNotNull = false,
        $countryCodes = null,
        $isMainOnly = true,
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
                $isMainOnly ? ['is_valid' => 1] : [],
                $isMainOnly ? ['parent_id' => null] : [],
                $countryCodes ? ['country_code' => $countryCodes] : [],
                $minCnt ? ['>=', 'cnt', $minCnt] : []
            ]
        );
    }

    /**
     * @param null $attributeNames
     * @param bool $clearErrors
     * @return bool
     */
    public function validate($attributeNames = null, $clearErrors = true)
    {
        $validation = parent::validate($attributeNames, $clearErrors);

        if ($validation) {
            if ($this->parent_id) {
                if (!$this->parent->is_valid) {
                    $this->addError('parent_id', 'Родитель не подтверждён');

                    return false;
                }

                return true;
            }

            if (!$this->is_valid && $this->childs) {
                $this->addError('is_valid', 'Есть синонимы');

                return false;
            }

            return true;
        }

        return false;
    }
}
