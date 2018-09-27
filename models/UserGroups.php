<?php

namespace app\models;

use app\classes\model\ActiveRecord;
use app\dao\user\UserGroupsDao;

class UserGroups extends ActiveRecord
{
    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait {
        getList as getListTrait;
    }

    const ADMIN = 'admin';

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'user_groups';
    }

    /**
     * @return UserGroupsDao
     */
    public static function dao()
    {
        return UserGroupsDao::me();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRights()
    {
        return $this->hasMany(UserGrantGroups::class, ['name' => 'usergroup']);
    }

    /**
     * @return int|string
     */
    public function getUsersCount()
    {
        return User::find()->where(['usergroup' => $this->usergroup])->count();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::class, ['usergroup' => 'usergroup']);
    }

    /**
     * Вернуть список всех доступных значений
     *
     * @param bool|string $isWithEmpty false - без пустого, true - с '----', string - с этим значением
     * @param bool $isWithNullAndNotNull
     * @return string[]
     */
    public static function getList(
        $isWithEmpty = false,
        $isWithNullAndNotNull = false
    ) {
        return self::getListTrait(
            $isWithEmpty,
            $isWithNullAndNotNull,
            $indexBy = 'usergroup',
            $select = 'comment',
            $orderBy = ['usergroup' => SORT_ASC],
            $where = []
        );
    }
}