<?php

namespace app\models;

use app\classes\model\ActiveRecord;
use app\dao\user\UserRightDao;

/**
 * @property string $resource
 * @property string $comment
 * @property string $values
 * @property string $values_desc
 * @property int $order
 */
class UserRight extends ActiveRecord
{

    public static function tableName()
    {
        return 'user_rights';
    }

    public static function dao()
    {
        return UserRightDao::me();
    }

}