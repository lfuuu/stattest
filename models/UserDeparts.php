<?php
namespace app\models;

use yii\db\ActiveRecord;

class UserDeparts extends ActiveRecord
{
    // Определяет getList (список для selectbox) и __toString
    use \app\classes\traits\GetListTrait;

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'user_departs';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::className(), ['depart_id' => 'id']);
    }

    /**
     * @return int
     */
    public function getUsersCount()
    {
        return count($this->users);
    }

}