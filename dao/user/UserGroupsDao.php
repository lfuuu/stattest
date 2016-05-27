<?php

namespace app\dao\user;

use app\classes\Singleton;
use yii\helpers\ArrayHelper;
use app\models\UserGroups;

/**
 * @method static UserGroupsDao me($args = null)
 * @property
 */
class UserGroupsDao extends Singleton
{

    public function getList($isWithEmpty = false)
    {
        $query = UserGroups::find();

        $list =
            ArrayHelper::map(
                $query
                    ->orderBy('usergroup')
                    ->asArray()
                    ->all(),
                'usergroup',
                'comment'
            );
        if ($isWithEmpty) {
            $list = ['' => '----'] + $list;
        }

        return $list;
    }

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