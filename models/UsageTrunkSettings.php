<?php
namespace app\models;

use app\classes\uu\model\Tariff;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $usage_id
 * @property int $type
 * @property int $order
 * @property int $src_number_id
 * @property int $dst_number_id
 * @property int $pricelist_id
 * @property int $package_id
 * @property int $minimum_minutes
 * @property int $minimum_cost
 * @property float $minimum_margin
 * @property int $minimum_margin_type
 *
 * @property UsageTrunk $usage
 * @property Tariff $package
 */
class UsageTrunkSettings extends ActiveRecord
{
    const TYPE_ORIGINATION = 1;
    const TYPE_TERMINATION = 2;
    const TYPE_DESTINATION = 3;

    const MIN_MARGIN_ABSENT = 0;
    const MIN_MARGIN_PERCENT = 1;
    const MIN_MARGIN_VALUE = 2;

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'usage_trunk_settings';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsage()
    {
        return $this->hasOne(UsageTrunk::className(), ['id' => 'usage_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPackage()
    {
        return $this->hasOne(Tariff::className(), ['id' => 'package_id']);
    }
}

