<?php

namespace app\models;

use app\classes\model\ActiveRecord;
use app\modules\uu\models\Tariff;

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
 * @property-read UsageTrunk $usage
 * @property-read Tariff $package
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
     * @return array
     */
    public function behaviors()
    {
        return [
            'HistoryChanges' => \app\classes\behaviors\HistoryChanges::class,
        ];
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'usage_trunk_settings';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [[
                'usage_id', 'type', 'order', 'src_number_id', 'dst_number_id',
                'pricelist_id', 'tmp', 'package_id', 'minimum_margin_type', 'minimum_cost', 'minimum_minutes',
            ], 'integer'],
            [['minimum_margin',], 'double'],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsage()
    {
        return $this->hasOne(UsageTrunk::class, ['id' => 'usage_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPackage()
    {
        return $this->hasOne(Tariff::class, ['id' => 'package_id']);
    }
}
