<?php

namespace app\dao\user;

use app\classes\Singleton;
use yii\helpers\ArrayHelper;
use app\models\UserDeparts;

/**
 * @method static UserDepartsDao me($args = null)
 * @property
 */
class UserDepartsDao extends Singleton
{

    public function getList($withEmpty = false)
    {
        $query = UserDeparts::find();

        $list =
            ArrayHelper::map(
                $query
                    ->orderBy('name')
                    ->asArray()
                    ->all(),
                'id',
                'name'
            );
        if ($withEmpty) {
            $list = ['' => '-- Отдел --'] + $list;
        }

        return $list;
    }

}