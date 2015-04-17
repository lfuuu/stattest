<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $usage_id
 * @property int $type
 * @property int $order
 * @property int $src_number_id
 * @property int $dst_number_id
 * @property int $pricelist_id
 *
 * @property UsageTrunk $usage
 * @property
 */
class UsageTrunkSettings extends ActiveRecord
{
    const TYPE_ORIGINATION = 1;
    const TYPE_TERMINATION = 2;
    const TYPE_DESTINATION = 3;

    public static function tableName()
    {
        return 'usage_trunk_settings';
    }

    public function getUsage()
    {
        return $this->hasOne(UsageTrunk::className(), ['id' => 'usage_id']);
    }
}

