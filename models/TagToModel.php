<?php
namespace app\models;

use yii\db\ActiveRecord;

class TagToModel extends ActiveRecord
{
    public static function tableName()
    {
        return 'tag_to_model';
    }

    public function getTag()
    {
        return $this->hasOne(Tag::className(), ['id' => 'tag_id']);
    }

    public static function getFormattedClassName($className)
    {
        if ($pos = strrpos($className, '\\'))
            return substr($className, $pos + 1);
        return $pos;
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}