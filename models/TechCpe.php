<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property
 */
class TechCpe extends ActiveRecord
{
    public static function tableName()
    {
        return 'tech_cpe';
    }
}