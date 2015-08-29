<?php

namespace app\dao\user;

use app\classes\Singleton;
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

}