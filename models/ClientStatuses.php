<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property
 */
class ClientStatuses extends ActiveRecord
{
    public static function tableName()
    {
        return 'client_statuses';
    }
}
