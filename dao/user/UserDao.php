<?php

namespace app\dao\user;

use app\classes\Singleton;
use app\models\User;

/**
 * @method static UserDao me($args = null)
 */
class UserDao extends Singleton
{
    /**
     * @param array $departments
     * @param bool $asArray
     * @return mixed
     */
    public function getListByDepartments($departments, $asArray = true)
    {
        if (!is_array($departments)) {
            $departments = (array)$departments;
        }

        if (in_array('manager', $departments)) {
            $departments[] = 'account_managers';
        }

        $query = User::find()
            ->select('`user_users`.*, ud.`name` as depart_name')
            ->leftJoin('`user_departs` ud', 'ud.`id` = user_users.`depart_id`')
            ->where(['enabled' => 'yes'])
            ->orderBy('`user_users`.`name` ASC');

        if (count($departments)) {
            $query->andWhere(['`user_users`.`usergroup`' => $departments]);
        }

        if ($asArray !== false) {
            $query->asArray();
        }

        return $query->all();
    }

}