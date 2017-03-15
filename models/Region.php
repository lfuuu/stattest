<?php
namespace app\models;

use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * @property int $id
 * @property string $name
 * @property string $short_name
 * @property int $code
 * @property string $timezone_name
 * @property int $country_id
 * @property int $is_active
 *
 * @property Datacenter $datacenter
 * @property Country $country
 */
class Region extends ActiveRecord
{
    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait {
        getList as getListTrait;
    }

    const MOSCOW = 99;
    const HUNGARY = 81;
    const TIMEZONE_MOSCOW = 'Europe/Moscow';

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'regions';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['id', 'country_id', 'code', 'country_id', 'is_active'], 'integer'],
            [['name', 'short_name', 'timezone_name'], 'string'],
        ];
    }

    /**
     * Вернуть имена полей
     *
     * @return array [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название',
            'short_name' => 'Короткое название',
            'code' => 'Код',
            'timezone_name' => 'Часовой пояс',
            'country_id' => 'Страна',
            'is_active' => 'Вкл/выкл',
        ];
    }

    /**
     * @return array
     */
    public static function getTimezoneList()
    {
        return self::getListTrait(
            $isWithEmpty = false,
            $isWithNullAndNotNull = false,
            $indexBy = 'timezone_name',
            $select = new \yii\db\Expression('DISTINCT timezone_name'),
            $orderBy = [],
            $where = []
        );
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDatacenter()
    {
        return $this->hasOne(Datacenter::className(), ['region' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['code' => 'country_id']);
    }

    /**
     * Вернуть список всех доступных значений
     *
     * @param bool|string $isWithEmpty false - без пустого, true - с '----', string - с этим значением
     * @param int $countryId
     * @return string[]
     */
    public static function getList(
        $isWithEmpty = false,
        $countryId = null
    ) {
        return self::getListTrait(
            $isWithEmpty,
            $isWithNullAndNotNull = false,
            $indexBy = 'id',
            $select = 'name',
            $orderBy = ['name' => SORT_ASC],
            $where = [
                'AND',
                ['is_active' => 1],
                $countryId ? ['country_id' => $countryId] : []
            ]
        );
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
        return Url::to(['/dictionary/region/edit', 'id' => $id]);
    }
}