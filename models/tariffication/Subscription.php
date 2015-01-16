<?php
namespace app\models\tariffication;

use yii\db\ActiveRecord;

/**
 * @property int    $id
 * @property string $name
 * @property int    $feature_id
 * @property
 */
class Subscription extends ActiveRecord
{
    public static function tableName()
    {
        return 'tariffication_subscription';
    }
}