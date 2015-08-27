<?php
namespace app\models;

use yii\db\ActiveRecord;
use app\dao\user\UserGroupsDao;

class UserGroups extends ActiveRecord
{

    public static function tableName()
    {
        return 'user_groups';
    }

    public static function dao()
    {
        return UserGroupsDao::me();
    }

}