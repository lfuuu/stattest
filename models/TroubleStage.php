<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property
 */
class TroubleStage extends ActiveRecord
{
    public static function tableName()
    {
        return 'tt_stages';
    }
}