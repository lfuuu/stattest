<?php

namespace app\models\important_events;

use yii\db\ActiveRecord;

class ImportantEventsNames extends ActiveRecord
{

    public static function tableName()
    {
        return 'important_events_names';
    }

    public function rules()
    {
        return [
            [['code', 'value', 'group_id'], 'required'],
            [['code', 'value',], 'trim'],
            ['group_id', 'integer']
        ];
    }

    public function attributeLabels()
    {
        return [
            'code' => 'Код',
            'value' => 'Название',
            'group_id' => 'Группа',
        ];
    }

    public function getGroup()
    {
        return $this->hasOne(ImportantEventsGroups::className(), ['id' => 'group_id']);
    }

}