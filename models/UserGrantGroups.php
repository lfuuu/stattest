<?php
namespace app\models;

use yii\db\ActiveRecord;

class UserGrantGroups extends ActiveRecord
{
    public static function tableName()
    {
        return 'user_grant_groups';
    }
}
