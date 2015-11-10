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

    const STATUS_INSTOCK  = 'instock';
    const STATUS_HOLD     = 'hold';
    const STATUS_ACTIVE   = 'active';
    const STATUS_RESERVED = 'reserved';
    const STATUS_NOTSELL  = 'notsell';

    public static $statusList = [
        self::STATUS_NOTSELL => 'Не продается',
        self::STATUS_INSTOCK  => 'Свободен',
        self::STATUS_RESERVED => 'Резерв',
        self::STATUS_ACTIVE   => 'Используется',
        self::STATUS_HOLD     => 'Отстойник',
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