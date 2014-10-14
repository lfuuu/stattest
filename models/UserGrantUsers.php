<?php
namespace app\models;

use yii\db\ActiveRecord;

class UserGrantUsers extends ActiveRecord
{
    public static function tableName()
    {
        return 'user_grant_users';
    }
}
