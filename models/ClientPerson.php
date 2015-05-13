<?php
namespace app\models;

use yii\db\ActiveRecord;
use yii\db\Query;

/**
 * @property int $id
 * @property
 */
class ClientBP extends ActiveRecord
{
    public static function tableName()
    {
        return 'grid_business_process';
    }
}
