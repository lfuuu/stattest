<?php
namespace app\models;

use yii\db\ActiveRecord;
use app\dao\user\UserGroupsDao;

class UserGroups extends ActiveRecord
{

    const ADMIN = 'admin';

    public static function tableName()
    {
        return 'user_groups';
    }

    public static function dao()
    {
        return UserGroupsDao::me();
    }

    public function getRights()
    {
        return $this->hasMany(UserGrantGroups::className(), ['name' => 'usergroup']);
    }

    public function getUsersCount()
    {
        return User::find()->where(['usergroup' => $this->usergroup])->count();
    }

    public function getUsers()
    {
        return $this->hasMany(User::className(),['usergroup' => 'usergroup']);
    }

}