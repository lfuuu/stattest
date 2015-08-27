<?php
namespace app\models;

use yii\db\ActiveRecord;
use app\dao\user\UserRightDao;

/**
 * @property string $resource
 * @property string $comment
 * @property string $values
 * @property string $values_desc
 * @property int    $order
 * @property
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