<?php

namespace app\models\rewards;

use app\classes\model\ActiveRecord;

class RewardsServiceTypeActive extends ActiveRecord
{
    const ACTIVE = 1;
    
    public function rules()
    {
        return [
            [['is_active'], 'boolean'],
            [['service_type_id'], 'integer']
        ];
    }
    
    public static function tableName()
    {
        return 'rewards_service_type_active';
    }
}