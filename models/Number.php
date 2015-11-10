<?php
namespace app\models;

use app\dao\NumberDao;
use yii\db\ActiveRecord;

/**
 * @property string $number
 * @property int $client_id
 * @property int $usage_id
 * @property int $city_id
 * @property int $did_group_id
 * @property string $status

 * @property City $city
 * @property DidGroup $didGroup
 * @property UsageVoip $usage

 * @method static Number findOne($condition)
 * @property
 */
class Number extends ActiveRecord
{

    const NUMBER_STATUS_INSTOCK  = 'instock';
    const NUMBER_STATUS_HOLD     = 'hold';
    const NUMBER_STATUS_ACTIVE   = 'active';
    const NUMBER_STATUS_RESERVED = 'reserved';
    const NUMBER_STATUS_NOTSELL  = 'notsell';

    public static $statusList = [
        self::NUMBER_STATUS_NOTSELL => 'Не продается',
        self::NUMBER_STATUS_INSTOCK  => 'Свободен',
        self::NUMBER_STATUS_RESERVED => 'Резерв',
        self::NUMBER_STATUS_ACTIVE   => 'Используется',
        self::NUMBER_STATUS_HOLD     => 'Отстойник',
    ];

    public static function tableName()
    {
        return 'voip_numbers';
    }

    public static function dao()
    {
        return NumberDao::me();
    }

    public function getCity()
    {
        return $this->hasOne(City::className(), ['id' => 'city_id']);
    }

    public function getDidGroup()
    {
        return $this->hasOne(DidGroup::className(), ['id' => 'did_group_id']);
    }

}