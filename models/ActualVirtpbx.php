<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * Class ActualVirtpbx
 *
 * @property int $usage_id
 * @property int $client_id
 * @property int $tarif_id
 * @property int $region_id
 * @property int $biller_version
 *
 * @package app\models
 */
class ActualVirtpbx extends ActiveRecord
{
    public static function tableName()
    {
        return 'actual_virtpbx';
    }
}
