<?php

namespace app\models;

use app\classes\Html;
use app\classes\model\ActiveRecord;
use app\helpers\DateTimeZoneHelper;
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
 * @property int $is_use_sip_trunk
 * @property int $is_use_vpbx
 *
 * @property-read Datacenter $datacenter
 * @property-read Country $country
 * @property-read City[] $cities
 */
class Region extends ActiveRecord
{
    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait {
        getList as getListTrait;
    }

    const ID_NON_RUSSIA = 82; // Если не Россия, то Европа (Франкфурт)

    const MOSCOW = 99;
    const HUNGARY = 81;

    const IZHEVSK = 74;
    const ASTRAKHAN = 75;
    const RYAZAN = 77;
    const VOLGOGRAD = 91;
    const NIZHNY_NOVGOROD = 88;
    const KRASNODAR = 97;
    const KRASNOIARSK = 55;
    const HABAROBSK = 83;
    const NNOVGOROD = 88;

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
            [['id', 'country_id', 'code', 'country_id', 'type_id', 'is_active', 'is_use_sip_trunk', 'is_use_vpbx'], 'integer'],
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
            'is_use_sip_trunk' => 'Используется в SIP-транках',
            'is_use_vpbx' => 'Наличие ВАТС',
        ];
    }

    /**
     * @return array
     */
    public static function getTimezoneList()
    {
        // select tz_name, TIMESTAMPDIFF(HOUR, convert_tz(UTC_TIMESTAMP(), tz_name, "UTC"), UTC_TIMESTAMP()) as tz_offset from (SELECT distinct timezone_name tz_name FROM `regions` union select 'UTC')a order by tz_offset
        return self::getListTrait(
            $isWithEmpty = false,
            $isWithNullAndNotNull = false,
            $indexBy = 'timezone_name',
            $select = new \yii\db\Expression('DISTINCT timezone_name'),
            $orderBy = ['timezone_name' => SORT_ASC],
            $where = []
        ) + [DateTimeZoneHelper::TIMEZONE_UTC => DateTimeZoneHelper::TIMEZONE_UTC];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDatacenter()
    {
        return $this->hasOne(Datacenter::class, ['region' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCountry()
    {
        return $this->hasOne(Country::class, ['code' => 'country_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCities()
    {
        return $this->hasMany(City::class, ['connection_point_id' => 'id'])
            ->andWhere(['in_use' => 1])
            ->orderBy(['order' => SORT_ASC]);
    }

    /**
     * Вернуть список всех доступных значений
     *
     * @param bool|string $isWithEmpty false - без пустого, true - с '----', string - с этим значением
     * @param int $countryId
     * @param int[] $typeId
     * @param int $isUseSipTrunk
     * @param int $isUseVpbx
     * @return string[]
     */
    public static function getList(
        $isWithEmpty = false,
        $countryId = null,
        $typeId = null,
        $isUseSipTrunk = null,
        $isUseVpbx = null
    ) {
        return self::getListTrait(
            $isWithEmpty,
            $isWithNullAndNotNull = false,
            $indexBy = 'id',
            $select = 'name',
            $orderBy = ['name' => SORT_ASC],
            $where = [
                'AND',
                [
                    'AND',
                    ['type_id' => isset($typeId) ? $typeId : array_keys(self::$typeNames)],
                    $countryId ? ['country_id' => $countryId] : []
                ],
                isset($isUseSipTrunk) ? ['OR', ['is_use_sip_trunk' => 1], ['id' => $isUseSipTrunk]] : [],
                isset($isUseVpbx) ? ['OR', ['is_use_vpbx' => 1], ['id' => $isUseVpbx]] : [],
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
     * Вернуть html: имя + ссылка
     *
     * @return string
     */
    public function getLink()
    {
        return Html::a(Html::encode($this->name), $this->getUrl());
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