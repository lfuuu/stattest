<?php
namespace app\models;

use yii\db\ActiveRecord;
use app\dao\user\UserDepartsDao;

class UserDeparts extends ActiveRecord
{

    public static function tableName()
    {
        return 'user_departs';
    }

    public static function dao()
    {
        return UserDepartsDao::me();
    }

    public function getUsers()
    {
        return $this->hasMany(User::className(), ['depart_id' => 'id']);
    }

    public function getUsersCount()
    {
        return count($this->users);
    }

}