<?php

namespace app\dao\user;

use app\classes\Singleton;
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
        $res = [];
        $groups = UserGroups::find()->innerJoinWith('users')->asArray()->all();
        foreach ($groups as $group) {
            foreach ($group['users'] as $user) {
                $res[$group['comment']][$user['id']] = $user['name'];
            }
        }

        return $res;
    }
}