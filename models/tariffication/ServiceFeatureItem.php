<?php
namespace app\models\tariffication;

use yii\db\ActiveRecord;

/**
 * @property int    $id
 * @property int    $service_id
 * @property int    $feature_id
 * @property
 */
class ServiceFeatureItem extends ActiveRecord
{
    public static function tableName()
    {
        return 'tariffication_service_feature_item';
    }
}