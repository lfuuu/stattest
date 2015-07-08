<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $name
 * @property string $type
 */
class Contract extends ActiveRecord
{
    const TYPE_OPERATOR = 3;

    public static function tableName()
    {
        return 'contract';
    }
}
