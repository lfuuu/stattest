<?php
namespace app\models;

use app\helpers\DateTimeZoneHelper;
use yii\db\ActiveRecord;
use yii\helpers\Url;

/**
 * @property int $id
 * @property string $name
 * @property string $short_name
 * @property int $code
 * @property string $timezone_name
 * @property int $country_id
 * @property int $type_id
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

    const IZHEVSK = 74;
    const ASTRAKHAN = 75;
    const RYAZAN = 77;
    const VOLGOGRAD = 91;

    const TYPE_HUB = 0;
    const TYPE_NODE = 1;
    const TYPE_POINT = 2;

    public static $typeNames = [
        self::TYPE_HUB => 'Хаб',
        self::TYPE_NODE => 'Узел',
        self::TYPE_POINT => 'Точка',
    ];

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
            [['id', 'country_id', 'code', 'country_id', 'type_id', 'is_active'], 'integer'],
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
            'type_id' => 'Тип',
            'is_active' => 'Активен',
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
                ['type_id' => [self::TYPE_NODE, self::TYPE_POINT]],
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

    /**
     * @return string
     */
    public function getTypeName()
    {
        return isset(self::$typeNames[$this->type_id]) ?
            self::$typeNames[$this->type_id] :
            '';
    }

    /**
     * @param int $regionId
     * @return string
     */
    public static function getTimezoneByRegionId($regionId)
    {
        $region = self::findOne($regionId);
        if (!$region) {
            return DateTimeZoneHelper::TIMEZONE_MOSCOW;
        }

        return $region->timezone_name;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }
}