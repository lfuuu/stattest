<?php

namespace app\models\rewards;
use app\modules\uu\models\ResourceModel;

use app\classes\model\ActiveRecord;

class RewardsServiceTypeResource extends ActiveRecord
{
    public function rules()
    {
        [['is_active'], 'boolean'];
    }
    public static function tableName()
    {
        return 'rewards_service_type_resource';
    }

    public function getResourceName()
    {
        return (ResourceModel::findOne(['id' => $this->resource_id]))->name;
    }

}