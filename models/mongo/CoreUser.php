<?php
namespace app\models\mongo;
use yii\mongodb\ActiveRecord;

/**
 * @property int $id
 * @property
 */
class CoreUser extends ActiveRecord
{
    public static function collectionName()
    {
        return 'user';
    }

    public function attributes()
    {
        return ['_id', 'email', 'first_name', 'last_name', 'middle_name'];
    }
}
