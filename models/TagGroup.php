<?php
namespace app\models;

use yii\db\ActiveRecord;

class TagGroup extends ActiveRecord
{
    public static function tableName()
    {
        return 'tag_group';
    }
}