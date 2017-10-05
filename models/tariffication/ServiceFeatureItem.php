<?php

namespace app\models\tariffication;

use app\classes\model\ActiveRecord;

/**
 * @property int $id
 * @property int $service_id
 * @property int $feature_id
 */
class ServiceFeatureItem extends ActiveRecord
{
    public static function tableName()
    {
        return 'tariffication_service_feature_item';
    }
}