<?php

namespace app\modules\nnp\models;

use app\classes\model\ActiveRecord;
use Yii;
use yii\helpers\Url;

/**
 * @property int $id
 * @property string $name
 * @property string $name_translit
 * @property int $country_code
 * @property int $cnt
 *
 * @property-read Country $country
 */
class Operator extends ActiveRecord
{
    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait {
        getList as getListTrait;
    }

    const MIN_CNT = 1000;

    /**
     * Имена полей
     *
     * @return array [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название',
            'name_translit' => 'Название транслитом',
            'country_code' => 'Страна',
            'cnt' => 'Кол-во номеров',
        ];
    }

    /**
     * Имя таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'nnp.operator';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name', 'name_translit'], 'string'],
            [['country_code'], 'integer'],
            [['name', 'country_code'], 'required'],
        ];
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
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return self::getUrlById($this->id);
    }

    /**
     * @param int $id
     * @return string
     */
    public static function getUrlById($id)
    {
        return Url::to(['/nnp/operator/edit', 'id' => $id]);
    }

    /**
     * Вернуть список всех доступных значений
     *
     * @param bool|string $isWithEmpty false - без пустого, true - с '----', string - с этим значением
     * @param bool $isWithNullAndNotNull
     * @param int|int[] $countryCode
     * @param int $minCnt
     * @return \string[]
     */
    public static function getList(
        $isWithEmpty = false,
        $isWithNullAndNotNull = false,
        $countryCode = null,
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
                $countryCode ? ['country_code' => $countryCode] : [],
                $minCnt ? ['>=', 'cnt', $minCnt] : []
            ]
        );
    }
}