<?php
namespace app\models;

use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

class Tag extends ActiveRecord
{
    public static function tableName()
    {
        return 'tag';
    }

    public function getGroup()
    {
        return $this->hasOne(TagGroup::className(), 'id'. 'group_id');
    }

    public static function getListByGroupId($groupId)
    {
        $models = self::find()->andWhere(['group_id' => $groupId])->all();
        return ArrayHelper::map($models, 'id', 'name');
    }
}