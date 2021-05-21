<?php

namespace app\dao\user;

use app\classes\Singleton;
use app\models\User;
use app\models\UserGroups;

/**
 * @method static UserGroupsDao me($args = null)
 */
class UserGroupsDao extends Singleton
{
    /**
     * @return array
     */
    public function getListWithUsers()
    {
        static $res = [];

        if ($res) {
            return $res;
        }

        $users = User::find()
            ->alias('u')
            ->select(['u.id', 'u.user', 'u.name', 'g.comment', 'u.enabled'])
            ->innerJoinWith('group g')
            ->orderBy(['g.comment' => SORT_ASC, 'u.enabled' => SORT_ASC ,'u.name' => SORT_ASC])
            ->asArray()
            ->all();

        foreach ($users as $user) {
            $name = trim($user['name']);
            $isForwardS = false;
            if (substr($name, 0, 1) == '*') {
                $isForwardS = true;
            }
            $res[$user['comment']][$user['id']] = ($user['enabled'] != 'yes' ? '**' . ($isForwardS ? '' : "*") : '') . $user['name'] . ' (' . $user['user'] . ')';
        }

        return $res;
    }
}