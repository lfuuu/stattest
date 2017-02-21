<?php
namespace app\models;

use app\dao\RegionDao;
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
    // Определяет getList (список для selectbox) и __toString
    use \app\classes\traits\GetListTrait;

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
     * @return RegionDao
     */
    public static function dao()
    {
        return RegionDao::me();
    }

    /**
     * @return array
     */
    public static function getTimezoneList()
    {
        $arr = self::find()->select(['timezone_name'])->groupBy(['timezone_name'])->all();
        return ArrayHelper::map($arr, 'timezone_name', 'timezone_name');
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
     * Вернуть список всех доступных моделей
     *
     * @param bool $isWithEmpty
     * @param bool $isWithNullAndNotNull
     * @param string $indexBy
     * @return array
     */
    public static function getList($isWithEmpty = false, $isWithNullAndNotNull = false, $indexBy = 'id')
    {
        $list = self::find()
            ->where(['is_active' => 1])
            ->orderBy(static::getListOrderBy())
            ->indexBy($indexBy)
            ->all();

        return self::getEmptyList($isWithEmpty, $isWithNullAndNotNull) + $list;
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