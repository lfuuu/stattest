<?php

namespace app\dao\user;

use app\classes\Singleton;
use app\models\UserDeparts;
use yii\helpers\ArrayHelper;
use app\models\User;

/**
 * @method static UserDao me($args = null)
 * @property
 */
class UserDao extends Singleton
{

    public function getList($withEmpty = false)
    {
        $query = User::find()->where(['enabled' => 'yes']);
        $list = $query->orderBy('name')->all();

        $result = [];
        foreach ($list as $user) {
            $result[ $user->user ] = $user->name . ' (' . $user->user . ')';
        }

        if ($withEmpty) {
            $result = ['' => '-- Пользователь --'] + $result;
        }

        return $result;
    }

    public function getListByDepartments($departments, $asArray = true)
    {
        if (!is_array($departments))
            $departments = (array) $departments;

        if(in_array('manager', $departments))
            $departments[] = 'account_managers';

        $query =
            User::find()
                ->select('`user_users`.*, ud.`name` as depart_name')
                ->leftJoin('`user_departs` ud', 'ud.`id` = user_users.`depart_id`')
                ->where(['enabled' => 'yes'])
                ->orderBy('`user_users`.`name` ASC');

        if (sizeof($departments))
            $query->andWhere(['`user_users`.`usergroup`' => $departments]);

        if ($asArray !== false)
            $query->asArray();

        return $query->all();
    }

}